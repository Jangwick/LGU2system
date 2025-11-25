<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Legislative Records Management System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg">
                <i class="bi bi-file-earmark-text text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
            <p class="text-gray-600 mt-2">Join the Legislative Records Management System</p>
        </div>
        
        <!-- Registration Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4"></div>
            
            <!-- Registration Form -->
            <form id="register-form" action="/modules/authentication/controllers/RegisterController.php" method="POST" class="space-y-5">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-person-badge mr-2 text-blue-600"></i>
                        Personal Information
                    </h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   placeholder="Juan Dela Cruz"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   placeholder="juan.delacruz@lgu.gov.ph"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        
                        <!-- Department -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <select id="department" 
                                    name="department" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">Select Department</option>
                                <option value="Legislative Office">Legislative Office</option>
                                <option value="Mayor's Office">Mayor's Office</option>
                                <option value="Legal Department">Legal Department</option>
                                <option value="Records Management">Records Management</option>
                                <option value="IT Department">IT Department</option>
                            </select>
                        </div>
                        
                        <!-- Position -->
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-2">
                                Position
                            </label>
                            <input type="text" 
                                   id="position" 
                                   name="position"
                                   placeholder="Legislative Staff"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        
                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select id="role" 
                                    name="role" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">Select Role</option>
                                <option value="USER">User</option>
                                <option value="STAFF">Staff</option>
                                <option value="MANAGER">Manager</option>
                                <option value="ADMIN">Administrator</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Security Information -->
                <div class="border-t pt-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-shield-lock mr-2 text-blue-600"></i>
                        Security Information
                    </h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="Minimum 8 characters"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <button type="button" 
                                        id="toggle-password" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye" id="eye-icon-1"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center text-xs space-x-2">
                                    <div id="strength-bar" class="flex-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                        <div id="strength-progress" class="h-full w-0 transition-all duration-300"></div>
                                    </div>
                                    <span id="strength-text" class="text-gray-500">Weak</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="confirm-password" 
                                       name="confirm_password" 
                                       required
                                       placeholder="Re-enter password"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <button type="button" 
                                        id="toggle-confirm-password" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye" id="eye-icon-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="border-t pt-5">
                    <label class="flex items-start">
                        <input type="checkbox" 
                               name="terms" 
                               id="terms"
                               required
                               class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">
                            I agree to the <a href="/modules/help/views/terms.php" class="text-blue-600 hover:text-blue-700 font-medium">Terms of Service</a> 
                            and <a href="/modules/help/views/privacy.php" class="text-blue-600 hover:text-blue-700 font-medium">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        id="register-btn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="bi bi-person-plus mr-2"></i>
                    <span id="register-btn-text">Create Account</span>
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-700 font-semibold">Sign In</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="/LLRMSystem/public/assets/js/auth.js"></script>
    <script>
        // Password visibility toggles
        document.getElementById('toggle-password')?.addEventListener('click', function() {
            const field = document.getElementById('password');
            const icon = document.getElementById('eye-icon-1');
            field.type = field.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
        
        document.getElementById('toggle-confirm-password')?.addEventListener('click', function() {
            const field = document.getElementById('confirm-password');
            const icon = document.getElementById('eye-icon-2');
            field.type = field.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
        
        // Password strength indicator
        document.getElementById('password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strength-progress');
            const strengthText = document.getElementById('strength-text');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';
            
            if (strength <= 2) {
                strengthBar.className = 'h-full bg-red-500 transition-all duration-300';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-red-500 text-xs';
            } else if (strength <= 3) {
                strengthBar.className = 'h-full bg-yellow-500 transition-all duration-300';
                strengthText.textContent = 'Medium';
                strengthText.className = 'text-yellow-600 text-xs';
            } else {
                strengthBar.className = 'h-full bg-green-500 transition-all duration-300';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-green-600 text-xs';
            }
        });
    </script>
</body>
</html>
