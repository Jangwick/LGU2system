<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$pageTitle = 'Contact Support';
$currentPage = 'help';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Help & Support', 'url' => BASE_URL . '/modules/help/views/index.php'],
    ['label' => 'Contact Support']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-950 p-3 md:p-6 custom-scrollbar">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
                <div class="relative">
                    <h1 class="text-xl md:text-2xl font-black mb-1 tracking-tight">
                        <i class="bi bi-headset mr-2"></i>Contact Support
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">
                        Need assistance? Reach out to our IT support team.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contact Form -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center">
                            <i class="bi bi-envelope-fill text-red-600 text-lg"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Send a Message</h2>
                    </div>
                    
                    <form onsubmit="submitContactForm(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Subject</label>
                                <select class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all">
                                    <option value="">Select a topic</option>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="access">Access Issue</option>
                                    <option value="question">General Question</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Message</label>
                                <textarea rows="5" placeholder="Describe your issue or question..." 
                                          class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all resize-none"></textarea>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <i class="bi bi-send"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">ICT Department</h2>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-envelope text-red-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">ict@valenzuela.gov.ph</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-telephone text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">(02) 8443-1700 local 2500</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-50 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-clock text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Office Hours</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">Mon - Fri, 8:00 AM - 5:00 PM</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-orange-50 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-geo-alt text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Location</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">ICT Department, City Hall Annex</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-xl p-5">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-lightbulb-fill text-blue-600 text-xl mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-blue-900 dark:text-blue-300">Tip</p>
                                <p class="text-xs text-blue-700 dark:text-blue-400 mt-1 leading-relaxed">
                                    For the fastest resolution, include your user ID, the page where you encountered the issue, 
                                    and any error messages you received in your support request.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <a href="<?php echo BASE_URL; ?>/modules/help/views/index.php" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 hover:text-red-700 transition-colors">
                    <i class="bi bi-arrow-left"></i>
                    Back to Help & Support
                </a>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
function submitContactForm(event) {
    event.preventDefault();
    showToast('Your message has been sent! The ICT team will respond within 24 hours.', 'success');
    event.target.reset();
}
</script>
