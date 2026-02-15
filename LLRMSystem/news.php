<?php
/**
 * News and Updates Archive Page
 */
require_once __DIR__ . '/modules/core/config/config.php';

$pageTitle = "City News & Updates";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#dc2626">
    <title><?php echo $pageTitle; ?> - City Government of Valenzuela</title>
    
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
    </style>
</head>
<body class="bg-[#fcfdfd] text-gray-900 overflow-x-hidden">
    <!-- Navigation -->
    <nav id="guest-nav" class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100/50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center group cursor-pointer flex-shrink-0">
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

                <a href="<?php echo BASE_URL; ?>/index.php#updates" class="inline-flex items-center text-gray-500 hover:text-red-600 font-bold text-sm transition-all group">
                    <i class="bi bi-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="pt-32 pb-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up">
                <h1 class="text-5xl md:text-7xl font-black text-gray-900 tracking-tight leading-none mb-4">City News & Updates</h1>
                <p class="text-gray-500 text-lg md:text-xl font-medium max-w-2xl">Complete archive of city government announcements and updates.</p>
            </div>
        </div>
    </section>

    <!-- News List -->
    <section class="pb-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-12">
                <!-- News Item 1: Oro Inodoro -->
                <div data-aos="fade-up" class="group bg-white rounded-[40px] border border-gray-100 p-6 md:p-10 flex flex-col md:flex-row gap-8 md:gap-12 hover:shadow-[0_40px_80px_-20px_rgba(0,0,0,0.08)] transition-all duration-500">
                    <div class="w-full md:w-[400px] h-64 md:h-auto overflow-hidden rounded-[30px] flex-shrink-0">
                        <img src="https://lacs.spvalenzuela.com/images/oro-inidoro.png" alt="Oro Inodoro Award" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-red-600 font-black text-[12px] uppercase tracking-widest">November 12, 2025</span>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-black uppercase tracking-wider">Awards</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 group-hover:text-red-600 transition-colors">Valenzuela City Receives Oro Inodoro Award</h2>
                        <p class="text-gray-500 text-lg font-medium leading-relaxed mb-8">Acknowledged for its exceptional environmental sanitation management, Valenzuela City was honored as the grand champion in Maynilad's search for cities with best sanitation practices.</p>
                        <div>
                            <a href="#" class="inline-flex items-center px-8 py-4 bg-[#0a111a] text-white rounded-2xl font-black text-sm hover:bg-gray-800 transition-all">
                                Read Full Article
                            </a>
                        </div>
                    </div>
                </div>

                <!-- News Item 2: Housing -->
                <div data-aos="fade-up" class="group bg-white rounded-[40px] border border-gray-100 p-6 md:p-10 flex flex-col md:flex-row gap-8 md:gap-12 hover:shadow-[0_40px_80px_-20px_rgba(0,0,0,0.08)] transition-all duration-500">
                    <div class="w-full md:w-[400px] h-64 md:h-auto overflow-hidden rounded-[30px] flex-shrink-0">
                        <img src="https://lacs.spvalenzuela.com/images/housing.jpg" alt="Housing Assistance" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-red-600 font-black text-[12px] uppercase tracking-widest">January 15, 2026</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-black uppercase tracking-wider">Housing</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 group-hover:text-red-600 transition-colors">P14M Housing Assistance for Wawang Pulo</h2>
                        <p class="text-gray-500 text-lg font-medium leading-relaxed mb-8">The SHFC has officially turned over checks amounting to Php 14,025,000 to members of the Wawang Pulo Homeowners' Association, marking a new chapter for 117 families.</p>
                        <div>
                            <a href="#" class="inline-flex items-center px-8 py-4 bg-[#0a111a] text-white rounded-2xl font-black text-sm hover:bg-gray-800 transition-all">
                                Read Full Article
                            </a>
                        </div>
                    </div>
                </div>

                <!-- News Item 3: Flood Control -->
                <div data-aos="fade-up" class="group bg-white rounded-[40px] border border-gray-100 p-6 md:p-10 flex flex-col md:flex-row gap-8 md:gap-12 hover:shadow-[0_40px_80px_-20px_rgba(0,0,0,0.08)] transition-all duration-500">
                    <div class="w-full md:w-[400px] h-64 md:h-auto overflow-hidden rounded-[30px] flex-shrink-0">
                        <img src="https://lacs.spvalenzuela.com/images/flood_control.png" alt="Flood Control" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1581094794329-c8112a89af12?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-red-600 font-black text-[12px] uppercase tracking-widest">August 28, 2025</span>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-black uppercase tracking-wider">Safety</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 group-hover:text-red-600 transition-colors">PANATAG Flood Control Launch</h2>
                        <p class="text-gray-500 text-lg font-medium leading-relaxed mb-8">The city government partners with UPRI to launch PANATAG, a digital flood monitoring and warning system designed to enhance disaster preparedness.</p>
                        <div>
                            <a href="#" class="inline-flex items-center px-8 py-4 bg-[#0a111a] text-white rounded-2xl font-black text-sm hover:bg-gray-800 transition-all">
                                Read Full Article
                            </a>
                        </div>
                    </div>
                </div>

                <!-- News Item 4: BOSS -->
                <div data-aos="fade-up" class="group bg-white rounded-[40px] border border-gray-100 p-6 md:p-10 flex flex-col md:flex-row gap-8 md:gap-12 hover:shadow-[0_40px_80px_-20px_rgba(0,0,0,0.08)] transition-all duration-500">
                    <div class="w-full md:w-[400px] h-64 md:h-auto overflow-hidden rounded-[30px] flex-shrink-0">
                        <img src="https://lacs.spvalenzuela.com/images/trade-industry.jpg" alt="BOSS 2026" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1554469384-e58fac16e23a?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-red-600 font-black text-[12px] uppercase tracking-widest">January 05, 2026</span>
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-[10px] font-black uppercase tracking-wider">Economy</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 group-hover:text-red-600 transition-colors">Valenzuela Launches 2026 BOSS</h2>
                        <p class="text-gray-500 text-lg font-medium leading-relaxed mb-8">City Government kicks off the 2026 Business One-Stop Shop (BOSS) to streamline permit renewals and encourage online transactions.</p>
                        <div>
                            <a href="#" class="inline-flex items-center px-8 py-4 bg-[#0a111a] text-white rounded-2xl font-black text-sm hover:bg-gray-800 transition-all">
                                Read Full Article
                            </a>
                        </div>
                    </div>
                </div>

                <!-- News Item 5: Education Summit -->
                <div data-aos="fade-up" class="group bg-white rounded-[40px] border border-gray-100 p-6 md:p-10 flex flex-col md:flex-row gap-8 md:gap-12 hover:shadow-[0_40px_80px_-20px_rgba(0,0,0,0.08)] transition-all duration-500">
                    <div class="w-full md:w-[400px] h-64 md:h-auto overflow-hidden rounded-[30px] flex-shrink-0">
                        <img src="https://lacs.spvalenzuela.com/images/education.jpg" alt="Education Summit" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1577896851231-70ef1460370e?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="flex-grow flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-red-600 font-black text-[12px] uppercase tracking-widest">January 22, 2026</span>
                            <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-[10px] font-black uppercase tracking-wider">Education</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-6 group-hover:text-red-600 transition-colors">1st Inclusive Education Summit</h2>
                        <p class="text-gray-500 text-lg font-medium leading-relaxed mb-8">Valenzuela holds its first Inclusive Summit, celebrating a decade of progress in providing accessible education for children with special needs.</p>
                        <div>
                            <a href="#" class="inline-flex items-center px-8 py-4 bg-[#0a111a] text-white rounded-2xl font-black text-sm hover:bg-gray-800 transition-all">
                                Read Full Article
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-16 md:py-24 border-t border-gray-100">
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
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Navigate</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black text-slate-600">
                            <li><a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Home</a></li>
                            <li><a href="<?php echo HELP_URL; ?>/views/faq.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">FAQ</a></li>
                            <li><a href="<?php echo HELP_URL; ?>/views/contact.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Support</a></li>
                        </ul>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Legal</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black text-slate-600">
                            <li><a href="<?php echo HELP_URL; ?>/views/privacy.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Privacy</a></li>
                            <li><a href="<?php echo HELP_URL; ?>/views/terms.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Terms</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-16 md:mt-20 pt-8 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-[9px] md:text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] text-center md:text-left">
                <div class="mb-4 md:mb-0">&copy; <?php echo date('Y'); ?> City of Valenzuela. <br class="md:hidden"> Distributed for transparency.</div>
                <div>Legislative Records Department</div>
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
            const nav = document.getElementById('guest-nav');
            if (!nav) return;
            if (window.scrollY > 50) {
                nav.classList.add('shadow-lg', 'bg-white/95');
                nav.classList.remove('bg-white/80');
            } else {
                nav.classList.remove('shadow-lg', 'bg-white/95');
                nav.classList.add('bg-white/80');
            }
        });

        // Mobile menu toggle for news page
    </script>
</body>
</html>
