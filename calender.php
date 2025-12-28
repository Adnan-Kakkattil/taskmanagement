<?php
require_once 'config.php';
requireLogin();
$currentUser = getCurrentUser();
$userId = getCurrentUserId();

$pdo = getDBConnection();

// Get current month and year from URL or use current date
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2000 || $year > 2100) $year = date('Y');

// Calculate previous and next month
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Get first day of month and number of days
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay); // 0 = Sunday, 6 = Saturday

// Get start date and end date for query
$startDate = date('Y-m-01', $firstDay);
$endDate = date('Y-m-t', $firstDay);

// Fetch tasks for the current month assigned to the user
$stmt = $pdo->prepare("
    SELECT t.*, p.name as project_name 
    FROM tasks t 
    LEFT JOIN projects p ON t.project_id = p.id 
    WHERE t.assigned_to = ? 
    AND t.due_date IS NOT NULL
    AND t.due_date >= ? 
    AND t.due_date <= ?
    ORDER BY t.due_date ASC, t.priority DESC
");
$stmt->execute([$userId, $startDate, $endDate]);
$tasks = $stmt->fetchAll();

// Group tasks by date
$tasksByDate = [];
foreach ($tasks as $task) {
    $date = date('j', strtotime($task['due_date'])); // Day of month (1-31)
    if (!isset($tasksByDate[$date])) {
        $tasksByDate[$date] = [];
    }
    $tasksByDate[$date][] = $task;
}

// Get today's date for highlighting
$today = date('Y-m-d');
$currentMonthYear = date('Y-m');
$displayMonthYear = date('Y-m', $firstDay);

// Helper function to get event class based on priority and status
function getEventClass($task) {
    if ($task['priority'] === 'high') {
        return 'event-urgent';
    } elseif ($task['status'] === 'done') {
        return 'event-task';
    } else {
        return 'event-task';
    }
}

// Get projects for dropdown
$projects = getAllProjects();

// Month names
$monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];
$monthName = $monthNames[$month - 1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - TaskFlow</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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
            --calendar-border: rgba(255, 255, 255, 0.05);
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

        /* Calendar Specifics */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: var(--calendar-border);
            border: 1px solid var(--calendar-border);
            border-radius: 1rem;
            overflow: hidden;
        }

        .calendar-day {
            background-color: rgba(20, 20, 35, 0.6);
            backdrop-filter: blur(10px);
            min-height: 120px;
            padding: 0.75rem;
            transition: background-color 0.2s ease;
        }

        .calendar-day:hover {
            background-color: rgba(30, 30, 50, 0.8);
        }

        .calendar-day.inactive {
            background-color: rgba(10, 10, 20, 0.4);
            color: #4b5563;
        }

        .calendar-day.today {
            background-color: rgba(0, 243, 255, 0.05);
        }

        .calendar-day.today .day-number {
            background-color: var(--primary);
            color: black;
            font-weight: bold;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .event-chip {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: transform 0.1s ease;
        }
        .event-chip:hover {
            transform: scale(1.02);
            z-index: 10;
        }

        .event-meeting { background: rgba(189, 0, 255, 0.15); color: #d8b4fe; border-left: 2px solid #bd00ff; }
        .event-task { background: rgba(0, 243, 255, 0.15); color: #67e8f9; border-left: 2px solid #00f3ff; }
        .event-urgent { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border-left: 2px solid #ef4444; }

        /* Mobile Overlay */
        .mobile-overlay {
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 z-20 mobile-overlay hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 glass-panel border-r border-r-white/5 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto flex flex-col sidebar bg-[#050510]">
        <!-- Brand -->
        <a href="dashboard.php" class="flex items-center justify-center h-20 border-b border-white/5 cursor-pointer">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-gradient-to-br from-cyan-400 to-purple-600 flex items-center justify-center font-bold text-white">T</div>
                <span class="font-bold text-xl tracking-wider text-white">Task<span class="text-cyan-400">Flow</span></span>
            </div>
        </a>

        <!-- Nav Links -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="tasks.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="font-medium">My Tasks</span>
            </a>
            <a href="projects.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                <span class="font-medium">Projects</span>
            </a>
            <a href="team.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Team</span>
            </a>
            <a href="calender.php" class="flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all">
                <i data-lucide="calendar" class="w-5 h-5"></i>
                <span class="font-medium">Calendar</span>
            </a>
        </nav>

        <!-- User Profile (Bottom) -->
        <div class="p-4 border-t border-white/5">
            <a href="profile.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 cursor-pointer transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-cyan-500 p-[2px]">
                    <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($currentUser['full_name'])); ?>" class="w-full h-full rounded-full object-cover border-2 border-[#050510]" alt="User">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($currentUser['full_name']); ?></p>
                    <p class="text-xs text-gray-400 truncate"><?php echo ucfirst($currentUser['role']); ?></p>
                </div>
                <a href="logout.php" class="text-gray-500 hover:text-red-400 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#050510]">
        
        <!-- Header -->
        <header class="flex items-center justify-between h-20 px-6 border-b border-white/5 bg-[#050510]/50 backdrop-blur-md sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-white">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex flex-col">
                    <h1 class="text-xl font-bold hidden sm:block">Calendar</h1>
                    <span class="text-xs text-gray-400 hidden sm:block"><?php echo $monthName . ' ' . $year; ?></span>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-4">
                <div class="flex items-center bg-white/5 rounded-lg border border-white/10 p-1">
                    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="p-1.5 hover:bg-white/10 rounded-md text-gray-400 hover:text-white">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </a>
                    <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="px-3 text-sm font-medium hover:text-cyan-400">Today</a>
                    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="p-1.5 hover:bg-white/10 rounded-md text-gray-400 hover:text-white">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </a>
                </div>

                <div class="flex bg-white/5 rounded-lg p-1 border border-white/10 hidden sm:flex">
                    <button class="p-2 rounded bg-white/10 text-white shadow-sm text-xs font-medium">Month</button>
                    <button class="p-2 rounded text-gray-400 hover:text-white hover:bg-white/5 text-xs font-medium">Week</button>
                    <button class="p-2 rounded text-gray-400 hover:text-white hover:bg-white/5 text-xs font-medium">Day</button>
                </div>
                
                <button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all flex items-center gap-2" onclick="openModal()">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Add Event</span>
                </button>
            </div>
        </header>

        <!-- Calendar Content -->
        <div class="flex-1 overflow-y-auto p-6">
            
            <!-- Days of week -->
            <div class="grid grid-cols-7 gap-1 mb-2 text-center">
                <div class="text-gray-500 text-sm font-medium py-2">SUN</div>
                <div class="text-gray-500 text-sm font-medium py-2">MON</div>
                <div class="text-gray-500 text-sm font-medium py-2">TUE</div>
                <div class="text-gray-500 text-sm font-medium py-2">WED</div>
                <div class="text-gray-500 text-sm font-medium py-2">THU</div>
                <div class="text-gray-500 text-sm font-medium py-2">FRI</div>
                <div class="text-gray-500 text-sm font-medium py-2">SAT</div>
            </div>

            <!-- Calendar Grid -->
            <div class="calendar-grid">
                
                <?php
                // Get last day of previous month
                $prevMonthDays = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));
                
                // Display previous month's trailing days
                $trailingDays = $dayOfWeek;
                for ($i = $trailingDays - 1; $i >= 0; $i--) {
                    $day = $prevMonthDays - $i;
                    echo '<div class="calendar-day inactive">';
                    echo '<div class="day-number text-sm">' . $day . '</div>';
                    echo '</div>';
                }
                
                // Display current month's days
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $isToday = ($dateStr === $today);
                    $dayTasks = $tasksByDate[$day] ?? [];
                    
                    $dayClasses = 'calendar-day';
                    if ($isToday) {
                        $dayClasses .= ' today';
                    }
                    
                    echo '<div class="' . $dayClasses . '">';
                    echo '<div class="day-number text-sm mb-1">' . $day . '</div>';
                    
                    // Display tasks for this day
                    foreach ($dayTasks as $task) {
                        $eventClass = getEventClass($task);
                        $taskTitle = htmlspecialchars($task['name']);
                        $priority = ucfirst($task['priority']);
                        $status = ucfirst($task['status']);
                        
                        // Truncate long titles
                        if (strlen($taskTitle) > 20) {
                            $taskTitle = substr($taskTitle, 0, 17) . '...';
                        }
                        
                        echo '<div class="event-chip ' . $eventClass . '" title="' . htmlspecialchars($task['name']) . ' (' . $priority . ' Priority, ' . $status . ')" onclick="showTaskDetails(' . $task['id'] . ')">';
                        echo $taskTitle;
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                // Display next month's leading days
                $totalCells = $trailingDays + $daysInMonth;
                $remainingCells = 42 - $totalCells; // 6 rows * 7 days = 42
                if ($remainingCells > 0) {
                    for ($day = 1; $day <= $remainingCells; $day++) {
                        echo '<div class="calendar-day inactive">';
                        echo '<div class="day-number text-sm">' . $day . '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </main>

    <!-- Add Event Modal -->
    <div id="addEventModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6 rounded-2xl glass-panel border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white">Add New Task</h2>
            <form id="eventForm" onsubmit="handleEventSubmit(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Task Title *</label>
                        <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none" placeholder="e.g. Project Sync">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Priority</label>
                            <select name="priority" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Due Date *</label>
                            <input type="date" name="due_date" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Project (Optional)</label>
                        <select name="project_id" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                            <option value="">No Project</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea name="description" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white h-24 outline-none" placeholder="Add details..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Save Task</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Details Modal -->
    <div id="taskDetailsModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeTaskDetails()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6 rounded-2xl glass-panel border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white">Task Details</h2>
            <div id="taskDetailsContent" class="space-y-4">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="pt-4 flex justify-end">
                <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeTaskDetails()">Close</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize Icons
        lucide.createIcons();

        // Toggle Sidebar
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');

        function toggleSidebar() {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Modal Functions
        function openModal() {
            document.getElementById('addEventModal').classList.remove('hidden');
            lucide.createIcons();
        }
        
        function closeModal() {
            document.getElementById('addEventModal').classList.add('hidden');
            document.getElementById('eventForm').reset();
        }

        // Handle Event Form Submission
        function handleEventSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'create_task');
            formData.append('assigned_to', '<?php echo $userId; ?>');
            formData.append('status', 'pending');
            
            // Convert project_id to null if empty
            const projectId = formData.get('project_id');
            if (!projectId) {
                formData.set('project_id', '');
            }
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Task created successfully!');
                    closeModal();
                    // Reload page to show new task
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create task'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to create task. Please try again.');
            });
        }

        // Show Task Details
        function showTaskDetails(taskId) {
            // Fetch task details
            fetch(`tasks.php?task_id=${taskId}`)
                .then(response => response.text())
                .then(html => {
                    // For now, just show a simple message
                    // In a full implementation, you'd fetch task details via API
                    document.getElementById('taskDetailsContent').innerHTML = `
                        <p class="text-gray-400">Task ID: ${taskId}</p>
                        <p class="text-white">Click "My Tasks" to view full task details.</p>
                    `;
                    document.getElementById('taskDetailsModal').classList.remove('hidden');
                    lucide.createIcons();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('taskDetailsContent').innerHTML = `
                        <p class="text-red-400">Error loading task details.</p>
                    `;
                    document.getElementById('taskDetailsModal').classList.remove('hidden');
                });
        }

        function closeTaskDetails() {
            document.getElementById('taskDetailsModal').classList.add('hidden');
        }
    </script>
</body>
</html>
