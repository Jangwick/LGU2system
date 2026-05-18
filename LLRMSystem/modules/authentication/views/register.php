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
    <title>Register - <?php echo APP_NAME; ?></title>
    
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
        
        /* Prevent zoom on input focus in iOS */
        @media screen and (max-width: 767px) {
            input, select, textarea { font-size: 16px !important; }
        }
        
        /* Logo Styles */
        .login-logo-container {
            width: 100px !important;
            height: 100px !important;
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
</style>
    
    <!-- Back to Landing Page -->
    <a href="<?php echo BASE_URL; ?>/index.php" class="fixed top-4 left-4 md:top-8 md:left-8 flex items-center text-gray-600 hover:text-red-600 font-medium transition-all duration-300 z-50 group bg-white/80 backdrop-blur-sm px-3 py-2 rounded-lg shadow-sm hover:shadow-md">
        <i class="bi bi-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i>
        <span class="hidden sm:inline">Back to Home</span>
        <span class="sm:hidden">Back</span>
    </a>

    <div class="w-full max-w-2xl py-8">
        <!-- Logo Section -->
        <div class="text-center mb-6 md:mb-8">
            <div class="inline-flex items-center justify-center mb-3 md:mb-4 logo-bounce">
                <div class="login-logo-container bg-white rounded-full shadow-xl items-center justify-center p-2 transform hover:scale-105 transition-all duration-300">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="City Government of Valenzuela" class="login-logo-img" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/images/valenzuela%20logo.webp';">
                </div>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-red-600 text-slide-up" style="animation-delay: 0.1s;">LRMS</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1 md:md-2 text-slide-up" style="animation-delay: 0.2s;">Legislative Records Management System</p>
            <p class="text-xs md:text-sm text-[#002d72] font-semibold mt-1 text-slide-up" style="animation-delay: 0.3s;">City Government of Valenzuela</p>
        </div>
        
        <!-- Registration Card -->
        <div class="bg-white rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 animate-fade-in-up animation-delay-300 transform hover:shadow-2xl transition-all duration-300">
            <div class="mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Create Account</h2>
                <p class="text-sm md:text-base text-gray-600 mt-1">Join the legislative workforce today</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alert-container" class="mb-4"></div>
            
            <!-- Registration Form -->
            <form id="register-form" action="<?php echo AUTH_URL; ?>/controllers/RegisterController.php" method="POST" class="space-y-6">
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-red-600 uppercase tracking-wider flex items-center">
                        <i class="bi bi-person-badge mr-2"></i>
                        Personal Information
                    </h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   placeholder="Juan Dela Cruz"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   placeholder="juan.delacruz@lgu.gov.ph"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        </div>
                        
                        <!-- Department -->
                        <div class="relative z-30">
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <div class="relative custom-select-container">
                                <div id="department-trigger" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition bg-white cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                                    <span id="department-value">Select Department</span>
                                    <i class="bi bi-chevron-down text-gray-400"></i>
                                </div>
                                <div id="department-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-xl z-[100] max-h-64 overflow-y-auto">
                                    <div class="p-2 space-y-1">
                                        <div class="department-option px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer text-sm font-bold text-gray-700 transition-colors" data-value="">Select Department</div>
                                        <div class="department-option px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer text-sm font-bold text-gray-700 transition-colors" data-value="Legislative Office">Legislative Office</div>
                                        <div class="department-option px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer text-sm font-bold text-gray-700 transition-colors" data-value="Mayor's Office">Mayor's Office</div>
                                        <div class="department-option px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer text-sm font-bold text-gray-700 transition-colors" data-value="Legal Department">Legal Department</div>
                                        <div class="department-option px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer text-sm font-bold text-gray-700 transition-colors" data-value="Records Management">Records Management</div>
                                        <div class="department-option px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer text-sm font-bold text-gray-700 transition-colors" data-value="IT Department">IT Department</div>
                                    </div>
                                </div>
                                <input type="hidden" id="department" name="department" required>
                            </div>
                        </div>
                        
                        <!-- Role (auto-assigned as Staff) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Role
                            </label>
                            <input type="hidden" name="role" value="staff">
                            <div class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium flex items-center">
                                <i class="bi bi-person-badge mr-2 text-red-500"></i>
                                Staff <span class="ml-2 text-xs text-amber-600 font-normal">(Requires Admin Approval)</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Information -->
                <div class="space-y-4 pt-2">
                    <h3 class="text-sm font-bold text-red-600 uppercase tracking-wider flex items-center">
                        <i class="bi bi-shield-lock mr-2"></i>
                        Security Information
                    </h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="8-14 unique characters"
                                       class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                                <button type="button" 
                                        id="toggle-password" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye" id="eye-icon-1"></i>
                                </button>
                            </div>
                            <div class="mt-2 text-right">
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
                            <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="confirm-password" 
                                       name="confirm_password" 
                                       required
                                       placeholder="Re-enter password"
                                       class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
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
                <div class="pt-2">
                    <label class="flex items-start group cursor-pointer">
                        <input type="checkbox" 
                               name="terms" 
                               id="terms"
                               required
                               class="w-4 h-4 mt-0.5 text-red-600 border-gray-300 rounded focus:ring-2 focus:ring-red-500 transition cursor-pointer">
                        <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-800 transition">
                            I agree to the <a href="/modules/help/views/terms.php" class="text-red-600 hover:text-red-700 font-semibold underline underline-offset-2">Terms of Service</a> 
                            and <a href="/modules/help/views/privacy.php" class="text-red-600 hover:text-red-700 font-semibold underline underline-offset-2">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        id="register-btn"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg flex items-center justify-center transform hover:-translate-y-0.5">
                    <i class="bi bi-person-plus-fill mr-2"></i>
                    <span id="register-btn-text">Create Account</span>
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="<?php echo LOGIN_URL; ?>" class="text-red-600 hover:text-red-700 font-bold ml-1 transition">Sign In</a>
                </p>
            </div>
        </div>
        
        <!-- Footer Info -->
        <div class="mt-8 text-center text-xs md:text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> LGU Legislative Office. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Application Configuration -->
    <script src="<?php echo asset('js/config.js'); ?>"></script>
    <script src="<?php echo asset('js/auth.js'); ?>"></script>
    <script>
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue) {
    const trigger = document.getElementById(triggerId);
    const dropdown = document.getElementById(dropdownId);
    const valueDisplay = document.getElementById(valueId);
    const hiddenInput = document.getElementById(inputId);
    const options = document.querySelectorAll(optionClass);
    
    if (!trigger || !dropdown || !valueDisplay || !hiddenInput) return;
    
    const selectedValue = hiddenInput.value;
    if (selectedValue) {
        const selectedOption = document.querySelector(`${optionClass}[data-value="${selectedValue}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.textContent;
        }
    }
    
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            valueDisplay.textContent = text;
            hiddenInput.value = value;
            dropdown.classList.add('hidden');
        });
    });
    
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initCustomDropdown('department-trigger', 'department-dropdown', 'department-value', 'department', '.department-option', 'Select Department');
});

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
            
            // Check unique characters
            const uniqueChars = new Set(password).size;
            const isEveryCharUnique = uniqueChars === password.length;
            
            let strength = 0;
            if (password.length >= 8 && password.length <= 14) strength++;
            if (isEveryCharUnique && password.length > 0) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/) || password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';
            
            if (password.length > 14) {
                strengthBar.className = 'h-full bg-red-600 transition-all duration-300';
                strengthText.textContent = 'Too Long (Max 14)';
                strengthText.className = 'text-red-600 text-xs';
            } else if (!isEveryCharUnique && password.length > 0) {
                strengthBar.className = 'h-full bg-red-400 transition-all duration-300';
                strengthText.textContent = 'Characters must be unique';
                strengthText.className = 'text-red-500 text-xs';
            } else if (strength <= 2) {
                strengthBar.className = 'h-full bg-red-400 transition-all duration-300';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-red-500 text-xs';
            } else if (strength <= 3) {
                strengthBar.className = 'h-full bg-yellow-400 transition-all duration-300';
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
