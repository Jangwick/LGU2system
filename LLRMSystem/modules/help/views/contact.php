<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

$pageTitle = 'Contact Us';
$currentPage = 'help';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<script>
    // Force light mode for help pages when viewed as guest
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
    }
</script>

<?php if (isset($_SESSION['user_id'])): ?>
    <?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden bg-white">
        <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
<?php else: ?>
    <div class="flex-1 flex flex-col min-h-screen bg-gray-50">
        <!-- Minimal landing navbar for guests -->
        <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" class="h-8 w-8">
                <span class="font-black text-xl tracking-tighter text-gray-900 uppercase">VALENZUELA<span class="text-red-600">LRMS</span></span>
            </div>
            <a href="<?php echo BASE_URL; ?>/index.php#legal-footer" onclick="if(window.history.length > 1){ window.history.back(); return false; }" class="text-[10px] font-black text-gray-400 hover:text-red-600 uppercase tracking-[0.2em] transition-all">
                <i class="bi bi-arrow-left mr-2"></i>BACK TO HOME
            </a>
        </nav>
<?php endif; ?>

    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6 pb-20">

        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-8 text-white">
                <div class="text-center">
                    <i class="bi bi-headset text-5xl mb-4 block"></i>
                    <h1 class="text-3xl font-black mb-2">Support & Contact</h1>
                    <p class="text-red-100 text-sm">We're here to help. Reach out through any of the channels below.</p>
                </div>
            </div>

            <!-- Contact Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-xl transition-all group">
                    <div class="bg-red-50 rounded-2xl w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="bi bi-envelope-fill text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Email</h3>
                    <p class="text-gray-500 text-sm mb-3">Send us an email anytime</p>
                    <a href="mailto:lrms@valenzuela.gov.ph" class="text-red-600 font-bold text-sm hover:underline">lrms@valenzuela.gov.ph</a>
                </div>
                
                <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-xl transition-all group">
                    <div class="bg-blue-50 rounded-2xl w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="bi bi-telephone-fill text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Phone</h3>
                    <p class="text-gray-500 text-sm mb-3">Mon–Fri, 8AM–5PM</p>
                    <span class="text-blue-600 font-bold text-sm">(02) 8443-3506</span>
                </div>
                
                <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-xl transition-all group">
                    <div class="bg-green-50 rounded-2xl w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="bi bi-geo-alt-fill text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Office</h3>
                    <p class="text-gray-500 text-sm mb-3">Visit during office hours</p>
                    <span class="text-green-600 font-bold text-sm">Valenzuela City Hall</span>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12 mb-8">
                <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                    <i class="bi bi-send text-red-600 mr-3"></i>
                    Send Us a Message
                </h2>
                
                <div id="contact-success" class="hidden mb-6 bg-green-50 border border-green-200 rounded-xl p-5 text-center">
                    <i class="bi bi-check-circle-fill text-green-600 text-3xl mb-2 block"></i>
                    <p class="text-green-700 font-bold">Message Sent Successfully!</p>
                    <p class="text-green-600 text-sm mt-1">We'll get back to you within 1–2 business days.</p>
                </div>

                <form id="contact-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Your Name</label>
                            <input type="text" id="contact-name" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Enter your full name" required>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Email Address</label>
                            <input type="email" id="contact-email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="your.email@valenzuela.gov.ph" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Category</label>
                        <select id="contact-category" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                            <option value="">Select a category...</option>
                            <option value="technical">Technical Issue</option>
                            <option value="account">Account & Access</option>
                            <option value="documents">Document Management</option>
                            <option value="feature">Feature Request</option>
                            <option value="bug">Bug Report</option>
                            <option value="general">General Inquiry</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Subject</label>
                        <input type="text" id="contact-subject" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Brief description of your inquiry" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Message</label>
                        <textarea id="contact-message" rows="5" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm resize-none" placeholder="Describe your issue or question in detail..." required></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-black px-8 py-3 rounded-xl shadow-lg shadow-red-200 transition-all hover:shadow-xl text-sm uppercase tracking-wider">
                            <i class="bi bi-send mr-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Help -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12 mb-8">
                <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                    <i class="bi bi-lightning text-red-600 mr-3"></i>
                    Quick Help
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="<?php echo HELP_URL; ?>/views/index.php" class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-all flex items-center group">
                        <div class="bg-red-50 rounded-xl w-12 h-12 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="bi bi-book text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Help Center</h3>
                            <p class="text-gray-400 text-xs">Guides, tutorials & FAQs</p>
                        </div>
                    </a>
                    <a href="<?php echo HELP_URL; ?>/views/privacy.php" class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-all flex items-center group">
                        <div class="bg-blue-50 rounded-xl w-12 h-12 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="bi bi-shield-lock text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Privacy Policy</h3>
                            <p class="text-gray-400 text-xs">How we protect your data</p>
                        </div>
                    </a>
                    <a href="<?php echo HELP_URL; ?>/views/terms.php" class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-all flex items-center group">
                        <div class="bg-purple-50 rounded-xl w-12 h-12 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="bi bi-file-earmark-ruled text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Terms of Service</h3>
                            <p class="text-gray-400 text-xs">Rules & usage guidelines</p>
                        </div>
                    </a>
                    <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-all flex items-center group cursor-default">
                        <div class="bg-yellow-50 rounded-xl w-12 h-12 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="bi bi-clock-history text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Response Time</h3>
                            <p class="text-gray-400 text-xs">1–2 business days</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQs -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12">
                <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                    <i class="bi bi-question-circle text-red-600 mr-3"></i>
                    Support FAQs
                </h2>
                <div class="space-y-3" id="support-faqs">

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">I forgot my password. How do I reset it?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Click the "Forgot Password" link on the login page. You'll receive a One-Time Password (OTP) at your registered email address. Enter the OTP to verify your identity and set a new password. If you don't receive the OTP, contact your system administrator.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">My account is locked. What should I do?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Accounts are temporarily locked after multiple failed login attempts for security purposes. Please wait 15–30 minutes and try again, or contact your system administrator to unlock your account immediately. Ensure you're using the correct username and password.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">I can't upload a document. What's wrong?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Common causes include: (1) the file exceeds the maximum upload size, (2) the file type is not supported (only PDF, DOCX, etc.), (3) you don't have the required role permissions to upload documents, or (4) a network connectivity issue. Check the file size and format, and try again. If the problem persists, contact support.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">How do I request access to additional features?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Feature access is based on your assigned role. To request elevated permissions or access to additional modules, submit a request through this Support page or contact your department's system administrator. Role changes require approval from an Admin or Super Admin.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">The system is running slowly. What can I do?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Try clearing your browser cache and cookies, using an updated modern browser (Chrome, Firefox, Edge), or checking your internet connection. If performance issues persist across multiple users, it may indicate a server-side issue—please report it via this contact form.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">How do I update my profile information?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Go to your profile settings by clicking your avatar in the top-right corner of the dashboard and selecting "Settings." From there, you can update your name, email, password, and profile picture. Changes take effect immediately.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Can I suggest a new feature?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Yes! We welcome feedback. Use the contact form above and select "Feature Request" as the category. Provide as much detail as possible about the feature you'd like to see, and our team will review it for future development sprints.</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="text-center mt-8 mb-4">
                <a href="<?php echo HELP_URL; ?>/views/index.php" class="text-red-600 hover:text-red-700 font-bold text-sm">
                    <i class="bi bi-arrow-left mr-1"></i> Back to Help & Support
                </a>
            </div>

        </div>
    </main>
<script>
function toggleFaq(btn) {
    const content = btn.nextElementSibling;
    const icon = btn.querySelector('i');
    const isHidden = content.classList.contains('hidden');
    
    // Close all
    document.querySelectorAll('#support-faqs .border > div:last-child').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#support-faqs .border button i').forEach(el => {
        el.classList.remove('rotate-180');
    });
    
    if (isHidden) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    }
}

// Contact Form Submission
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('contact-name').value;
    const email = document.getElementById('contact-email').value;
    const category = document.getElementById('contact-category').value;
    const subject = document.getElementById('contact-subject').value;
    const message = document.getElementById('contact-message').value;
    
    if (!name || !email || !category || !subject || !message) {
        alert('Please fill in all fields.');
        return;
    }
    
    // Show success message
    document.getElementById('contact-form').classList.add('hidden');
    document.getElementById('contact-success').classList.remove('hidden');
    
    // Reset after 5 seconds
    setTimeout(() => {
        document.getElementById('contact-form').classList.remove('hidden');
        document.getElementById('contact-form').reset();
        document.getElementById('contact-success').classList.add('hidden');
    }, 5000);
});
</script>

    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

