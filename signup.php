<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - TaskFlow</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary: #00f3ff;
            --secondary: #bd00ff;
            --bg-dark: #050510;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: var(--bg-dark);
            color: white;
            margin: 0;
        }

        /* Glassmorphism Input Fields */
        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.1);
        }

        /* Button Glow */
        .btn-glow {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-glow:hover {
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.4);
            transform: translateY(-2px);
        }

        /* Text Gradients */
        .text-gradient-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="h-screen w-full overflow-hidden flex">

    <!-- Left Side: Illustration -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img 
                src="https://images.unsplash.com/photo-1614850523459-c2f4c699c52e?q=80&w=2070&auto=format&fit=crop" 
                alt="Abstract Neon Fluid" 
                class="w-full h-full object-cover"
            >
            <!-- Overlay Gradient to match brand -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#050510]/90 to-[#050510]/40 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-[#050510]/20"></div>
        </div>

        <!-- Content over image -->
        <div class="relative z-10 w-full flex flex-col justify-between p-12">
            <a href="index.php" class="flex items-center gap-2 cursor-pointer">
                <div class="w-8 h-8 rounded bg-gradient-to-br from-cyan-400 to-purple-600 flex items-center justify-center font-bold text-white">T</div>
                <span class="font-bold text-xl tracking-wider text-white">Task<span class="text-cyan-400">Flow</span></span>
            </a>

            <div class="mb-12">
                <h2 class="text-4xl font-bold mb-6">Join the <br><span class="text-cyan-400">Future of Work.</span></h2>
                <div class="space-y-4">
                    <div class="flex items-center text-gray-200">
                        <div class="p-2 rounded-full bg-white/10 mr-4 backdrop-blur-md border border-white/10">
                            <i data-lucide="zap" class="w-5 h-5 text-yellow-400"></i>
                        </div>
                        <p class="font-medium">Boost productivity by 200%</p>
                    </div>
                    <div class="flex items-center text-gray-200">
                        <div class="p-2 rounded-full bg-white/10 mr-4 backdrop-blur-md border border-white/10">
                            <i data-lucide="shield" class="w-5 h-5 text-green-400"></i>
                        </div>
                        <p class="font-medium">Enterprise-grade security</p>
                    </div>
                    <div class="flex items-center text-gray-200">
                        <div class="p-2 rounded-full bg-white/10 mr-4 backdrop-blur-md border border-white/10">
                            <i data-lucide="users" class="w-5 h-5 text-purple-400"></i>
                        </div>
                        <p class="font-medium">Real-time collaboration</p>
                    </div>
                </div>
            </div>

            <div class="text-xs text-gray-400">
                &copy; 2025 TaskFlow Inc. Art by Unsplash.
            </div>
        </div>
    </div>

    <!-- Right Side: Signup Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-[#050510] relative">
        <!-- Mobile Background Blob -->
        <div class="lg:hidden absolute top-[-20%] right-[-20%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="lg:hidden absolute bottom-[-20%] left-[-20%] w-[500px] h-[500px] bg-cyan-600/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="max-w-md w-full relative z-10">
            <!-- Mobile Logo -->
            <a href="index.php" class="lg:hidden flex items-center justify-center gap-2 mb-8 cursor-pointer">
                <div class="w-8 h-8 rounded bg-gradient-to-br from-cyan-400 to-purple-600 flex items-center justify-center font-bold text-white">T</div>
                <span class="font-bold text-xl tracking-wider text-white">Task<span class="text-cyan-400">Flow</span></span>
            </a>

            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold mb-2">Create Account</h1>
                <p class="text-gray-400">Start organizing your life today.</p>
            </div>

            <form action="#" method="POST" class="space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Full Name</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="text" placeholder="John Doe" class="w-full pl-12 pr-4 py-3 rounded-xl glass-input text-white placeholder-gray-600 focus:ring-0">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="email" placeholder="john@example.com" class="w-full pl-12 pr-4 py-3 rounded-xl glass-input text-white placeholder-gray-600 focus:ring-0">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="password" placeholder="••••••••" class="w-full pl-12 pr-4 py-3 rounded-xl glass-input text-white placeholder-gray-600 focus:ring-0">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="lock-keyhole" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="password" placeholder="••••••••" class="w-full pl-12 pr-4 py-3 rounded-xl glass-input text-white placeholder-gray-600 focus:ring-0">
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-center">
                    <input id="terms" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500/20">
                    <label for="terms" class="ml-2 block text-sm text-gray-400">
                        I agree to the <a href="#" class="text-cyan-400 hover:text-cyan-300">Terms</a> and <a href="#" class="text-cyan-400 hover:text-cyan-300">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="button" class="w-full py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold text-lg btn-glow shadow-lg shadow-cyan-500/20 mt-4">
                    Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-8">
                <p class="text-gray-400 text-sm">
                    Already have an account? 
                    <a href="login.php" class="text-cyan-400 font-bold hover:text-cyan-300 transition-colors">Log In</a>
                </p>
            </div>
            
            <!-- Social Sign up (Optional) -->
            <div class="mt-8 pt-8 border-t border-gray-800">
                <div class="flex justify-center gap-4">
                    <button class="p-3 rounded-full bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6 h-6" alt="Google">
                    </button>
                    <button class="p-3 rounded-full bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                        <img src="https://www.svgrepo.com/show/448234/github.svg" class="w-6 h-6 invert" alt="GitHub">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Init Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>