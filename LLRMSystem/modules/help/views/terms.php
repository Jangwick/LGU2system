<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

$pageTitle = 'Terms & Conditions';
$currentPage = 'help';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<script>
    // Force light mode for legal/help pages when viewed as guest
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
        <!-- Minimal landing navbar for guests - matching landing page style -->
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
                    <i class="bi bi-file-earmark-ruled text-5xl mb-4 block"></i>
                    <h1 class="text-3xl font-black mb-2">Terms of Service</h1>
                    <p class="text-red-100 text-sm">Last updated: <?php echo date('F d, Y'); ?></p>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12 space-y-10">

                <!-- Acceptance -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-hand-thumbs-up text-red-600 mr-3"></i>
                        Acceptance of Terms
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        By accessing and using the Legislative Records Management System (LRMS) operated by the City Government of Valenzuela, you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, you must discontinue use of the system immediately. These terms apply to all users, including administrators, staff, and viewers.
                    </p>
                </section>

                <!-- System Purpose -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-bullseye text-red-600 mr-3"></i>
                        System Purpose
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        LRMS is a digital ecosystem designed to preserve, organize, manage, and analyze the legislative records of the City of Valenzuela. The system facilitates:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-5 flex items-start">
                            <i class="bi bi-archive text-red-600 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">Document Archiving</h3>
                                <p class="text-gray-500 text-xs mt-1">Secure storage and versioning of legislative documents.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 flex items-start">
                            <i class="bi bi-search text-red-600 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">Search & Retrieval</h3>
                                <p class="text-gray-500 text-xs mt-1">Advanced search across all legislative records.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 flex items-start">
                            <i class="bi bi-graph-up text-red-600 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">Analytics & Reports</h3>
                                <p class="text-gray-500 text-xs mt-1">Data-driven insights on legislative activities.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 flex items-start">
                            <i class="bi bi-people text-red-600 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm">Collaboration</h3>
                                <p class="text-gray-500 text-xs mt-1">Multi-user access with role-based permissions.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- User Accounts -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-person-badge text-red-600 mr-3"></i>
                        User Accounts & Responsibilities
                    </h2>
                    <div class="space-y-4">
                        <div class="border-l-4 border-red-600 pl-5">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Account Security</h3>
                            <p class="text-gray-500 text-sm">You are responsible for maintaining the confidentiality of your login credentials. Do not share your username or password with anyone. Report any unauthorized access immediately.</p>
                        </div>
                        <div class="border-l-4 border-red-600 pl-5">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Accurate Information</h3>
                            <p class="text-gray-500 text-sm">You must provide accurate, current, and complete information during registration and keep your profile information up to date.</p>
                        </div>
                        <div class="border-l-4 border-red-600 pl-5">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Authorized Use Only</h3>
                            <p class="text-gray-500 text-sm">Access is granted strictly for official government purposes. Any use of the system for personal gain, unauthorized data extraction, or malicious activities is strictly prohibited.</p>
                        </div>
                        <div class="border-l-4 border-red-600 pl-5">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Account Termination</h3>
                            <p class="text-gray-500 text-sm">The system administrators reserve the right to suspend or terminate your account if you violate these terms or engage in activities that compromise system security.</p>
                        </div>
                    </div>
                </section>

                <!-- Acceptable Use -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-check2-square text-red-600 mr-3"></i>
                        Acceptable Use Policy
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-bold text-green-700 text-sm mb-3 flex items-center"><i class="bi bi-check-circle-fill mr-2"></i>Permitted</h3>
                            <ul class="text-gray-600 space-y-2 text-sm">
                                <li class="flex items-start"><i class="bi bi-check text-green-500 mr-2 mt-0.5"></i>Uploading and managing official legislative documents</li>
                                <li class="flex items-start"><i class="bi bi-check text-green-500 mr-2 mt-0.5"></i>Searching and viewing records per your role permissions</li>
                                <li class="flex items-start"><i class="bi bi-check text-green-500 mr-2 mt-0.5"></i>Generating authorized reports and analytics</li>
                                <li class="flex items-start"><i class="bi bi-check text-green-500 mr-2 mt-0.5"></i>Using the system for official city government business</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-bold text-red-700 text-sm mb-3 flex items-center"><i class="bi bi-x-circle-fill mr-2"></i>Prohibited</h3>
                            <ul class="text-gray-600 space-y-2 text-sm">
                                <li class="flex items-start"><i class="bi bi-x text-red-500 mr-2 mt-0.5"></i>Uploading malicious files, viruses, or harmful content</li>
                                <li class="flex items-start"><i class="bi bi-x text-red-500 mr-2 mt-0.5"></i>Attempting to bypass security or access controls</li>
                                <li class="flex items-start"><i class="bi bi-x text-red-500 mr-2 mt-0.5"></i>Unauthorized data scraping, copying, or distribution</li>
                                <li class="flex items-start"><i class="bi bi-x text-red-500 mr-2 mt-0.5"></i>Impersonating other users or falsifying records</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Intellectual Property -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-bank text-red-600 mr-3"></i>
                        Intellectual Property & Government Records
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        All legislative records within the system are the property of the City Government of Valenzuela. The LRMS software, its design, features, and underlying code are protected by applicable intellectual property laws. Public documents may be accessed in accordance with the <strong>Freedom of Information (FOI)</strong> program and relevant government disclosure policies.
                    </p>
                </section>

                <!-- System Availability -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-cloud-check text-red-600 mr-3"></i>
                        System Availability
                    </h2>
                    <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-100">
                        <p class="text-gray-600 text-sm leading-relaxed">
                            While we strive to maintain 24/7 system availability, the City Government of Valenzuela does not guarantee uninterrupted access. The system may be temporarily unavailable due to scheduled maintenance, upgrades, or unforeseen technical issues. We will make reasonable efforts to notify users in advance of any planned downtime.
                        </p>
                    </div>
                </section>

                <!-- Limitation of Liability -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-exclamation-triangle text-red-600 mr-3"></i>
                        Limitation of Liability
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        The City Government of Valenzuela shall not be liable for any indirect, incidental, or consequential damages arising from the use of LRMS. The system is provided on an "as-is" basis. Users are advised to maintain local copies of critical documents as an additional safeguard. The system's backup and recovery features are provided as a courtesy but do not constitute a guarantee against data loss.
                    </p>
                </section>

                <!-- Amendments -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-pencil-square text-red-600 mr-3"></i>
                        Amendments
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        The City Government of Valenzuela reserves the right to update or modify these Terms of Service at any time. Users will be notified of significant changes via system notifications or email. Continued use of the system after changes are posted constitutes acceptance of the revised terms.
                    </p>
                </section>

                <!-- Governing Law -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-building text-red-600 mr-3"></i>
                        Governing Law
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        These Terms of Service are governed by and construed in accordance with the laws of the Republic of the Philippines, including but not limited to the <strong>Data Privacy Act of 2012 (RA 10173)</strong>, the <strong>Electronic Commerce Act (RA 8792)</strong>, and applicable local government regulations. Any disputes shall be resolved under the jurisdiction of the courts of Valenzuela City.
                    </p>
                </section>

            </div>

            <!-- FAQs -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12 mt-8">
                <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                    <i class="bi bi-question-circle text-red-600 mr-3"></i>
                    Terms FAQs
                </h2>
                <div class="space-y-3" id="terms-faqs">

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Who is authorized to use LRMS?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">LRMS is exclusively for authorized personnel of the City Government of Valenzuela. Accounts are created and approved by system administrators. Access is restricted based on your assigned role (Super Admin, Admin, Staff, or Viewer).</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Can I share my login credentials with a colleague?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">No. Each user must have their own account. Sharing credentials is a violation of these terms and compromises the audit trail. If a colleague needs access, they should request a new account from the system administrator.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">What happens if I violate these terms?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Violations may result in immediate suspension or termination of your account. Depending on the severity of the breach, additional administrative or legal actions may be taken in accordance with civil service rules and applicable Philippine laws.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Can I download or export documents from the system?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Users with appropriate permissions may download and export documents. However, all exports are logged in the activity audit trail. Documents are for official use only and should not be distributed outside authorized channels without proper authorization.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">What types of documents can I upload?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">LRMS accepts standard document formats including PDF, DOCX, and other common file types. All uploads must be official legislative documents. The system includes built-in file type validation and size limits to maintain system integrity and security.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">How often are these terms updated?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">These Terms of Service are reviewed and updated as necessary. Users will be notified through system notifications whenever significant changes are made. We recommend reviewing these terms periodically to stay informed of any updates.</p>
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
    document.querySelectorAll('#terms-faqs .border > div:last-child').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#terms-faqs .border button i').forEach(el => {
        el.classList.remove('rotate-180');
    });
    
    if (isHidden) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    }
}
</script>

    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

