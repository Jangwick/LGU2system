<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

// If already logged in with a valid session, redirect to dashboard
checkAlreadyLoggedIn();
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
        /* Prevent zoom on input focus in iOS */
        @media screen and (max-width: 767px) {
            input, select, textarea { font-size: 16px !important; }
        }
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
        /* Logo-safe animations that don't hide content */
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
</style>
    
    <!-- Back to Landing Page -->
    <a href="<?php echo BASE_URL; ?>/index.php" class="fixed top-4 left-4 md:top-8 md:left-8 flex items-center text-gray-600 hover:text-red-600 font-medium transition-all duration-300 z-50 group bg-white/80 backdrop-blur-sm px-3 py-2 rounded-lg shadow-sm hover:shadow-md">
        <i class="bi bi-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i>
        <span class="hidden sm:inline">Back to Home</span>
        <span class="sm:hidden">Back</span>
    </a>

    <div class="w-full max-w-md">
        <!-- Session Error Message -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'session_superseded'): ?>
        <div class="mb-6 animate-fade-in-up bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-xl mr-3"></i>
                <div>
                    <p class="font-bold text-amber-800">Security Notice</p>
                    <p class="text-sm text-amber-700">This account was logged in on another device. For your security, the previous session has been closed.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Logo Section -->
        <div class="text-center mb-6 md:mb-8">
            <div class="inline-flex items-center justify-center mb-3 md:mb-4 logo-bounce">
                <div class="login-logo-container bg-white rounded-full shadow-xl items-center justify-center p-3 transform hover:scale-105 transition-all duration-300">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="City Government of Valenzuela" class="login-logo-img" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/images/valenzuela%20logo.webp';">
                </div>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-red-600 text-slide-up" style="animation-delay: 0.1s;">LRMS</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2 text-slide-up" style="animation-delay: 0.2s;">Legislative Records Management System</p>
            <p class="text-xs md:text-sm text-[#002d72] font-semibold mt-1 text-slide-up" style="animation-delay: 0.3s;">City Government of Valenzuela</p>
            <p class="text-xs text-gray-500 text-slide-up" style="animation-delay: 0.4s;">Metropolitan Manila</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300">
            <div class="mb-4 md:mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-sm md:text-base text-gray-600 mt-1">Sign in to access your account</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4">
                <?php
                // Show logout success message
                if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
                    echo '<div class="bg-green-50 border border-green-200 text-green-700 px-3 md:px-4 py-2 md:py-3 rounded-lg flex items-center text-sm">
                            <i class="bi bi-check-circle mr-2"></i>
                            <span>You have been logged out successfully.</span>
                          </div>';
                }
                
                // Show login error message
                if (isset($_SESSION['login_error'])) {
                    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-3 md:px-4 py-2 md:py-3 rounded-lg flex items-center text-sm">
                            <i class="bi bi-exclamation-circle mr-2"></i>
                            <span>' . htmlspecialchars($_SESSION['login_error']) . '</span>
                          </div>';
                    unset($_SESSION['login_error']);
                }
                ?>
            </div>
            
            <!-- Login Form -->
            <form id="login-form" action="<?php echo AUTH_URL; ?>/controllers/LoginController.php" method="POST" class="space-y-4 md:space-y-5">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1 md:mb-2">
                        <i class="bi bi-envelope mr-1"></i>Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required
                           placeholder="your.email@lgu.gov.ph"
                           class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                    <span class="text-red-500 text-xs hidden" id="email-error"></span>
                </div>
                
                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <i class="bi bi-lock mr-1"></i>Password
                        </label>
                        <a href="forgot-password.php" class="text-xs md:text-sm font-semibold text-red-600 hover:text-red-700 transition">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="Enter your password"
                               class="w-full pl-3 md:pl-4 pr-10 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-base">
                        <button type="button" 
                                id="toggle-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        id="submit-btn"
                        class="w-full bg-red-600 text-white font-bold py-3 md:py-4 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 transform transition-all active:scale-95 shadow-lg flex items-center justify-center">
                    <span id="btn-text">Sign In</span>
                    <i class="bi bi-arrow-right-short ml-2 text-xl" id="btn-icon"></i>
                </button>
            </form>

            <!-- OTP Form (Hidden by default) -->
            <form id="otp-form" action="<?php echo AUTH_URL; ?>/controllers/VerifyOtpController.php" method="POST" class="hidden space-y-4 md:space-y-5 animate-fade-in">
                <div class="text-center mb-4">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full text-red-600 mb-3">
                        <i class="bi bi-shield-lock text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Verify Your Identity</h3>
                    <p class="text-sm text-gray-600 mt-1">We've sent a 6-digit code to <br><span id="otp-target-email" class="font-semibold text-gray-800">your email</span></p>
                </div>

                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                    <input type="text" 
                           id="otp" 
                           name="otp" 
                           maxlength="6"
                           required
                           placeholder="000000"
                           class="w-full text-center text-2xl tracking-[0.5em] font-bold px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <div class="text-center text-xs md:text-sm text-gray-500">
                    Didn't receive the code? 
                    <button type="button" id="resend-otp" class="text-red-600 font-bold hover:underline disabled:text-gray-400">Resend Code</button>
                    <span id="countdown" class="hidden">(60s)</span>
                </div>

                <div class="flex gap-3">
                    <button type="button" 
                            id="back-to-login"
                            class="flex-1 bg-gray-100 text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-200 transition">
                        Back
                    </button>
                    <button type="submit" 
                            id="verify-btn"
                            class="flex-[2] bg-red-600 text-white font-bold py-3 rounded-lg hover:bg-red-700 transition shadow-lg flex items-center justify-center">
                        Verify & Login
                    </button>
                </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const otpForm = document.getElementById('otp-form');
            const alertContainer = document.getElementById('alert-container');
            const backToLoginBtn = document.getElementById('back-to-login');
            const resendOtpBtn = document.getElementById('resend-otp');
            const countdownSpan = document.getElementById('countdown');
            
            // Toggle password functionality (fixed for new IDs)
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

            function showAlert(message, type = 'red') {
                let bgColor, icon;
                
                switch(type) {
                    case 'green':
                        bgColor = 'bg-green-50 border-green-200 text-green-700';
                        icon = 'bi-check-circle';
                        break;
                    case 'amber':
                        bgColor = 'bg-amber-50 border-amber-200 text-amber-800';
                        icon = 'bi-exclamation-triangle';
                        break;
                    case 'red':
                    default:
                        bgColor = 'bg-red-50 border-red-200 text-red-700';
                        icon = 'bi-exclamation-circle';
                }
                
                alertContainer.innerHTML = `
                    <div class="${bgColor} border px-4 py-3 rounded-lg flex items-center text-sm animate-shake">
                        <i class="bi ${icon} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;
            }

            // Handle Login Form Submission
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submit-btn');
                const btnText = document.getElementById('btn-text');
                const btnIcon = document.getElementById('btn-icon');
                const originalText = btnText.innerText;
                
                // Loading state
                submitBtn.disabled = true;
                btnText.innerText = 'Checking...';
                btnIcon.className = 'bi bi-arrow-repeat animate-spin ml-2 text-xl';

                try {
                    const formData = new FormData(loginForm);
                    const response = await fetch(loginForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const responseText = await response.text();
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        console.error('Invalid JSON response:', responseText);
                        throw new SyntaxError('The server returned an invalid response. Please check if MySQL is running.');
                    }

                    if (data.requires_otp) {
                        // Switch to OTP form
                        document.getElementById('otp-target-email').innerText = data.email || formData.get('email');
                        loginForm.classList.add('hidden');
                        otpForm.classList.remove('hidden');
                        
                        if (data.already_logged_in) {
                            showAlert(data.message, 'amber');
                        } else {
                            showAlert('A verification code has been sent.', 'green');
                        }
                        
                        startResendCountdown();
                    } else if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        showAlert(data.message || 'Login failed. Please check your credentials.');
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    showAlert('An error occurred. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    btnText.innerText = originalText;
                    btnIcon.className = 'bi bi-arrow-right-short ml-2 text-xl';
                }
            });

            // Handle OTP Verification Submission
            otpForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const verifyBtn = document.getElementById('verify-btn');
                const originalText = verifyBtn.innerText;
                
                verifyBtn.disabled = true;
                verifyBtn.innerText = 'Verifying...';

                try {
                    const formData = new FormData(otpForm);
                    // Add email to the OTP request if needed (usually it's in session)
                    const response = await fetch(otpForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const responseText = await response.text();
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        console.error('OTP parsing error:', responseText);
                        throw new SyntaxError('The server returned an invalid response.');
                    }

                    if (data.success) {
                        showAlert('Verification successful! Redirecting...', 'green');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Invalid verification code.');
                    }
                } catch (error) {
                    console.error('OTP error:', error);
                    showAlert('An error occurred during verification.');
                } finally {
                    verifyBtn.disabled = false;
                    verifyBtn.innerText = originalText;
                }
            });

            // Back to Login Link
            backToLoginBtn.addEventListener('click', function() {
                otpForm.classList.add('hidden');
                loginForm.classList.remove('hidden');
                alertContainer.innerHTML = '';
            });

            // Resend OTP functionality
            let countdown = 0;
            let countdownInterval;

            function startResendCountdown() {
                countdown = 60;
                resendOtpBtn.disabled = true;
                countdownSpan.classList.remove('hidden');
                
                countdownInterval = setInterval(() => {
                    countdown--;
                    countdownSpan.innerText = `(${countdown}s)`;
                    
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        resendOtpBtn.disabled = false;
                        countdownSpan.classList.add('hidden');
                    }
                }, 1000);
            }

            resendOtpBtn.addEventListener('click', async function() {
                if (countdown > 0) return;
                
                resendOtpBtn.innerText = 'Sending...';
                
                try {
                    // We call the login controller again with current credentials
                    // or have a specific resend endpoint. For simplicity, we can re-POST login
                    const formData = new FormData(loginForm);
                    const response = await fetch(loginForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.requires_otp) {
                        showAlert('A new verification code has been sent.', 'green');
                        startResendCountdown();
                    } else {
                        showAlert(data.message || 'Error resending code.');
                    }
                } catch (error) {
                    showAlert('Error resending code.');
                } finally {
                    resendOtpBtn.innerText = 'Resend Code';
                }
            });
        });
    </script>
</body>
</html>
