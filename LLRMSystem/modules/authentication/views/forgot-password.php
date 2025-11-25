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
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg">
                <i class="bi bi-key text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Forgot Password?</h1>
            <p class="text-gray-600 mt-2">No worries, we'll send you reset instructions</p>
        </div>
        
        <!-- Forgot Password Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
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
