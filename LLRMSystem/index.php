<?php
/**
 * Root index file - Landing Page
 */
require_once __DIR__ . '/modules/core/config/config.php';

// Check if user is already logged in, if so, redirect to dashboard
session_start();
if (isset($_SESSION['user_id'])) {
    redirectToDashboard();
}

$pageTitle = "Home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#dc2626">
    <title><?php echo APP_NAME; ?> - City Government of Valenzuela</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animate on Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --val-red: #dc2626;
            --val-red-dark: #991b1b;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            scroll-behavior: smooth;
        }
        .hero-gradient {
            background: radial-gradient(circle at 50% -20%, #fee2e2 0%, #ffffff 60%, #f9fafb 100%);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .feature-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px -12px rgba(220, 38, 38, 0.15);
        }
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }
        .reveal-on-scroll.active {
            opacity: 1;
            transform: translateY(0);
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(4px);
        }
        .btn-modern {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-modern:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.4);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-[#fcfdfd] text-gray-900 overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-gray-100/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20">
                <div class="flex items-center group cursor-pointer">
                    <div class="relative">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-10 w-10 md:h-12 md:w-12 mr-3 transition-transform duration-500 group-hover:rotate-12 shadow-sm rounded-full" onerror="this.src='<?php echo BASE_URL; ?>/public/assets/images/valenzuela-logo.webp'">
                    </div>
                    <div>
                        <span class="text-xl md:text-2xl font-black text-[#002d72] tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
                        <div class="hidden md:flex items-center">
                            <span class="h-px w-4 bg-red-600 mr-2"></span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.2em] leading-none">Legislative Office</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2 md:space-x-6">
                    <a href="<?php echo LOGIN_URL; ?>" class="text-gray-600 hover:text-red-600 font-bold px-4 py-2 text-sm transition-all">Sign In</a>
                    <a href="<?php echo REGISTER_URL; ?>" class="btn-modern bg-red-600 hover:bg-red-700 text-white font-black px-6 py-2.5 rounded-full text-sm shadow-xl shadow-red-200/50">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden hero-gradient pt-32 pb-24 md:pt-48 md:pb-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <div data-aos="fade-down" class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-red-50 text-red-700 text-xs font-black mb-8 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-red-600 mr-2 animate-pulse"></span>
                    OFFICIAL LEGISLATIVE ARCHIVE
                </div>
                <h1 data-aos="fade-up" data-aos-delay="100" class="text-5xl md:text-7xl lg:text-8xl font-black text-gray-900 mb-8 tracking-tighter leading-[0.9]">
                    Preserving the <br class="hidden md:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-br from-red-600 to-red-900">Legislative Legacy.</span>
                </h1>
                <p data-aos="fade-up" data-aos-delay="200" class="max-w-2xl mx-auto text-lg md:text-2xl text-gray-500 mb-12 leading-relaxed font-medium">
                    A digital ecosystem for the City Government of Valenzuela to preserve, query, and analyze the legislative DNA of our community.
                </p>
                <div data-aos="fade-up" data-aos-delay="300" class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="<?php echo REGISTER_URL; ?>" class="w-full sm:w-auto btn-modern bg-gray-900 hover:bg-black text-white font-black px-12 py-5 rounded-2xl text-lg shadow-2xl">
                        Start Your Journey
                        <i class="bi bi-arrow-right-short ml-1 text-2xl align-middle"></i>
                    </a>
                    <a href="#features" class="w-full sm:w-auto btn-modern bg-white hover:bg-gray-50 text-gray-900 font-bold px-12 py-5 rounded-2xl text-lg border border-gray-200 shadow-sm">
                        View Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Abstract Decorations -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-red-50/50 to-transparent pointer-events-none"></div>
        <div class="absolute -bottom-48 left-1/2 -ml-[1000px] w-[2000px] h-[500px] bg-red-600/5 blur-[120px] rounded-full pointer-events-none"></div>
    </div>

    <!-- Features Section -->
    <section id="features" class="py-32 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-16 items-center mb-24">
                <div class="lg:col-span-12 text-center" data-aos="fade-up">
                    <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Core Architecture</h2>
                    <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-6">Designed for speed. <br>Built for reliability.</p>
                    <div class="h-2 w-20 bg-red-600 mx-auto rounded-full"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <!-- Feature 1: Document Management -->
                <div data-aos="fade-up" data-aos-delay="100" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-[100px] group-hover:bg-red-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-red-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-red-200 relative z-10 floating">
                        <i class="bi bi-stack"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Smart Archive</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Beyond simple storage. We categorize every ordinance and resolution with metadata that makes cataloging effortless and retrieval instantaneous.</p>
                </div>

                <!-- Feature 2: Advanced Search -->
                <div data-aos="fade-up" data-aos-delay="200" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-[100px] group-hover:bg-blue-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-blue-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-blue-200 relative z-10 floating" style="animation-delay: 0.5s">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Instant Query</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Our search engine indexed years of legislative history, delivering results in milliseconds. Filter by date, author, department, or keyword seamlessly.</p>
                </div>

                <!-- Feature 3: Analytics -->
                <div data-aos="fade-up" data-aos-delay="300" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-bl-[100px] group-hover:bg-green-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-green-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-green-200 relative z-10 floating" style="animation-delay: 1s">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Legislative IQ</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Convert dry data into strategic insights. Track which committees are most active and what policy trends are shaping the city's future.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Stats Section -->
    <section class="py-32 bg-[#0a0a0b] text-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div data-aos="zoom-out" class="max-w-4xl mx-auto text-center mb-24">
                <h2 class="text-red-500 font-black tracking-[0.3em] uppercase text-xs mb-8">System performance</h2>
                <p class="text-5xl md:text-7xl font-black tracking-tighter leading-none">Scalable. Efficient. <br>Driven by Accuracy.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">
                <div data-aos="fade-up" data-aos-delay="100" class="stat-card p-8 rounded-3xl text-center">
                    <div class="text-6xl font-black mb-4 tracking-tighter text-white">12,400+</div>
                    <div class="text-red-500 font-black uppercase tracking-widest text-[10px]">Indexed Records</div>
                </div>
                <div data-aos="fade-up" data-aos-delay="200" class="stat-card p-8 rounded-3xl text-center">
                    <div class="text-6xl font-black mb-4 tracking-tighter text-white">0.4ms</div>
                    <div class="text-red-500 font-black uppercase tracking-widest text-[10px]">Global Latency</div>
                </div>
                <div data-aos="fade-up" data-aos-delay="300" class="stat-card p-8 rounded-3xl text-center">
                    <div class="text-6xl font-black mb-4 tracking-tighter text-white">99.9%</div>
                    <div class="text-red-500 font-black uppercase tracking-widest text-[10px]">Data Reliability</div>
                </div>
                <div data-aos="fade-up" data-aos-delay="400" class="stat-card p-8 rounded-3xl text-center">
                    <div class="text-6xl font-black mb-4 tracking-tighter text-white">2.5k</div>
                    <div class="text-red-500 font-black uppercase tracking-widest text-[10px]">Daily Consults</div>
                </div>
            </div>
        </div>
        <!-- Decorative noise overlay -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: url('https://www.transparenttextures.com/patterns/p6.png');"></div>
    </section>

    <!-- CT section -->
    <section class="py-32 bg-white relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div data-aos="fade-up" class="bg-red-600 rounded-[60px] p-12 md:p-24 relative overflow-hidden shadow-2xl shadow-red-200">
                <!-- Background patterns -->
                <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                    <div class="absolute top-0 left-0 w-64 h-64 border-[40px] border-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 border-[60px] border-white rounded-full translate-x-1/3 translate-y-1/3"></div>
                </div>
                
                <h2 class="text-4xl md:text-6xl font-black text-white mb-8 tracking-tighter leading-none">Ready to shape <br>the future?</h2>
                <p class="text-red-100 text-lg md:text-xl mb-12 max-w-2xl mx-auto font-bold opacity-80">Secure your access to Valenzuela's official legislative portal and start managing records with precision.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 relative z-10">
                    <a href="<?php echo LOGIN_URL; ?>" class="btn-modern bg-white text-gray-900 font-black px-12 py-5 rounded-2xl text-lg shadow-xl">
                        <i class="bi bi-door-open-fill mr-2"></i>
                        Portal Login
                    </a>
                    <a href="<?php echo REGISTER_URL; ?>" class="btn-modern bg-red-900/40 text-white font-black px-12 py-5 rounded-2xl text-lg hover:bg-red-900 transition-all border border-red-400/30">
                        Register Account
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-20 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-12 md:mb-0">
                    <div class="flex items-center mb-6">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-12 w-12 mr-4 shadow-sm rounded-full">
                        <div class="text-[#002d72] font-black text-2xl tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></div>
                    </div>
                    <p class="text-gray-400 font-bold text-xs uppercase tracking-widest max-w-xs leading-loose">
                        Official Legislative Records Management System. <br>
                        City Government of Valenzuela.
                    </p>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-12 md:gap-24">
                    <div>
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6">System</h4>
                        <ul class="space-y-4 text-sm font-bold text-gray-500">
                            <li><a href="#features" class="hover:text-red-600 transition-colors">Features</a></li>
                            <li><a href="<?php echo LOGIN_URL; ?>" class="hover:text-red-600 transition-colors">Login</a></li>
                            <li><a href="<?php echo REGISTER_URL; ?>" class="hover:text-red-600 transition-colors">Register</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6">Legal</h4>
                        <ul class="space-y-4 text-sm font-bold text-gray-500">
                            <li><a href="#" class="hover:text-red-600 transition-colors">Privacy</a></li>
                            <li><a href="#" class="hover:text-red-600 transition-colors">Terms</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-20 pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">
                <div>© <?php echo date('Y'); ?> City of Valenzuela. Distributed for transparency.</div>
                <div class="mt-4 md:mt-0">Legislative Records Department • v1.0.0</div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Navigation scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-lg', 'bg-white/90');
                nav.classList.remove('glass-nav');
            } else {
                nav.classList.remove('shadow-lg', 'bg-white/90');
                nav.classList.add('glass-nav');
            }
        });
    </script>
</body>
</html>
