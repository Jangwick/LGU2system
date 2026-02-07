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
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #fee2e2 0%, #ffffff 50%, #fee2e2 100%);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20">
                <div class="flex items-center">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-10 w-10 md:h-12 md:w-12 mr-3" onerror="this.src='<?php echo BASE_URL; ?>/public/assets/images/valenzuela-logo.webp'">
                    <div>
                        <span class="text-xl md:text-2xl font-extrabold text-red-600 tracking-tight">LRMS</span>
                        <span class="hidden md:block text-[10px] text-gray-500 font-medium uppercase tracking-widest leading-none">Valenzuela City</span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 md:space-x-4">
                    <a href="<?php echo LOGIN_URL; ?>" class="text-gray-600 hover:text-red-600 font-semibold px-4 py-2 text-sm md:text-base transition-colors">Sign In</a>
                    <a href="<?php echo REGISTER_URL; ?>" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded-full text-sm md:text-base shadow-lg shadow-red-200 transition-all hover:-translate-y-0.5 active:translate-y-0">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden hero-gradient pt-16 pb-24 md:pt-24 md:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-red-50 border border-red-100 text-red-700 text-sm font-bold mb-6 animate-fade-in">
                    <span class="flex h-2 w-2 rounded-full bg-red-600 mr-2"></span>
                    Legislation Reimagined for Valenzuela
                </div>
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-gray-900 mb-6 tracking-tight leading-tight">
                    Legislative Records <br class="hidden md:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-800">Management System</span>
                </h1>
                <p class="max-w-2xl mx-auto text-lg md:text-xl text-gray-600 mb-10 leading-relaxed">
                    A comprehensive platform for the City Government of Valenzuela to manage, search, and analyze ordinances, resolutions, and legislative documents with modern data insights.
                </p>
                <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="<?php echo REGISTER_URL; ?>" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-black px-10 py-4 rounded-xl text-lg shadow-xl shadow-red-100 transition-all hover:scale-105 active:scale-95">
                        Start Now
                    </a>
                    <a href="#features" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-800 font-bold px-10 py-4 rounded-xl text-lg border border-gray-200 shadow-sm transition-all">
                        Expore Features
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Background Decoration -->
        <div class="absolute -bottom-24 left-1/2 -ml-[800px] w-[1600px] h-64 bg-red-600/5 blur-[120px] rounded-full pointer-events-none"></div>
    </div>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base text-red-600 font-bold tracking-wide uppercase mb-2">Capabilities</h2>
                <p class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Everything you need to manage local legislation</p>
                <div class="h-1.5 w-24 bg-red-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12">
                <!-- Feature 1: Document Management -->
                <div class="feature-card p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mb-6 text-2xl shadow-inner">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Smart Document Cabinet</h3>
                    <p class="text-gray-600 leading-relaxed font-medium text-sm">Organized storage for ordinances, resolutions, and session minutes with automated reference numbering and multi-format support.</p>
                </div>

                <!-- Feature 2: Advanced Search -->
                <div class="feature-card p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 transition-all duration-300">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-6 text-2xl shadow-inner text-blue-600">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Advanced Retrieval</h3>
                    <p class="text-gray-600 leading-relaxed font-medium text-sm">Find exactly what you need with full-text search, multi-criteria filters, and keyword extraction across decades of legislative records.</p>
                </div>

                <!-- Feature 3: Analytics -->
                <div class="feature-card p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 transition-all duration-300">
                    <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mb-6 text-2xl shadow-inner text-green-600">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Legislative Analytics</h3>
                    <p class="text-gray-600 leading-relaxed font-medium text-sm">Visual KPIs and trend analysis to monitor legislative productivity, top topics, and historical patterns in city governance.</p>
                </div>

                <!-- Feature 4: Voting Analytics -->
                <div class="feature-card p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 transition-all duration-300">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mb-6 text-2xl shadow-inner text-purple-600">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Voting Insights</h3>
                    <p class="text-gray-600 leading-relaxed font-medium text-sm">Deep analysis of voting patterns, behavior insights, and policy alignment among council members over time.</p>
                </div>

                <!-- Feature 5: Document Similarity -->
                <div class="feature-card p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 transition-all duration-300">
                    <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center mb-6 text-2xl shadow-inner text-orange-600">
                        <i class="bi bi-intersect"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Comparative Analysis</h3>
                    <p class="text-gray-600 leading-relaxed font-medium text-sm">Automatically identify similar precedents or related documents using TF-IDF and vector-based similarity indexing.</p>
                </div>

                <!-- Feature 6: Audit & Security -->
                <div class="feature-card p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 transition-all duration-300">
                    <div class="w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center mb-6 text-2xl shadow-inner text-teal-600">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Enterprise Security</h3>
                    <p class="text-gray-600 leading-relaxed font-medium text-sm">Role-based access control (RBAC), full audit trails for compliance, and automated daily backups for data integrity.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Stats Section -->
    <section class="py-20 bg-gray-900 text-white overflow-hidden relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-black mb-2 text-red-500">10k+</div>
                    <div class="text-gray-400 font-bold uppercase tracking-wider text-xs">Total Documents</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-black mb-2 text-red-500">100%</div>
                    <div class="text-gray-400 font-bold uppercase tracking-wider text-xs">Digital Retrieval</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-black mb-2 text-red-500">24/7</div>
                    <div class="text-gray-400 font-bold uppercase tracking-wider text-xs">System Uptime</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-black mb-2 text-red-500">< 1s</div>
                    <div class="text-gray-400 font-bold uppercase tracking-wider text-xs">Search Response</div>
                </div>
            </div>
        </div>
        <div class="absolute top-0 right-0 w-1/3 h-full bg-red-600/10 -skew-x-12 transform translate-x-1/2"></div>
    </section>

    <!-- CT section -->
    <section class="py-24 bg-red-600 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl md:text-5xl font-black text-white mb-8">Ready to modernize your legislative workflow?</h2>
            <p class="text-red-100 text-lg md:text-xl mb-12 max-w-2xl mx-auto font-medium">Join the City Government of Valenzuela and transform how legislative records are managed and analyzed.</p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="<?php echo REGISTER_URL; ?>" class="bg-white text-red-600 font-black px-12 py-4 rounded-xl text-lg shadow-2xl hover:scale-105 transition-all">Create Account</a>
                <a href="<?php echo LOGIN_URL; ?>" class="bg-red-800 text-white font-black px-12 py-4 rounded-xl text-lg hover:bg-red-900 transition-all">Sign In</a>
            </div>
        </div>
        <!-- Decorative Circle -->
        <div class="absolute -top-24 -left-24 w-64 h-64 bg-red-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-red-700 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-12 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-8 md:mb-0">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-10 w-10 mr-4 grayscale opacity-70">
                    <div>
                        <div class="text-gray-900 font-black text-xl tracking-tighter">LRMS VALENZUELA</div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">© <?php echo date('Y'); ?> All Rights Reserved</div>
                    </div>
                </div>
                <div class="flex space-x-8 text-sm font-bold text-gray-500 uppercase tracking-widest">
                    <a href="<?php echo url('modules/help/views/privacy.php'); ?>" class="hover:text-red-600 transition-colors">Privacy</a>
                    <a href="<?php echo url('modules/help/views/terms.php'); ?>" class="hover:text-red-600 transition-colors">Terms</a>
                    <a href="<?php echo url('modules/help/views/contact.php'); ?>" class="hover:text-red-600 transition-colors">Contact</a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-50 text-center text-xs text-gray-400 font-medium tracking-wide italic">
                Designed for the Legislative Office of the City Government of Valenzuela, Metropolitan Manila.
            </div>
        </div>
    </footer>
</body>
</html>
