<?php
/**
 * Root index file - Landing Page
 */
require_once __DIR__ . '/modules/core/config/config.php';
require_once __DIR__ . '/modules/dashboard/services/DashboardService.php';

// ── Security Response Headers ────────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://static.cloudflareinsights.com; " .
    "script-src-elem 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://static.cloudflareinsights.com; " .
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; " .
    "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com; " .
    "img-src 'self' data: blob: https://lacs.spvalenzuela.com https://images.unsplash.com https://valenzuela.gov.ph; " .
    "connect-src 'self' https://cdn.jsdelivr.net https://static.cloudflareinsights.com; " .
    "frame-ancestors 'self'; " .
    "base-uri 'self'; " .
    "form-action 'self';"
);
// ─────────────────────────────────────────────────────────────────────────────

// Check if user is already logged in with a valid session
checkAlreadyLoggedIn();

$dashboardService = new DashboardService();
$perfStats = $dashboardService->getSystemPerformanceStats();

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
        html.dark .feature-card .bg-red-50 { background: rgba(220,38,38,0.1) !important; }
        html.dark .feature-card .bg-blue-50 { background: rgba(37,99,235,0.1) !important; }
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

        /* Search Showcase (not the browser mockup) */
        html.dark #search-showcase span.inline-flex { background: #141414 !important; border-color: #222 !important; }
        html.dark #search-showcase div.inline-flex.rounded-full { background: #1a1a1a !important; border-color: #222 !important; }

        /* Generic Borders */
        html.dark .border-gray-100 { border-color: #1a1a1a; }
        html.dark .border-gray-50 { border-color: #151515; }

        /* Official Modal */
        html.dark #officialModal .bg-white { background: #1a1a1a !important; }
        html.dark #officialModal .bg-gray-50 { background: #111 !important; }
        html.dark #officialModal .bg-white\/80 { background: rgba(26,26,26,0.8) !important; }
        html.dark #officialModal .bg-red-50 { background: rgba(220,38,38,0.1) !important; }
        html.dark #officialModal .bg-blue-50 { background: rgba(37,99,235,0.1) !important; }

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
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 md:h-12 md:w-12 mr-2 md:mr-3 transition-transform duration-500 group-hover:rotate-12 shadow-sm rounded-full" onerror="this.src='<?php echo BASE_URL; ?>/public/assets/images/valenzuela-logo.webp'">
                    </div>
                    <div class="flex flex-col md:block">
                        <span class="text-lg md:text-2xl font-black text-[#002d72] tracking-tighter leading-none">VALENZUELA<span class="text-red-600">LRMS</span></span>
                        <div class="hidden md:flex items-center">
                            <span class="h-px w-4 bg-red-600 mr-2"></span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.2em] leading-none">Legislative Office</span>
                        </div>
                    </div>
                </a>


                <div class="hidden lg:flex items-center space-x-2 xl:space-x-4">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-600 hover:text-red-600 font-bold text-[11px] xl:text-sm transition-all">Home</a>
                    <div class="relative group">
                        <button type="button" class="flex items-center text-gray-600 hover:text-red-600 font-bold text-[11px] xl:text-sm transition-all py-2">
                            About <i class="bi bi-caret-down-fill ml-1 text-[10px]"></i>
                        </button>
                        <div class="nav-dropdown absolute top-full pt-2 w-44 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-gray-700 border border-gray-100 dark:border-gray-700 overflow-hidden z-50 origin-top">
                            <a href="#roots" class="block px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 transition-all">Our History</a>
                            <a href="#governance" class="block px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 transition-all">Governance</a>
                            <a href="#recognition" class="block px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 transition-all">Awards</a>
                        </div>
                    </div>
                    <a href="#leadership" class="text-gray-600 hover:text-red-600 font-bold text-[11px] xl:text-sm transition-all">Officials</a>
                    <div class="relative group">
                        <button type="button" class="flex items-center text-gray-600 hover:text-red-600 font-bold text-[11px] xl:text-sm transition-all py-2">
                            Legislation <i class="bi bi-caret-down-fill ml-1 text-[10px]"></i>
                        </button>
                        <div class="nav-dropdown absolute top-full pt-2 w-48 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-gray-700 border border-gray-100 dark:border-gray-700 overflow-hidden z-50 origin-top">
                            <a href="<?php echo PUBLIC_PORTAL_URL; ?>" class="block px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 transition-all">Public Portal</a>
                            <a href="#landmarks" class="block px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 transition-all">Landmarks</a>
                        </div>
                    </div>
                    <div class="relative group">
                        <button type="button" class="flex items-center text-gray-600 hover:text-red-600 font-bold text-[11px] xl:text-sm transition-all py-2">
                            Services <i class="bi bi-caret-down-fill ml-1 text-[10px]"></i>
                        </button>
                        <div class="nav-dropdown absolute top-full pt-2 w-44 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-gray-700 border border-gray-100 dark:border-gray-700 overflow-hidden z-50 origin-top">
                            <a href="#infrastructure" class="block px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 transition-all">Infrastructure</a>
                        </div>
                    </div>
                    <a href="#updates" class="text-gray-600 hover:text-red-600 font-bold text-[11px] xl:text-sm transition-all">News</a>
                </div>
                <div class="flex items-center space-x-2 xl:space-x-6">
                    <a href="<?php echo LOGIN_URL; ?>" class="hidden lg:block text-gray-600 hover:text-red-600 font-bold px-3 py-2 text-[11px] xl:text-sm transition-all whitespace-nowrap">Sign In</a>
                    <a href="<?php echo REGISTER_URL; ?>" class="hidden lg:inline-flex btn-modern bg-red-600 hover:bg-red-700 text-white font-black px-4 md:px-6 py-2 md:py-2.5 rounded-full text-[12px] md:text-sm shadow-xl shadow-red-200/50 whitespace-nowrap">
                        Get Started
                    </a>
                    <button type="button" onclick="toggleDarkMode()" class="hidden lg:flex dark-toggle w-8 h-8 md:w-10 md:h-10 rounded-full border border-gray-200 items-center justify-center text-gray-500 hover:text-red-600 hover:border-red-200" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                        <i id="darkModeIcon" class="bi bi-moon-fill text-sm"></i>
                    </button>
                    <button type="button" onclick="toggleDarkMode()" class="lg:hidden dark-toggle w-8 h-8 md:w-10 md:h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-600 hover:border-red-200" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                        <i id="darkModeIconMobile" class="bi bi-moon-fill text-sm"></i>
                    </button>
                    <button type="button" id="mobile-landing-toggle" class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl text-gray-700 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all" aria-label="Open menu">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    
    <!-- Mobile Menu Overlay -->
    <div id="mobile-landing-menu" class="hidden fixed inset-0 z-[200] bg-white overflow-y-auto animate-fade-in lg:hidden">
        <div class="fixed top-0 w-full p-4 flex justify-between items-center border-b border-gray-100 bg-white/80 backdrop-blur-md z-10">
            <div class="flex items-center">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 mr-2 rounded-full shadow-sm" onerror="this.src='<?php echo BASE_URL; ?>/public/assets/images/valenzuela-logo.webp'">
                <span class="text-xl font-black text-[#002d72] tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
            </div>
            <button id="mobile-landing-close" class="p-2 text-gray-500 hover:text-red-600 transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <div class="pt-24 pb-12 px-8 flex flex-col items-center space-y-8 text-center animate-slide-in-top">
            <h3 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-2 opacity-50">Quick Links</h3>
            <div class="h-px w-8 bg-red-600/20"></div>
            
            <a href="<?php echo PUBLIC_PORTAL_URL; ?>" class="mobile-nav-link text-3xl font-black text-red-600 uppercase tracking-tighter hover:text-red-700 transition-colors flex items-center gap-3"><i class="bi bi-globe2"></i>Public Portal</a>
            
            <div class="h-px w-16 bg-gray-100 my-0"></div>
            
            <a href="#leadership" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Officials</a>
            <a href="#roots" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Our History</a>
            <a href="#governance" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Governance</a>
            <a href="#recognition" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Awards</a>
            <a href="#infrastructure" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Infrastructure</a>
            <a href="#landmarks" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Landmarks</a>
            <a href="#updates" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">News & Updates</a>
            
            <div class="h-px w-16 bg-gray-100 my-2"></div>
            <h3 class="text-gray-400 font-black uppercase tracking-widest text-[10px] mb-0">Help & Legal</h3>
            <a href="<?php echo HELP_URL; ?>/views/privacy.php" class="mobile-nav-link text-lg font-bold text-gray-400 uppercase tracking-wider hover:text-red-600 transition-colors">Privacy</a>
            <a href="<?php echo HELP_URL; ?>/views/terms.php" class="mobile-nav-link text-lg font-bold text-gray-400 uppercase tracking-wider hover:text-red-600 transition-colors">Terms</a>

            <div class="pt-8 w-full border-t border-gray-50 dark:border-gray-800 grid grid-cols-2 gap-4">
                <a href="<?php echo LOGIN_URL; ?>" class="flex items-center justify-center border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:text-red-600 hover:border-red-300 dark:hover:border-red-500 font-black uppercase tracking-widest text-sm py-4 rounded-xl transition-all">Sign In</a>
                <a href="<?php echo REGISTER_URL; ?>" class="flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-sm py-4 rounded-xl shadow-xl shadow-red-200/50">Get Started</a>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="relative overflow-hidden hero-gradient pt-24 pb-16 md:pt-48 md:pb-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <div data-aos="fade-down" class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-red-50 text-red-700 text-[10px] md:text-xs font-black mb-6 md:mb-8 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-red-600 mr-2 animate-pulse"></span>
                    OFFICIAL LEGISLATIVE RECORDS
                </div>
                <h1 data-aos="fade-up" data-aos-delay="100" class="text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-black text-gray-900 mb-6 md:mb-8 tracking-tighter leading-[1] md:leading-[0.9]">
                    Preserving the <br class="hidden md:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-br from-red-600 to-red-900">Legislative Legacy.</span>
                </h1>
                <p data-aos="fade-up" data-aos-delay="200" class="max-w-2xl mx-auto text-base md:text-2xl text-gray-500 mb-8 md:mb-12 leading-relaxed font-medium px-4">
                    A digital ecosystem for the City Government of Valenzuela to preserve, query, and analyze the legislative DNA of our community.
                </p>
                <div data-aos="fade-up" data-aos-delay="300" class="flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4 px-6 md:px-0">
                    <a href="<?php echo PUBLIC_PORTAL_URL; ?>" class="w-full sm:w-auto btn-modern bg-red-600 hover:bg-red-700 text-white font-black px-8 md:px-12 py-4 md:py-5 rounded-xl md:rounded-2xl text-base md:text-lg shadow-2xl shadow-red-200/50">
                        <i class="bi bi-globe2 mr-2"></i>Public Document Portal
                    </a>
                    <a href="<?php echo REGISTER_URL; ?>" class="w-full sm:w-auto btn-modern bg-gray-900 hover:bg-black text-white font-black px-8 md:px-12 py-4 md:py-5 rounded-xl md:rounded-2xl text-base md:text-lg shadow-2xl">
                        Get Started
                        <i class="bi bi-arrow-right-short ml-1 text-2xl align-middle"></i>
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
                <div data-aos="fade-up" data-aos-delay="100" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group cursor-pointer" onclick="window.location.href='<?php echo LOGIN_URL; ?>'">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-[100px] group-hover:bg-red-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-red-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-red-200 relative z-10 floating">
                        <i class="bi bi-stack"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Smart Archive</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Beyond simple storage. We categorize every ordinance and resolution with metadata that makes cataloging effortless and retrieval instantaneous.</p>
                </div>

                <!-- Feature 2: Advanced Search -->
                <div data-aos="fade-up" data-aos-delay="200" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group cursor-pointer" onclick="window.location.href='<?php echo LOGIN_URL; ?>'">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-[100px] group-hover:bg-blue-100 transition-colors duration-500"></div>
                    <div class="w-16 h-16 bg-blue-600 text-white rounded-3xl flex items-center justify-center mb-8 text-3xl shadow-lg shadow-blue-200 relative z-10 floating" style="animation-delay: 0.5s">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-gray-900">Instant Query</h3>
                    <p class="text-gray-500 leading-relaxed font-semibold text-sm">Our search engine indexed years of legislative history, delivering results in milliseconds. Filter by date, author, department, or keyword seamlessly.</p>
                </div>

                <!-- Feature 3: Analytics -->
                <div data-aos="fade-up" data-aos-delay="300" class="feature-card p-10 bg-white rounded-[40px] border border-gray-100 shadow-2xl shadow-gray-200/40 relative overflow-hidden group cursor-pointer" onclick="window.location.href='<?php echo LOGIN_URL; ?>'">
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

    <!-- Advanced Search Showcase Section -->
    <section id="search-showcase" class="py-24 md:py-40 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
        <!-- Subtle background decoration -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1200px] h-[600px] bg-red-600/[0.03] blur-[120px] rounded-full pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Section Header -->
            <div class="text-center mb-16 md:mb-24" data-aos="fade-up">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Search Intelligence</h2>
                <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-6">Find any record<br>in milliseconds.</p>
                <p class="max-w-2xl mx-auto text-gray-500 font-medium text-lg leading-relaxed">AI-powered hybrid search combining keywords with semantic understanding. Filter by type, status, and time period — all in one interface.</p>
                <div class="h-2 w-20 bg-red-600 mx-auto rounded-full mt-8"></div>
            </div>
            
            <!-- Browser Mockup with Light/Dark Toggle -->
            <div class="max-w-6xl mx-auto" data-aos="zoom-in" data-aos-duration="1200">
                <!-- Toggle Buttons -->
                <div class="flex justify-center mb-6 md:mb-8">
                    <div class="inline-flex bg-white rounded-full p-1.5 shadow-lg border border-gray-100">
                        <button type="button" onclick="showSearchMode('light')" id="search-light-btn" class="px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all duration-300 bg-gray-900 text-white shadow-md">
                            <i class="bi bi-sun-fill mr-1.5"></i>Light
                        </button>
                        <button type="button" onclick="showSearchMode('dark')" id="search-dark-btn" class="px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:text-gray-900">
                            <i class="bi bi-moon-fill mr-1.5"></i>Dark
                        </button>
                    </div>
                </div>

                <!-- Browser Chrome -->
                <div class="rounded-2xl md:rounded-[28px] overflow-hidden shadow-[0_50px_100px_-30px_rgba(0,0,0,0.25)] border border-gray-200/60 transition-all duration-700" id="search-browser-frame">
                    <!-- Title Bar -->
                    <div class="h-10 md:h-12 flex items-center px-4 md:px-5 space-x-2 transition-colors duration-700 bg-gray-100 border-b border-gray-200" id="search-titlebar">
                        <div class="flex items-center space-x-1.5 md:space-x-2">
                            <div class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 mx-4 md:mx-8">
                            <div class="h-6 md:h-7 rounded-lg flex items-center px-3 md:px-4 text-[10px] md:text-xs font-medium transition-colors duration-700 bg-white text-gray-400 border border-gray-200" id="search-urlbar">
                                <i class="bi bi-lock-fill mr-1.5 text-green-500"></i>
                                <span class="truncate">valenzuela-lrms.gov.ph/modules/search</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Screen Content -->
                    <div class="transition-colors duration-700 bg-white" id="search-screen-bg">
                        <!-- Hero Banner -->
                        <div class="bg-gradient-to-r from-red-700 to-red-900 px-4 md:px-10 py-6 md:py-8">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-8">
                                <div>
                                    <div class="flex items-center space-x-2 mb-2">
                                        <div class="h-px w-6 bg-white/40"></div>
                                        <span class="text-white/70 text-[8px] md:text-[10px] font-black uppercase tracking-[0.2em]">AI-Powered Intelligence</span>
                                    </div>
                                    <h3 class="text-lg md:text-3xl font-black text-white italic tracking-tight uppercase">Advanced Search</h3>
                                    <p class="text-red-200 text-[9px] md:text-xs font-medium mt-1 max-w-[250px] md:max-w-none">Hybrid engine combining keywords with semantic understanding.</p>
                                </div>
                                <div class="flex space-x-2 flex-shrink-0">
                                    <span class="hero-toggle-btn px-2.5 md:px-4 py-1.5 md:py-2 rounded-lg text-[9px] md:text-xs font-black uppercase tracking-wider border-2 border-white text-red-700 bg-white shadow-lg whitespace-nowrap">Documents</span>
                                    <span class="px-2.5 md:px-4 py-1.5 md:py-2 rounded-lg text-[9px] md:text-xs font-black uppercase tracking-wider text-white/70 border border-white/20 hover:border-white/40 transition-colors whitespace-nowrap">Legislations</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Main Content Area -->
                        <div class="flex">
                            <!-- Sidebar Filters -->
                            <div class="hidden md:block w-64 flex-shrink-0 p-6 border-r transition-colors duration-700 border-gray-100" id="search-sidebar">
                                <div class="flex items-center justify-between mb-5">
                                    <h4 class="text-sm font-black flex items-center transition-colors duration-700 text-gray-900" id="search-refine-title">
                                        <i class="bi bi-sliders2 text-red-600 mr-2"></i>Refine Results
                                    </h4>
                                    <span class="text-[10px] font-bold uppercase tracking-wider transition-colors duration-700 text-gray-400 cursor-pointer hover:text-red-600">Clear All</span>
                                </div>
                                
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest mb-3 transition-colors duration-700 text-gray-500" id="search-label-type">Document Type</p>
                                        <div class="space-y-2.5" id="search-filter-list">
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-full border-2 mr-2.5 transition-colors duration-700 border-gray-300"></div><span class="text-xs font-semibold transition-colors duration-700 text-gray-700 group-hover:text-red-600">Ordinance</span></div>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400">2</span>
                                            </label>
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-full border-2 mr-2.5 border-red-500 bg-red-500"></div><span class="text-xs font-bold text-red-600">Resolution</span></div>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400">2</span>
                                            </label>
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-full border-2 mr-2.5 transition-colors duration-700 border-gray-300"></div><span class="text-xs font-semibold transition-colors duration-700 text-gray-700 group-hover:text-red-600">Session</span></div>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400">4</span>
                                            </label>
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-full border-2 mr-2.5 transition-colors duration-700 border-gray-300"></div><span class="text-xs font-semibold transition-colors duration-700 text-gray-700 group-hover:text-red-600">Agenda</span></div>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400">1</span>
                                            </label>
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-full border-2 mr-2.5 transition-colors duration-700 border-gray-300"></div><span class="text-xs font-semibold transition-colors duration-700 text-gray-700 group-hover:text-red-600">Committee</span></div>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400">0</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest mb-3 transition-colors duration-700 text-gray-500">Status</p>
                                        <div class="h-9 rounded-lg border text-xs font-medium flex items-center px-3 transition-colors duration-700 bg-white border-gray-200 text-gray-600" id="search-status-select">All Statuses</div>
                                    </div>
                                    
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest mb-3 transition-colors duration-700 text-gray-500">Time Period</p>
                                        <div class="space-y-2">
                                            <div class="h-9 rounded-lg border text-xs font-medium flex items-center px-3 transition-colors duration-700 bg-white border-gray-200 text-gray-400" id="search-date-from"><i class="bi bi-calendar3 mr-2"></i>mm/dd/yyyy</div>
                                            <div class="h-9 rounded-lg border text-xs font-medium flex items-center px-3 transition-colors duration-700 bg-white border-gray-200 text-gray-400" id="search-date-to"><i class="bi bi-calendar3 mr-2"></i>mm/dd/yyyy</div>
                                        </div>
                                    </div>
                                    
                                    <button class="w-full py-2.5 bg-red-600 text-white text-xs font-black uppercase tracking-wider rounded-lg shadow-md shadow-red-200/50 hover:bg-red-700 transition-all">
                                        <i class="bi bi-funnel-fill mr-1.5"></i>Apply Filters
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Results Area -->
                            <div class="flex-1 p-3 md:p-6 overflow-hidden">
                                <!-- Search Bar -->
                                <div class="flex items-center gap-2 md:gap-3 mb-5">
                                    <div class="flex-1 flex items-center h-12 rounded-xl border px-3 md:px-4 transition-colors duration-700 bg-white border-gray-200 min-w-0 shadow-sm" id="search-input-bar">
                                        <i class="bi bi-search mr-2 md:mr-3 transition-colors duration-700 text-gray-400 flex-shrink-0"></i>
                                        <span class="text-[11px] md:text-sm transition-colors duration-700 text-gray-400 truncate mr-1">Search documents or intent...</span>
                                        <div class="ml-auto flex space-x-1 flex-shrink-0">
                                            <span class="px-2 py-0.5 md:px-2.5 md:py-1 rounded-md text-[9px] md:text-[10px] font-bold transition-colors duration-700 bg-emerald-100 text-emerald-700 border border-emerald-200">Hybrid</span>
                                            <span class="hidden sm:inline-block px-2.5 py-1 rounded-md text-[10px] font-bold transition-colors duration-700 bg-gray-100 text-gray-500">Semantic</span>
                                        </div>
                                    </div>
                                    <div class="w-12 h-12 bg-red-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-red-200/40 hover:bg-red-700 transition-all cursor-pointer flex-shrink-0">
                                        <i class="bi bi-arrow-right text-lg"></i>
                                    </div>
                                </div>
                                
                                <!-- Results Header -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3 sm:gap-0">
                                    <div class="flex flex-wrap items-center gap-2 md:gap-3">
                                        <span class="text-xs font-bold transition-colors duration-700 text-gray-600" id="search-found-text">Found <strong>9</strong> matches</span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>Hybrid Engine</span>
                                        <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border transition-colors duration-700 border-gray-200 text-gray-500 cursor-pointer hover:border-red-200 hover:text-red-600"><i class="bi bi-download mr-1"></i>Export CSV</span>
                                    </div>
                                    <div class="hidden md:flex space-x-1">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-700 border border-gray-200 text-gray-400 cursor-pointer"><i class="bi bi-grid-3x3-gap-fill text-xs"></i></div>
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-900 text-white cursor-pointer"><i class="bi bi-list-ul text-xs"></i></div>
                                    </div>
                                </div>
                                
                                <!-- Result Card 1 -->
                                <div class="rounded-xl border p-4 md:p-5 mb-3 transition-all duration-700 hover:shadow-md bg-white border-gray-100" id="search-result-1">
                                    <div class="flex items-start gap-4">
                                        <div class="hidden md:flex w-12 h-12 rounded-xl items-center justify-center flex-shrink-0 bg-emerald-50 text-emerald-600">
                                            <i class="bi bi-people text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase transition-colors duration-700 bg-gray-100 text-gray-700">Draft</span>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400 uppercase tracking-wider">Ref: 23123123</span>
                                            </div>
                                            <h4 class="text-sm font-black mb-1 transition-colors duration-700 text-gray-900" id="search-result-title-1">Resolution No. 2025-001</h4>
                                            <p class="text-xs mb-3 transition-colors duration-700 text-gray-500" id="search-result-desc-1">Committee Management resolution submitted for review and approval by the legislative body.</p>
                                            <div class="h-px transition-colors duration-700 bg-gray-100 mb-3"></div>
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4 text-[10px] transition-colors duration-700 text-gray-400">
                                                    <span><i class="bi bi-calendar3 text-red-400 mr-1"></i>Feb 15, 2026</span>
                                                    <span><i class="bi bi-person-circle mr-1"></i>Admin User</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="hidden md:inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold bg-gray-900 text-white cursor-pointer hover:bg-gray-800 transition-colors"><i class="bi bi-eye mr-1"></i>Preview</span>
                                                    <span class="w-8 h-8 rounded-lg bg-red-600 text-white flex items-center justify-center cursor-pointer hover:bg-red-700 transition-colors shadow-sm"><i class="bi bi-download text-xs"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Result Card 2 -->
                                <div class="rounded-xl border p-4 md:p-5 transition-all duration-700 hover:shadow-md bg-white border-gray-100" id="search-result-2">
                                    <div class="flex items-start gap-4">
                                        <div class="hidden md:flex w-12 h-12 rounded-xl items-center justify-center flex-shrink-0 bg-blue-50 text-blue-600">
                                            <i class="bi bi-file-earmark-check text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border border-yellow-200 bg-yellow-100 text-yellow-700">Pending</span>
                                                <span class="text-[10px] font-bold transition-colors duration-700 text-gray-400 uppercase tracking-wider">Ref: SDASDASD</span>
                                            </div>
                                            <h4 class="text-sm font-black mb-1 transition-colors duration-700 text-gray-900" id="search-result-title-2">Ordinance No. 2025-044</h4>
                                            <p class="text-xs mb-3 transition-colors duration-700 text-gray-500" id="search-result-desc-2">Environmental protection and urban greening ordinance for District 2 implementation.</p>
                                            <div class="h-px transition-colors duration-700 bg-gray-100 mb-3"></div>
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4 text-[10px] transition-colors duration-700 text-gray-400">
                                                    <span><i class="bi bi-calendar3 text-red-400 mr-1"></i>Feb 14, 2026</span>
                                                    <span><i class="bi bi-person-circle mr-1"></i>Admin User</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="hidden md:inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold bg-gray-900 text-white cursor-pointer hover:bg-gray-800 transition-colors"><i class="bi bi-eye mr-1"></i>Preview</span>
                                                    <span class="w-8 h-8 rounded-lg bg-red-600 text-white flex items-center justify-center cursor-pointer hover:bg-red-700 transition-colors shadow-sm"><i class="bi bi-download text-xs"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Feature Pills -->
                <div class="flex flex-wrap justify-center gap-3 mt-8 md:mt-12" data-aos="fade-up" data-aos-delay="200">
                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-gray-100 shadow-sm text-xs font-bold text-gray-600"><i class="bi bi-lightning-charge-fill text-yellow-500 mr-2"></i>Hybrid + Semantic Engine</span>
                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-gray-100 shadow-sm text-xs font-bold text-gray-600"><i class="bi bi-funnel-fill text-red-500 mr-2"></i>Multi-Filter System</span>
                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-gray-100 shadow-sm text-xs font-bold text-gray-600"><i class="bi bi-moon-fill text-indigo-500 mr-2"></i>Dark Mode Support</span>
                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-white border border-gray-100 shadow-sm text-xs font-bold text-gray-600"><i class="bi bi-download text-emerald-500 mr-2"></i>CSV Export</span>
                </div>
                
                <!-- CTA -->
                <div class="text-center mt-10 md:mt-14" data-aos="fade-up" data-aos-delay="300">
                    <a href="<?php echo PUBLIC_PORTAL_URL; ?>" class="inline-flex items-center px-8 md:px-10 py-4 md:py-5 bg-red-600 hover:bg-red-700 text-white font-black rounded-2xl text-sm md:text-base shadow-2xl shadow-red-600/20 transition-all hover:scale-105">
                        <i class="bi bi-globe2 mr-2"></i>Try Public Document Portal
                        <i class="bi bi-arrow-right-short ml-1 text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- System Performance Section -->
    <section class="py-24 md:py-40 bg-[#0a0a0a] text-white overflow-hidden relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-20 md:mb-32" data-aos="fade-up">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-6">System Performance</h2>
                <h3 class="text-4xl md:text-8xl font-black tracking-tighter leading-[0.9] mb-4">
                    Scalable. Efficient.<br>
                    Driven by Accuracy.
                </h3>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
                <!-- Records -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['total_records']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Indexed Records</div>
                </div>

                <!-- Latency -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['latency']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Global Latency</div>
                </div>

                <!-- Reliability -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['reliability']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Data Reliability</div>
                </div>

                <!-- Consults -->
                <div class="p-8 md:p-12 rounded-[40px] text-center border border-white/5 bg-white/[0.01] backdrop-blur-3xl transition-all duration-500 hover:bg-white/[0.03] hover:border-white/10 group" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-4xl md:text-6xl font-black mb-4 tracking-tighter group-hover:scale-110 transition-transform duration-500"><?php echo $perfStats['daily_consults']; ?></div>
                    <div class="text-[10px] md:text-xs font-black text-red-600 uppercase tracking-[0.2em] opacity-80">Daily Consults</div>
                </div>
            </div>
        </div>
        
        <!-- Decoration -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-[radial-gradient(circle_at_50%_50%,#dc262610,transparent_70%)] pointer-events-none"></div>
        <div class="absolute -bottom-48 -left-48 w-96 h-96 bg-red-600/20 blur-[120px] rounded-full opacity-50"></div>
        <div class="absolute -top-48 -right-48 w-96 h-96 bg-red-900/20 blur-[120px] rounded-full opacity-50"></div>
    </section>

    <script>
    function showSearchMode(mode) {
        const lightBtn = document.getElementById('search-light-btn');
        const darkBtn = document.getElementById('search-dark-btn');
        const frame = document.getElementById('search-browser-frame');
        const titlebar = document.getElementById('search-titlebar');
        const urlbar = document.getElementById('search-urlbar');
        const screenBg = document.getElementById('search-screen-bg');
        const sidebar = document.getElementById('search-sidebar');
        
        // Dark color classes
        const darkBg = '#1a1a1a';
        const darkCard = '#242424';
        const darkBorder = '#333';
        const darkText = '#e5e5e5';
        const darkMuted = '#999';
        
        if (mode === 'dark') {
            // Toggle buttons
            lightBtn.className = 'px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:text-gray-900';
            darkBtn.className = 'px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all duration-300 bg-gray-900 text-white shadow-md';
            
            // Frame
            frame.style.borderColor = '#333';
            
            // Title bar
            titlebar.style.backgroundColor = '#1e1e1e';
            titlebar.style.borderColor = '#333';
            
            // URL bar
            urlbar.style.backgroundColor = '#2a2a2a';
            urlbar.style.borderColor = '#444';
            urlbar.style.color = '#888';
            
            // Screen
            screenBg.style.backgroundColor = darkBg;
            
            // Sidebar
            if (sidebar) {
                sidebar.style.borderColor = darkBorder;
                sidebar.style.backgroundColor = darkBg;
            }
            
            // Filter labels
            document.querySelectorAll('#search-filter-list .text-gray-700').forEach(el => { el.style.color = darkText; });
            document.querySelectorAll('#search-label-type, #search-sidebar .text-gray-500').forEach(el => { el.style.color = darkMuted; });
            document.getElementById('search-refine-title').style.color = darkText;
            
            // Input fields
            ['search-status-select', 'search-date-from', 'search-date-to'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.style.backgroundColor = darkCard; el.style.borderColor = darkBorder; el.style.color = darkMuted; }
            });
            
            // Search bar
            const inputBar = document.getElementById('search-input-bar');
            if (inputBar) { inputBar.style.backgroundColor = darkCard; inputBar.style.borderColor = darkBorder; }
            
            // Found text
            const foundText = document.getElementById('search-found-text');
            if (foundText) foundText.style.color = darkMuted;
            
            // Result cards
            ['search-result-1', 'search-result-2'].forEach(id => {
                const card = document.getElementById(id);
                if (card) { card.style.backgroundColor = darkCard; card.style.borderColor = darkBorder; }
            });
            
            // Result titles & descriptions
            ['search-result-title-1', 'search-result-title-2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.color = darkText;
            });
            ['search-result-desc-1', 'search-result-desc-2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.color = darkMuted;
            });
            
        } else {
            // Toggle buttons
            lightBtn.className = 'px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all duration-300 bg-gray-900 text-white shadow-md';
            darkBtn.className = 'px-5 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all duration-300 text-gray-500 hover:text-gray-900';
            
            // Reset all inline styles
            frame.style.borderColor = '';
            titlebar.style.backgroundColor = '';
            titlebar.style.borderColor = '';
            urlbar.style.backgroundColor = '';
            urlbar.style.borderColor = '';
            urlbar.style.color = '';
            screenBg.style.backgroundColor = '';
            
            if (sidebar) {
                sidebar.style.borderColor = '';
                sidebar.style.backgroundColor = '';
            }
            
            document.querySelectorAll('#search-filter-list .text-gray-700').forEach(el => { el.style.color = ''; });
            document.querySelectorAll('#search-label-type, #search-sidebar .text-gray-500').forEach(el => { el.style.color = ''; });
            document.getElementById('search-refine-title').style.color = '';
            
            ['search-status-select', 'search-date-from', 'search-date-to'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.style.backgroundColor = ''; el.style.borderColor = ''; el.style.color = ''; }
            });
            
            const inputBar = document.getElementById('search-input-bar');
            if (inputBar) { inputBar.style.backgroundColor = ''; inputBar.style.borderColor = ''; }
            
            const foundText = document.getElementById('search-found-text');
            if (foundText) foundText.style.color = '';
            
            ['search-result-1', 'search-result-2'].forEach(id => {
                const card = document.getElementById(id);
                if (card) { card.style.backgroundColor = ''; card.style.borderColor = ''; }
            });
            
            ['search-result-title-1', 'search-result-title-2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.color = '';
            });
            ['search-result-desc-1', 'search-result-desc-2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.color = '';
            });
        }
    }
    </script>

    <!-- Leadership Section -->
    <section id="leadership" class="py-32 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-24">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Leadership</h2>
                <h3 class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-6">City Officials</h3>
                <p class="max-w-2xl mx-auto text-gray-500 font-medium text-lg leading-relaxed mb-6">Meet the dedicated leaders serving Valenzuela City.</p>
                <div class="h-2 w-20 bg-red-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-12 max-w-6xl mx-auto text-center">
                <!-- Mayor -->
                <div data-aos="fade-up" onclick="showOfficialDetails('Wes Gatchalian', 'CITY MAYOR', 'https://lacs.spvalenzuela.com/images/mayor_wes.png?v=1771093587', 'Leading Valenzuela City towards a progressive and livable future through innovative governance and compassionate public service. Known for his Gatchalian brand of proactive leadership, he has prioritized education, healthcare, and economic digitalization.', 'Focus: Education 360°, VCares, Paspas Permit', 'https://www.facebook.com/WESGatchalian')" class="group bg-white p-10 md:p-16 rounded-[40px] shadow-[0_15px_50px_-15px_rgba(0,0,0,0.08)] border border-gray-50 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl cursor-pointer">
                    <div class="w-48 h-48 md:w-56 md:h-56 mx-auto mb-10 overflow-hidden rounded-full border-8 border-gray-50 shadow-xl relative">
                        <img src="https://lacs.spvalenzuela.com/images/mayor_wes.png?v=1771093587" alt="Mayor Wes Gatchalian" class="w-full h-full object-cover transition-all duration-1000">
                    </div>
                    <h3 class="text-3xl md:text-4xl font-black text-gray-900 mb-2">Wes Gatchalian</h3>
                    <p class="text-red-600 font-bold uppercase tracking-[0.2em] text-xs mb-6">CITY MAYOR</p>
                    <p class="text-gray-500 font-medium leading-relaxed px-4">Leading Valenzuela City towards a progressive and livable future through innovative governance and compassionate public service.</p>
                </div>

                <!-- Vice Mayor -->
                <div data-aos="fade-up" data-aos-delay="200" onclick="showOfficialDetails('Marlon Alejandrino', 'VICE MAYOR', 'https://lacs.spvalenzuela.com/images/vice_marlon.png?v=1771093587', 'Presiding over the City Council with a focus on legislative excellence and community empowerment. He ensures that every ordinance passed serves the best interest of Valenzuelanos.', 'Focus: Legislative Oversight, Community Programs, Social Justice', 'https://www.facebook.com/councilormarlon.alejandrino')" class="group bg-white p-10 md:p-16 rounded-[40px] shadow-[0_15px_50px_-15px_rgba(0,0,0,0.08)] border border-gray-50 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl cursor-pointer">
                    <div class="w-48 h-48 md:w-56 md:h-56 mx-auto mb-10 overflow-hidden rounded-full border-8 border-gray-50 shadow-xl relative">
                        <img src="https://lacs.spvalenzuela.com/images/vice_marlon.png?v=1771093587" alt="Vice Mayor Marlon Alejandrino" class="w-full h-full object-cover transition-all duration-1000">
                    </div>
                    <h3 class="text-3xl md:text-4xl font-black text-gray-900 mb-2">Marlon Alejandrino</h3>
                    <p class="text-red-600 font-bold uppercase tracking-[0.2em] text-xs mb-6">VICE MAYOR</p>
                    <p class="text-gray-500 font-medium leading-relaxed px-4">Presiding over the City Council with a focus on legislative excellence and community empowerment.</p>
                </div>
            </div>

            <!-- Councilors -->
            <div data-aos="fade-up" class="mt-32 text-center mb-20">
                <h3 class="text-3xl font-black text-gray-900 tracking-tight">City Councilors</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- District 1 -->
                <div data-aos="fade-up" data-aos-delay="100" onclick="showOfficialDetails('Ramon Encarnacion', 'District 1 Councilor', 'https://lacs.spvalenzuela.com/images/ramon-encarnacion.jpg', 'Advocate for youth development and sports programs across the district. He believes in empowering the next generation through active participation and mentorship.', 'Focus: Youth & Sports', 'https://www.facebook.com/counramon.encarnacion')" class="group bg-white p-8 rounded-[35px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex items-center space-x-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-xl cursor-pointer">
                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-gray-50 shadow-md">
                        <img src="https://lacs.spvalenzuela.com/images/ramon-encarnacion.jpg" class="w-full h-full object-cover transition-all duration-700">
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-xl mb-1">Ramon Encarnacion</h4>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3">District 1 Councilor</p>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Advocate for youth development and sports programs across the district.</p>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="200" onclick="showOfficialDetails('Ricardo Enriquez', 'District 1 Councilor', 'https://lacs.spvalenzuela.com/images/ricardo-enriquez.jpg', 'Championing environmental sustainability and urban greening projects. His vision includes a cleaner and greener Valenzuela for all residents.', 'Focus: Environment & Parks', 'https://www.facebook.com/ricarr.enriquez')" class="group bg-white p-8 rounded-[35px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex items-center space-x-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-xl cursor-pointer">
                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-gray-50 shadow-md">
                        <img src="https://lacs.spvalenzuela.com/images/ricardo-enriquez.jpg" class="w-full h-full object-cover transition-all duration-700">
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-xl mb-1">Ricardo Enriquez</h4>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3">District 1 Councilor</p>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Championing environmental sustainability and urban greening projects.</p>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="300" onclick="showOfficialDetails('Cristina Marie Feliciano', 'District 1 Councilor', 'https://lacs.spvalenzuela.com/images/cristina-marie.jpg', 'Focused on healthcare accessible and women\'s welfare initiatives. She works tirelessly to ensure social services reach every household.', 'Focus: Health & Women\'s Welfare', 'https://www.facebook.com/CrisFeliciano2022')" class="group bg-white p-8 rounded-[35px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex items-center space-x-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-xl cursor-pointer">
                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-gray-50 shadow-md">
                        <img src="https://lacs.spvalenzuela.com/images/cristina-marie.jpg" class="w-full h-full object-cover transition-all duration-700">
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-xl mb-1">Cristina Marie</h4>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3">District 1 Councilor</p>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Focused on healthcare accessible and women's welfare initiatives.</p>
                    </div>
                </div>

                <!-- District 2 & Others -->
                <div data-aos="fade-up" data-aos-delay="400" onclick="showOfficialDetails('Ghogo Deato Lee', 'District 1 Councilor', 'https://lacs.spvalenzuela.com/images/ghogo-deato.jpg', 'Supporting local businesses and economic growth in the community. He advocates for policies that foster a business-friendly environment.', 'Focus: Economy & Trade', 'https://www.facebook.com/ghogo.lee')" class="group bg-white p-8 rounded-[35px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex items-center space-x-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-xl cursor-pointer">
                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-gray-50 shadow-md">
                        <img src="https://lacs.spvalenzuela.com/images/ghogo-deato.jpg" class="w-full h-full object-cover transition-all duration-700">
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-xl mb-1">Ghogo Deato Lee</h4>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3">District 1 Councilor</p>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Supporting local businesses and economic growth in the community.</p>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="500" onclick="showOfficialDetails('Louie Nolasco', 'District 2 Councilor', 'https://lacs.spvalenzuela.com/images/louie-nolasco.jpg', 'Dedicated to education reform and scholarship programs for students. He believes that education is the key to breaking the cycle of poverty.', 'Focus: Education & Student Welfare', 'https://www.facebook.com/nolascolouie1111')" class="group bg-white p-8 rounded-[35px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex items-center space-x-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-xl cursor-pointer">
                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-gray-50 shadow-md">
                        <img src="https://lacs.spvalenzuela.com/images/louie-nolasco.jpg" class="w-full h-full object-cover transition-all duration-700">
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-xl mb-1">Louie Nolasco</h4>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3">District 2 Councilor</p>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Dedicated to education reform and scholarship programs for students.</p>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="600" onclick="showOfficialDetails('Chiqui Carreon', 'District 2 Councilor', 'https://lacs.spvalenzuela.com/images/chiqui-carreon.jpg', 'Promoting culture, arts, and tourism in Valenzuela City. She works to preserve the city\'s heritage while showcasing its modern attractions.', 'Focus: Tourism & Cultural Heritage', 'https://www.facebook.com/profile.php?id=61553756044758')" class="group bg-white p-8 rounded-[35px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex items-center space-x-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-xl cursor-pointer">
                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-gray-50 shadow-md">
                        <img src="https://lacs.spvalenzuela.com/images/chiqui-carreon.jpg" class="w-full h-full object-cover transition-all duration-700">
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-xl mb-1">Chiqui Carreon</h4>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-3">District 2 Councilor</p>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Promoting culture, arts, and tourism in Valenzuela City.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Roots Section -->
    <section id="roots" class="py-32 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <!-- Left Column: Visuals -->
                <div data-aos="fade-right" class="space-y-8">
                    <div class="relative rounded-[40px] overflow-hidden shadow-2xl transition-transform duration-700 hover:scale-[1.02]">
                        <img src="https://lacs.spvalenzuela.com/images/city_hall.png" alt="Valenzuela City Hall" class="w-full h-[500px] object-cover">
                        <!-- Established Badge -->
                        <div class="absolute bottom-10 left-10 text-white z-10">
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-80 mb-2">Established</p>
                            <h3 class="text-6xl font-black tracking-tighter">1623</h3>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>
                    
                    <!-- YouTube CTA -->
                    <a href="https://www.youtube.com/watch?v=IBUCnYd6CaQ" target="_blank" class="flex items-center p-6 bg-gray-50 rounded-3xl border border-gray-100 group transition-all hover:bg-white hover:shadow-xl hover:-translate-y-1">
                        <div class="w-14 h-14 bg-red-600 rounded-2xl flex items-center justify-center text-white text-2xl mr-6 shadow-lg shadow-red-200 group-hover:scale-110 transition-transform">
                            <i class="bi bi-play-fill text-3xl"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-gray-900">Watch City History</h4>
                            <p class="text-xs font-bold text-red-600 transition-colors uppercase tracking-widest mt-1">View on YouTube <i class="bi bi-box-arrow-up-right ml-1"></i></p>
                        </div>
                    </a>
                </div>

                <!-- Right Column: Content -->
                <div data-aos="fade-left" class="pt-8">
                    <h2 class="text-red-600 font-bold tracking-[0.2em] uppercase text-xs mb-4">OUR ROOTS</h2>
                    <h3 class="text-5xl md:text-7xl font-black text-[#002d72] tracking-tight mb-8">The Story of Valenzuela</h3>
                    
                    <div class="space-y-8">
                        <p class="text-gray-500 font-medium text-lg leading-relaxed">
                            Originally known as <strong>Polo</strong>, derived from the Tagalog word <span class="text-gray-900 font-bold">"pulo"</span> meaning island, our city's journey began in 1623. What started as a small settlement of fishermen has evolved into the industrial powerhouse it is today.
                        </p>

                        <!-- Arkong Bato Wide Image -->
                        <div class="rounded-[30px] overflow-hidden h-40 shadow-lg border border-gray-100">
                            <img src="https://lacs.spvalenzuela.com/images/arkong-bato.jpg" alt="Arkong Bato" class="w-full h-full object-cover">
                        </div>

                        <p class="text-gray-500 font-medium text-lg leading-relaxed">
                            Renamed in honor of <strong>Dr. Pio Valenzuela</strong>, a physician and a prominent figure in the Katipunan, the city embodies a legacy of patriotism and service.
                        </p>

                        <!-- Dr. Pio Quote -->
                        <div class="flex items-start space-x-6 py-6 border-y border-gray-50">
                            <img src="https://lacs.spvalenzuela.com/images/pio-valenzuela.jpg" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-xl flex-shrink-0">
                            <p class="text-gray-400 italic font-medium pt-2 text-sm leading-relaxed">
                                "Dr. Pio Valenzuela was a Filipino physician and revolutionary who was a principal member of the Katipunan."
                            </p>
                        </div>

                        <!-- Highlights list -->
                        <div class="grid grid-cols-2 gap-8 pt-4">
                            <div class="flex items-start group">
                                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600 mr-4 flex-shrink-0 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <div>
                                    <h5 class="font-black text-gray-900 text-sm mb-1 uppercase tracking-tight">San Diego Church</h5>
                                    <p class="text-[11px] text-gray-400 font-medium leading-relaxed">One of the city's oldest landmarks and a symbol of faith and history.</p>
                                </div>
                            </div>
                            <div class="flex items-start group">
                                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600 mr-4 flex-shrink-0 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div>
                                    <h5 class="font-black text-gray-900 text-sm mb-1 uppercase tracking-tight">Arkong Bato</h5>
                                    <p class="text-[11px] text-gray-400 font-medium leading-relaxed">The historic stone arch that serves as the boundary and gateway.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Governance: Five Pillars -->
    <section id="governance" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-20">
                <h2 class="text-red-600 font-bold tracking-[0.2em] uppercase text-xs mb-4">GOVERNANCE</h2>
                <h3 class="text-5xl md:text-6xl font-black text-[#002d72] tracking-tight mb-6">Five Pillars of Progress</h3>
                <p class="max-w-3xl mx-auto text-gray-500 font-medium text-lg">Our comprehensive programs are built upon five core pillars dedicated to improving every Valenzuelano's life.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-4 md:px-0">
                <!-- Education -->
                <div data-aos="fade-up" data-aos-delay="100" class="bg-white p-10 rounded-[45px] shadow-[0_10px_50px_-15px_rgba(0,0,0,0.05)] border border-gray-50 flex flex-col group hover:shadow-2xl transition-all duration-500">
                    <div class="w-16 h-16 bg-blue-50/50 rounded-2xl flex items-center justify-center mb-10 transition-colors group-hover:bg-blue-100/50">
                        <i class="bi bi-book text-blue-600 text-2xl"></i>
                    </div>
                    <h4 class="text-2xl font-black text-gray-900 mb-5">Education</h4>
                    <p class="text-gray-500 font-medium leading-relaxed mb-10 flex-grow">Building state-of-the-art schools and providing free supplies through the Education 360 Degrees Investment Program.</p>
                    <div class="rounded-3xl overflow-hidden h-52 shadow-inner">
                        <img src="https://lacs.spvalenzuela.com/images/education.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                </div>

                <!-- Health -->
                <div data-aos="fade-up" data-aos-delay="200" class="bg-white p-10 rounded-[45px] shadow-[0_10px_50px_-15px_rgba(0,0,0,0.05)] border border-gray-50 flex flex-col group hover:shadow-2xl transition-all duration-500">
                    <div class="w-16 h-16 bg-red-50/50 rounded-2xl flex items-center justify-center mb-10 transition-colors group-hover:bg-red-100/50">
                        <i class="bi bi-heart text-red-600 text-2xl"></i>
                    </div>
                    <h4 class="text-2xl font-black text-gray-900 mb-5">Health</h4>
                    <p class="text-gray-500 font-medium leading-relaxed mb-10 flex-grow">Expanding healthcare with VCares and the new Valenzuela City Eye Center for specialized care.</p>
                    <div class="rounded-3xl overflow-hidden h-52 shadow-inner">
                        <img src="https://lacs.spvalenzuela.com/images/health.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                </div>

                <!-- Housing -->
                <div data-aos="fade-up" data-aos-delay="300" class="bg-white p-10 rounded-[45px] shadow-[0_10px_50px_-15px_rgba(0,0,0,0.05)] border border-gray-50 flex flex-col group hover:shadow-2xl transition-all duration-500">
                    <div class="w-16 h-16 bg-green-50/50 rounded-2xl flex items-center justify-center mb-10 transition-colors group-hover:bg-green-100/50">
                        <i class="bi bi-house text-green-600 text-2xl"></i>
                    </div>
                    <h4 class="text-2xl font-black text-gray-900 mb-5">Housing</h4>
                    <p class="text-gray-500 font-medium leading-relaxed mb-10 flex-grow">Providing dignified homes through Disiplina Village, the largest in-city resettlement project in the Philippines.</p>
                    <div class="rounded-3xl overflow-hidden h-52 shadow-inner">
                        <img src="https://lacs.spvalenzuela.com/images/housing.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                </div>
            </div>

            <!-- Bottom Row for 5 items layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8 px-4 md:px-0 max-w-5xl mx-auto">
                <!-- Trade & Industry -->
                <div data-aos="fade-up" data-aos-delay="400" class="bg-white p-10 rounded-[45px] shadow-[0_10px_50px_-15px_rgba(0,0,0,0.05)] border border-gray-50 flex flex-col group hover:shadow-2xl transition-all duration-500">
                    <div class="w-16 h-16 bg-orange-50/50 rounded-2xl flex items-center justify-center mb-10 transition-colors group-hover:bg-orange-100/50">
                        <i class="bi bi-briefcase text-orange-600 text-2xl"></i>
                    </div>
                    <h4 class="text-2xl font-black text-gray-900 mb-5">Trade & Industry</h4>
                    <p class="text-gray-500 font-medium leading-relaxed mb-10 flex-grow">Simplifying business processes through the Paspas Permit and supporting the Pamilyang Valenzuelano program.</p>
                    <div class="rounded-3xl overflow-hidden h-52 shadow-inner">
                        <img src="https://lacs.spvalenzuela.com/images/trade-industry.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                </div>

                <!-- Livelihood -->
                <div data-aos="fade-up" data-aos-delay="500" class="bg-white p-10 rounded-[45px] shadow-[0_10px_50px_-15px_rgba(0,0,0,0.05)] border border-gray-50 flex flex-col group hover:shadow-2xl transition-all duration-500">
                    <div class="w-16 h-16 bg-teal-50/50 rounded-2xl flex items-center justify-center mb-10 transition-colors group-hover:bg-teal-100/50">
                        <i class="bi bi-tree text-teal-600 text-2xl"></i>
                    </div>
                    <h4 class="text-2xl font-black text-gray-900 mb-5">Livelihood</h4>
                    <p class="text-gray-500 font-medium leading-relaxed mb-10 flex-grow">Empowering Valenzuelanos through skills training and sustainable employment opportunities.</p>
                    <div class="rounded-3xl overflow-hidden h-52 shadow-inner">
                        <img src="https://lacs.spvalenzuela.com/images/livelihood.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recognition section -->
    <section id="recognition" class="py-32 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-16">
                <h2 class="text-red-600 font-bold tracking-[0.2em] uppercase text-xs mb-4">RECOGNITION</h2>
                <h3 class="text-5xl md:text-6xl font-black text-[#002d72] tracking-tight">A Legacy of Excellence</h3>
            </div>

            <!-- Swiper Carousel -->
            <div class="swiper recognition-swiper py-4">
                <div class="swiper-wrapper">
                    <!-- Seal of SGLG -->
                    <div class="swiper-slide h-auto">
                        <div class="bg-white rounded-[40px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex flex-col h-full overflow-hidden group">
                            <div class="h-64 overflow-hidden">
                                <img src="https://lacs.spvalenzuela.com/images/seal-sglg.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                            </div>
                            <div class="p-10 text-center flex-grow">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-sm">
                                    <i class="bi bi-patch-check-fill"></i>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Seal of SGLG</h4>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">2024 RECIPIENT</p>
                                <p class="text-gray-500 text-sm leading-relaxed">Seal of Good Local Governance for transparency and accountability.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Good Education -->
                    <div class="swiper-slide h-auto">
                        <div class="bg-white rounded-[40px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex flex-col h-full overflow-hidden group">
                            <div class="h-64 overflow-hidden text-center bg-green-50">
                                <img src="https://lacs.spvalenzuela.com/images/good-education.jpeg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                            </div>
                            <div class="p-10 text-center flex-grow">
                                <div class="w-10 h-10 bg-red-500/10 border border-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-red-600 text-sm font-black">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Good Education</h4>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">2025 SYCIP AWARD</p>
                                <p class="text-gray-500 text-sm leading-relaxed">6th time receiving the Seal of Good Education Governance.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Oro Inodoro Award -->
                    <div class="swiper-slide h-auto">
                        <div class="bg-white rounded-[40px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex flex-col h-full overflow-hidden group">
                            <div class="h-64 overflow-hidden">
                                <img src="https://lacs.spvalenzuela.com/images/oro-inidoro.png" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                            </div>
                            <div class="p-10 text-center flex-grow">
                                <div class="w-10 h-10 text-teal-500 flex items-center justify-center mx-auto mb-6 text-2xl">
                                    <i class="bi bi-droplet-fill"></i>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Oro Inodoro Award</h4>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">2025 GRAND CHAMPION</p>
                                <p class="text-gray-500 text-sm leading-relaxed">National Grand Champion for Best Sanitation Practices.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Good Housekeeping -->
                    <div class="swiper-slide h-auto">
                        <div class="bg-white rounded-[40px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-100 flex flex-col h-full overflow-hidden group">
                            <div class="h-64 overflow-hidden">
                                <img src="https://lacs.spvalenzuela.com/images/trade-industry.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                            </div>
                            <div class="p-10 text-center flex-grow">
                                <div class="w-10 h-10 text-green-500 flex items-center justify-center mx-auto mb-6 text-2xl">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Good Housekeeping</h4>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">2024 DILG PASSER</p>
                                <p class="text-gray-500 text-sm leading-relaxed">Consistently passing the Seal of Good Financial Housekeeping.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Galing Pook -->
                    <div class="swiper-slide h-auto">
                        <div class="bg-white rounded-[40px] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] border border-gray-50 flex flex-col h-full overflow-hidden group">
                            <div class="h-64 overflow-hidden">
                                <img src="https://lacs.spvalenzuela.com/images/galing-pook.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                            </div>
                            <div class="p-10 text-center flex-grow">
                                <div class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="bi bi-trophy-fill"></i>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Galing Pook Award</h4>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">2024 WINNER</p>
                                <p class="text-gray-500 text-sm leading-relaxed">National recognition for excellence in local governance and child protection.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Infrastructure Projects -->
    <!-- Infrastructure Projects -->
    <section id="infrastructure" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-20">
                <h2 class="text-red-600 font-bold tracking-[0.2em] uppercase text-xs mb-4">INFRASTRUCTURE</h2>
                <h3 class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight mb-6">Featured Projects</h3>
                <p class="max-w-3xl mx-auto text-gray-400 font-medium text-lg leading-relaxed">Valenzuela City is committed to continuous development through sustainable infrastructure and community-centered projects.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mb-16">
                <!-- People's Park -->
                <div data-aos="fade-up" data-aos-delay="100" class="group relative h-[450px] overflow-hidden rounded-[35px] shadow-2xl cursor-pointer">
                    <img src="https://lacs.spvalenzuela.com/images/peoples-park.jpg" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-10">
                        <div class="inline-block self-start px-3 py-1 bg-red-600 rounded-lg text-white text-[10px] font-black uppercase tracking-widest mb-4">Recreation</div>
                        <h4 class="text-3xl font-black text-white mb-2">Valenzuela People's Park</h4>
                    </div>
                </div>

                <!-- Disiplina Village -->
                <div data-aos="fade-up" data-aos-delay="200" class="group relative h-[450px] overflow-hidden rounded-[35px] shadow-2xl cursor-pointer">
                    <img src="https://lacs.spvalenzuela.com/images/housing.jpg" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-10">
                        <div class="inline-block self-start px-3 py-1 bg-blue-600 rounded-lg text-white text-[10px] font-black uppercase tracking-widest mb-4">Housing</div>
                        <h4 class="text-3xl font-black text-white mb-2">Disiplina Village</h4>
                        <p class="text-gray-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500">A benchmark for in-city relocation, providing safe and decent housing for informal settler families.</p>
                    </div>
                </div>

                <!-- New Legislative Building -->
                <div data-aos="fade-up" data-aos-delay="300" class="group relative h-[450px] overflow-hidden rounded-[35px] shadow-2xl cursor-pointer">
                    <img src="https://lacs.spvalenzuela.com/images/city-hall.jpg" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-10">
                        <div class="inline-block self-start px-3 py-1 bg-gray-500 rounded-lg text-white text-[10px] font-black uppercase tracking-widest mb-4">Government</div>
                        <h4 class="text-3xl font-black text-white mb-2">New Legislative Building</h4>
                        <p class="text-gray-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500">Modernizing governance facilities to better serve the people of Valenzuela.</p>
                    </div>
                </div>

                <!-- Flood Control -->
                <div data-aos="fade-up" data-aos-delay="400" class="group relative h-[450px] overflow-hidden rounded-[35px] shadow-2xl cursor-pointer">
                    <img src="https://lacs.spvalenzuela.com/images/flood_control.png" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-10">
                        <div class="inline-block self-start px-3 py-1 bg-teal-500 rounded-lg text-white text-[10px] font-black uppercase tracking-widest mb-4">Resilience</div>
                        <h4 class="text-3xl font-black text-white mb-2">Flood Control Infrastructure</h4>
                        <p class="text-gray-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500">Advanced pumping stations and catchment basins to mitigate flooding and ensure safety.</p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/public/infrastructure.php" class="inline-flex items-center text-red-600 font-bold uppercase tracking-widest text-xs hover:text-red-700 transition-colors group">
                    View All Infrastructure Projects 
                    <i class="bi bi-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Heritage & Progress: Landmarks Swiper -->
    <section id="landmarks" class="py-32 bg-[#f8fafc] overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-16">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Heritage & Progress</h2>
                <h3 class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-6">City Landmarks</h3>
                <p class="max-w-2xl mx-auto text-gray-500 font-medium text-lg leading-relaxed">Discover the historical sites and modern infrastructures that define Valenzuela City.</p>
            </div>

            <div class="relative group" data-aos="zoom-in">
                <!-- Swiper -->
                <div class="swiper landmarks-swiper rounded-[40px] shadow-2xl overflow-hidden aspect-[16/9] md:aspect-[21/9]">
                    <div class="swiper-wrapper">
                        <!-- Slide 1: Museo ni Dr. Pio -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/museo-val.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1554907984-15263bfd63bd?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Museo ni Dr. Pio Valenzuela</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">The ancestral home and commemorative museum of our city's namesake.</p>
                            </div>
                        </div>

                        <!-- Slide 2: Family Park -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/family-park.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1582234372722-50d7ccc30ebd?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Valenzuela City Family Park</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-2xl leading-relaxed">Nature-themed recreational space featuring a playground and green canopy.</p>
                            </div>
                        </div>

                        <!-- Slide 3: People's Park -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/peoples-park.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Valenzuela People's Park</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-2xl leading-relaxed">A major urban park offering interactive fountains, amphitheaters, and lush gardens.</p>
                            </div>
                        </div>

                        <!-- Slide 4: Bell Tower -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/bell-tower.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1548013146-72479768bbaa?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Bell Tower of San Diego De Alcala Church</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">A 17th-century historical belfry standing as a witness to the city's rich past.</p>
                            </div>
                        </div>

                        <!-- Slide 5: Arkong Bato -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/arkong-bato.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Arkong Bato Park</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">A historical stone arch built in 1910 marking the boundary between Bulacan and Rizal.</p>
                            </div>
                        </div>

                        <!-- Slide 6: WES Arena -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/wes-arena.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1504450758481-7338eba7524a?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">WES Arena</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">State-of-the-art sports and multi-purpose indoor facility.</p>
                            </div>
                        </div>

                        <!-- Slide 7: Polo Mini Park -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/polo-park.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Polo Mini Park</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">A historical plaza at the heart of the city's oldest district.</p>
                            </div>
                        </div>

                        <!-- Slide 8: Tagalag -->
                        <div class="swiper-slide relative group">
                            <img src="https://valenzuela.gov.ph/wp-content/uploads/2024/01/Tagalag-Fishing-Village-scaled.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1544257750-572358f5da22?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Tagalag Fishing Village</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">The city's premier eco-tourism destination promoting sustainable livelihood.</p>
                            </div>
                        </div>

                        <!-- Slide 9: Fatima Shrine -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/nat-shrine.jpg" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1548013146-72479768bbaa?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Fatima National Shrine</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">A place of pilgrimage and history, the National Shrine of Our Lady of Fatima.</p>
                            </div>
                        </div>

                        <!-- Slide 10: City Hall -->
                        <div class="swiper-slide relative group">
                            <img src="https://lacs.spvalenzuela.com/images/city_hall.png" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?auto=format&fit=crop&q=80&w=2000'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-12 left-12 right-12 text-white">
                                <h4 class="text-3xl md:text-6xl font-black mb-4 tracking-tighter">Valenzuela City Hall</h4>
                                <p class="text-lg md:text-xl text-gray-300 font-medium max-w-3xl leading-relaxed">The seat of local government, serving every Valenzuelano with compassion and excellence.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="swiper-button-next !text-white after:!content-[''] bg-white/10 backdrop-blur-md w-16 h-16 rounded-full border border-white/20 opacity-0 group-hover:opacity-100 transition-all hover:bg-white/20 flex items-center justify-center">
                        <i class="bi bi-chevron-right text-2xl"></i>
                    </div>
                    <div class="swiper-button-prev !text-white after:!content-[''] bg-white/10 backdrop-blur-md w-16 h-16 rounded-full border border-white/20 opacity-0 group-hover:opacity-100 transition-all hover:bg-white/20 flex items-center justify-center">
                        <i class="bi bi-chevron-left text-2xl"></i>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="swiper-pagination !bottom-8 !flex !justify-center gap-2"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Updates/News -->
    <section id="updates" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="mb-20 flex justify-between items-end">
                <div>
                    <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Updates</h2>
                    <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight">Latest News</p>
                </div>
                <div class="hidden md:block">
                    <a href="news.php" class="inline-flex items-center text-red-600 font-bold text-sm hover:text-red-700 transition-colors group">
                        Read All News 
                        <i class="bi bi-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </a>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <!-- News Card 1 -->
                <div data-aos="fade-up" data-aos-delay="100" class="bg-white rounded-[40px] overflow-hidden shadow-[0_10px_40px_-20px_rgba(0,0,0,0.1)] border border-gray-50 group hover:-translate-y-2 transition-all duration-500 cursor-pointer" onclick="window.location.href='news.php'">
                    <div class="h-56 overflow-hidden relative">
                        <img src="https://lacs.spvalenzuela.com/images/oro-inidoro.png" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="text-red-600 font-black text-[10px] uppercase tracking-widest">NOV 2025</span>
                        </div>
                        <h4 class="text-2xl font-black text-gray-900 mb-4 group-hover:text-red-600 transition-colors leading-tight">Maynilad's 2025 Oro Inodoro Award</h4>
                        <p class="text-gray-500 font-medium text-sm leading-relaxed mb-6">Valenzuela City wins prestigious award for environmental sanitation management.</p>
                    </div>
                </div>

                <!-- News Card 2 -->
                <div data-aos="fade-up" data-aos-delay="200" class="bg-white rounded-[40px] overflow-hidden shadow-[0_10px_40px_-20px_rgba(0,0,0,0.1)] border border-gray-50 group hover:-translate-y-2 transition-all duration-500 cursor-pointer" onclick="window.location.href='news.php'">
                    <div class="h-56 overflow-hidden relative">
                        <img src="https://lacs.spvalenzuela.com/images/housing.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="text-red-600 font-black text-[10px] uppercase tracking-widest">JAN 2026</span>
                        </div>
                        <h4 class="text-2xl font-black text-gray-900 mb-4 group-hover:text-red-600 transition-colors leading-tight">P14M Housing Assistance Granted</h4>
                        <p class="text-gray-500 font-medium text-sm leading-relaxed mb-6">SHFC grants over Php 14 Million for Wawang Pulo Homeowners' Association.</p>
                    </div>
                </div>

                <!-- News Card 3 -->
                <div data-aos="fade-up" data-aos-delay="300" class="bg-white rounded-[40px] overflow-hidden shadow-[0_10px_40px_-20px_rgba(0,0,0,0.1)] border border-gray-50 group hover:-translate-y-2 transition-all duration-500 cursor-pointer" onclick="window.location.href='news.php'">
                    <div class="h-56 overflow-hidden relative">
                        <img src="https://lacs.spvalenzuela.com/images/flood_control.png" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.src='https://images.unsplash.com/photo-1581094794329-c8112a89af12?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="text-red-600 font-black text-[10px] uppercase tracking-widest">AUG 2025</span>
                        </div>
                        <h4 class="text-2xl font-black text-gray-900 mb-4 group-hover:text-red-600 transition-colors leading-tight">PANATAG Flood Control Launch</h4>
                        <p class="text-gray-500 font-medium text-sm leading-relaxed mb-6">City launches comprehensive flood control resilience initiatives with UPRI.</p>
                    </div>
                </div>
            </div>

            <!-- Mobile Read All Button -->
            <div class="md:hidden text-center mt-12" data-aos="fade-up">
                <a href="news.php" class="inline-flex items-center text-red-600 font-bold text-sm hover:text-red-700 transition-colors group">
                    Read All News 
                    <i class="bi bi-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="legal-footer" class="bg-white py-16 md:py-24 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-12 md:gap-0">
                <div class="w-full md:w-auto text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start mb-6">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-10 w-10 md:h-12 md:w-12 mr-3 md:mr-4 shadow-sm rounded-full">
                        <div class="text-[#002d72] font-black text-xl md:text-2xl tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></div>
                    </div>
                    <p class="text-gray-400 font-black text-[10px] md:text-xs uppercase tracking-[0.15em] max-w-xs mx-auto md:ml-0 md:mr-0 leading-relaxed md:leading-loose">
                        Official Legislative Records <br class="md:hidden"> Management System. <br>
                        City Government of Valenzuela.
                    </p>
                </div>
                
                <div class="w-full md:w-auto grid grid-cols-2 gap-8 sm:gap-12 md:gap-24">
                    <div class="text-center md:text-left">
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Quick Links</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black text-slate-600">
                            <li><a href="#leadership" class="hover:text-red-600 transition-colors uppercase tracking-wider">Officials</a></li>
                            <li><a href="#roots" class="hover:text-red-600 transition-colors uppercase tracking-wider">Our History</a></li>
                            <li><a href="#governance" class="hover:text-red-600 transition-colors uppercase tracking-wider">Governance</a></li>
                            <li><a href="#updates" class="hover:text-red-600 transition-colors uppercase tracking-wider">News & Updates</a></li>
                        </ul>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Legal</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black text-slate-600">
                            <li><a href="<?php echo HELP_URL; ?>/views/faq.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">FAQ</a></li>
                            <li><a href="<?php echo HELP_URL; ?>/views/contact.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Support</a></li>
                            <li><a href="<?php echo HELP_URL; ?>/views/privacy.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Privacy Policy</a></li>
                            <li><a href="<?php echo HELP_URL; ?>/views/terms.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-16 md:mt-20 pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-[9px] md:text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] text-center md:text-left">
                <div class="mb-4 md:mb-0">© <?php echo date('Y'); ?> City of Valenzuela. <br class="md:hidden"> Distributed for transparency.</div>
                <div>Legislative Records Department • v1.0.0</div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="no-ripple fixed bottom-12 right-6 md:bottom-12 md:right-6 z-[9999] w-12 h-12 md:w-[46px] md:h-[46px] bg-red-600 text-white rounded-full border-3 border-white cursor-pointer shadow-lg shadow-red-600/50 flex items-center justify-center transition-all duration-300 hover:bg-red-700 hover:scale-110 active:scale-95 hidden"
            title="Back to top"
            aria-label="Scroll to top">
        <i class="bi bi-arrow-up text-xl md:text-base leading-none pointer-events-none"></i>
    </button>

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

        // Initialize Swiper for Recognition
        const swiper = new Swiper('.recognition-swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            speed: 8000,
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },
            freeMode: true,
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 4,
                },
            }
        });

        // Initialize Swiper for Landmarks
        const landmarksSwiper = new Swiper('.landmarks-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>

    <!-- Official Details Modal -->
    <div id="officialModal" class="fixed inset-0 z-[100] hidden overflow-y-auto">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeOfficialModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4 md:p-8">
            <div class="bg-white rounded-[32px] md:rounded-[50px] shadow-2xl w-full max-w-2xl overflow-hidden relative animate-modal-up">
                <button type="button" onclick="closeOfficialModal()" class="absolute top-4 right-4 md:top-8 md:right-8 text-gray-400 hover:text-red-600 transition-colors z-10 bg-white/80 backdrop-blur-md md:bg-gray-50 h-10 w-10 rounded-full flex items-center justify-center shadow-lg">
                    <i class="bi bi-x-lg"></i>
                </button>
                
                <div class="flex flex-col md:flex-row">
                    <!-- Image Panel -->
                    <div class="w-full md:w-2/5 relative h-80 md:h-auto bg-gray-50">
                        <img id="modalImg" src="" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    
                    <!-- Content Panel -->
                    <div class="w-full md:w-3/5 p-8 md:p-12">
                        <div class="mb-8">
                            <span id="modalRole" class="inline-block px-3 py-1 bg-red-50 text-red-600 rounded-lg text-[10px] font-black uppercase tracking-[0.2em] mb-4"></span>
                            <h2 id="modalName" class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight leading-none mb-2"></h2>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-xs">Biography</h4>
                                <p id="modalBio" class="text-gray-500 font-medium leading-relaxed"></p>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-xs">Advocacy</h4>
                                <div id="modalFocus" class="text-gray-900 font-bold text-sm"></div>
                            </div>
                        </div>

                        <div class="mt-8 md:mt-12 flex space-x-4">
                            <a id="modalFb" href="#" target="_blank" class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all text-xl">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="h-10 w-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all text-xl">
                                <i class="bi bi-envelope-fill"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes modal-up {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slide-in-top {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce-in {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        .animate-modal-up {
            animation: modal-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out forwards;
        }
        .animate-slide-in-top {
            animation: slide-in-top 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-bounce-in {
            animation: bounce-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .nav-dropdown {
            left: 50%;
            transform: translateX(-50%) translateY(8px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s linear;
            transition-delay: 0.35s;
        }
        .group:hover .nav-dropdown {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateX(-50%) translateY(0);
            transition-delay: 0s;
        }
    </style>

    <script>
        function showOfficialDetails(name, role, imgSrc, bio, focus, fbLink) {
            document.getElementById('modalName').innerText = name;
            document.getElementById('modalRole').innerText = role;
            document.getElementById('modalImg').src = imgSrc;
            document.getElementById('modalBio').innerText = bio;
            document.getElementById('modalFocus').innerText = focus;
            document.getElementById('modalFb').href = fbLink || '#';
            
            const modal = document.getElementById('officialModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeOfficialModal() {
            const modal = document.getElementById('officialModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close on ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeOfficialModal();
                closeMobileMenu();
            }
        });

        // Mobile Menu Logic
        const mobileToggle = document.getElementById('mobile-landing-toggle');
        const mobileClose = document.getElementById('mobile-landing-close');
        const mobileMenu = document.getElementById('mobile-landing-menu');
        const mobileLinks = document.querySelectorAll('.mobile-nav-link');

        function openMobileMenu() {
            mobileMenu.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobileMenu.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        mobileToggle.addEventListener('click', openMobileMenu);
        mobileClose.addEventListener('click', closeMobileMenu);
        
        // Close menu when clicking links
        mobileLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // Back to Top Button
        (function() {
            const btn = document.getElementById('back-to-top');
            if (!btn) return;

            function checkScroll() {
                let scrolled = false;
                if (window.pageYOffset > 200 || document.documentElement.scrollTop > 200) {
                    scrolled = true;
                }
                const main = document.querySelector('main');
                if (main && main.scrollTop > 200) {
                    scrolled = true;
                }
                document.querySelectorAll('.overflow-y-auto').forEach(el => {
                    if (el.scrollTop > 200) {
                        scrolled = true;
                    }
                });

                if (scrolled) {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                } else {
                    btn.classList.add('hidden');
                    btn.classList.remove('flex');
                }
            }

            function scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                const main = document.querySelector('main');
                if (main) main.scrollTo({ top: 0, behavior: 'smooth' });
                document.querySelectorAll('.overflow-y-auto').forEach(el => {
                    el.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            btn.onclick = scrollToTop;
            window.addEventListener('scroll', checkScroll, { passive: true });
            const main = document.querySelector('main');
            if (main) {
                main.addEventListener('scroll', checkScroll, { passive: true });
            }
            document.querySelectorAll('.overflow-y-auto').forEach(el => {
                el.addEventListener('scroll', checkScroll, { passive: true });
            });
            checkScroll();
        })();
    </script>

    <!-- Dark Mode Toggle Script -->
    <script>
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateDarkModeIcons(isDark);
            document.querySelector('meta[name="theme-color"]').content = isDark ? '#0a0a0a' : '#dc2626';
        }

        function updateDarkModeIcons(isDark) {
            const desktopIcon = document.getElementById('darkModeIcon');
            const mobileIcon = document.getElementById('darkModeIconMobile');
            if (desktopIcon) desktopIcon.className = isDark ? 'bi bi-sun-fill text-sm' : 'bi bi-moon-fill text-sm';
            if (mobileIcon) mobileIcon.className = isDark ? 'bi bi-sun-fill text-sm' : 'bi bi-moon-fill text-sm';
        }

        // Initialize dark mode icons on load
        (function() {
            const isDark = document.documentElement.classList.contains('dark');
            updateDarkModeIcons(isDark);
        })();
    </script>
</body>
</html>
