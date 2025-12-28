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

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 z-20 mobile-overlay hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 glass-panel border-r border-r-white/5 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto flex flex-col sidebar bg-[#050510]">
        <!-- Brand -->
        <div class="flex items-center justify-center h-20 border-b border-white/5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-gradient-to-br from-cyan-400 to-purple-600 flex items-center justify-center font-bold text-white">T</div>
                <span class="font-bold text-xl tracking-wider text-white">Task<span class="text-cyan-400">Flow</span></span>
            </div>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.html" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="font-medium">My Tasks</span>
                <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white">12</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                <span class="font-medium">Projects</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Team</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="calendar" class="w-5 h-5"></i>
                <span class="font-medium">Calendar</span>
            </a>
        </nav>

        <!-- User Profile (Bottom) -->
        <div class="p-4 border-t border-white/5">
            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 cursor-pointer transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-cyan-500 p-[2px]">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop" class="w-full h-full rounded-full object-cover border-2 border-[#050510]" alt="User">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">Alex Morgan</p>
                    <p class="text-xs text-gray-400 truncate">Pro Member</p>
                </div>
                <i data-lucide="log-out" class="w-4 h-4 text-gray-500 hover:text-red-400 transition-colors"></i>
            </div>
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
                <input type="text" placeholder="Filter tasks..." class="bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-cyan-500 w-full sm:w-64 transition-all">
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <span class="text-xs text-gray-500 font-medium uppercase mr-2">Sort By:</span>
                <select class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-cyan-500">
                    <option>Priority</option>
                    <option>Due Date</option>
                    <option>Created Date</option>
                </select>
                <div class="h-6 w-px bg-white/10 mx-2"></div>
                <div class="flex -space-x-2">
                    <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&fit=crop" alt="User">
                    <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&fit=crop" alt="User">
                    <div class="w-8 h-8 rounded-full border-2 border-[#050510] bg-gray-700 flex items-center justify-center text-xs text-white">+3</div>
                </div>
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
                            <span class="bg-white/10 text-xs px-2 py-0.5 rounded text-gray-400">4</span>
                        </div>
                        <button class="text-gray-500 hover:text-white"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    </div>
                    
                    <!-- Draggable Area -->
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 kanban-column" id="todo" ondrop="drop(event)" ondragover="allowDrop(event)">
                        
                        <!-- Task Card 1 -->
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 group" draggable="true" ondragstart="drag(event)" id="task-1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-red-400 bg-red-400/10 px-2 py-0.5 rounded">High</span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-white mb-1">Database Schema Redesign</h4>
                            <p class="text-sm text-gray-400 mb-3 line-clamp-2">Optimize the user tables for faster query performance on the dashboard.</p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <div class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> Oct 24</div>
                                <div class="flex items-center gap-1"><i data-lucide="message-square" class="w-3 h-3"></i> 3</div>
                            </div>
                        </div>

                        <!-- Task Card 2 -->
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 group" draggable="true" ondragstart="drag(event)" id="task-2">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-green-400 bg-green-400/10 px-2 py-0.5 rounded">Low</span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-white mb-1">Update dependencies</h4>
                            <p class="text-sm text-gray-400 mb-3 line-clamp-2">Check npm audit and fix vulnerability warnings in the frontend repo.</p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <div class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> Oct 28</div>
                                <div class="flex -space-x-1">
                                    <div class="w-5 h-5 rounded-full bg-blue-500"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Column: In Progress -->
                <div class="flex-1 flex flex-col glass-column rounded-2xl min-w-[300px] h-full">
                    <div class="p-4 border-b border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                            <h3 class="font-bold text-gray-200">In Progress</h3>
                            <span class="bg-white/10 text-xs px-2 py-0.5 rounded text-gray-400">2</span>
                        </div>
                        <button class="text-gray-500 hover:text-white"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    </div>
                    
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 kanban-column" id="progress" ondrop="drop(event)" ondragover="allowDrop(event)">
                        
                        <!-- Task Card 3 -->
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 group" draggable="true" ondragstart="drag(event)" id="task-3">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-yellow-400 bg-yellow-400/10 px-2 py-0.5 rounded">Medium</span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-white mb-1">API Authentication</h4>
                            <p class="text-sm text-gray-400 mb-3 line-clamp-2">Implement JWT token refresh mechanism for the user session.</p>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-700 h-1.5 rounded-full mb-3">
                                <div class="bg-cyan-400 h-1.5 rounded-full" style="width: 60%"></div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <div class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> Tomorrow</div>
                                <div class="flex items-center gap-1"><i data-lucide="paperclip" class="w-3 h-3"></i> 2</div>
                            </div>
                        </div>

                         <!-- Task Card 4 -->
                         <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 group" draggable="true" ondragstart="drag(event)" id="task-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-red-400 bg-red-400/10 px-2 py-0.5 rounded">High</span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-white mb-1">Mobile Responsive Fixes</h4>
                            <p class="text-sm text-gray-400 mb-3 line-clamp-2">Sidebar not collapsing correctly on iPhone 12 Safari.</p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <div class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> Today</div>
                                <div class="flex -space-x-1">
                                    <div class="w-5 h-5 rounded-full bg-purple-500"></div>
                                    <div class="w-5 h-5 rounded-full bg-pink-500"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Column: Done -->
                <div class="flex-1 flex flex-col glass-column rounded-2xl min-w-[300px] h-full">
                    <div class="p-4 border-b border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <h3 class="font-bold text-gray-200">Done</h3>
                            <span class="bg-white/10 text-xs px-2 py-0.5 rounded text-gray-400">12</span>
                        </div>
                        <button class="text-gray-500 hover:text-white"><i data-lucide="check" class="w-4 h-4"></i></button>
                    </div>
                    
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 kanban-column" id="done" ondrop="drop(event)" ondragover="allowDrop(event)">
                        
                        <!-- Task Card 5 -->
                        <div class="task-card glass-panel p-4 rounded-xl border border-white/5 hover:border-cyan-500/50 opacity-70 group" draggable="true" ondragstart="drag(event)" id="task-5">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-green-400 bg-green-400/10 px-2 py-0.5 rounded">Low</span>
                                <button class="text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                            <h4 class="font-bold text-gray-300 line-through mb-1">Setup Repo</h4>
                            <p class="text-sm text-gray-500 mb-3 line-clamp-2">Initialize Git and configure .gitignore.</p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                                <div class="flex items-center gap-1 text-green-400"><i data-lucide="check-circle-2" class="w-3 h-3"></i> Completed</div>
                            </div>
                        </div>

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
            <form>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Task Title</label>
                        <input type="text" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none" placeholder="e.g. Fix Navigation Bug">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Priority</label>
                            <select class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                                <option>Low</option>
                                <option>Medium</option>
                                <option>High</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Due Date</label>
                            <input type="date" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white h-24 outline-none" placeholder="Add details..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeModal()">Cancel</button>
                        <button type="button" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Create Task</button>
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
            document.getElementById('addTaskModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('addTaskModal').classList.add('hidden');
        }

        // Drag and Drop Logic
        function allowDrop(ev) {
            ev.preventDefault();
            // Optional: Add visual cue for drop target
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
            
            // Remove dragging styles
            draggedElement.classList.remove('dragging');
            document.querySelectorAll('.kanban-column').forEach(c => c.classList.remove('drag-over'));

            // Find the closest column content area
            const dropTarget = ev.target.closest('.kanban-column');
            
            if (dropTarget) {
                dropTarget.appendChild(draggedElement);
                
                // Optional: Update styling based on column
                if(dropTarget.id === 'done') {
                    draggedElement.classList.add('opacity-70');
                    draggedElement.querySelector('h4').classList.add('line-through', 'text-gray-300');
                    draggedElement.querySelector('h4').classList.remove('text-white');
                } else {
                    draggedElement.classList.remove('opacity-70');
                    draggedElement.querySelector('h4').classList.remove('line-through', 'text-gray-300');
                    draggedElement.querySelector('h4').classList.add('text-white');
                }
            }
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