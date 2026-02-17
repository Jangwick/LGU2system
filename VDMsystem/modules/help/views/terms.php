<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$pageTitle = 'Terms of Use';
$currentPage = 'help';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Help & Support', 'url' => BASE_URL . '/modules/help/views/index.php'],
    ['label' => 'Terms of Use']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-950 p-3 md:p-6 custom-scrollbar">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 md:p-10 animate-fade-in">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-file-earmark-text text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Terms of Use</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: <?php echo date('F Y'); ?></p>
                    </div>
                </div>
                
                <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-400 space-y-6">
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">1. Acceptance of Terms</h2>
                        <p class="leading-relaxed">By accessing and using the Voting and Decision-Making (VDM) System, you agree to be bound by these Terms of Use. This system is the property of the City Government of Valenzuela and is intended for authorized government personnel only.</p>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">2. Authorized Use</h2>
                        <p class="leading-relaxed">The VDM System is exclusively for:</p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Conducting official legislative voting sessions</li>
                            <li>Recording and managing votes on legislative items</li>
                            <li>Generating official reports and minutes</li>
                            <li>Managing user accounts and system settings (admin only)</li>
                        </ul>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">3. User Responsibilities</h2>
                        <p class="leading-relaxed">As a user of this system, you are responsible for:</p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Maintaining the confidentiality of your login credentials</li>
                            <li>Casting votes truthfully and in accordance with your legislative mandate</li>
                            <li>Not sharing your account with any other person</li>
                            <li>Reporting any unauthorized access or suspicious activity</li>
                            <li>Complying with all applicable laws and regulations</li>
                        </ul>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">4. Vote Integrity</h2>
                        <p class="leading-relaxed">All votes cast through this system are recorded permanently as part of the official legislative record. Votes cannot be changed once submitted. Any attempt to manipulate, falsify, or interfere with the voting process is a serious offense and will be handled accordingly.</p>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">5. System Availability</h2>
                        <p class="leading-relaxed">While we strive to maintain continuous availability, the ICT Department may need to perform maintenance or updates. Users will be notified in advance of any planned downtime whenever possible.</p>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">6. Modifications</h2>
                        <p class="leading-relaxed">The City Government of Valenzuela reserves the right to modify these Terms of Use at any time. Continued use of the system after changes constitutes acceptance of the modified terms.</p>
                    </section>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="<?php echo BASE_URL; ?>/modules/help/views/index.php" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 hover:text-red-700 transition-colors">
                        <i class="bi bi-arrow-left"></i>
                        Back to Help & Support
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>
