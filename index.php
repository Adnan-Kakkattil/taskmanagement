<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Next Gen Task Management</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Three.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
            overflow-x: hidden;
            margin: 0;
        }

        /* Smooth Scroll Behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a1a;
        }
        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* 3D Canvas Background */
        #canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
            opacity: 0; /* Fade in on load */
            transition: opacity 1.5s ease;
        }

        /* Glassmorphism Utilities */
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .glass-nav {
            background: rgba(5, 5, 16, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Text Gradients */
        .text-gradient {
            background: linear-gradient(135deg, #fff 0%, #a5a5a5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .text-gradient-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Button Hover Effects */
        .btn-glow {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-glow:hover {
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.4);
            transform: translateY(-2px);
        }

        /* Card Hover Effects */
        .feature-card {
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            border-color: rgba(0, 243, 255, 0.3);
        }

        /* Floating Animation for 2D elements */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="antialiased">

    <!-- 3D Background -->
    <div id="canvas-container"></div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer" onclick="window.scrollTo(0,0)">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-cyan-400 to-purple-600 flex items-center justify-center font-bold text-white">T</div>
                    <span class="font-bold text-xl tracking-wider text-white">Task<span class="text-cyan-400">Flow</span></span>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="#features" class="hover:text-cyan-400 text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition-colors">Features</a>
                        <a href="#tech" class="hover:text-cyan-400 text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition-colors">Tech Stack</a>
                        <a href="#pricing" class="hover:text-cyan-400 text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition-colors">Pricing</a>
                        <a href="login.php" class="hover:text-cyan-400 text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition-colors">Log In</a>
                        <a href="signup.php" class="bg-cyan-500 hover:bg-cyan-600 text-black font-bold px-5 py-2 rounded-full text-sm transition-all btn-glow">Get Started</a>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none" aria-controls="mobile-menu" aria-expanded="false" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden md:hidden glass" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#features" class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Features</a>
                <a href="#tech" class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Tech Stack</a>
                <a href="login.php" class="text-gray-300 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Log In</a>
                <a href="signup.php" class="bg-cyan-500 text-black block px-3 py-2 rounded-md text-base font-medium mt-4">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            
            <div class="inline-block px-4 py-1 mb-6 rounded-full glass border-cyan-500/30 animate-float">
                <span class="text-cyan-400 text-sm font-semibold tracking-wider uppercase">v2.0 Now Available</span>
            </div>

            <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold tracking-tight mb-8">
                Organize Chaos.<br>
                <span class="text-gradient-primary">Master Productivity.</span>
            </h1>
            
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-400 mb-10 leading-relaxed">
                The PHP-powered task management solution designed for developers and teams who demand speed, security, and simplicity.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="signup.php" class="px-8 py-4 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold text-lg btn-glow shadow-lg shadow-cyan-500/20">
                    Start for Free
                </a>
                <a href="#demo" class="px-8 py-4 rounded-full glass hover:bg-white/10 text-white font-bold text-lg transition-all flex items-center justify-center gap-2 group">
                    View Demo
                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                </a>
            </div>

            <!-- Stats -->
            <div class="mt-20 grid grid-cols-2 gap-8 md:grid-cols-4 border-t border-gray-800 pt-10">
                <div class="glass p-4 rounded-xl">
                    <div class="text-3xl font-bold text-white">10k+</div>
                    <div class="text-sm text-gray-400 mt-1">Active Users</div>
                </div>
                <div class="glass p-4 rounded-xl">
                    <div class="text-3xl font-bold text-cyan-400">99.9%</div>
                    <div class="text-sm text-gray-400 mt-1">Uptime</div>
                </div>
                <div class="glass p-4 rounded-xl">
                    <div class="text-3xl font-bold text-purple-400">PHP 8</div>
                    <div class="text-sm text-gray-400 mt-1">Modern Core</div>
                </div>
                <div class="glass p-4 rounded-xl">
                    <div class="text-3xl font-bold text-white">0.2s</div>
                    <div class="text-sm text-gray-400 mt-1">Latency</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="text-base text-cyan-400 font-semibold tracking-wide uppercase">Core Capabilities</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                    Everything you need to ship faster.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass p-8 rounded-2xl feature-card fade-in-up delay-100 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center mb-6 text-2xl text-cyan-400 border border-gray-700">
                        🔒
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Secure Authentication</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Built with robust PHP session management and bcrypt password hashing. Your data stays yours.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass p-8 rounded-2xl feature-card fade-in-up delay-200 relative overflow-hidden group">
                     <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center mb-6 text-2xl text-purple-400 border border-gray-700">
                        ⚡
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Real-time CRUD</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Create, Read, Update, and Delete tasks instantly. Optimized MySQL queries ensure zero lag.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass p-8 rounded-2xl feature-card fade-in-up delay-300 relative overflow-hidden group">
                     <div class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center mb-6 text-2xl text-pink-400 border border-gray-700">
                        📊
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Smart Analytics</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Visual dashboards showing pending vs completed tasks. Track your productivity velocity.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Stack Parallax Section -->
    <section id="tech" class="py-24 relative overflow-hidden">
        <!-- Parallax Background Element -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-cyan-900/20 to-transparent pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="fade-in-up">
                    <h2 class="text-4xl font-bold text-white mb-6">Built on a <span class="text-gradient-primary">Solid Foundation</span></h2>
                    <p class="text-gray-400 text-lg mb-8 leading-relaxed">
                        We don't rely on bloated frameworks. TaskFlow is built on pure, optimized PHP and MySQL, ensuring it runs on any server with minimal configuration.
                    </p>
                    
                    <ul class="space-y-4">
                        <li class="flex items-center text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center mr-3 text-xs">✓</span>
                            PDO & Prepared Statements
                        </li>
                        <li class="flex items-center text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center mr-3 text-xs">✓</span>
                            MVC Architecture Pattern
                        </li>
                        <li class="flex items-center text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center mr-3 text-xs">✓</span>
                            Responsive Tailwind Design
                        </li>
                        <li class="flex items-center text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center mr-3 text-xs">✓</span>
                            CSRF Protection
                        </li>
                    </ul>
                </div>

                <div class="glass p-1 rounded-2xl fade-in-up delay-200">
                    <div class="bg-[#1e1e1e] rounded-xl p-6 font-mono text-sm overflow-hidden shadow-2xl">
                        <div class="flex gap-2 mb-4">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <div class="text-gray-400">
                            <span class="text-pink-500">class</span> <span class="text-yellow-300">TaskController</span> {<br>
                            &nbsp;&nbsp;<span class="text-cyan-400">public function</span> <span class="text-yellow-300">index</span>() {<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-blue-400">$tasks</span> = <span class="text-blue-400">$this</span>-><span class="text-green-400">model</span>-><span class="text-yellow-300">getAll</span>(<span class="text-blue-400">$_SESSION</span>[<span class="text-orange-400">'user_id'</span>]);<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-purple-400">return</span> <span class="text-blue-400">$this</span>-><span class="text-green-400">view</span>(<span class="text-orange-400">'dashboard'</span>, [<span class="text-orange-400">'tasks'</span> => <span class="text-blue-400">$tasks</span>]);<br>
                            &nbsp;&nbsp;}<br>
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="glass p-12 rounded-3xl border border-cyan-500/30 relative overflow-hidden fade-in-up">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-cyan-500 to-transparent"></div>
                
                <h2 class="text-4xl font-bold text-white mb-6">Ready to take control?</h2>
                <p class="text-xl text-gray-300 mb-10">
                    Join thousands of developers managing their workflow with TaskFlow. Open source and free for personal use.
                </p>
                <button class="bg-white text-black font-bold py-4 px-10 rounded-full hover:bg-gray-200 transition-colors btn-glow text-lg">
                    Download Source Code
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black py-12 border-t border-gray-800 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-2 mb-4 md:mb-0">
                <div class="w-6 h-6 rounded bg-gradient-to-br from-cyan-400 to-purple-600 flex items-center justify-center font-bold text-white text-xs">T</div>
                <span class="font-bold text-lg text-white">Task<span class="text-cyan-400">Flow</span></span>
            </div>
            <div class="text-gray-500 text-sm">
                &copy; 2025 TaskFlow Project. All rights reserved.
            </div>
            <div class="flex gap-6 mt-4 md:mt-0">
                <a href="#" class="text-gray-500 hover:text-white transition-colors">GitHub</a>
                <a href="#" class="text-gray-500 hover:text-white transition-colors">Twitter</a>
                <a href="#" class="text-gray-500 hover:text-white transition-colors">Docs</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // --- 1. Three.js Animation ---
        
        const scene = new THREE.Scene();
        // Fog for depth
        scene.fog = new THREE.FogExp2(0x050510, 0.002);

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        document.getElementById('canvas-container').appendChild(renderer.domElement);

        // Geometries
        const particlesGeometry = new THREE.BufferGeometry();
        const particlesCount = 700;
        
        const posArray = new Float32Array(particlesCount * 3);
        
        for(let i = 0; i < particlesCount * 3; i++) {
            // Spread particles across a wide area
            posArray[i] = (Math.random() - 0.5) * 20; 
        }
        
        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
        
        // Material for particles
        const particlesMaterial = new THREE.PointsMaterial({
            size: 0.03,
            color: 0x00f3ff,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending
        });
        
        const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
        scene.add(particlesMesh);

        // Abstract "Core" Shape (Icosahedron)
        const geometry = new THREE.IcosahedronGeometry(2, 0);
        const material = new THREE.MeshBasicMaterial({ 
            color: 0xbd00ff, 
            wireframe: true,
            transparent: true,
            opacity: 0.15
        });
        const coreShape = new THREE.Mesh(geometry, material);
        scene.add(coreShape);

        // Secondary Shape
        const geometry2 = new THREE.IcosahedronGeometry(3, 0);
        const material2 = new THREE.MeshBasicMaterial({ 
            color: 0x00f3ff, 
            wireframe: true,
            transparent: true,
            opacity: 0.05
        });
        const outerShape = new THREE.Mesh(geometry2, material2);
        scene.add(outerShape);

        camera.position.z = 5;

        // --- 2. Mouse Interaction & Scroll ---
        
        let mouseX = 0;
        let mouseY = 0;
        let targetX = 0;
        let targetY = 0;

        const windowHalfX = window.innerWidth / 2;
        const windowHalfY = window.innerHeight / 2;

        document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX - windowHalfX);
            mouseY = (event.clientY - windowHalfY);
        });

        // Resize handler
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Scroll handler for parallax
        let scrollY = 0;
        window.addEventListener('scroll', () => {
            scrollY = window.scrollY;
            
            // Fade in/out header based on scroll
            const nav = document.getElementById('navbar');
            if (scrollY > 50) {
                nav.classList.add('glass-nav');
                nav.classList.remove('bg-transparent');
            } else {
                nav.classList.remove('glass-nav');
                nav.classList.add('bg-transparent');
            }
        });

        // Animation Loop
        const clock = new THREE.Clock();

        const tick = () => {
            const elapsedTime = clock.getElapsedTime();

            targetX = mouseX * 0.001;
            targetY = mouseY * 0.001;

            // Rotation Logic
            particlesMesh.rotation.y = elapsedTime * 0.05 + (targetX * 0.5);
            particlesMesh.rotation.x = -mouseY * 0.0005;

            coreShape.rotation.x += 0.002;
            coreShape.rotation.y += 0.002;
            coreShape.rotation.z += 0.001;

            outerShape.rotation.x -= 0.001;
            outerShape.rotation.y -= 0.001;

            // Parallax effect on 3D objects based on scroll
            camera.position.y = -scrollY * 0.002;

            // Mouse ease
            particlesMesh.rotation.y += 0.05 * (targetX - particlesMesh.rotation.y);

            renderer.render(scene, camera);
            window.requestAnimationFrame(tick);
        }

        tick();

        // Reveal Canvas gracefully
        setTimeout(() => {
            document.getElementById('canvas-container').style.opacity = '1';
        }, 500);


        // --- 3. Scroll Reveal Animation (Intersection Observer) ---
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => {
            observer.observe(el);
        });

    </script>
</body>
</html>