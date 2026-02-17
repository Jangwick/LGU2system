<?php
/**
 * Root index file - Landing Page
 */
require_once __DIR__ . '/modules/core/config/config.php';
require_once __DIR__ . '/modules/dashboard/controllers/DashboardController.php';

// Check if user is already logged in
session_start();
if (isAuthenticated()) {
    redirectToDashboard();
}

$dashboardController = new DashboardController();
$perfStats = $dashboardController->getSystemPerformanceStats();

$pageTitle = "Home";
?>
<!DOCTYPE html>
<html lang="en">
<script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
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
    
    <!-- Swiper JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
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
        
        /* Continuous Carousel Smoothness */
        .recognition-swiper .swiper-wrapper {
            transition-timing-function: linear !important;
        }

        /* Landmarks Swiper Pagination */
        .landmarks-swiper .swiper-pagination-bullet {
            width: 30px;
            height: 3px;
            border-radius: 0;
            background: rgba(255, 255, 255, 0.3);
            opacity: 1;
            transition: all 0.3s ease;
            margin: 0 4px !important;
        }
        .landmarks-swiper .swiper-pagination-bullet-active {
            background: #fff;
            width: 40px;
        }

        /* =============================================
           GLOBAL DARK MODE THEME
           ============================================= */
        html.dark { color-scheme: dark; }
        html.dark body { background: #0a0a0a; color: #d4d4d4; }

        /* Smooth theme transitions */
        body, section, footer, nav, .feature-card,
        #mobile-landing-toggle, #mobile-landing-menu {
            transition: background-color 0.5s ease, border-color 0.4s ease;
        }

        /* --- Navigation --- */
        html.dark nav {
            background: rgba(10, 10, 10, 0.9) !important;
            border-bottom-color: #1a1a1a !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* --- Hero --- */
        html.dark .hero-gradient {
            background: radial-gradient(circle at 50% -20%, #1a0808 0%, #0a0a0a 60%, #0a0a0a 100%) !important;
        }
        html.dark .hero-gradient .inline-flex.rounded-full.bg-white {
            background: rgba(255,255,255,0.05) !important;
            border-color: rgba(220,38,38,0.2) !important;
        }
        html.dark .hero-gradient a.bg-white {
            background: #1a1a1a !important;
            border-color: #333 !important;
            color: #d4d4d4 !important;
        }

        /* --- All Sections --- */
        html.dark section { background: #0a0a0a !important; }
        html.dark #landmarks { background: #0f0f0f !important; }
        html.dark #search-showcase { background: linear-gradient(to bottom, #111, #0a0a0a) !important; }

        /* --- Footer --- */
        html.dark footer#legal-footer {
            background: #0a0a0a !important;
            border-top-color: #1a1a1a !important;
        }

        /* --- Mobile --- */
        html.dark #mobile-landing-toggle {
            background: rgba(15,15,15,0.95) !important;
            border-color: #333 !important;
            color: #d4d4d4 !important;
        }
        html.dark #mobile-landing-menu { background: #0a0a0a !important; }
        html.dark #mobile-landing-menu > .fixed {
            background: rgba(10,10,10,0.95) !important;
            border-color: #222 !important;
        }
        html.dark #mobile-landing-menu .text-slate-800 { color: #e5e5e5 !important; }

        /* --- Global Text Colors --- */
        html.dark .text-gray-900 { color: #e5e5e5 !important; }
        html.dark .text-\[\#002d72\] { color: #7eb3ff !important; }
        html.dark .text-gray-500 { color: #888 !important; }
        html.dark .text-gray-400 { color: #666 !important; }
        html.dark .text-gray-600 { color: #999 !important; }
        html.dark .text-slate-600 { color: #999 !important; }
        html.dark .text-slate-800 { color: #d4d4d4 !important; }

        /* CTA buttons inside red bg keep readable */
        html.dark .bg-red-600 a.text-gray-900 { color: #111 !important; }

        /* --- Feature Cards --- */
        html.dark .feature-card {
            background: #141414 !important;
            border-color: #222 !important;
            box-shadow: 0 10px 40px -15px rgba(0,0,0,0.5) !important;
        }
        html.dark .feature-card:hover {
            box-shadow: 0 25px 50px -12px rgba(220,38,38,0.15) !important;
        }
        html.dark .feature-card .bg-blue-50 { background: rgba(37,99,235,0.1) !important; }
        html.dark .feature-card .bg-red-50 { background: rgba(220,38,38,0.1) !important; }
        html.dark .feature-card .bg-green-50 { background: rgba(22,163,74,0.1) !important; }

        /* --- Section Cards --- */
        html.dark #leadership .bg-white,
        html.dark #governance .bg-white,
        html.dark #recognition .bg-white,
        html.dark #updates .bg-white {
            background: #141414 !important;
            border-color: #1a1a1a !important;
            box-shadow: 0 10px 40px -15px rgba(0,0,0,0.4) !important;
        }

        /* Governance pillar icons */
        html.dark #governance .bg-blue-50\/50 { background: rgba(37,99,235,0.08) !important; }
        html.dark #governance .bg-red-50\/50 { background: rgba(220,38,38,0.08) !important; }
        html.dark #governance .bg-green-50\/50 { background: rgba(22,163,74,0.08) !important; }
        html.dark #governance .bg-orange-50\/50 { background: rgba(234,88,12,0.08) !important; }
        html.dark #governance .bg-teal-50\/50 { background: rgba(20,184,166,0.08) !important; }

        /* Our Roots */
        html.dark #roots a.bg-gray-50 { background: #141414 !important; border-color: #222 !important; }
        html.dark #roots .border-y { border-color: #1a1a1a !important; }
        html.dark #roots .bg-red-50 { background: rgba(220,38,38,0.08) !important; }

        /* Generic Borders */
        html.dark .border-gray-100 { border-color: #1a1a1a; }
        html.dark .border-gray-50 { border-color: #151515; }

        /* Dark Mode Toggle Button */
        .dark-toggle { transition: all 0.3s ease; }
        html.dark .dark-toggle {
            background: #1a1a1a !important;
            border-color: #444 !important;
            color: #fbbf24 !important;
        }
        html.dark .dark-toggle:hover { border-color: #fbbf24 !important; }
    </style>
</head>
<body class="bg-[#fcfdfd] text-gray-900 overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-gray-100/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <a href="<?php echo BASE_URL; ?>" class="flex items-center group cursor-pointer flex-shrink-0">
                    <div class="relative">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 md:h-12 md:w-12 mr-2 md:mr-3 transition-transform duration-500 group-hover:rotate-12 shadow-sm rounded-full" onerror="this.src='https://valenzuela.gov.ph/images/valenzuela-logo.webp'">
                    </div>
                    <div class="flex flex-col md:block">
                        <span class="text-lg md:text-2xl font-black text-[#002d72] tracking-tighter leading-none">VALENZUELA<span class="text-red-600">VDM</span></span>
                        <div class="hidden md:flex items-center">
                            <span class="h-px w-4 bg-red-600 mr-2"></span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.2em] leading-none">Legislative Voting</span>
                        </div>
                    </div>
                </a>

                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Features</a>
                    <a href="#voting" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Voting</a>
                    <a href="#documents" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Documents</a>
                    <a href="#performance" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Performance</a>
                </div>
                <div class="flex items-center space-x-2 md:space-x-6">
                    <a href="<?php echo LOGIN_URL; ?>" class="hidden lg:block text-gray-600 hover:text-red-600 font-bold px-3 py-2 text-sm transition-all whitespace-nowrap">Sign In</a>
                    <a href="<?php echo REGISTER_URL; ?>" class="btn-modern bg-red-600 hover:bg-red-700 text-white font-black px-4 md:px-6 py-2 md:py-2.5 rounded-full text-[12px] md:text-sm shadow-xl shadow-red-200/50 whitespace-nowrap">
                        Get Started
                    </a>
                    <button onclick="toggleDarkMode()" class="hidden lg:flex dark-toggle w-8 h-8 md:w-10 md:h-10 rounded-full border border-gray-200 items-center justify-center text-gray-500 hover:text-red-600 hover:border-red-200" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                        <i id="darkModeIcon" class="bi bi-moon-fill text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden hero-gradient pt-24 pb-16 md:pt-48 md:pb-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <div data-aos="fade-down" class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-red-50 text-red-700 text-[10px] md:text-xs font-black mb-6 md:mb-8 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-red-600 mr-2 animate-pulse"></span>
                    OFFICIAL LEGISLATIVE VOTING SYSTEM
                </div>
                <h1 data-aos="fade-up" data-aos-delay="100" class="text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-black text-gray-900 mb-6 md:mb-8 tracking-tighter leading-[1] md:leading-[0.9]">
                    Deciding the <br class="hidden md:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-br from-red-600 to-red-900">City's Future Today.</span>
                </h1>
                <p data-aos="fade-up" data-aos-delay="200" class="max-w-2xl mx-auto text-base md:text-2xl text-gray-500 mb-8 md:mb-12 leading-relaxed font-medium px-4">
                    A comprehensive platform for the City Government of Valenzuela to manage legislative voting, document routing, and transparent decision-making.
                </p>
                <div data-aos="fade-up" data-aos-delay="300" class="flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4 px-6 md:px-0">
                    <a href="<?php echo REGISTER_URL; ?>" class="w-full sm:w-auto btn-modern bg-gray-900 hover:bg-black text-white font-black px-8 md:px-12 py-4 md:py-5 rounded-xl md:rounded-2xl text-base md:text-lg shadow-2xl">
                        Start Voting
                        <i class="bi bi-arrow-right-short ml-1 text-2xl align-middle"></i>
                    </a>
                    <a href="<?php echo LOGIN_URL; ?>" class="w-full sm:w-auto btn-modern bg-white hover:bg-gray-50 text-gray-900 font-bold px-8 md:px-12 py-4 md:py-5 rounded-xl md:rounded-2xl text-base md:text-lg border border-gray-200 shadow-sm">
                        View Sessions
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
                    <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-6">Transparent. Secure. <br>Built for Democracy.</p>
                    <div class="h-2 w-20 bg-red-600 mx-auto rounded-full"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <!-- Feature 1: Real-time Voting -->
                <div data-aos="fade-up" data-aos-delay="100" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group cursor-pointer" onclick="window.location.href='<?php echo LOGIN_URL; ?>'">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-[100px] group-hover:bg-red-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-red-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-red-200 relative z-10 floating">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Real-time Voting</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Conduct live voting sessions with instant result tallying. Councilors can cast their votes securely from their devices with full audit trails.</p>
                </div>

                <!-- Feature 2: Document Workflow -->
                <div data-aos="fade-up" data-aos-delay="200" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group cursor-pointer" onclick="window.location.href='<?php echo LOGIN_URL; ?>'">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-[100px] group-hover:bg-red-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-red-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-red-200 relative z-10 floating" style="animation-delay: 0.5s">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Smart Workflow</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Route ordinances and resolutions through various committees effortlessly. Track the status of every legislative document in a visual pipeline.</p>
                </div>

                <!-- Feature 3: Analytics -->
                <div data-aos="fade-up" data-aos-delay="300" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group cursor-pointer" onclick="window.location.href='<?php echo LOGIN_URL; ?>'">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-[100px] group-hover:bg-emerald-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-emerald-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-emerald-200 relative z-10 floating" style="animation-delay: 1s">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Decision Analytics</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Gain deep insights into voting patterns and legislative efficiency. Generate comprehensive reports on participation and decision trends.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Performance Section -->
    <section id="performance" class="py-24 md:py-40 bg-[#0a0a0a] text-white overflow-hidden relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-20 md:mb-32" data-aos="fade-up">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-6">System Performance</h2>
                <h3 class="text-4xl md:text-8xl font-black tracking-tighter leading-[0.9] mb-4">
                    Reliable. Precise. <br>
                    Data at Scale.
                </h3>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
                <!-- Records -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['total_records']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Legislative Items</div>
                </div>

                <!-- Latency -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['latency']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Processing Time</div>
                </div>

                <!-- Reliability -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['reliability']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Uptime Reliability</div>
                </div>

                <!-- Consults -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['daily_consults']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Daily Actions</div>
                </div>
            </div>
        </div>
        
        <!-- Decoration -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-[radial-gradient(circle_at_50%_50%,#dc262610,transparent_70%)] pointer-events-none"></div>
    </section>

    <!-- Footer -->
    <footer id="legal-footer" class="bg-white py-16 md:py-24 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-12 md:gap-0">
                <div class="w-full md:w-auto text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start mb-6">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-10 w-10 md:h-12 md:w-12 mr-3 md:mr-4 shadow-sm rounded-full">
                        <div class="text-[#002d72] font-black text-xl md:text-2xl tracking-tighter">VALENZUELA<span class="text-red-600">VDM</span></div>
                    </div>
                    <p class="text-gray-400 font-black text-[10px] md:text-xs uppercase tracking-[0.15em] max-w-xs mx-auto md:ml-0 md:mr-0 leading-relaxed md:leading-loose">
                        Official Voting and Decision-Making <br class="md:hidden"> System. <br>
                        City Government of Valenzuela.
                    </p>
                </div>
                
                <div class="w-full md:w-auto grid grid-cols-2 gap-8 sm:gap-12 md:gap-24">
                    <div class="text-center md:text-left">
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Quick Links</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black text-slate-600">
                            <li><a href="#features" class="hover:text-red-600 transition-colors uppercase tracking-wider">Features</a></li>
                            <li><a href="#voting" class="hover:text-red-600 transition-colors uppercase tracking-wider">Voting</a></li>
                            <li><a href="#documents" class="hover:text-red-600 transition-colors uppercase tracking-wider">Documents</a></li>
                            <li><a href="<?php echo LOGIN_URL; ?>" class="hover:text-red-600 transition-colors uppercase tracking-wider">Sign In</a></li>
                        </ul>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Support</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black text-slate-600">
                            <li><a href="#" class="hover:text-red-600 transition-colors uppercase tracking-wider">Help Center</a></li>
                            <li><a href="#" class="hover:text-red-600 transition-colors uppercase tracking-wider">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-red-600 transition-colors uppercase tracking-wider">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-16 md:mt-20 pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-[9px] md:text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] text-center md:text-left">
                <div class="mb-4 md:mb-0">© <?php echo date('Y'); ?> City of Valenzuela. <br class="md:hidden"> Powered by transparency.</div>
                <div>LGU Module MOD-05 • v1.0.0</div>
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

        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateDarkModeIcons(isDark);
            document.querySelector('meta[name="theme-color"]').content = isDark ? '#0a0a0a' : '#dc2626';
        }

        function updateDarkModeIcons(isDark) {
            const desktopIcon = document.getElementById('darkModeIcon');
            if (desktopIcon) desktopIcon.className = isDark ? 'bi bi-sun-fill text-sm' : 'bi bi-moon-fill text-sm';
        }

        // Initialize dark mode icons on load
        (function() {
            const isDark = document.documentElement.classList.contains('dark');
            updateDarkModeIcons(isDark);
        })();
    </script>
</body>
</html>
