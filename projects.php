<?php
require_once 'config.php';
requireLogin();
$currentUser = getCurrentUser();
$userId = getCurrentUserId();

$pdo = getDBConnection();
$error = '';
$success = '';

// Handle project creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_project') {
    $name = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'planning';
    $team_id = !empty($_POST['team_id']) ? (int)$_POST['team_id'] : null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    
    if (empty($name)) {
        $error = 'Project name is required';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO projects (name, description, status, team_id, created_by, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $status, $team_id, $userId, $start_date ?: null, $end_date ?: null]);
            $success = 'Project created successfully!';
            // Redirect to prevent form resubmission
            header('Location: projects.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to create project. Please try again.';
        }
    }
}

// Fetch all projects with related data
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        t.name as team_name,
        u.full_name as creator_name,
        COUNT(DISTINCT ts.id) as total_tasks,
        SUM(CASE WHEN ts.status = 'done' THEN 1 ELSE 0 END) as completed_tasks
    FROM projects p
    LEFT JOIN teams t ON p.team_id = t.id
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN tasks ts ON p.id = ts.project_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$projects = $stmt->fetchAll();

// Calculate progress for each project
foreach ($projects as &$project) {
    if ($project['total_tasks'] > 0) {
        $project['progress'] = round(($project['completed_tasks'] / $project['total_tasks']) * 100);
    } else {
        $project['progress'] = 0;
    }
}

// Get teams for dropdown
$teams = getAllTeams();

// Get project count
$projectCount = count($projects);

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'active': return 'bg-green-500/10 text-green-400 border-green-500/20';
        case 'planning': return 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
        case 'on_hold': return 'bg-gray-500/10 text-gray-400 border-gray-500/20';
        case 'completed': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        default: return 'bg-gray-500/10 text-gray-400 border-gray-500/20';
    }
}

// Helper function to format date
function formatProjectDate($date) {
    if (!$date) return 'TBD';
    return date('M j', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - TaskFlow</title>
    
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

        /* Project Card Hover */
        .project-card {
            transition: all 0.3s ease;
        }
        .project-card:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 243, 255, 0.3);
            box-shadow: 0 10px 30px -10px rgba(0, 243, 255, 0.1);
        }

        /* Mobile Overlay */
        .mobile-overlay {
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php
    // Include dynamic sidebar
    $currentPage = 'projects';
    $showProjectCount = true;
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
                <h1 class="text-xl font-bold hidden sm:block">Projects</h1>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-4">
                <div class="flex bg-white/5 rounded-lg p-1 border border-white/10 hidden sm:flex">
                    <button class="p-2 rounded bg-white/10 text-white shadow-sm"><i data-lucide="grid" class="w-4 h-4"></i></button>
                    <button class="p-2 rounded text-gray-400 hover:text-white"><i data-lucide="list" class="w-4 h-4"></i></button>
                </div>
                
                <button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all flex items-center gap-2" onclick="openModal()">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">New Project</span>
                </button>
            </div>
        </header>

        <!-- Filters Toolbar -->
        <div class="px-6 py-4 border-b border-white/5 flex flex-col sm:flex-row gap-4 justify-between items-center bg-[#050510]">
            <div class="flex gap-4 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" placeholder="Search projects..." class="bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-cyan-500 w-full transition-all">
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <button class="px-4 py-2 rounded-lg text-sm font-medium bg-white/10 text-white border border-white/10 whitespace-nowrap">All Projects</button>
                <button class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 whitespace-nowrap">Ongoing</button>
                <button class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 whitespace-nowrap">Completed</button>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                // Icon colors based on project status
                $iconColors = [
                    'active' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                    'planning' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                    'on_hold' => 'bg-pink-500/10 text-pink-400 border-pink-500/20',
                    'completed' => 'bg-orange-500/10 text-orange-400 border-orange-500/20'
                ];
                
                // Progress bar colors
                $progressColors = [
                    'active' => 'from-cyan-500 to-blue-500',
                    'planning' => 'from-purple-500 to-pink-500',
                    'on_hold' => 'from-pink-500 to-red-500',
                    'completed' => 'from-orange-500 to-yellow-500'
                ];
                
                foreach ($projects as $project): 
                    $statusClass = getStatusBadgeClass($project['status']);
                    $iconClass = $iconColors[$project['status']] ?? $iconColors['planning'];
                    $progressClass = $progressColors[$project['status']] ?? $progressColors['planning'];
                ?>
                <!-- Project Card -->
                <div class="glass-panel p-6 rounded-2xl project-card group cursor-pointer relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl <?php echo $iconClass; ?>">
                            <i data-lucide="folder-kanban" class="w-6 h-6"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border <?php echo $statusClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?></span>
                            <button class="text-gray-500 hover:text-white"><i data-lucide="more-vertical" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars($project['name']); ?></h3>
                    <p class="text-gray-400 text-sm mb-6 line-clamp-2 h-10"><?php echo htmlspecialchars($project['description'] ?: 'No description'); ?></p>
                    
                    <div class="mb-6">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-400">Progress</span>
                            <span class="text-white font-medium"><?php echo $project['progress']; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r <?php echo $progressClass; ?> h-full rounded-full" style="width: <?php echo $project['progress']; ?>%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-white/5 pt-4">
                        <div class="flex items-center gap-2">
                            <?php if ($project['team_name']): ?>
                            <span class="text-xs text-cyan-400"><i data-lucide="users" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($project['team_name']); ?></span>
                            <?php else: ?>
                            <span class="text-xs text-gray-500">No team</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <i data-lucide="calendar" class="w-3 h-3"></i> <?php echo formatProjectDate($project['end_date']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Add New Project Card (Placeholder) -->
                <div onclick="openModal()" class="border-2 border-dashed border-white/10 rounded-2xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:border-cyan-500/50 hover:bg-white/5 transition-all group min-h-[280px]">
                    <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4 group-hover:bg-cyan-500/20 group-hover:text-cyan-400 transition-colors">
                        <i data-lucide="plus" class="w-8 h-8 text-gray-400 group-hover:text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-300 group-hover:text-white">Create New Project</h3>
                    <p class="text-sm text-gray-500 mt-2">Start a new workflow</p>
                </div>

            </div>
        </div>
    </main>

    <!-- Add Project Modal -->
    <div id="addProjectModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6 rounded-2xl glass-panel border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white">Create New Project</h2>
            <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="projects.php" id="projectForm">
                <input type="hidden" name="action" value="create_project">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Project Name *</label>
                        <input type="text" name="project_name" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none" placeholder="e.g. Website Redesign">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Status</label>
                            <select name="status" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                                <option value="planning" selected>Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Team (Optional)</label>
                            <select name="team_id" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                                <option value="">No Team</option>
                                <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Start Date</label>
                            <input type="date" name="start_date" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea name="description" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white h-24 outline-none" placeholder="Brief description of the project..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Create Project</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize Icons
        lucide.createIcons();

        // Toggle Sidebar function is now in sidebar.php

        // Modal Functions
        function openModal() {
            document.getElementById('addProjectModal').classList.remove('hidden');
            lucide.createIcons();
        }
        function closeModal() {
            document.getElementById('addProjectModal').classList.add('hidden');
            document.getElementById('projectForm').reset();
        }
    </script>
</body>
</html>