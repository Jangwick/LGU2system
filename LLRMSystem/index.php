<?php
/**
 * Root index file - Landing Page
 */
require_once __DIR__ . '/modules/core/config/config.php';

// Check if user is already logged in with a valid session
checkAlreadyLoggedIn();

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
            <div class="flex justify-between items-center h-16 md:h-20">
                <div class="flex items-center group cursor-pointer flex-shrink-0">
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
                </div>
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#leadership" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Officials</a>
                    <a href="#roots" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">History</a>
                    <a href="#governance" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Governance</a>
                    <a href="#recognition" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">Awards</a>
                </div>
                <div class="flex items-center space-x-1 md:space-x-6">
                    <a href="<?php echo LOGIN_URL; ?>" class="text-gray-600 hover:text-red-600 font-bold px-3 py-2 text-[12px] md:text-sm transition-all whitespace-nowrap">Sign In</a>
                    <a href="<?php echo REGISTER_URL; ?>" class="btn-modern bg-red-600 hover:bg-red-700 text-white font-black px-4 md:px-6 py-2 md:py-2.5 rounded-full text-[12px] md:text-sm shadow-xl shadow-red-200/50 whitespace-nowrap">
                        Get Started
                    </a>
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
                    <a href="<?php echo REGISTER_URL; ?>" class="w-full sm:w-auto btn-modern bg-gray-900 hover:bg-black text-white font-black px-8 md:px-12 py-4 md:py-5 rounded-xl md:rounded-2xl text-base md:text-lg shadow-2xl">
                        Start Your Journey
                        <i class="bi bi-arrow-right-short ml-1 text-2xl align-middle"></i>
                    </a>
                    <a href="#features" class="w-full sm:w-auto btn-modern bg-white hover:bg-gray-50 text-gray-900 font-bold px-8 md:px-12 py-4 md:py-5 rounded-xl md:rounded-2xl text-base md:text-lg border border-gray-200 shadow-sm">
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

    <!-- Leadership Section -->
    <section id="leadership" class="py-32 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-24">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Leadership</h2>
                <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-6">City Officials</p>
                <div class="h-2 w-20 bg-red-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-12 max-w-4xl mx-auto text-center">
                <!-- Mayor -->
                <div data-aos="fade-right" class="group">
                    <div class="relative overflow-hidden rounded-[40px] mb-8 shadow-2xl transition-transform duration-500 group-hover:scale-[1.02]">
                        <img src="https://lacs.spvalenzuela.com/images/mayor_wes.png?v=1771093587" alt="Mayor Wes Gatchalian" class="w-full grayscale group-hover:grayscale-0 transition-all duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-red-900/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 mb-2">Wes Gatchalian</h3>
                    <p class="text-red-600 font-bold uppercase tracking-widest text-sm mb-4">CITY MAYOR</p>
                    <p class="text-gray-500 font-medium leading-relaxed">Leading Valenzuela City towards a progressive and livable future through innovative governance and compassionate public service.</p>
                </div>

                <!-- Vice Mayor -->
                <div data-aos="fade-left" class="group">
                    <div class="relative overflow-hidden rounded-[40px] mb-8 shadow-2xl transition-transform duration-500 group-hover:scale-[1.02]">
                        <img src="https://lacs.spvalenzuela.com/images/vice_marlon.png?v=1771093587" alt="Vice Mayor Marlon Alejandrino" class="w-full grayscale group-hover:grayscale-0 transition-all duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#002d72]/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 mb-2">Marlon Alejandrino</h3>
                    <p class="text-red-600 font-bold uppercase tracking-widest text-sm mb-4">VICE MAYOR</p>
                    <p class="text-gray-500 font-medium leading-relaxed">Presiding over the City Council with a focus on legislative excellence and community empowerment.</p>
                </div>
            </div>

            <!-- Councilors -->
            <div data-aos="fade-up" class="mt-32 text-center mb-16">
                <p class="text-2xl font-black text-gray-900 tracking-tight">City Councilors</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-6 gap-6">
                <!-- District 1 -->
                <div data-aos="zoom-in" data-aos-delay="100" class="group text-center">
                    <div class="aspect-square rounded-2xl overflow-hidden mb-4 shadow-lg border border-gray-100">
                        <img src="https://lacs.spvalenzuela.com/images/ramon-encarnacion.jpg" alt="Ramon Encarnacion" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h4 class="font-black text-gray-900 text-sm">Ramon Encarnacion</h4>
                    <p class="text-[9px] text-red-600 font-bold uppercase tracking-widest">District 1</p>
                </div>
                <!-- Add more councilors... -->
                <div data-aos="zoom-in" data-aos-delay="200" class="group text-center">
                    <div class="aspect-square rounded-2xl overflow-hidden mb-4 shadow-lg border border-gray-100">
                        <img src="https://lacs.spvalenzuela.com/images/ricardo-enriquez.jpg" alt="Ricardo Enriquez" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h4 class="font-black text-gray-900 text-sm">Ricardo Enriquez</h4>
                    <p class="text-[9px] text-red-600 font-bold uppercase tracking-widest">District 1</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="300" class="group text-center">
                    <div class="aspect-square rounded-2xl overflow-hidden mb-4 shadow-lg border border-gray-100">
                        <img src="https://lacs.spvalenzuela.com/images/cristina-marie.jpg" alt="Cristina Marie Feliciano" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h4 class="font-black text-gray-900 text-sm">Cristina Marie</h4>
                    <p class="text-[9px] text-red-600 font-bold uppercase tracking-widest">District 1</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="400" class="group text-center">
                    <div class="aspect-square rounded-2xl overflow-hidden mb-4 shadow-lg border border-gray-100">
                        <img src="https://lacs.spvalenzuela.com/images/ghogo-deato.jpg" alt="Ghogo Deato Lee" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h4 class="font-black text-gray-900 text-sm">Ghogo Deato Lee</h4>
                    <p class="text-[9px] text-red-600 font-bold uppercase tracking-widest">District 1</p>
                </div>
                <!-- District 2 -->
                <div data-aos="zoom-in" data-aos-delay="500" class="group text-center">
                    <div class="aspect-square rounded-2xl overflow-hidden mb-4 shadow-lg border border-gray-100">
                        <img src="https://lacs.spvalenzuela.com/images/louie-nolasco.jpg" alt="Louie Nolasco" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h4 class="font-black text-gray-900 text-sm">Louie Nolasco</h4>
                    <p class="text-[9px] text-red-600 font-bold uppercase tracking-widest">District 2</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="600" class="group text-center">
                    <div class="aspect-square rounded-2xl overflow-hidden mb-4 shadow-lg border border-gray-100">
                        <img src="https://lacs.spvalenzuela.com/images/chiqui-carreon.jpg" alt="Chiqui Carreon" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h4 class="font-black text-gray-900 text-sm">Chiqui Carreon</h4>
                    <p class="text-[9px] text-red-600 font-bold uppercase tracking-widest">District 2</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Roots & Governance -->
    <section id="roots" class="py-32 bg-[#f8fafc] overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-24 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Our Roots</h2>
                    <p class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight mb-8">The Story of Valenzuela</p>
                    <div class="prose prose-lg text-gray-500 font-medium leading-relaxed mb-8">
                        <p>Originally known as Polo, derived from the Tagalog word "pulo" meaning island, our city's journey began in 1623. What started as a small settlement of fishermen has evolved into the industrial powerhouse it is today.</p>
                        <p class="mt-4">Renamed in honor of Dr. Pio Valenzuela, a physician and a prominent figure in the Katipunan, the city embodies a legacy of patriotism and service.</p>
                    </div>
                    <div class="flex items-center space-x-6">
                        <div class="flex -space-x-4">
                            <img src="https://lacs.spvalenzuela.com/images/arkong-bato.jpg" class="w-16 h-16 rounded-full border-4 border-white shadow-lg object-cover">
                            <img src="https://lacs.spvalenzuela.com/images/pio-valenzuela.jpg" class="w-16 h-16 rounded-full border-4 border-white shadow-lg object-cover">
                        </div>
                        <div class="text-xs font-black uppercase tracking-widest text-[#002d72]">Est. 1623</div>
                    </div>
                </div>
                <div data-aos="fade-left" class="relative">
                    <div class="aspect-square bg-white rounded-[60px] shadow-2xl overflow-hidden rotate-3 hover:rotate-0 transition-transform duration-500">
                        <img src="https://lacs.spvalenzuela.com/images/city_hall.png" alt="Valenzuela History" class="w-full h-full object-cover">
                    </div>
                    <!-- Stats badge -->
                    <div class="absolute -bottom-8 -left-8 bg-black text-white p-10 rounded-[40px] shadow-2xl">
                        <div class="text-4xl font-black mb-1">400+</div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-red-500">Years of History</div>
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
    <section id="recognition" class="py-32 bg-[#050505] text-white relative overflow-hidden">
        <!-- Background light effect -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-red-600/10 blur-[150px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div data-aos="fade-up" class="text-center mb-24">
                <h2 class="text-red-500 font-black tracking-[0.4em] uppercase text-[10px] md:text-xs mb-6">Recognition</h2>
                <p class="text-5xl md:text-8xl font-black tracking-tighter leading-none mb-8">A Legacy of <br class="hidden md:block"> Excellence</p>
                <div class="h-1.5 w-24 bg-red-600 mx-auto rounded-full shadow-[0_0_20px_rgba(220,38,38,0.5)]"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-10">
                <!-- Award 1 -->
                <div data-aos="zoom-in" data-aos-delay="100" class="group relative p-10 bg-white/[0.03] backdrop-blur-sm rounded-[50px] border border-white/10 hover:border-red-600/50 transition-all duration-500 hover:-translate-y-4 hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10 text-center">
                        <img src="https://lacs.spvalenzuela.com/images/galing-pook.jpg" class="h-28 mx-auto mb-10 object-contain transition-transform duration-500 group-hover:scale-110">
                        <h4 class="font-black text-2xl mb-2 text-white group-hover:text-red-500 transition-colors">Galing Pook</h4>
                        <div class="inline-block px-4 py-1.5 rounded-full bg-red-600/10 border border-red-600/20">
                            <p class="text-red-500 text-[10px] font-black uppercase tracking-[0.2em]">2024 WINNER</p>
                        </div>
                    </div>
                </div>

                <!-- Award 2 -->
                <div data-aos="zoom-in" data-aos-delay="200" class="group relative p-10 bg-white/[0.03] backdrop-blur-sm rounded-[50px] border border-white/10 hover:border-red-600/50 transition-all duration-500 hover:-translate-y-4 hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10 text-center">
                        <img src="https://lacs.spvalenzuela.com/images/seal-sglg.jpg" class="h-28 mx-auto mb-10 object-contain transition-transform duration-500 group-hover:scale-110">
                        <h4 class="font-black text-2xl mb-2 text-white group-hover:text-red-500 transition-colors">SGLG Award</h4>
                        <div class="inline-block px-4 py-1.5 rounded-full bg-red-600/10 border border-red-600/20">
                            <p class="text-red-500 text-[10px] font-black uppercase tracking-[0.2em]">2024 RECIPIENT</p>
                        </div>
                    </div>
                </div>

                <!-- Award 3 -->
                <div data-aos="zoom-in" data-aos-delay="300" class="group relative p-10 bg-white/[0.03] backdrop-blur-sm rounded-[50px] border border-white/10 hover:border-red-600/50 transition-all duration-500 hover:-translate-y-4 hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10 text-center">
                        <img src="https://lacs.spvalenzuela.com/images/good-education.jpeg" class="h-28 mx-auto mb-10 object-contain transition-transform duration-500 group-hover:scale-110">
                        <h4 class="font-black text-2xl mb-2 text-white group-hover:text-red-500 transition-colors">Good Education</h4>
                        <div class="inline-block px-4 py-1.5 rounded-full bg-red-600/10 border border-red-600/20">
                            <p class="text-red-500 text-[10px] font-black uppercase tracking-[0.2em]">SYCIP AWARD</p>
                        </div>
                    </div>
                </div>

                <!-- Award 4 -->
                <div data-aos="zoom-in" data-aos-delay="400" class="group relative p-10 bg-white/[0.03] backdrop-blur-sm rounded-[50px] border border-white/10 hover:border-red-600/50 transition-all duration-500 hover:-translate-y-4 hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10 text-center">
                        <img src="https://lacs.spvalenzuela.com/images/oro-inidoro.png" class="h-28 mx-auto mb-10 object-contain transition-transform duration-500 group-hover:scale-110">
                        <h4 class="font-black text-2xl mb-2 text-white group-hover:text-red-500 transition-colors">Oro Inodoro</h4>
                        <div class="inline-block px-4 py-1.5 rounded-full bg-red-600/10 border border-red-600/20">
                            <p class="text-red-500 text-[10px] font-black uppercase tracking-[0.2em]">GRAND CHAMPION</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Infrastructure Projects -->
    <section id="infrastructure" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="mb-24 flex justify-between items-end">
                <div>
                    <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Infrastructure</h2>
                    <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight">Featured Projects</p>
                </div>
                <a href="#" class="hidden md:block text-gray-400 font-black uppercase tracking-widest text-[10px] hover:text-red-600 transition-colors">View All Projects <i class="bi bi-arrow-right ml-2"></i></a>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div data-aos="fade-right" class="group cursor-pointer">
                    <div class="relative h-96 overflow-hidden rounded-[40px] shadow-2xl mb-8">
                        <img src="https://lacs.spvalenzuela.com/images/peoples-park.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-6 left-6 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full text-white text-[10px] font-black uppercase tracking-widest border border-white/30">RECREATION</div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 mb-2">Valenzuela People's Park</h3>
                    <p class="text-gray-500 font-medium leading-relaxed">1.5 hectare interactive public space with state-of-the-art amenities for families.</p>
                </div>
                <div data-aos="fade-left" class="group cursor-pointer">
                    <div class="relative h-96 overflow-hidden rounded-[40px] shadow-2xl mb-8">
                        <img src="https://lacs.spvalenzuela.com/images/housing.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-6 left-6 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full text-white text-[10px] font-black uppercase tracking-widest border border-white/30">HOUSING</div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 mb-2">Disiplina Village</h3>
                    <p class="text-gray-500 font-medium leading-relaxed">A benchmark for in-city relocation, providing safe and decent housing for informal settler families.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Heritage & Progress: Landmarks -->
    <section id="landmarks" class="py-32 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-24">
                <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Heritage & Progress</h2>
                <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight mb-6">City Landmarks</p>
                <div class="h-2 w-20 bg-red-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div data-aos="fade-up" data-aos-delay="100" class="bg-white p-4 rounded-[40px] shadow-xl hover:shadow-2xl transition-all duration-500">
                    <div class="h-64 rounded-[32px] overflow-hidden mb-6">
                        <img src="https://lacs.spvalenzuela.com/images/bell-tower.jpg" class="w-full h-full object-cover">
                    </div>
                    <div class="px-4 pb-4">
                        <h4 class="text-xl font-black text-gray-900 mb-2">San Diego De Alcala</h4>
                        <p class="text-gray-400 text-sm font-medium">17th-century historical belfry standing as a witness to the city's rich past.</p>
                    </div>
                </div>
                <div data-aos="fade-up" data-aos-delay="200" class="bg-white p-4 rounded-[40px] shadow-xl hover:shadow-2xl transition-all duration-500">
                    <div class="h-64 rounded-[32px] overflow-hidden mb-6">
                        <img src="https://lacs.spvalenzuela.com/images/museo-val.jpg" class="w-full h-full object-cover">
                    </div>
                    <div class="px-4 pb-4">
                        <h4 class="text-xl font-black text-gray-900 mb-2">Museo ng Valenzuela</h4>
                        <p class="text-gray-400 text-sm font-medium">Repository of the city's historical and cultural heritage.</p>
                    </div>
                </div>
                <div data-aos="fade-up" data-aos-delay="300" class="bg-white p-4 rounded-[40px] shadow-xl hover:shadow-2xl transition-all duration-500">
                    <div class="h-64 rounded-[32px] overflow-hidden mb-6">
                        <img src="https://lacs.spvalenzuela.com/images/wes-arena.jpg?v=1.1" class="w-full h-full object-cover">
                    </div>
                    <div class="px-4 pb-4">
                        <h4 class="text-xl font-black text-gray-900 mb-2">WES Arena</h4>
                        <p class="text-gray-400 text-sm font-medium">State-of-the-art sports and multi-purpose indoor facility.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Updates/News -->
    <section id="updates" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div data-aos="fade-up" class="mb-24 flex justify-between items-end border-b-2 border-gray-50 pb-12">
                <div>
                    <h2 class="text-red-600 font-black tracking-[0.3em] uppercase text-xs mb-4">Updates</h2>
                    <p class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight">Latest News</p>
                </div>
                <div class="text-right">
                    <div class="text-gray-900 font-black text-4xl mb-1">2026</div>
                    <div class="text-red-600 font-black uppercase tracking-widest text-[10px]">Legislative Year</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-16">
                <!-- News 1 -->
                <div data-aos="fade-up" class="flex gap-8 group cursor-pointer">
                    <div class="w-48 h-48 rounded-3xl overflow-hidden shrink-0 shadow-xl">
                        <img src="https://lacs.spvalenzuela.com/images/oro-inidoro.png" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div>
                        <div class="text-red-600 font-black text-xs uppercase tracking-widest mb-2">NOV 2025</div>
                        <h4 class="text-2xl font-black text-gray-900 mb-3 leading-tight group-hover:text-red-600 transition-colors">Maynilad's 2025 Oro Inodoro Award</h4>
                        <p class="text-gray-500 text-sm font-medium line-clamp-2">Valenzuela City wins prestigious award for environmental sanitation management.</p>
                    </div>
                </div>
                <!-- News 2 -->
                <div data-aos="fade-up" data-aos-delay="100" class="flex gap-8 group cursor-pointer">
                    <div class="w-48 h-48 rounded-3xl overflow-hidden shrink-0 shadow-xl">
                        <img src="https://lacs.spvalenzuela.com/images/housing.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div>
                        <div class="text-red-600 font-black text-xs uppercase tracking-widest mb-2">JAN 2026</div>
                        <h4 class="text-2xl font-black text-gray-900 mb-3 leading-tight group-hover:text-red-600 transition-colors">P14M Housing Assistance Granted</h4>
                        <p class="text-gray-500 text-sm font-medium line-clamp-2">SHFC grants over Php 14 Million for Wawang Pulo Homeowners' Association.</p>
                    </div>
                </div>
            </div>
        </div>
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
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Quick Links</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black md:font-bold text-gray-500">
                            <li><a href="#leadership" class="hover:text-red-600 transition-colors uppercase md:capitalize">Officials</a></li>
                            <li><a href="#roots" class="hover:text-red-600 transition-colors uppercase md:capitalize">Our Roots</a></li>
                            <li><a href="#governance" class="hover:text-red-600 transition-colors uppercase md:capitalize">Governance</a></li>
                            <li><a href="#recognition" class="hover:text-red-600 transition-colors uppercase md:capitalize">Recognition</a></li>
                            <li><a href="#infrastructure" class="hover:text-red-600 transition-colors uppercase md:capitalize">Infrastructure</a></li>
                        </ul>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-6 border-b border-gray-100 md:border-none pb-2 md:pb-0">Legal</h4>
                        <ul class="space-y-4 text-xs md:text-sm font-black md:font-bold text-gray-500">
                            <li><a href="#" class="hover:text-red-600 transition-colors uppercase md:capitalize">Privacy</a></li>
                            <li><a href="#" class="hover:text-red-600 transition-colors uppercase md:capitalize">Terms</a></li>
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
