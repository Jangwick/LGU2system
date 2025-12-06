<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#dc2626">
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/assets/images/logo.png">
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen flex items-center justify-center p-3 md:p-4">
    <style>
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce-in {
            0% { transform: scale(0.5); }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        .animate-fade-in { animation: fade-in 0.6s ease-out forwards; }
        .animate-fade-in-up { animation: fade-in-up 0.6s ease-out forwards; }
        .animate-bounce-in { animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards; }
        .animation-delay-100 { animation-delay: 100ms; }
        .animation-delay-200 { animation-delay: 200ms; }
        .animation-delay-300 { animation-delay: 300ms; }
        .animation-delay-400 { animation-delay: 400ms; }
        
        .login-logo-container {
            width: 120px !important;
            height: 120px !important;
            display: flex !important;
        }
        .login-logo-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
        }
    </style>
    
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-6 md:mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center mb-3 md:mb-4 animate-bounce-in">
                <div class="login-logo-container bg-white rounded-full shadow-xl items-center justify-center p-3 transform hover:scale-105 transition-all duration-300">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="City Government of Valenzuela" class="login-logo-img">
                </div>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 animate-fade-in-up animation-delay-100">VDM System</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2 animate-fade-in-up animation-delay-200">Voting and Decision-Making System</p>
            <p class="text-xs md:text-sm text-red-600 font-semibold mt-1 animate-fade-in-up animation-delay-300">City Government of Valenzuela</p>
            <p class="text-xs text-gray-500 animate-fade-in-up animation-delay-400">Metropolitan Manila</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300">
            <div class="mb-4 md:mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-sm md:text-base text-gray-600 mt-1">Sign in to access your account</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4">
                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center text-sm">
                        <i class="bi bi-check-circle mr-2"></i>
                        <span>You have been logged out successfully.</span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center text-sm">
                        <i class="bi bi-exclamation-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['login_error']); ?></span>
                    </div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>
            </div>
            
            <!-- Login Form -->
            <form id="login-form" action="<?php echo AUTH_URL; ?>/controllers/LoginController.php" method="POST" class="space-y-4 md:space-y-5">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="bi bi-envelope mr-1"></i>Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required
                           placeholder="your.email@lgu.gov.ph"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                </div>
                
                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="bi bi-lock mr-1"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="Enter your password"
                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        <button type="button" 
                                id="toggle-password" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="bi bi-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember"
                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-2 focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-700">Remember me</span>
                    </label>
                    <a href="<?php echo AUTH_URL; ?>/views/forgot-password.php" class="text-sm text-red-600 hover:text-red-700 font-medium">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        id="login-btn"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg flex items-center justify-center">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right ml-2"></i>
                </button>
            </form>
            
            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>
            
            <!-- Alternative Login Options -->
            <div class="grid grid-cols-2 gap-3">
                <button class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="bi bi-microsoft text-lg mr-2"></i>
                    <span class="text-sm font-medium text-gray-700">Microsoft</span>
                </button>
                <button class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="bi bi-google text-lg mr-2 text-red-500"></i>
                    <span class="text-sm font-medium text-gray-700">Google</span>
                </button>
            </div>
            
            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="<?php echo REGISTER_URL; ?>" class="text-red-600 hover:text-red-700 font-semibold">Create Account</a>
                </p>
            </div>
        </div>
        
        <!-- Demo Credentials (Same as LLRMSystem) -->
        <div class="mt-4 p-4 bg-red-50 rounded-lg border border-red-200 animate-fade-in-up animation-delay-400">
            <p class="text-xs font-semibold text-red-800 mb-2"><i class="bi bi-info-circle mr-1"></i> Demo Credentials (Same as LLRM):</p>
            <div class="text-xs text-red-700 space-y-1">
                <p><strong>Admin:</strong> admin@lgu.gov.ph / admin123</p>
                <p><strong>Officer:</strong> officer@lgu.gov.ph / admin123</p>
                <p><strong>Staff:</strong> staff@lgu.gov.ph / admin123</p>
                <p><strong>Viewer:</strong> viewer@lgu.gov.ph / admin123</p>
            </div>
        </div>
        
        <!-- Footer Info -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>&copy; <?php echo date('Y'); ?> City Government of Valenzuela. All rights reserved.</p>
            <div class="mt-2 space-x-4">
                <a href="#" class="hover:text-red-600">Privacy Policy</a>
                <span>•</span>
                <a href="#" class="hover:text-red-600">Terms of Service</a>
                <span>•</span>
                <a href="#" class="hover:text-red-600">Help</a>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('toggle-password')?.addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
