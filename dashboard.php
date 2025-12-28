<?php
require_once 'config.php';
requireLogin();
$currentUser = getCurrentUser();
$userId = getCurrentUserId();

$pdo = getDBConnection();

// Fetch Task Statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
        SUM(CASE WHEN status = 'progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'pending' AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_tasks
    FROM tasks 
    WHERE assigned_to = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

// Calculate percentage change (comparing this week to last week)
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as this_week,
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) 
                 AND DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as last_week
    FROM tasks 
    WHERE assigned_to = ?
");
$stmt->execute([$userId]);
$weekComparison = $stmt->fetch();
$totalChange = 0;
if ($weekComparison['last_week'] > 0) {
    $totalChange = round((($weekComparison['this_week'] - $weekComparison['last_week']) / $weekComparison['last_week']) * 100);
}

// Get tasks completed today
$stmt = $pdo->prepare("
    SELECT COUNT(*) as completed_today
    FROM tasks 
    WHERE assigned_to = ? AND status = 'done' AND DATE(completed_at) = CURDATE()
");
$stmt->execute([$userId]);
$completedToday = $stmt->fetch()['completed_today'];

// Fetch Recent Tasks (limit 10)
$stmt = $pdo->prepare("
    SELECT t.*, p.name as project_name, u.full_name as assigned_to_name
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.assigned_to = ?
    ORDER BY t.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$recentTasks = $stmt->fetchAll();

// Fetch Upcoming Deadlines (next 7 days)
$stmt = $pdo->prepare("
    SELECT t.*, p.name as project_name
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.assigned_to = ? 
    AND t.due_date IS NOT NULL 
    AND t.due_date >= CURDATE() 
    AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND t.status != 'done'
    ORDER BY t.due_date ASC, t.priority DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$upcomingDeadlines = $stmt->fetchAll();

// Fetch Chart Data - Tasks completed per day for last 7 days
$stmt = $pdo->prepare("
    SELECT 
        DATE(completed_at) as date,
        COUNT(*) as count
    FROM tasks 
    WHERE assigned_to = ? 
    AND status = 'done' 
    AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(completed_at)
    ORDER BY date ASC
");
$stmt->execute([$userId]);
$chartDataRaw = $stmt->fetchAll();

// Create chart data for last 7 days
$chartLabels = [];
$chartData = [];
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Get last 7 days
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = $days[date('w', strtotime($date))];
    $chartLabels[] = $dayName;
    
    // Find matching data
    $found = false;
    foreach ($chartDataRaw as $row) {
        if ($row['date'] == $date) {
            $chartData[] = (int)$row['count'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $chartData[] = 0;
    }
}

// Helper function to format date
function formatDate($date) {
    if (!$date) return 'No date';
    $timestamp = strtotime($date);
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');
    
    if ($timestamp < $today) {
        return date('M j', $timestamp);
    } elseif ($timestamp == $today) {
        return 'Today';
    } elseif ($timestamp == $tomorrow) {
        return 'Tomorrow';
    } else {
        return date('M j', $timestamp);
    }
}

// Helper function to get priority display
function getPriorityDisplay($priority) {
    switch ($priority) {
        case 'high':
            return ['class' => 'status-high', 'dot' => 'bg-red-400', 'text' => 'High'];
        case 'medium':
            return ['class' => 'status-med', 'dot' => 'bg-yellow-400', 'text' => 'Medium'];
        case 'low':
            return ['class' => 'status-low', 'dot' => 'bg-green-400', 'text' => 'Low'];
        default:
            return ['class' => 'status-med', 'dot' => 'bg-gray-400', 'text' => 'Medium'];
    }
}

// Get projects for task creation form
$projects = getAllProjects();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaskFlow</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #00f3ff;
            --secondary: #bd00ff;
            --bg-dark: #050510;
            --card-bg: rgba(255, 255, 255, 0.03);
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: var(--bg-dark);
            color: white;
        }

        /* Glassmorphism */
        .glass-panel {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a1a;
        }
        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* Sidebar Transition */
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-pending { background: rgba(253, 186, 116, 0.1); color: #fdba74; border: 1px solid rgba(253, 186, 116, 0.2); }
        .status-progress { background: rgba(0, 243, 255, 0.1); color: #00f3ff; border: 1px solid rgba(0, 243, 255, 0.2); }
        .status-done { background: rgba(134, 239, 172, 0.1); color: #86efac; border: 1px solid rgba(134, 239, 172, 0.2); }
        .status-high { color: #f87171; }
        .status-med { color: #fbbf24; }
        .status-low { color: #34d399; }

        /* Mobile Overlay */
        .mobile-overlay {
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        /* Modal Styles */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 10px rgba(0, 243, 255, 0.1);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php
    // Include dynamic sidebar
    $currentPage = 'dashboard';
    include 'sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#050510]">
        
        <!-- Header -->
        <header class="flex items-center justify-between h-20 px-6 border-b border-white/5 bg-[#050510]/50 backdrop-blur-md sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-white">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-xl font-bold hidden sm:block">Overview</h1>
            </div>

            <!-- Search & Actions -->
            <div class="flex items-center gap-4">
                <div class="relative hidden md:block">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" placeholder="Search tasks..." class="bg-white/5 border border-white/10 rounded-full pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-cyan-500 w-64 transition-all">
                </div>
                
                <button class="relative p-2 text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    <span class="absolute top-1 right-2 w-2 h-2 bg-red-500 rounded-full border border-[#050510]"></span>
                </button>
                
                <button onclick="openTaskModal()" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">New Task</span>
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="flex-1 overflow-y-auto p-6">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="layers" class="w-16 h-16 text-cyan-400"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm text-gray-400 font-medium mb-1">Total Tasks</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo $stats['total_tasks'] ?? 0; ?></h3>
                        <?php if ($totalChange != 0): ?>
                        <p class="text-xs <?php echo $totalChange > 0 ? 'text-green-400' : 'text-red-400'; ?> flex items-center gap-1 mt-2">
                            <i data-lucide="<?php echo $totalChange > 0 ? 'trending-up' : 'trending-down'; ?>" class="w-3 h-3"></i> 
                            <?php echo $totalChange > 0 ? '+' : ''; ?><?php echo $totalChange; ?>% from last week
                        </p>
                        <?php else: ?>
                        <p class="text-xs text-gray-400 mt-2">No change from last week</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="clock" class="w-16 h-16 text-yellow-400"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm text-gray-400 font-medium mb-1">Pending</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo $stats['pending_tasks'] ?? 0; ?></h3>
                        <?php if ($stats['overdue_tasks'] > 0): ?>
                        <p class="text-xs text-yellow-400 flex items-center gap-1 mt-2">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo $stats['overdue_tasks']; ?> overdue
                        </p>
                        <?php else: ?>
                        <p class="text-xs text-gray-400 mt-2">All on track</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="zap" class="w-16 h-16 text-purple-400"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm text-gray-400 font-medium mb-1">In Progress</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo $stats['in_progress_tasks'] ?? 0; ?></h3>
                        <p class="text-xs text-gray-400 mt-2">Active now</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="check-circle" class="w-16 h-16 text-green-400"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm text-gray-400 font-medium mb-1">Completed</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo $stats['completed_tasks'] ?? 0; ?></h3>
                        <?php if ($completedToday > 0): ?>
                        <p class="text-xs text-green-400 flex items-center gap-1 mt-2">
                            <i data-lucide="trending-up" class="w-3 h-3"></i> +<?php echo $completedToday; ?> today
                        </p>
                        <?php else: ?>
                        <p class="text-xs text-gray-400 mt-2">Keep going!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column (Task Table) -->
                <div class="lg:col-span-2 glass-panel rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold">Recent Tasks</h2>
                        <a href="tasks.php" class="text-sm text-cyan-400 hover:text-cyan-300">View All</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs text-gray-400 border-b border-white/10 uppercase tracking-wider">
                                    <th class="pb-3 pl-2">Task Name</th>
                                    <th class="pb-3">Priority</th>
                                    <th class="pb-3">Status</th>
                                    <th class="pb-3">Due Date</th>
                                    <th class="pb-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php if (empty($recentTasks)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">No tasks found. Create your first task!</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($recentTasks as $task): 
                                    $priority = getPriorityDisplay($task['priority']);
                                    $statusClass = 'status-' . $task['status'];
                                    $statusText = ucfirst(str_replace('_', ' ', $task['status']));
                                    if ($task['status'] == 'progress') {
                                        $statusText = 'In Progress';
                                    }
                                ?>
                                <tr class="group hover:bg-white/5 transition-colors border-b border-white/5">
                                    <td class="py-4 pl-2 font-medium"><?php echo htmlspecialchars($task['name']); ?></td>
                                    <td class="py-4">
                                        <span class="flex items-center gap-1 <?php echo $priority['class']; ?>">
                                            <div class="w-2 h-2 rounded-full <?php echo $priority['dot']; ?>"></div> 
                                            <?php echo $priority['text']; ?>
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td class="py-4 text-gray-400">
                                        <?php echo $task['due_date'] ? formatDate($task['due_date']) : 'No date'; ?>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button class="p-1 hover:text-cyan-400 text-gray-400"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column (Charts & widgets) -->
                <div class="space-y-6">
                    <!-- Chart Widget -->
                    <div class="glass-panel p-6 rounded-2xl">
                        <h2 class="text-lg font-bold mb-4">Productivity</h2>
                        <div class="chart-container">
                            <canvas id="taskChart"></canvas>
                        </div>
                    </div>

                    <!-- Upcoming Widget -->
                    <div class="glass-panel p-6 rounded-2xl">
                        <h2 class="text-lg font-bold mb-4">Upcoming Deadlines</h2>
                        <div class="space-y-4">
                            <?php if (empty($upcomingDeadlines)): ?>
                            <div class="text-center text-gray-500 text-sm py-4">No upcoming deadlines</div>
                            <?php else: ?>
                            <?php foreach ($upcomingDeadlines as $deadline): 
                                $dueDate = strtotime($deadline['due_date']);
                                $day = date('j', $dueDate);
                                $isToday = date('Y-m-d', $dueDate) == date('Y-m-d');
                                $isOverdue = $dueDate < strtotime('today');
                                $bgColor = $isOverdue ? 'bg-red-500/10 text-red-400' : ($isToday ? 'bg-yellow-500/10 text-yellow-400' : 'bg-cyan-500/10 text-cyan-400');
                            ?>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl <?php echo $bgColor; ?> flex items-center justify-center">
                                    <span class="font-bold text-sm"><?php echo $day; ?></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate"><?php echo htmlspecialchars($deadline['name']); ?></p>
                                    <p class="text-xs text-gray-400">
                                        <?php if ($deadline['project_name']): ?>
                                        <?php echo htmlspecialchars($deadline['project_name']); ?> • 
                                        <?php endif; ?>
                                        <?php echo formatDate($deadline['due_date']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- New Task Modal -->
    <div id="taskModal" class="fixed inset-0 z-50 modal-overlay hidden flex items-center justify-center p-4">
        <div class="modal-content glass-panel rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">Create New Task</h2>
                <button onclick="closeTaskModal()" class="text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="taskForm" onsubmit="handleTaskSubmit(event)" class="space-y-4">
                <!-- Task Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Task Name *</label>
                    <input type="text" name="taskName" required class="w-full px-4 py-3 rounded-xl glass-input text-white placeholder-gray-500" placeholder="Enter task name">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-3 rounded-xl glass-input text-white placeholder-gray-500 resize-none" placeholder="Add task description..."></textarea>
                </div>

                <!-- Priority and Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Priority *</label>
                        <select name="priority" required class="w-full px-4 py-3 rounded-xl glass-input text-white">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Status *</label>
                        <select name="status" required class="w-full px-4 py-3 rounded-xl glass-input text-white">
                            <option value="pending" selected>Pending</option>
                            <option value="progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Due Date</label>
                    <input type="date" name="dueDate" class="w-full px-4 py-3 rounded-xl glass-input text-white">
                </div>

                <!-- Project/Category (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Project (Optional)</label>
                    <select name="project_id" class="w-full px-4 py-3 rounded-xl glass-input text-white">
                        <option value="">No Project</option>
                        <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeTaskModal()" class="flex-1 px-4 py-3 rounded-xl glass-panel text-gray-300 hover:text-white hover:bg-white/10 transition-all font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-medium hover:shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize Icons
        lucide.createIcons();

        // Toggle Sidebar function is now in sidebar.php

        // Initialize Chart.js
        const ctx = document.getElementById('taskChart').getContext('2d');
        
        // Gradient for Chart
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(0, 243, 255, 0.5)');
        gradient.addColorStop(1, 'rgba(0, 243, 255, 0)');

        const taskChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Tasks Completed',
                    data: <?php echo json_encode($chartData); ?>,
                    backgroundColor: gradient,
                    borderColor: '#00f3ff',
                    borderWidth: 2,
                    pointBackgroundColor: '#050510',
                    pointBorderColor: '#00f3ff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(5, 5, 16, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });

        // Task Modal Functions
        function openTaskModal() {
            const modal = document.getElementById('taskModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                lucide.createIcons(); // Reinitialize icons for modal
            }
        }

        function closeTaskModal() {
            const modal = document.getElementById('taskModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                const form = document.getElementById('taskForm');
                if (form) {
                    form.reset();
                }
            }
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('taskModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeTaskModal();
                    }
                });
            }
        });

        // Handle form submission
        function handleTaskSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const submitData = new FormData();
            submitData.append('action', 'create_task');
            submitData.append('name', formData.get('taskName'));
            submitData.append('description', formData.get('description'));
            submitData.append('priority', formData.get('priority'));
            submitData.append('status', formData.get('status'));
            submitData.append('due_date', formData.get('dueDate'));
            const projectId = formData.get('project_id');
            if (projectId) {
                submitData.append('project_id', projectId);
            }
            const assignedTo = formData.get('assigned_to');
            if (assignedTo) {
                submitData.append('assigned_to', assignedTo);
            } else {
                // Assign to current user if not specified
                submitData.append('assigned_to', <?php echo $userId; ?>);
            }

            // Send to API
            fetch('api.php', {
                method: 'POST',
                body: submitData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                    showNotification('Task created successfully!', 'success');
                    
                    // Close modal and reset form
                    closeTaskModal();
                    
                    // Reload page after a short delay to show new task
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showNotification(data.message || 'Failed to create task. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500/90 text-white' : 'bg-red-500/90 text-white'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>