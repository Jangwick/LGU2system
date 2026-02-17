<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

// If already logged in with a valid session, redirect to dashboard
if (function_exists('checkAlreadyLoggedIn')) {
    checkAlreadyLoggedIn();
} else if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/dashboard/views/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#dc2626">
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen flex items-center justify-center p-3 md:p-4">
    <style>
        /* Animation Keyframes */
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce-in {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        .animate-fade-in { opacity: 0; animation: fade-in 0.6s ease-out forwards; }
        .animate-fade-in-up { opacity: 0; transform: translateY(20px); animation: fade-in-up 0.6s ease-out forwards; }
        .animate-bounce-in { opacity: 0; transform: scale(0.3); animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards; }
        .animation-delay-100 { animation-delay: 100ms; }
        .animation-delay-200 { animation-delay: 200ms; }
        .animation-delay-300 { animation-delay: 300ms; }
        .animation-delay-400 { animation-delay: 400ms; }
        
        /* Ensure logo is always visible */
        .login-logo-container {
            width: 120px !important;
            height: 120px !important;
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .login-logo-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .logo-bounce {
            animation: logo-bounce-anim 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }
        @keyframes logo-bounce-anim {
            0% { transform: scale(0.5); }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        .text-slide-up {
            animation: text-slide-anim 0.5s ease-out forwards;
        }
        @keyframes text-slide-anim {
            from { transform: translateY(10px); opacity: 0.5; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Prevent zoom on input focus in iOS */
        @media screen and (max-width: 767px) {
            input, select, textarea { font-size: 16px !important; }
        }
    </style>
    
    <!-- Back to Landing Page -->
    <a href="<?php echo BASE_URL; ?>/index.php" class="fixed top-4 left-4 md:top-8 md:left-8 flex items-center text-gray-600 hover:text-red-600 font-medium transition-all duration-300 z-50 group bg-white/80 backdrop-blur-sm px-3 py-2 rounded-lg shadow-sm hover:shadow-md">
        <i class="bi bi-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i>
        <span class="hidden sm:inline">Back to Home</span>
        <span class="sm:hidden">Back</span>
    </a>

    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-6 md:mb-8">
            <div class="inline-flex items-center justify-center mb-3 md:mb-4 logo-bounce">
                <div class="login-logo-container bg-white rounded-full shadow-xl items-center justify-center p-3 transform hover:scale-105 transition-all duration-300">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="City Government of Valenzuela" class="login-logo-img" onerror="this.onerror=null; this.src='https://valenzuela.gov.ph/images/valenzuela-logo.webp';">
                </div>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-red-600 text-slide-up" style="animation-delay: 0.1s;">VDMS</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2 text-slide-up" style="animation-delay: 0.2s;">Voting & Decision-Making System</p>
            <p class="text-xs md:text-sm text-[#002d72] font-semibold mt-1 text-slide-up" style="animation-delay: 0.3s;">City Government of Valenzuela</p>
            <p class="text-xs text-gray-500 text-slide-up" style="animation-delay: 0.4s;">Metropolitan Manila</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300">
            <div class="mb-4 md:mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-sm md:text-base text-gray-600 mt-1">Sign in to access your account</p>
            </div>
            
            <!-- Alert Container -->
            <div id="alert-container" class="mb-4">
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-3 md:px-4 py-2 md:py-3 rounded-lg flex items-center text-sm">
                        <i class="bi bi-exclamation-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-3 md:px-4 py-2 md:py-3 rounded-lg flex items-center text-sm">
                        <i class="bi bi-check-circle mr-2"></i>
                        <span>You have been logged out successfully.</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Login Form -->
            <form id="login-form" action="<?php echo AUTH_URL; ?>/controllers/LoginController.php" method="POST" class="space-y-4 md:space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1 md:mb-2">
                        <i class="bi bi-envelope mr-1"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" required placeholder="your.email@lgu.gov.ph"
                           class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <i class="bi bi-lock mr-1"></i>Password
                        </label>
                        <a href="forgot-password.php" class="text-xs md:text-sm font-semibold text-red-600 hover:text-red-700 transition">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <input type="password" id="password" name="password" required placeholder="Enter your password"
                               class="w-full pl-3 md:pl-4 pr-10 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                        <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <label for="remember" class="ml-2 text-xs md:text-sm text-gray-600 cursor-pointer">Remember this device</label>
                </div>

                <button type="submit" id="submit-btn" class="w-full bg-red-600 text-white font-bold py-3 md:py-4 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 transform transition-all active:scale-95 shadow-lg flex items-center justify-center">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right-short ml-2 text-xl"></i>
                </button>
            </form>

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
                <a href="#" class="hover:text-red-600">Privacy Policy</a>
                <span>•</span>
                <a href="#" class="hover:text-red-600">Terms of Service</a>
                <span>•</span>
                <a href="#" class="hover:text-red-600">Help</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password functionality
            document.getElementById('toggle-password')?.addEventListener('click', function() {
                const passwordField = document.getElementById('password');
                const passwordIcon = document.getElementById('password-icon');
                
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    passwordIcon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    passwordField.type = 'password';
                    passwordIcon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });

            // Modern form handling
            const loginForm = document.getElementById('login-form');
            if (loginForm && window.fetch) {
                loginForm.addEventListener('submit', async function(e) {
                    // We allow the direct POST for VDM if AJAX isn't strictly required by the controller,
                    // but we'll add a subtle loading state.
                    const submitBtn = document.getElementById('submit-btn');
                    submitBtn.disabled = true;
                    submitBtn.querySelector('span').innerText = 'Signing In...';
                });
            }
        });
    </script>
</body>
</html>
