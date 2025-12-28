<?php
require_once 'config.php';
requireLogin();
$currentUser = getCurrentUser();
$userId = getCurrentUserId();

$pdo = getDBConnection();
$error = '';
$success = '';

// Handle adding member to team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_member') {
    $team_id = (int)$_POST['team_id'];
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'] ?? 'member';
    
    if (empty($team_id) || empty($user_id)) {
        $error = 'Team and user are required';
    } else {
        try {
            // Check if user is already in team
            $stmt = $pdo->prepare("SELECT id FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$team_id, $user_id]);
            if ($stmt->fetch()) {
                $error = 'User is already a member of this team';
            } else {
                $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?)");
                $stmt->execute([$team_id, $user_id, $role]);
                $success = 'Member added to team successfully!';
                // Redirect to prevent form resubmission
                header('Location: team.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Failed to add member. Please try again.';
        }
    }
}

// Fetch all users with their team memberships and task stats
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.full_name,
        u.email,
        u.role as user_role,
        u.avatar,
        u.is_active,
        COUNT(DISTINCT ts.id) as total_tasks,
        SUM(CASE WHEN ts.status = 'done' THEN 1 ELSE 0 END) as completed_tasks,
        GROUP_CONCAT(DISTINCT tm.team_id) as team_ids,
        GROUP_CONCAT(DISTINCT te.name SEPARATOR ', ') as team_names
    FROM users u
    LEFT JOIN tasks ts ON u.id = ts.assigned_to
    LEFT JOIN team_members tm ON u.id = tm.user_id
    LEFT JOIN teams te ON tm.team_id = te.id
    WHERE u.is_active = 1
    GROUP BY u.id, u.full_name, u.email, u.role, u.avatar, u.is_active
    ORDER BY u.full_name ASC
");
$stmt->execute();
$allUsers = $stmt->fetchAll();

// Calculate efficiency for each user (based on completed tasks)
foreach ($allUsers as &$user) {
    if ($user['total_tasks'] > 0) {
        $user['efficiency'] = round(($user['completed_tasks'] / $user['total_tasks']) * 100);
    } else {
        $user['efficiency'] = 0;
    }
}

// Get teams for dropdown
$teams = getAllTeams();

// Get all users for member dropdown (excluding current user if needed)
$availableUsers = getAllUsers();

// Get total team member count
$teamMemberCount = count($allUsers);

// Helper function to get status indicator color
function getStatusColor($isActive) {
    return $isActive ? 'bg-green-500' : 'bg-gray-500';
}

// Helper function to get efficiency color
function getEfficiencyColor($efficiency) {
    if ($efficiency >= 95) return 'text-green-400';
    if ($efficiency >= 85) return 'text-yellow-400';
    return 'text-red-400';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team - TaskFlow</title>
    
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

        /* Team Card Hover */
        .team-card {
            transition: all 0.3s ease;
        }
        .team-card:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 243, 255, 0.3);
            box-shadow: 0 10px 30px -10px rgba(0, 243, 255, 0.1);
        }
        
        .team-card:hover .social-links {
            opacity: 1;
            transform: translateY(0);
        }

        .social-links {
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

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
            <a href="team.php" class="flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Team</span>
                <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white"><?php echo $teamMemberCount; ?></span>
            </a>
            <a href="calender.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
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
                <h1 class="text-xl font-bold hidden sm:block">Team Members</h1>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-4">
                <div class="flex bg-white/5 rounded-lg p-1 border border-white/10 hidden sm:flex">
                    <button class="p-2 rounded bg-white/10 text-white shadow-sm"><i data-lucide="grid" class="w-4 h-4"></i></button>
                    <button class="p-2 rounded text-gray-400 hover:text-white"><i data-lucide="list" class="w-4 h-4"></i></button>
                </div>
                
                <button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all flex items-center gap-2" onclick="openModal()">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Add Member</span>
                </button>
            </div>
        </header>

        <!-- Filters Toolbar -->
        <div class="px-6 py-4 border-b border-white/5 flex flex-col sm:flex-row gap-4 justify-between items-center bg-[#050510]">
            <div class="flex gap-4 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" placeholder="Search members..." class="bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-cyan-500 w-full transition-all">
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <button class="px-4 py-2 rounded-lg text-sm font-medium bg-white/10 text-white border border-white/10 whitespace-nowrap">All Team</button>
                <button class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 whitespace-nowrap">Design</button>
                <button class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 whitespace-nowrap">Engineering</button>
                <button class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 whitespace-nowrap">Marketing</button>
            </div>
        </div>

        <!-- Team Grid -->
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php 
                // Gradient colors for avatars
                $gradients = [
                    'from-cyan-400 to-purple-600',
                    'from-purple-500 to-pink-500',
                    'from-blue-500 to-cyan-500',
                    'from-pink-500 to-orange-500',
                    'from-green-500 to-teal-500',
                    'from-yellow-500 to-orange-500',
                    'from-indigo-500 to-purple-500',
                    'from-red-500 to-pink-500'
                ];
                
                foreach ($allUsers as $index => $user): 
                    $gradient = $gradients[$index % count($gradients)];
                    $statusColor = getStatusColor($user['is_active']);
                    $efficiencyColor = getEfficiencyColor($user['efficiency']);
                    $avatarUrl = $user['avatar'] ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($user['full_name']);
                ?>
                <!-- Team Member Card -->
                <div class="glass-panel p-6 rounded-2xl team-card text-center group relative overflow-hidden">
                    <div class="absolute top-4 right-4">
                        <span class="flex h-3 w-3 relative">
                            <?php if ($user['is_active']): ?>
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <?php endif; ?>
                            <span class="relative inline-flex rounded-full h-3 w-3 <?php echo $statusColor; ?>"></span>
                        </span>
                    </div>
                    
                    <div class="relative inline-block mb-4">
                        <div class="w-24 h-24 rounded-full p-[2px] bg-gradient-to-br <?php echo $gradient; ?>">
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" class="w-full h-full rounded-full object-cover border-4 border-[#050510]" alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-bold text-white mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p class="text-cyan-400 text-sm mb-4"><?php echo ucfirst($user['user_role']); ?></p>
                    
                    <?php if ($user['team_names']): ?>
                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        <?php 
                        $teamNames = explode(', ', $user['team_names']);
                        foreach (array_slice($teamNames, 0, 3) as $teamName): 
                        ?>
                        <span class="px-2 py-1 rounded text-xs bg-white/5 text-gray-400 border border-white/5"><?php echo htmlspecialchars($teamName); ?></span>
                        <?php endforeach; ?>
                        <?php if (count($teamNames) > 3): ?>
                        <span class="px-2 py-1 rounded text-xs bg-white/5 text-gray-400 border border-white/5">+<?php echo count($teamNames) - 3; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        <span class="px-2 py-1 rounded text-xs bg-white/5 text-gray-500 border border-white/5">No Team</span>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 gap-4 border-t border-white/5 pt-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Tasks</p>
                            <p class="font-bold text-white"><?php echo $user['total_tasks']; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Efficiency</p>
                            <p class="font-bold <?php echo $efficiencyColor; ?>"><?php echo $user['efficiency']; ?>%</p>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4 social-links">
                        <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="mail" class="w-5 h-5"></i></a>
                        <button class="text-gray-400 hover:text-white transition-colors"><i data-lucide="message-square" class="w-5 h-5"></i></button>
                        <button class="text-gray-400 hover:text-white transition-colors"><i data-lucide="user" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Add New Member Card -->
                <div onclick="openModal()" class="border-2 border-dashed border-white/10 rounded-2xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:border-cyan-500/50 hover:bg-white/5 transition-all group min-h-[350px]">
                    <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mb-4 group-hover:bg-cyan-500/20 group-hover:text-cyan-400 transition-colors">
                        <i data-lucide="user-plus" class="w-10 h-10 text-gray-400 group-hover:text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-300 group-hover:text-white">Invite Member</h3>
                    <p class="text-sm text-gray-500 mt-2">Add to workspace</p>
                </div>

            </div>
        </div>
    </main>

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6 rounded-2xl glass-panel border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white">Add Member to Team</h2>
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
            <form method="POST" action="team.php" id="memberForm">
                <input type="hidden" name="action" value="add_member">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Select User *</label>
                        <select name="user_id" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none">
                            <option value="">Choose a user...</option>
                            <?php foreach ($availableUsers as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name'] . ' (' . $user['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Select Team *</label>
                        <select name="team_id" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none">
                            <option value="">Choose a team...</option>
                            <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Team Role</label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="role" value="member" class="text-cyan-500 focus:ring-cyan-500 bg-gray-700 border-gray-600" checked>
                                <span class="text-sm text-gray-300">Member</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="role" value="leader" class="text-cyan-500 focus:ring-cyan-500 bg-gray-700 border-gray-600">
                                <span class="text-sm text-gray-300">Leader</span>
                            </label>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Add to Team</button>
                    </div>
                </div>
            </form>
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
            document.getElementById('addMemberModal').classList.remove('hidden');
            lucide.createIcons();
        }
        function closeModal() {
            document.getElementById('addMemberModal').classList.add('hidden');
            document.getElementById('memberForm').reset();
        }
    </script>
</body>
</html>