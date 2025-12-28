<?php
/**
 * Dynamic Sidebar Component
 * 
 * Usage: include 'sidebar.php';
 * 
 * Optional parameters (set before including):
 * - $currentPage: Current page identifier ('dashboard', 'tasks', 'projects', 'team', 'calendar', 'profile')
 * - $showTaskCount: Show task count badge (default: true)
 * - $showProjectCount: Show project count badge (default: false)
 * - $showTeamCount: Show team count badge (default: false)
 */

// Ensure user is logged in
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}
if (!isset($userId)) {
    $userId = getCurrentUserId();
}

// Default values
$currentPage = $currentPage ?? 'dashboard';
$showTaskCount = $showTaskCount ?? true;
$showProjectCount = $showProjectCount ?? false;
$showTeamCount = $showTeamCount ?? false;

// Fetch dynamic data
$pdo = getDBConnection();

// Get task count for current user
$taskCount = 0;
if ($showTaskCount) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $taskCount = $result['count'] ?? 0;
    } catch (Exception $e) {
        $taskCount = 0;
    }
}

// Get project count
$projectCount = 0;
if ($showProjectCount) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.id) as count 
            FROM projects p
            LEFT JOIN teams t ON p.team_id = t.id
            LEFT JOIN team_members tm ON t.id = tm.team_id
            WHERE p.created_by = ? OR tm.user_id = ?
        ");
        $stmt->execute([$userId, $userId]);
        $result = $stmt->fetch();
        $projectCount = $result['count'] ?? 0;
    } catch (Exception $e) {
        $projectCount = 0;
    }
}

// Get team member count
$teamMemberCount = 0;
if ($showTeamCount) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $result = $stmt->fetch();
        $teamMemberCount = $result['count'] ?? 0;
    } catch (Exception $e) {
        $teamMemberCount = 0;
    }
}

// Get user avatar URL
$avatarUrl = $currentUser['avatar'] ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($currentUser['full_name'] ?? 'User');

// Helper function to get nav link classes
function getNavLinkClass($page, $currentPage) {
    if ($page === $currentPage) {
        return 'flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all';
    }
    return 'flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all';
}

// Helper function to check if profile page is active
function isProfileActive($currentPage) {
    return $currentPage === 'profile';
}
?>

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
        <a href="dashboard.php" class="<?php echo getNavLinkClass('dashboard', $currentPage); ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>
        <a href="tasks.php" class="<?php echo getNavLinkClass('tasks', $currentPage); ?>">
            <i data-lucide="check-square" class="w-5 h-5"></i>
            <span class="font-medium">My Tasks</span>
            <?php if ($showTaskCount && $taskCount > 0): ?>
            <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white"><?php echo $taskCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="projects.php" class="<?php echo getNavLinkClass('projects', $currentPage); ?>">
            <i data-lucide="folder-kanban" class="w-5 h-5"></i>
            <span class="font-medium">Projects</span>
            <?php if ($showProjectCount && $projectCount > 0): ?>
            <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white"><?php echo $projectCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="team.php" class="<?php echo getNavLinkClass('team', $currentPage); ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="font-medium">Team</span>
            <?php if ($showTeamCount && $teamMemberCount > 0): ?>
            <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white"><?php echo $teamMemberCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="calender.php" class="<?php echo getNavLinkClass('calendar', $currentPage); ?>">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            <span class="font-medium">Calendar</span>
        </a>
    </nav>

    <!-- User Profile (Bottom) -->
    <div class="p-4 border-t border-white/5">
        <a href="profile.php" class="flex items-center gap-3 p-3 rounded-xl <?php echo isProfileActive($currentPage) ? 'bg-white/10 border border-white/5' : 'hover:bg-white/5'; ?> cursor-pointer transition-colors">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-cyan-500 p-[2px]">
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" class="w-full h-full rounded-full object-cover border-2 border-[#050510]" alt="User">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'User'); ?></p>
                <p class="text-xs text-gray-400 truncate"><?php echo ucfirst($currentUser['role'] ?? 'member'); ?></p>
            </div>
            <?php if (isProfileActive($currentPage)): ?>
            <i data-lucide="settings" class="w-4 h-4 text-cyan-400"></i>
            <?php else: ?>
            <a href="logout.php" class="text-gray-500 hover:text-red-400 transition-colors">
                <i data-lucide="log-out" class="w-4 h-4"></i>
            </a>
            <?php endif; ?>
        </a>
    </div>
</aside>

<!-- Sidebar Toggle Script (include this in your page scripts) -->
<script>
    // Toggle Sidebar Function (can be called from any page)
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        if (sidebar && overlay) {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    }
</script>

