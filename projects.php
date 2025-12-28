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
            <a href="tasks.html" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="font-medium">My Tasks</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                <span class="font-medium">Projects</span>
                <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full text-white">5</span>
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

                <!-- Project Card 1: Web App -->
                <div class="glass-panel p-6 rounded-2xl project-card group cursor-pointer relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                            <i data-lucide="globe" class="w-6 h-6"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-400 border border-green-500/20">Active</span>
                            <button class="text-gray-500 hover:text-white"><i data-lucide="more-vertical" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-cyan-400 transition-colors">SaaS Dashboard Revamp</h3>
                    <p class="text-gray-400 text-sm mb-6 line-clamp-2 h-10">Redesigning the main analytics dashboard for better user experience and performance.</p>
                    
                    <div class="mb-6">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-400">Progress</span>
                            <span class="text-white font-medium">75%</span>
                        </div>
                        <div class="w-full bg-gray-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-full rounded-full" style="width: 75%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-white/5 pt-4">
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&fit=crop" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&fit=crop" alt="User">
                            <div class="w-8 h-8 rounded-full border-2 border-[#050510] bg-gray-700 flex items-center justify-center text-xs text-white">+2</div>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <i data-lucide="calendar" class="w-3 h-3"></i> Nov 24
                        </div>
                    </div>
                </div>

                <!-- Project Card 2: Mobile App -->
                <div class="glass-panel p-6 rounded-2xl project-card group cursor-pointer relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
                            <i data-lucide="smartphone" class="w-6 h-6"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Planning</span>
                            <button class="text-gray-500 hover:text-white"><i data-lucide="more-vertical" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-400 transition-colors">TaskFlow Mobile</h3>
                    <p class="text-gray-400 text-sm mb-6 line-clamp-2 h-10">Native mobile application for iOS and Android with offline capabilities.</p>
                    
                    <div class="mb-6">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-400">Progress</span>
                            <span class="text-white font-medium">15%</span>
                        </div>
                        <div class="w-full bg-gray-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-full rounded-full" style="width: 15%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-white/5 pt-4">
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?w=100&fit=crop" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&fit=crop" alt="User">
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <i data-lucide="calendar" class="w-3 h-3"></i> Dec 10
                        </div>
                    </div>
                </div>

                <!-- Project Card 3: Marketing -->
                <div class="glass-panel p-6 rounded-2xl project-card group cursor-pointer relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl bg-pink-500/10 text-pink-400 border border-pink-500/20">
                            <i data-lucide="megaphone" class="w-6 h-6"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-500/10 text-gray-400 border border-gray-500/20">On Hold</span>
                            <button class="text-gray-500 hover:text-white"><i data-lucide="more-vertical" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-pink-400 transition-colors">Holiday Campaign</h3>
                    <p class="text-gray-400 text-sm mb-6 line-clamp-2 h-10">Q4 marketing materials and social media strategy for holiday season.</p>
                    
                    <div class="mb-6">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-400">Progress</span>
                            <span class="text-white font-medium">45%</span>
                        </div>
                        <div class="w-full bg-gray-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-pink-500 to-red-500 h-full rounded-full" style="width: 45%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-white/5 pt-4">
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=100&fit=crop" alt="User">
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <i data-lucide="calendar" class="w-3 h-3"></i> TBD
                        </div>
                    </div>
                </div>

                <!-- Project Card 4: Design System -->
                <div class="glass-panel p-6 rounded-2xl project-card group cursor-pointer relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl bg-orange-500/10 text-orange-400 border border-orange-500/20">
                            <i data-lucide="pen-tool" class="w-6 h-6"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">Review</span>
                            <button class="text-gray-500 hover:text-white"><i data-lucide="more-vertical" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-orange-400 transition-colors">Aurora Design System</h3>
                    <p class="text-gray-400 text-sm mb-6 line-clamp-2 h-10">Standardizing UI components across all company products.</p>
                    
                    <div class="mb-6">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-400">Progress</span>
                            <span class="text-white font-medium">90%</span>
                        </div>
                        <div class="w-full bg-gray-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 to-yellow-500 h-full rounded-full" style="width: 90%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-white/5 pt-4">
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&fit=crop" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=100&fit=crop" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-[#050510]" src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?w=100&fit=crop" alt="User">
                            <div class="w-8 h-8 rounded-full border-2 border-[#050510] bg-gray-700 flex items-center justify-center text-xs text-white">+4</div>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <i data-lucide="calendar" class="w-3 h-3"></i> Oct 30
                        </div>
                    </div>
                </div>

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
            <form>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Project Name</label>
                        <input type="text" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none" placeholder="e.g. Website Redesign">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Category</label>
                            <select class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                                <option>Development</option>
                                <option>Marketing</option>
                                <option>Design</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Due Date</label>
                            <input type="date" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white h-24 outline-none" placeholder="Brief description of the project..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeModal()">Cancel</button>
                        <button type="button" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Create Project</button>
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
            document.getElementById('addProjectModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('addProjectModal').classList.add('hidden');
        }
    </script>
</body>
</html>