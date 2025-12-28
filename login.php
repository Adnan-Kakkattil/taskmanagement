<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - TaskFlow</title>
    
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
                src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1964&auto=format&fit=crop" 
                alt="Abstract Neon Lines" 
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
                <h2 class="text-4xl font-bold mb-6">Welcome <br><span class="text-cyan-400">Back, Creator.</span></h2>
                <div class="space-y-4">
                    <p class="text-gray-300 text-lg max-w-md leading-relaxed">
                        Your workspace is ready. Pick up exactly where you left off and keep the momentum going.
                    </p>
                </div>
            </div>

            <div class="text-xs text-gray-400">
                &copy; 2025 TaskFlow Inc. Art by Unsplash.
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
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
                <h1 class="text-3xl font-bold mb-2">Log In</h1>
                <p class="text-gray-400">Enter your credentials to access your account.</p>
            </div>

            <form action="#" method="POST" class="space-y-6">
                
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
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-400">Password</label>
                        <a href="#" class="text-xs text-cyan-400 hover:text-cyan-300">Forgot Password?</a>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="password" placeholder="••••••••" class="w-full pl-12 pr-4 py-3 rounded-xl glass-input text-white placeholder-gray-600 focus:ring-0">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500/20">
                    <label for="remember" class="ml-2 block text-sm text-gray-400">
                        Remember me for 30 days
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="button" class="w-full py-4 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold text-lg btn-glow shadow-lg shadow-cyan-500/20 mt-4">
                    Sign In
                </button>
            </form>

            <!-- Signup Link -->
            <div class="text-center mt-8">
                <p class="text-gray-400 text-sm">
                    Don't have an account? 
                    <a href="signup.php" class="text-cyan-400 font-bold hover:text-cyan-300 transition-colors">Sign Up</a>
                </p>
            </div>
            
            <!-- Social Login -->
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