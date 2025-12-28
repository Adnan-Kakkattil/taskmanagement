<?php
require_once 'config.php';
requireLogin();
$currentUser = getCurrentUser();
$userId = getCurrentUserId();

$pdo = getDBConnection();
$error = '';
$success = '';

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_task') {
    $name = trim($_POST['task_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
    
    if (empty($name)) {
        $error = 'Task name is required';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (name, description, priority, due_date, project_id, assigned_to, status, created_by) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$name, $description, $priority, $due_date ?: null, $project_id, $userId, $userId]);
            $success = 'Task created successfully!';
            // Redirect to prevent form resubmission
            header('Location: tasks.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to create task. Please try again.';
        }
    }
}

// Handle task status update (for drag and drop)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $task_id = (int)$_POST['task_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
        $stmt->execute([$status, $task_id, $userId]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Fetch user's tasks
$stmt = $pdo->prepare("
    SELECT t.*, p.name as project_name 
    FROM tasks t 
    LEFT JOIN projects p ON t.project_id = p.id 
    WHERE t.assigned_to = ? 
    ORDER BY t.priority DESC, t.due_date ASC, t.created_at DESC
");
$stmt->execute([$userId]);
$allTasks = $stmt->fetchAll();

// Group tasks by status
$tasksByStatus = [
    'pending' => [],
    'progress' => [],
    'done' => []
];

foreach ($allTasks as $task) {
    $tasksByStatus[$task['status']][] = $task;
}

// Get task counts
$taskCounts = [
    'pending' => count($tasksByStatus['pending']),
    'progress' => count($tasksByStatus['progress']),
    'done' => count($tasksByStatus['done']),
    'total' => count($allTasks)
];

// Get projects for dropdown
$projects = getAllProjects();

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

// Helper function to get priority color
function getPriorityColor($priority) {
    switch ($priority) {
        case 'high': return 'text-red-400 bg-red-400/10';
        case 'medium': return 'text-yellow-400 bg-yellow-400/10';
        case 'low': return 'text-green-400 bg-green-400/10';
        default: return 'text-gray-400 bg-gray-400/10';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - TaskFlow</title>
    
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
            --column-bg: rgba(20, 20, 35, 0.6);
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

        .glass-column {
            background: var(--column-bg);
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

        /* Drag and Drop Styles */
        .task-card {
            cursor: grab;
            transition: all 0.2s ease;
        }
        .task-card:active {
            cursor: grabbing;
            transform: scale(1.02);
        }
        .task-card.dragging {
            opacity: 0.5;
            border: 1px dashed var(--primary);
        }
        .kanban-column {
            transition: background 0.3s ease;
        }
        .kanban-column.drag-over {
            background: rgba(0, 243, 255, 0.05);
            border: 1px dashed rgba(0, 243, 255, 0.3);
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
    $currentPage = 'tasks';
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
                <h1 class="text-xl font-bold hidden sm:block">My Tasks</h1>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-4">
                <div class="flex bg-white/5 rounded-lg p-1 border border-white/10">
                    <button class="p-2 rounded bg-white/10 text-white shadow-sm"><i data-lucide="kanban" class="w-4 h-4"></i></button>
                    <button class="p-2 rounded text-gray-400 hover:text-white"><i data-lucide="list" class="w-4 h-4"></i></button>
                </div>
                
                <button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all flex items-center gap-2" onclick="openModal()">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Add Task</span>
                </button>
            </div>
        </header>

        <!-- Filters Toolbar -->
        <div class="px-6 py-4 border-b border-white/5 flex flex-col sm:flex-row gap-4 justify-between items-center bg-[#050510]">
            <div class="relative w-full sm:w-auto">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                <input type="text" id="searchInput" placeholder="Filter tasks..." class="bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-cyan-500 w-full sm:w-64 transition-all" onkeyup="filterTasks()">
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <span class="text-xs text-gray-500 font-medium uppercase mr-2">Sort By:</span>
                <select id="sortSelect" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-cyan-500" onchange="sortTasks()">
                    <option value="priority">Priority</option>
                    <option value="due_date">Due Date</option>
                    <option value="created_at">Created Date</option>
                </select>
            </div>
        </div>

        <!-- Kanban Board Area -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-6">
            <div class="h-full flex flex-col sm:flex-row gap-6 min-w-full sm:min-w-[1000px]">
                
                <!-- Column: To Do -->
                <div class="flex-1 flex flex-col glass-column rounded-2xl min-w-[300px] h-full">
                    <!-- Column Header -->
                    <div class="p-4 border-b border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-orange-400"></div>
                            <h3 class="font-bold text-gray-200">To Do</h3>
                            <span class="bg-white/10 text-xs px-2 py-0.5 rounded text-gray-400"><?php echo $taskCounts['pending']; ?></span>
                        </div>
                        <button class="text-gray-500 hover:text-white" onclick="openModal()"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    </div>
                    
                    <!-- Draggable Area -->
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 kanban-column" id="todo" data-status="pending" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <?php foreach ($tasksByStatus['pending'] as $task): ?>
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 group" draggable="true" ondragstart="drag(event)" id="task-<?php echo $task['id']; ?>" data-task-id="<?php echo $task['id']; ?>">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold <?php echo getPriorityColor($task['priority']); ?> px-2 py-0.5 rounded"><?php echo ucfirst($task['priority']); ?></span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-white mb-1"><?php echo htmlspecialchars($task['name']); ?></h4>
                            <?php if ($task['description']): ?>
                            <p class="text-sm text-gray-400 mb-3 line-clamp-2"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            <?php if ($task['project_name']): ?>
                            <p class="text-xs text-cyan-400 mb-2"><i data-lucide="folder" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($task['project_name']); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <?php if ($task['due_date']): ?>
                                <div class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?php echo formatDate($task['due_date']); ?></div>
                                <?php else: ?>
                                <div></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($tasksByStatus['pending'])): ?>
                        <div class="text-center text-gray-500 text-sm py-8">No tasks</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Column: In Progress -->
                <div class="flex-1 flex flex-col glass-column rounded-2xl min-w-[300px] h-full">
                    <div class="p-4 border-b border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                            <h3 class="font-bold text-gray-200">In Progress</h3>
                            <span class="bg-white/10 text-xs px-2 py-0.5 rounded text-gray-400"><?php echo $taskCounts['progress']; ?></span>
                        </div>
                        <button class="text-gray-500 hover:text-white" onclick="openModal()"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    </div>
                    
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 kanban-column" id="progress" data-status="progress" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <?php foreach ($tasksByStatus['progress'] as $task): ?>
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 group" draggable="true" ondragstart="drag(event)" id="task-<?php echo $task['id']; ?>" data-task-id="<?php echo $task['id']; ?>">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold <?php echo getPriorityColor($task['priority']); ?> px-2 py-0.5 rounded"><?php echo ucfirst($task['priority']); ?></span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-white mb-1"><?php echo htmlspecialchars($task['name']); ?></h4>
                            <?php if ($task['description']): ?>
                            <p class="text-sm text-gray-400 mb-3 line-clamp-2"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            <?php if ($task['project_name']): ?>
                            <p class="text-xs text-cyan-400 mb-2"><i data-lucide="folder" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($task['project_name']); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <?php if ($task['due_date']): ?>
                                <div class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?php echo formatDate($task['due_date']); ?></div>
                                <?php else: ?>
                                <div></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($tasksByStatus['progress'])): ?>
                        <div class="text-center text-gray-500 text-sm py-8">No tasks</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Column: Done -->
                <div class="flex-1 flex flex-col glass-column rounded-2xl min-w-[300px] h-full">
                    <div class="p-4 border-b border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <h3 class="font-bold text-gray-200">Done</h3>
                            <span class="bg-white/10 text-xs px-2 py-0.5 rounded text-gray-400"><?php echo $taskCounts['done']; ?></span>
                        </div>
                        <button class="text-gray-500 hover:text-white"><i data-lucide="check" class="w-4 h-4"></i></button>
                    </div>
                    
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 kanban-column" id="done" data-status="done" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <?php foreach ($tasksByStatus['done'] as $task): ?>
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 opacity-70 group" draggable="true" ondragstart="drag(event)" id="task-<?php echo $task['id']; ?>" data-task-id="<?php echo $task['id']; ?>">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold <?php echo getPriorityColor($task['priority']); ?> px-2 py-0.5 rounded"><?php echo ucfirst($task['priority']); ?></span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-gray-300 line-through mb-1"><?php echo htmlspecialchars($task['name']); ?></h4>
                            <?php if ($task['description']): ?>
                            <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            <?php if ($task['project_name']): ?>
                            <p class="text-xs text-cyan-400 mb-2"><i data-lucide="folder" class="w-3 h-3 inline"></i> <?php echo htmlspecialchars($task['project_name']); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <div class="flex items-center gap-1 text-green-400">
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i> 
                                    <?php echo $task['completed_at'] ? 'Completed ' . date('M j', strtotime($task['completed_at'])) : 'Completed'; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($tasksByStatus['done'])): ?>
                        <div class="text-center text-gray-500 text-sm py-8">No tasks</div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Add Task Modal -->
    <div id="addTaskModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6 rounded-2xl glass-panel border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white">New Task</h2>
            <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="tasks.php" id="taskForm">
                <input type="hidden" name="action" value="create_task">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Task Title *</label>
                        <input type="text" name="task_name" required class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none" placeholder="e.g. Fix Navigation Bug">
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
                            <label class="block text-sm text-gray-400 mb-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
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
                        <button type="submit" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Create Task</button>
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
            document.getElementById('addTaskModal').classList.remove('hidden');
            lucide.createIcons();
        }
        function closeModal() {
            document.getElementById('addTaskModal').classList.add('hidden');
            document.getElementById('taskForm').reset();
        }

        // Drag and Drop Logic
        function allowDrop(ev) {
            ev.preventDefault();
            const column = ev.target.closest('.kanban-column');
            if(column) {
                document.querySelectorAll('.kanban-column').forEach(c => c.classList.remove('drag-over'));
                column.classList.add('drag-over');
            }
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.target.classList.add('dragging');
        }

        function drop(ev) {
            ev.preventDefault();
            var data = ev.dataTransfer.getData("text");
            const draggedElement = document.getElementById(data);
            const taskId = draggedElement.getAttribute('data-task-id');
            
            // Remove dragging styles
            draggedElement.classList.remove('dragging');
            document.querySelectorAll('.kanban-column').forEach(c => c.classList.remove('drag-over'));

            // Find the closest column content area
            const dropTarget = ev.target.closest('.kanban-column');
            
            if (dropTarget && taskId) {
                const newStatus = dropTarget.getAttribute('data-status');
                
                // Update task status via AJAX
                updateTaskStatus(taskId, newStatus, draggedElement, dropTarget);
            }
        }

        // Update task status via AJAX
        function updateTaskStatus(taskId, status, element, targetColumn) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('task_id', taskId);
            formData.append('status', status);

            fetch('tasks.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Move element to new column
                    targetColumn.appendChild(element);
                    
                    // Update styling based on status
                    if(status === 'done') {
                        element.classList.add('opacity-70');
                        const title = element.querySelector('h4');
                        if (title) {
                            title.classList.add('line-through', 'text-gray-300');
                            title.classList.remove('text-white');
                        }
                    } else {
                        element.classList.remove('opacity-70');
                        const title = element.querySelector('h4');
                        if (title) {
                            title.classList.remove('line-through', 'text-gray-300');
                            title.classList.add('text-white');
                        }
                    }
                    
                    // Update task counts
                    updateTaskCounts();
                } else {
                    alert('Failed to update task status');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update task status');
                location.reload();
            });
        }

        // Update task counts (simple reload for now)
        function updateTaskCounts() {
            // Could implement AJAX to update counts without reload
            // For now, we'll just reload after a short delay
            setTimeout(() => {
                location.reload();
            }, 500);
        }

        // Filter tasks
        function filterTasks() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');
            
            taskCards.forEach(card => {
                const taskName = card.querySelector('h4').textContent.toLowerCase();
                const taskDesc = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
                
                if (taskName.includes(searchTerm) || taskDesc.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Sort tasks (placeholder - would need server-side implementation)
        function sortTasks() {
            // This would require server-side sorting
            location.reload();
        }

        // Clean up drag styles if dropped outside
        document.addEventListener('dragend', function(e) {
            if(e.target.classList.contains('task-card')) {
                 e.target.classList.remove('dragging');
                 document.querySelectorAll('.kanban-column').forEach(c => c.classList.remove('drag-over'));
            }
        });
    </script>
</body>
</html>
