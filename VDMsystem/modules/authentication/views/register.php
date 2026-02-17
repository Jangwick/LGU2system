<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
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
    <title>Create Account - <?php echo APP_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen flex items-center justify-center p-3 md:p-6">
    <style>
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fade-in 0.6s ease-out forwards; }
        .animate-fade-in-up { opacity: 0; transform: translateY(20px); animation: fade-in-up 0.6s ease-out forwards; }
        .animation-delay-100 { animation-delay: 100ms; }
        .animation-delay-200 { animation-delay: 200ms; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(220, 38, 38, 0.1);
        }
        
        .input-group:focus-within label {
            color: #dc2626;
            transform: translateY(-2px);
        }
        
        .vdm-btn {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .vdm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(220, 38, 38, 0.4);
        }
    </style>

    <!-- Back to Login -->
    <a href="login.php" class="fixed top-4 left-4 md:top-8 md:left-8 flex items-center text-gray-600 hover:text-red-600 font-medium transition-all duration-300 z-50 group bg-white/80 backdrop-blur-sm px-4 py-2.5 rounded-xl shadow-sm hover:shadow-md">
        <i class="bi bi-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i>
        <span>Login</span>
    </a>

    <div class="w-full max-w-2xl animate-fade-in-up">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-3xl shadow-xl p-4 mb-4 transform hover:rotate-6 transition-transform">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Create Account</h1>
            <p class="text-gray-500 font-medium mt-1">Join the VDM System today.</p>
        </div>

        <!-- Registration Card -->
        <div class="glass-card rounded-[2.5rem] shadow-2xl p-8 md:p-12 relative overflow-hidden">
            <!-- Decorative accent -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>

            <!-- Alert Container -->
            <div id="alert-container" class="mb-8">
                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3.5 rounded-2xl flex items-center text-sm font-bold animate-shake">
                        <i class="bi bi-exclamation-octagon mr-2 text-lg"></i>
                        <span><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3.5 rounded-2xl flex items-center text-sm font-bold">
                        <i class="bi bi-check-circle mr-2 text-lg"></i>
                        <span><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <form id="register-form" action="../api/register-process.php" method="POST" class="space-y-6">
                <!-- Row 1: Full Name & Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Full Name</label>
                        <div class="relative">
                            <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="full_name" required placeholder="Juana Dela Cruz"
                                   class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all font-medium">
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Email Address</label>
                        <div class="relative">
                            <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="email" name="email" required placeholder="juana@example.com"
                                   class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all font-medium">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Username & Role -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Username</label>
                        <div class="relative">
                            <i class="bi bi-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="username" required placeholder="juana_dc"
                                   class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all font-medium">
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Role / Title</label>
                        <div class="relative">
                            <i class="bi bi-briefcase absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="role" id="role-select" required class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 appearance-none font-medium cursor-pointer">
                                <option value="viewer">Public Viewer</option>
                                <option value="councilor">Councilor</option>
                                <option value="secretary">Legislative Secretary</option>
                            </select>
                            <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                        <!-- Approval notice for privileged roles -->
                        <div id="approval-notice" class="hidden mt-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-xs font-bold flex items-start gap-2">
                            <i class="bi bi-shield-exclamation text-amber-500 text-base mt-0.5 shrink-0"></i>
                            <span>This role requires <strong>administrator approval</strong> before you can sign in. You will be notified once your account has been reviewed.</span>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Department & Position -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Department</label>
                        <div class="relative">
                            <i class="bi bi-building absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="department" required class="w-full pl-12 pr-10 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 appearance-none font-medium cursor-pointer">
                                <option value="" disabled selected>Select Department</option>
                                <option value="Legislative Affairs">Legislative Affairs</option>
                                <option value="Sangguniang Panlungsod">Sangguniang Panlungsod</option>
                                <option value="Office of the Vice Mayor">Office of the Vice Mayor</option>
                                <option value="Finance & Budget">Finance & Budget</option>
                                <option value="Legal & Audit">Legal & Audit</option>
                                <option value="General Public">General Public</option>
                            </select>
                            <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Position</label>
                        <div class="relative">
                            <i class="bi bi-award absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="position" required class="w-full pl-12 pr-10 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 appearance-none font-medium cursor-pointer">
                                <option value="" disabled selected>Select Position</option>
                                <option value="City Councilor">City Councilor</option>
                                <option value="District Councilor">District Councilor</option>
                                <option value="Legislative Secretary">Legislative Secretary</option>
                                <option value="Administrative Staff">Administrative Staff</option>
                                <option value="Session Encoder">Session Encoder</option>
                                <option value="Public Citizen">Public Citizen</option>
                            </select>
                            <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Password</label>
                        <div class="relative">
                            <i class="bi bi-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="password" id="password" name="password" required placeholder="••••••••"
                                   class="w-full pl-12 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all font-medium">
                            <button type="button" onclick="togglePassword('password')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-600 transition-colors">
                                <i class="bi bi-eye" id="password-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 transition-all">Confirm Password</label>
                        <div class="relative">
                            <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••"
                                   class="w-full pl-12 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all font-medium">
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start gap-3 py-2">
                    <input type="checkbox" required class="mt-1 w-5 h-5 text-red-600 border-gray-300 rounded-lg focus:ring-red-500">
                    <p class="text-sm text-gray-600 leading-relaxed font-medium">
                        I agree to the <a href="#" class="text-red-600 hover:underline">Terms of Service</a> and 
                        <a href="#" class="text-red-600 hover:underline">Privacy Policy</a>.
                    </p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full vdm-btn text-white font-black py-4 rounded-2xl shadow-xl flex items-center justify-center gap-3 group text-lg tracking-tight">
                    <span>Create Account</span>
                    <i class="bi bi-arrow-right transition-transform group-hover:translate-x-1"></i>
                </button>
            </form>

            <div class="mt-8 text-center border-t border-gray-100 pt-8">
                <p class="text-gray-600 font-medium">
                    Already have an account? 
                    <a href="login.php" class="text-red-600 hover:text-red-700 font-black">Sign In</a>
                </p>
            </div>
        </div>

        <p class="mt-8 text-center text-gray-400 text-sm font-medium">
            &copy; <?php echo date('Y'); ?> LGU Legislative Office. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const eye = document.getElementById(id + '-eye');
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        // Role-based approval notice toggle
        const roleSelect = document.getElementById('role-select');
        const approvalNotice = document.getElementById('approval-notice');
        const submitBtn = document.querySelector('button[type="submit"]');
        const submitBtnText = submitBtn.querySelector('span');

        function updateApprovalNotice() {
            const role = roleSelect.value;
            const needsApproval = (role === 'councilor' || role === 'secretary');
            
            if (needsApproval) {
                approvalNotice.classList.remove('hidden');
                submitBtnText.textContent = 'Submit for Approval';
            } else {
                approvalNotice.classList.add('hidden');
                submitBtnText.textContent = 'Create Account';
            }
        }

        roleSelect.addEventListener('change', updateApprovalNotice);
        // Run on page load
        updateApprovalNotice();

        document.getElementById('register-form').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (pass !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Processing...';
        });
    </script>
</body>
</html>
