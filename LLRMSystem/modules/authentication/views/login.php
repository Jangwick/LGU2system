<?php require_once __DIR__ . '/../../core/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-4">
                <div class="bg-white rounded-full shadow-xl flex items-center justify-center overflow-hidden" style="width: 160px; height: 160px;">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="City Government of Valenzuela" style="width: 120%; height: 120%;" class="object-cover">
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">LRMS</h1>
            <p class="text-gray-600 mt-2">Legislative Records Management System</p>
            <p class="text-sm text-red-600 font-semibold mt-1">City Government of Valenzuela</p>
            <p class="text-xs text-gray-500">Metropolitan Manila</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-gray-600 mt-1">Sign in to access your account</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4">
                <?php
                session_start();
                
                // Show logout success message
                if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
                    echo '<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                            <i class="bi bi-check-circle mr-2"></i>
                            <span>You have been logged out successfully.</span>
                          </div>';
                }
                
                // Show login error message
                if (isset($_SESSION['login_error'])) {
                    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                            <i class="bi bi-exclamation-circle mr-2"></i>
                            <span>' . htmlspecialchars($_SESSION['login_error']) . '</span>
                          </div>';
                    unset($_SESSION['login_error']);
                }
                ?>
            </div>
            
            <!-- Login Form -->
            <form id="login-form" action="<?php echo AUTH_URL; ?>/controllers/LoginController.php" method="POST" class="space-y-5">
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
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    <span class="text-red-500 text-xs hidden" id="email-error"></span>
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
                    <span class="text-red-500 text-xs hidden" id="password-error"></span>
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
                    <span id="login-btn-text">Sign In</span>
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
                    <i class="bi bi-google text-lg mr-2"></i>
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
        
        <!-- Footer Info -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>&copy; <?php echo date('Y'); ?> LGU Legislative Office. All rights reserved.</p>
            <div class="mt-2 space-x-4">
                <a href="<?php echo url('modules/help/views/privacy.php'); ?>" class="hover:text-blue-600">Privacy Policy</a>
                <span>•</span>
                <a href="<?php echo url('modules/help/views/terms.php'); ?>" class="hover:text-blue-600">Terms of Service</a>
                <span>•</span>
                <a href="<?php echo url('modules/help/views/contact.php'); ?>" class="hover:text-blue-600">Help</a>
            </div>
        </div>
    </div>
    
    <!-- Application Configuration -->
    <script src="<?php echo asset('js/config.js'); ?>"></script>
    <script src="<?php echo asset('js/auth.js'); ?>"></script>
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
