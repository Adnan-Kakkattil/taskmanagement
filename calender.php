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
            <a href="projects.html" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                <span class="font-medium">Projects</span>
            </a>
            <a href="team.html" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-all">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="font-medium">Team</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-cyan-400 bg-cyan-400/10 rounded-xl border border-cyan-400/20 transition-all">
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
                <div class="flex flex-col">
                    <h1 class="text-xl font-bold hidden sm:block">Calendar</h1>
                    <span class="text-xs text-gray-400 hidden sm:block">October 2023</span>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-4">
                <div class="flex items-center bg-white/5 rounded-lg border border-white/10 p-1">
                    <button class="p-1.5 hover:bg-white/10 rounded-md text-gray-400 hover:text-white">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
                    <span class="px-3 text-sm font-medium">Today</span>
                    <button class="p-1.5 hover:bg-white/10 rounded-md text-gray-400 hover:text-white">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
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
                
                <!-- Previous Month -->
                <div class="calendar-day inactive">
                    <div class="day-number text-sm">29</div>
                </div>
                <div class="calendar-day inactive">
                    <div class="day-number text-sm">30</div>
                </div>

                <!-- Current Month (October) -->
                <!-- Day 1 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">1</div>
                    <div class="event-chip event-meeting">10:00 Team Sync</div>
                </div>
                
                <!-- Day 2 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">2</div>
                </div>

                <!-- Day 3 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">3</div>
                    <div class="event-chip event-task">Frontend Fixes</div>
                </div>

                <!-- Day 4 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">4</div>
                    <div class="event-chip event-meeting">14:00 Client Call</div>
                </div>

                <!-- Day 5 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">5</div>
                </div>

                <!-- Day 6 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">6</div>
                    <div class="event-chip event-urgent">Deploy v2.1</div>
                </div>

                <!-- Day 7 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">7</div>
                </div>

                <!-- Day 8 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">8</div>
                </div>

                <!-- Day 9 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">9</div>
                    <div class="event-chip event-meeting">Weekly Review</div>
                </div>

                <!-- Day 10 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">10</div>
                </div>

                <!-- Day 11 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">11</div>
                </div>

                <!-- Day 12 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">12</div>
                    <div class="event-chip event-task">Design Handoff</div>
                    <div class="event-chip event-meeting">15:30 Sprint Planning</div>
                </div>

                <!-- Day 13 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">13</div>
                </div>

                <!-- Day 14 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">14</div>
                </div>

                <!-- Day 15 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">15</div>
                </div>

                <!-- Day 16 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">16</div>
                    <div class="event-chip event-meeting">11:00 Marketing Sync</div>
                </div>

                <!-- Day 17 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">17</div>
                </div>

                <!-- Day 18 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">18</div>
                    <div class="event-chip event-urgent">Server Maintenance</div>
                </div>

                <!-- Day 19 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">19</div>
                </div>

                <!-- Day 20 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">20</div>
                    <div class="event-chip event-task">Code Review</div>
                </div>

                <!-- Day 21 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">21</div>
                </div>

                <!-- Day 22 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">22</div>
                </div>

                <!-- Day 23 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">23</div>
                    <div class="event-chip event-meeting">Weekly Review</div>
                </div>

                <!-- Day 24 -->
                <div class="calendar-day today">
                    <div class="day-number text-sm mb-1">24</div>
                    <div class="event-chip event-task">DB Migration</div>
                    <div class="event-chip event-meeting">16:00 All Hands</div>
                </div>

                <!-- Day 25 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">25</div>
                </div>

                <!-- Day 26 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">26</div>
                </div>

                <!-- Day 27 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">27</div>
                </div>

                <!-- Day 28 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">28</div>
                </div>

                <!-- Day 29 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">29</div>
                </div>

                <!-- Day 30 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">30</div>
                    <div class="event-chip event-task">Month End Report</div>
                </div>

                <!-- Day 31 -->
                <div class="calendar-day">
                    <div class="day-number text-sm mb-1">31</div>
                    <div class="event-chip event-meeting">Halloween Party 🎃</div>
                </div>

                <!-- Next Month -->
                <div class="calendar-day inactive">
                    <div class="day-number text-sm">1</div>
                </div>
                 <div class="calendar-day inactive">
                    <div class="day-number text-sm">2</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Event Modal -->
    <div id="addEventModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-6 rounded-2xl glass-panel border border-white/10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white">Add New Event</h2>
            <form>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Event Title</label>
                        <input type="text" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-cyan-500 outline-none" placeholder="e.g. Project Sync">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Type</label>
                            <select class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                                <option>Meeting</option>
                                <option>Task</option>
                                <option>Urgent</option>
                                <option>Personal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Date</label>
                            <input type="date" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Start Time</label>
                            <input type="time" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">End Time</label>
                            <input type="time" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white h-24 outline-none" placeholder="Add details or meeting link..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5" onclick="closeModal()">Cancel</button>
                        <button type="button" class="px-6 py-2 rounded-lg bg-cyan-500 text-black font-bold hover:bg-cyan-400">Save Event</button>
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
            document.getElementById('addEventModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('addEventModal').classList.add('hidden');
        }
    </script>
</body>
</html>