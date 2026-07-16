<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Legislative Records Management System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen flex items-center justify-center p-4">
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
    </style>
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg animate-bounce-in transform hover:scale-105 transition-all duration-300">
                <i class="bi bi-key text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 animate-fade-in-up animation-delay-100">Forgot Password?</h1>
            <p class="text-gray-600 mt-2 animate-fade-in-up animation-delay-200">No worries, we'll send you reset instructions</p>
        </div>
        
        <!-- Forgot Password Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300">
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4"></div>
            
            <!-- Form -->
            <form id="forgot-password-form" action="/modules/authentication/controllers/ForgotPasswordController.php" method="POST" class="space-y-5">
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
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-500 mt-2">Enter the email address associated with your account</p>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        id="submit-btn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="bi bi-send mr-2"></i>
                    <span id="submit-btn-text">Send Reset Link</span>
                </button>
            </form>
            
            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="login.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium inline-flex items-center">
                    <i class="bi bi-arrow-left mr-1"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
