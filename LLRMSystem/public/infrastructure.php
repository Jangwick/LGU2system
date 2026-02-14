<?php
/**
 * Infrastructure Projects Page
 */
require_once __DIR__ . '/../modules/core/config/config.php';

$pageTitle = "Infrastructure Projects";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#dc2626">
    <title><?php echo APP_NAME; ?> - Infrastructure Projects</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <a href="<?php echo BASE_URL; ?>" class="flex items-center group cursor-pointer">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 md:h-12 md:w-12 mr-2 md:mr-3 transition-transform duration-500 group-hover:rotate-12 shadow-sm rounded-full" onerror="this.src='<?php echo BASE_URL; ?>/public/assets/images/valenzuela-logo.webp'">
                    <span class="text-lg md:text-2xl font-black text-[#002d72] tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
                </a>
                <a href="<?php echo BASE_URL; ?>" class="text-gray-600 hover:text-red-600 font-bold text-sm transition-all">
                    <i class="bi bi-arrow-left mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="pt-32 pb-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-7xl font-black text-[#002d72] tracking-tight mb-6">Infrastructure Projects</h1>
            <p class="text-xl text-gray-500 font-medium max-w-3xl mx-auto">Complete archive of city government infrastructure developments and initiatives.</p>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12">
                
                <!-- Valenzuela People's Park -->
                <div class="bg-white rounded-[40px] shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="h-80 overflow-hidden">
                        <img src="https://lacs.spvalenzuela.com/images/peoples-park.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-3 mb-6">
                            <span class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-[10px] font-black uppercase tracking-widest">RECREATION</span>
                            <span class="text-xs text-gray-400 font-medium">Completed 2015</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-4">Valenzuela People's Park</h3>
                        <p class="text-gray-500 font-medium leading-relaxed mb-8">A 1.5-hectare urban park featuring a mini-zoo, dancing fountain, children's playground, and ample green space for families and fitness enthusiasts.</p>
                        
                        <!-- Features -->
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Interactive Fountain</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Amphitheater</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Aero Circle</span>
                            </div>
                        </div>

                        <a href="#" class="inline-block px-8 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors text-sm">
                            View Details
                        </a>
                    </div>
                </div>

                <!-- Disiplina Village -->
                <div class="bg-white rounded-[40px] shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="h-80 overflow-hidden">
                        <img src="https://lacs.spvalenzuela.com/images/housing.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-3 mb-6">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest">HOUSING</span>
                            <span class="text-xs text-gray-400 font-medium">Ongoing Phase</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-4">Disiplina Village Bignay</h3>
                        <p class="text-gray-500 font-medium leading-relaxed mb-8">The country's biggest in-city resettlement site, providing safe and decent homes to thousands of families previously living in danger zones.</p>
                        
                        <!-- Features -->
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">100+ Buildings</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">School & Health Centers</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Livelihood Training Center</span>
                            </div>
                        </div>

                        <a href="#" class="inline-block px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors text-sm">
                            View Details
                        </a>
                    </div>
                </div>

                <!-- New Legislative Building -->
                <div class="bg-white rounded-[40px] shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="h-80 overflow-hidden">
                        <img src="https://lacs.spvalenzuela.com/images/city-hall.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-3 mb-6">
                            <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-lg text-[10px] font-black uppercase tracking-widest">GOVERNMENT</span>
                            <span class="text-xs text-gray-400 font-medium">Modernized</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-4">New Legislative Building</h3>
                        <p class="text-gray-500 font-medium leading-relaxed mb-8">A modern facility housing the sessions of the City Council, providing a transparent and efficient environment for local legislation.</p>

                        <a href="#" class="inline-block px-8 py-3 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition-colors text-sm">
                            View Details
                        </a>
                    </div>
                </div>

                <!-- Paspas Flood Control -->
                <div class="bg-white rounded-[40px] shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="h-80 overflow-hidden">
                        <img src="https://lacs.spvalenzuela.com/images/flood_control.png" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-3 mb-6">
                            <span class="px-3 py-1 bg-teal-50 text-teal-600 rounded-lg text-[10px] font-black uppercase tracking-widest">INFRASTRUCTURE</span>
                            <span class="text-xs text-gray-400 font-medium">Resilience</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-4">Paspas Flood Control</h3>
                        <p class="text-gray-500 font-medium leading-relaxed mb-8">Series of pumping stations and drainage upgrades across low-lying barangays to significantly reduce flooding during rainy seasons.</p>

                        <a href="#" class="inline-block px-8 py-3 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition-colors text-sm">
                            View Details
                        </a>
                    </div>
                </div>

                <!-- Polo Riverwalk -->
                <div class="bg-white rounded-[40px] shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="h-80 overflow-hidden relative">
                        <img src="https://lacs.spvalenzuela.com/images/riverwalk1.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" onerror="this.src='https://images.unsplash.com/photo-1544257750-572358f5da22?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-3 mb-6">
                            <span class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-[10px] font-black uppercase tracking-widest">TOURISM</span>
                            <span class="text-xs text-gray-400 font-medium">New Opening</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-4">Polo Riverwalk Phase 1</h3>
                        <p class="text-gray-500 font-medium leading-relaxed mb-8">A 6-kilometer linear park featuring walking paths and cycle lanes along the Polo River, connecting multiple barangays and promoting active lifestyle.</p>
                        
                        <!-- Features -->
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Bikeways & Jogging Paths</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">River Rehabilitation</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Public Lighting & Safety</span>
                            </div>
                        </div>

                        <a href="#" class="inline-block px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-colors text-sm">
                            View Details
                        </a>
                    </div>
                </div>

                <!-- Sentro Health Hubs -->
                <div class="bg-white rounded-[40px] shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="h-80 overflow-hidden relative">
                        <img src="https://lacs.spvalenzuela.com/images/healthhub.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" onerror="this.src='https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&q=80&w=800'">
                    </div>
                    <div class="p-10">
                        <div class="flex items-center space-x-3 mb-6">
                            <span class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-[10px] font-black uppercase tracking-widest">HEALTHCARE</span>
                            <span class="text-xs text-gray-400 font-medium">Inaugurated</span>
                        </div>
                        <h3 class="text-3xl font-black text-gray-900 mb-4">Sentro Health Hubs</h3>
                        <p class="text-gray-500 font-medium leading-relaxed mb-8">State-of-the-art community health centers offering specialized diagnostic services, laboratory tests, and primary care.</p>
                        
                        <!-- Features -->
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">Diagnostic Laboratory</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">X-Ray & Ultrasound</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="bi bi-check-circle-fill text-green-500 mr-3"></i>
                                <span class="text-gray-600 font-medium">24/7 Primary Care</span>
                            </div>
                        </div>

                        <a href="#" class="inline-block px-8 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors text-sm">
                            View Details
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> City Government of Valenzuela. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
