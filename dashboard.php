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
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="tasks.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="font-medium">My Tasks</span>
                <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white">12</span>
            </a>
            <a href="projects.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                <span class="font-medium">Projects</span>
            </a>
            <a href="team.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Team</span>
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
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop" class="w-full h-full rounded-full object-cover border-2 border-[#050510]" alt="User">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">Alex Morgan</p>
                    <p class="text-xs text-gray-400 truncate">Pro Member</p>
                </div>
                <a href="login.php" class="text-gray-500 hover:text-red-400 transition-colors">
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
                        <h3 class="text-3xl font-bold text-white">42</h3>
                        <p class="text-xs text-green-400 flex items-center gap-1 mt-2">
                            <i data-lucide="trending-up" class="w-3 h-3"></i> +12% from last week
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="clock" class="w-16 h-16 text-yellow-400"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm text-gray-400 font-medium mb-1">Pending</p>
                        <h3 class="text-3xl font-bold text-white">14</h3>
                        <p class="text-xs text-yellow-400 flex items-center gap-1 mt-2">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i> 3 overdue
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="zap" class="w-16 h-16 text-purple-400"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm text-gray-400 font-medium mb-1">In Progress</p>
                        <h3 class="text-3xl font-bold text-white">8</h3>
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
                        <h3 class="text-3xl font-bold text-white">20</h3>
                        <p class="text-xs text-green-400 flex items-center gap-1 mt-2">
                            <i data-lucide="trending-up" class="w-3 h-3"></i> +5 today
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column (Task Table) -->
                <div class="lg:col-span-2 glass-panel rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold">Recent Tasks</h2>
                        <button class="text-sm text-cyan-400 hover:text-cyan-300">View All</button>
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
                                <!-- Row 1 -->
                                <tr class="group hover:bg-white/5 transition-colors border-b border-white/5">
                                    <td class="py-4 pl-2 font-medium">Design System Update</td>
                                    <td class="py-4"><span class="flex items-center gap-1 status-high"><div class="w-2 h-2 rounded-full bg-red-400"></div> High</span></td>
                                    <td class="py-4"><span class="status-badge status-progress">In Progress</span></td>
                                    <td class="py-4 text-gray-400">Oct 24</td>
                                    <td class="py-4 text-right">
                                        <button class="p-1 hover:text-cyan-400 text-gray-400"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="group hover:bg-white/5 transition-colors border-b border-white/5">
                                    <td class="py-4 pl-2 font-medium">Integration API Fix</td>
                                    <td class="py-4"><span class="flex items-center gap-1 status-high"><div class="w-2 h-2 rounded-full bg-red-400"></div> High</span></td>
                                    <td class="py-4"><span class="status-badge status-pending">Pending</span></td>
                                    <td class="py-4 text-gray-400">Oct 25</td>
                                    <td class="py-4 text-right">
                                        <button class="p-1 hover:text-cyan-400 text-gray-400"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="group hover:bg-white/5 transition-colors border-b border-white/5">
                                    <td class="py-4 pl-2 font-medium">Client Meeting Prep</td>
                                    <td class="py-4"><span class="flex items-center gap-1 status-med"><div class="w-2 h-2 rounded-full bg-yellow-400"></div> Medium</span></td>
                                    <td class="py-4"><span class="status-badge status-done">Done</span></td>
                                    <td class="py-4 text-gray-400">Oct 22</td>
                                    <td class="py-4 text-right">
                                        <button class="p-1 hover:text-cyan-400 text-gray-400"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr class="group hover:bg-white/5 transition-colors">
                                    <td class="py-4 pl-2 font-medium">Homepage Animation</td>
                                    <td class="py-4"><span class="flex items-center gap-1 status-low"><div class="w-2 h-2 rounded-full bg-green-400"></div> Low</span></td>
                                    <td class="py-4"><span class="status-badge status-progress">In Progress</span></td>
                                    <td class="py-4 text-gray-400">Oct 30</td>
                                    <td class="py-4 text-right">
                                        <button class="p-1 hover:text-cyan-400 text-gray-400"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
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
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400">
                                    <span class="font-bold text-sm">24</span>
                                </div>
                                <div>
                                    <p class="font-medium">Database Migration</p>
                                    <p class="text-xs text-gray-400">Database Team • 10:00 AM</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                                    <span class="font-bold text-sm">26</span>
                                </div>
                                <div>
                                    <p class="font-medium">Quarterly Review</p>
                                    <p class="text-xs text-gray-400">Management • 02:00 PM</p>
                                </div>
                            </div>
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
                    <label class="block text-sm font-medium text-gray-400 mb-2">Project/Category</label>
                    <input type="text" name="project" class="w-full px-4 py-3 rounded-xl glass-input text-white placeholder-gray-500" placeholder="Optional project name">
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

        // Toggle Sidebar for Mobile
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

        // Initialize Chart.js
        const ctx = document.getElementById('taskChart').getContext('2d');
        
        // Gradient for Chart
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(0, 243, 255, 0.5)');
        gradient.addColorStop(1, 'rgba(0, 243, 255, 0)');

        const taskChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Tasks Completed',
                    data: [4, 6, 8, 5, 10, 7, 12],
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
            const taskData = {
                taskName: formData.get('taskName'),
                description: formData.get('description'),
                priority: formData.get('priority'),
                status: formData.get('status'),
                dueDate: formData.get('dueDate'),
                project: formData.get('project')
            };

            // Here you would normally send this to a backend API
            // For now, we'll just show a success message and close the modal
            console.log('New Task Created:', taskData);
            
            // Show success notification
            alert('Task "' + taskData.taskName + '" created successfully!');
            
            // Close modal and reset form
            closeTaskModal();
        }
    </script>
</body>
</html>