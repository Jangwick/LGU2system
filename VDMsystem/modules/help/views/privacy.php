<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$pageTitle = 'Privacy Policy';
$currentPage = 'help';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Help & Support', 'url' => BASE_URL . '/modules/help/views/index.php'],
    ['label' => 'Privacy Policy']
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
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-shield-check text-red-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Privacy Policy</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: <?php echo date('F Y'); ?></p>
                    </div>
                </div>
                
                <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-400 space-y-6">
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">1. Information We Collect</h2>
                        <p class="leading-relaxed">The VDM System collects the following information from registered users:</p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Full name, email address, and contact information</li>
                            <li>Position, department, and role within the organization</li>
                            <li>Voting records and session attendance data</li>
                            <li>Login timestamps and access logs</li>
                            <li>Profile pictures (if uploaded)</li>
                        </ul>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">2. How We Use Your Information</h2>
                        <p class="leading-relaxed">Your information is used solely for the purpose of facilitating the legislative voting and decision-making process of the City Government of Valenzuela. This includes:</p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Authenticating your identity for secure system access</li>
                            <li>Recording and attributing votes to authorized council members</li>
                            <li>Generating official reports and session records</li>
                            <li>Maintaining audit trails for transparency and accountability</li>
                        </ul>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">3. Data Security</h2>
                        <p class="leading-relaxed">All data is stored securely within the internal network of the City Government of Valenzuela. We employ industry-standard security measures including encrypted passwords, session management, and access controls to protect your information.</p>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">4. Data Retention</h2>
                        <p class="leading-relaxed">Voting records and session data are retained indefinitely as part of the official legislative record. User account information is maintained as long as the account is active. Deactivated accounts may be archived in accordance with local data retention policies.</p>
                    </section>
                    
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2">5. Contact</h2>
                        <p class="leading-relaxed">For questions or concerns about this privacy policy, please contact the ICT Department at <strong>ict@valenzuela.gov.ph</strong>.</p>
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
