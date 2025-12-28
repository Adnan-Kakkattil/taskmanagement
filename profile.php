<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - TaskFlow</title>
    
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

        /* Input Fields */
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

        /* Avatar Overlay */
        .avatar-group:hover .avatar-overlay {
            opacity: 1;
        }
        .avatar-overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
            background: rgba(0,0,0,0.6);
        }

        /* Toggle Switch */
        .toggle-checkbox:checked {
            right: 0;
            border-color: var(--primary);
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: var(--primary);
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
            <a href="team.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Team</span>
            </a>
            <a href="calender.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="calendar" class="w-5 h-5"></i>
                <span class="font-medium">Calendar</span>
            </a>
        </nav>

        <!-- User Profile (Bottom) - Active State -->
        <div class="p-4 border-t border-white/5">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 border border-white/5 cursor-pointer transition-colors">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-cyan-500 p-[2px]">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop" class="w-full h-full rounded-full object-cover border-2 border-[#050510]" alt="User">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">Alex Morgan</p>
                    <p class="text-xs text-gray-400 truncate">Pro Member</p>
                </div>
                <i data-lucide="settings" class="w-4 h-4 text-cyan-400"></i>
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
                <h1 class="text-xl font-bold hidden sm:block">My Profile</h1>
            </div>

            <div class="flex items-center gap-4">
                 <button class="p-2 rounded-full hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>
                <a href="login.php" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-300 font-medium px-4 py-2 rounded-lg hover:bg-red-400/10 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Sign Out
                </a>
            </div>
        </header>

        <!-- Profile Content -->
        <div class="flex-1 overflow-y-auto p-6">
            
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Identity Card -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Main Card -->
                    <div class="glass-panel rounded-2xl p-8 flex flex-col items-center text-center relative overflow-hidden">
                        <!-- Banner bg effect -->
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-cyan-500/20 to-transparent"></div>
                        
                        <div class="relative mb-4 avatar-group cursor-pointer group">
                            <div class="w-32 h-32 rounded-full p-1 bg-gradient-to-tr from-cyan-400 to-purple-600">
                                <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=400&auto=format&fit=crop" class="w-full h-full rounded-full object-cover border-4 border-[#050510]" alt="User">
                            </div>
                            <div class="absolute inset-0 rounded-full flex items-center justify-center avatar-overlay">
                                <i data-lucide="camera" class="w-8 h-8 text-white"></i>
                            </div>
                            <div class="absolute bottom-1 right-1 w-6 h-6 bg-green-500 border-4 border-[#050510] rounded-full"></div>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-white mb-1">Alex Morgan</h2>
                        <p class="text-cyan-400 font-medium mb-6">Senior Product Designer</p>
                        
                        <div class="flex gap-4 w-full mb-6">
                            <div class="flex-1 bg-white/5 rounded-xl p-3">
                                <div class="text-2xl font-bold text-white">124</div>
                                <div class="text-xs text-gray-400 uppercase tracking-wider">Tasks</div>
                            </div>
                            <div class="flex-1 bg-white/5 rounded-xl p-3">
                                <div class="text-2xl font-bold text-white">12</div>
                                <div class="text-xs text-gray-400 uppercase tracking-wider">Projects</div>
                            </div>
                        </div>

                        <div class="w-full space-y-3">
                            <div class="flex items-center gap-3 text-sm text-gray-400">
                                <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                                alex.morgan@taskflow.com
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-400">
                                <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
                                San Francisco, CA
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-400">
                                <i data-lucide="calendar-days" class="w-4 h-4 text-gray-500"></i>
                                Joined March 2021
                            </div>
                        </div>
                    </div>

                    <!-- Skills / Tags -->
                    <div class="glass-panel rounded-2xl p-6">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Skills</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 rounded-full text-xs bg-purple-500/10 text-purple-400 border border-purple-500/20">UI Design</span>
                            <span class="px-3 py-1 rounded-full text-xs bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">Figma</span>
                            <span class="px-3 py-1 rounded-full text-xs bg-green-500/10 text-green-400 border border-green-500/20">Prototyping</span>
                            <span class="px-3 py-1 rounded-full text-xs bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">User Research</span>
                            <span class="px-3 py-1 rounded-full text-xs bg-pink-500/10 text-pink-400 border border-pink-500/20">HTML/CSS</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings Form -->
                <div class="lg:col-span-2">
                    <div class="glass-panel rounded-2xl overflow-hidden">
                        
                        <!-- Tabs -->
                        <div class="flex border-b border-white/5 bg-white/5">
                            <button class="px-6 py-4 text-sm font-medium text-cyan-400 border-b-2 border-cyan-400 bg-white/5">
                                General Info
                            </button>
                            <button class="px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                                Security
                            </button>
                            <button class="px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                                Notifications
                            </button>
                        </div>

                        <!-- Content Area -->
                        <div class="p-8">
                            <form>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">First Name</label>
                                        <input type="text" value="Alex" class="w-full rounded-lg px-4 py-3 glass-input text-sm focus:ring-0">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">Last Name</label>
                                        <input type="text" value="Morgan" class="w-full rounded-lg px-4 py-3 glass-input text-sm focus:ring-0">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">Email Address</label>
                                        <input type="email" value="alex.morgan@taskflow.com" class="w-full rounded-lg px-4 py-3 glass-input text-sm focus:ring-0">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">Phone Number</label>
                                        <input type="tel" value="+1 (555) 123-4567" class="w-full rounded-lg px-4 py-3 glass-input text-sm focus:ring-0">
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm text-gray-400 mb-2">Bio</label>
                                    <textarea class="w-full rounded-lg px-4 py-3 glass-input text-sm h-32 focus:ring-0 resize-none">Passionate about creating intuitive user experiences and bridging the gap between design and engineering.</textarea>
                                </div>

                                <div class="mb-8">
                                    <label class="block text-sm text-gray-400 mb-2">Role & Department</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <input type="text" value="Senior Product Designer" disabled class="w-full rounded-lg px-4 py-3 glass-input text-sm opacity-60 cursor-not-allowed">
                                        <input type="text" value="Design Team" disabled class="w-full rounded-lg px-4 py-3 glass-input text-sm opacity-60 cursor-not-allowed">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Contact admin to change role details
                                    </p>
                                </div>

                                <div class="flex items-center justify-end gap-4 pt-6 border-t border-white/5">
                                    <button type="button" class="px-6 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancel</button>
                                    <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-bold bg-cyan-500 text-black hover:bg-cyan-400 shadow-[0_0_15px_rgba(0,243,255,0.3)] transition-all">Save Changes</button>
                                </div>
                            </form>
                        </div>

                    </div>

                    <!-- Security Preview (Static for visual) -->
                    <div class="glass-panel rounded-2xl p-8 mt-6">
                         <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-white">Password & Security</h3>
                                <p class="text-sm text-gray-400">Manage your password and 2FA settings</p>
                            </div>
                            <button class="text-cyan-400 text-sm hover:text-cyan-300">Edit</button>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 rounded-xl bg-white/5">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-white/5">
                                        <i data-lucide="key" class="w-5 h-5 text-gray-300"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">Password</p>
                                        <p class="text-xs text-gray-500">Last changed 3 months ago</p>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400">••••••••••••</span>
                            </div>

                             <div class="flex items-center justify-between p-4 rounded-xl bg-white/5">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-white/5">
                                        <i data-lucide="shield-check" class="w-5 h-5 text-green-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">Two-Factor Authentication</p>
                                        <p class="text-xs text-gray-500">Enabled via Authenticator App</p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <div class="w-10 h-6 bg-cyan-500 rounded-full shadow-inner"></div>
                                    <div class="absolute right-0 top-0 block w-6 h-6 bg-white rounded-full shadow border-2 border-cyan-500 transform scale-110"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
    </script>
</body>
</html>