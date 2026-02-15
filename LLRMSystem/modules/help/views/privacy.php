<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

$pageTitle = 'Privacy Policy';
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
                    <i class="bi bi-shield-lock text-5xl mb-4 block"></i>
                    <h1 class="text-3xl font-black mb-2">Privacy Policy</h1>
                    <p class="text-red-100 text-sm">Last updated: <?php echo date('F d, Y'); ?></p>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12 space-y-10">
                
                <!-- Introduction -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-info-circle text-red-600 mr-3"></i>
                        Introduction
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        The City Government of Valenzuela ("we", "our", or "us") is committed to protecting the privacy and security of your personal information. This Privacy Policy explains how the Legislative Records Management System (LRMS) collects, uses, stores, and protects data in accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> and its implementing rules and regulations.
                    </p>
                </section>

                <!-- Information We Collect -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-collection text-red-600 mr-3"></i>
                        Information We Collect
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="font-bold text-gray-800 mb-2">Personal Information</h3>
                            <ul class="text-gray-600 space-y-2 text-sm">
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Full name, email address, and contact details</li>
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Department and role within the City Government</li>
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Profile picture (optional)</li>
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Login credentials (passwords are encrypted)</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="font-bold text-gray-800 mb-2">System Usage Data</h3>
                            <ul class="text-gray-600 space-y-2 text-sm">
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Login timestamps and session information</li>
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Document access and activity logs</li>
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>IP addresses and browser information</li>
                                <li class="flex items-start"><i class="bi bi-check-circle-fill text-green-500 mr-2 mt-0.5"></i>Search queries and system interactions</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- How We Use Your Information -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-gear text-red-600 mr-3"></i>
                        How We Use Your Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow">
                            <div class="text-red-600 text-2xl mb-2"><i class="bi bi-person-check"></i></div>
                            <h3 class="font-bold text-gray-800 mb-1 text-sm">Authentication</h3>
                            <p class="text-gray-500 text-sm">To verify your identity and manage access to the system.</p>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow">
                            <div class="text-red-600 text-2xl mb-2"><i class="bi bi-file-earmark-text"></i></div>
                            <h3 class="font-bold text-gray-800 mb-1 text-sm">Records Management</h3>
                            <p class="text-gray-500 text-sm">To manage, track, and organize legislative documents.</p>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow">
                            <div class="text-red-600 text-2xl mb-2"><i class="bi bi-clipboard-data"></i></div>
                            <h3 class="font-bold text-gray-800 mb-1 text-sm">Audit Trail</h3>
                            <p class="text-gray-500 text-sm">To maintain a transparent log of all system activities.</p>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow">
                            <div class="text-red-600 text-2xl mb-2"><i class="bi bi-shield-check"></i></div>
                            <h3 class="font-bold text-gray-800 mb-1 text-sm">Security</h3>
                            <p class="text-gray-500 text-sm">To prevent unauthorized access and protect system integrity.</p>
                        </div>
                    </div>
                </section>

                <!-- Data Protection -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-lock text-red-600 mr-3"></i>
                        Data Protection Measures
                    </h2>
                    <div class="bg-red-50 rounded-xl p-6 border border-red-100">
                        <ul class="text-gray-700 space-y-3 text-sm">
                            <li class="flex items-start"><i class="bi bi-shield-fill-check text-red-600 mr-3 mt-0.5"></i><span><strong>Encryption:</strong> All passwords are hashed using industry-standard algorithms. Data in transit is protected via HTTPS/SSL.</span></li>
                            <li class="flex items-start"><i class="bi bi-shield-fill-check text-red-600 mr-3 mt-0.5"></i><span><strong>Access Control:</strong> Role-based access control (RBAC) ensures users only access data relevant to their responsibilities.</span></li>
                            <li class="flex items-start"><i class="bi bi-shield-fill-check text-red-600 mr-3 mt-0.5"></i><span><strong>Session Management:</strong> Automatic session timeouts and concurrent login prevention protect your account.</span></li>
                            <li class="flex items-start"><i class="bi bi-shield-fill-check text-red-600 mr-3 mt-0.5"></i><span><strong>Audit Logging:</strong> All system activities are recorded for accountability and security monitoring.</span></li>
                            <li class="flex items-start"><i class="bi bi-shield-fill-check text-red-600 mr-3 mt-0.5"></i><span><strong>Backup & Recovery:</strong> Regular automated backups ensure data is not lost in the event of a system failure.</span></li>
                        </ul>
                    </div>
                </section>

                <!-- Data Retention -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-clock-history text-red-600 mr-3"></i>
                        Data Retention
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        Personal data is retained for as long as your account is active or as necessary to fulfill the purposes for which it was collected. Legislative records and related documents are retained permanently in accordance with government archival and records management policies. Activity logs are retained for a minimum of five (5) years for audit and compliance purposes.
                    </p>
                </section>

                <!-- Your Rights -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-person-raised-hand text-red-600 mr-3"></i>
                        Your Rights Under RA 10173
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-4">As a data subject, you have the following rights:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Right to be Informed</h3>
                            <p class="text-gray-500 text-xs">Know how your personal data is being processed.</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Right to Access</h3>
                            <p class="text-gray-500 text-xs">Request a copy of your personal data held by us.</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Right to Correction</h3>
                            <p class="text-gray-500 text-xs">Request correction of inaccurate or incomplete data.</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Right to Erasure</h3>
                            <p class="text-gray-500 text-xs">Request deletion of your data when no longer necessary.</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Right to Object</h3>
                            <p class="text-gray-500 text-xs">Object to the processing of your personal data.</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-bold text-gray-800 text-sm mb-1">Right to File a Complaint</h3>
                            <p class="text-gray-500 text-xs">File a complaint with the National Privacy Commission.</p>
                        </div>
                    </div>
                </section>

                <!-- Contact -->
                <section>
                    <h2 class="text-xl font-black text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-envelope text-red-600 mr-3"></i>
                        Contact Us
                    </h2>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="text-gray-600 text-sm mb-4">For questions, concerns, or requests regarding this Privacy Policy or your personal data, please contact:</p>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-800 font-bold">Data Protection Officer</p>
                            <p class="text-gray-600">Legislative Records Management Office</p>
                            <p class="text-gray-600">City Government of Valenzuela</p>
                            <p class="text-gray-600">Valenzuela City, Metropolitan Manila, Philippines</p>
                            <p class="text-gray-600 mt-2"><i class="bi bi-envelope-fill text-red-600 mr-2"></i>lrms@valenzuela.gov.ph</p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- FAQs -->
            <div class="bg-white rounded-2xl shadow-md p-8 md:p-12 mt-8">
                <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                    <i class="bi bi-question-circle text-red-600 mr-3"></i>
                    Privacy FAQs
                </h2>
                <div class="space-y-3" id="privacy-faqs">
                    
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Who can see my personal information?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Only authorized administrators with the appropriate role permissions can view personal user information. Regular users can only see their own profile data. All access to personal data is logged in the audit trail.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Is my password stored securely?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Yes. Your password is never stored in plain text. We use PHP's <code class="bg-gray-100 px-1 rounded">password_hash()</code> function with the bcrypt algorithm, which is an industry-standard one-way hashing method. Even system administrators cannot retrieve your original password.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">What happens to my data if my account is deactivated?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">When an account is deactivated, your personal information is retained but your access to the system is revoked. Your activity history is preserved for audit purposes. You may request full deletion of your personal data by contacting the Data Protection Officer.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Does the system track my activity?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Yes. For security and accountability, LRMS records login/logout events, document uploads, edits, deletions, and other significant actions. This audit trail is essential for maintaining the integrity of legislative records and is required under government transparency policies.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Can I download a copy of my data?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Under your Right to Access per RA 10173, you may request a copy of all personal data the system holds about you. Please contact the Data Protection Officer to submit a formal request. We will respond within 30 days.</p>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-bold text-gray-800 text-sm">Is this system compliant with the Data Privacy Act?</span>
                            <i class="bi bi-chevron-down text-gray-400 transition-transform duration-300"></i>
                        </button>
                        <div class="hidden px-5 pb-5">
                            <p class="text-gray-500 text-sm leading-relaxed">Yes. The LRMS has been designed with the Data Privacy Act of 2012 (RA 10173) as a foundational requirement. We implement organizational, physical, and technical security measures to protect personal data in compliance with NPC guidelines.</p>
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
    document.querySelectorAll('#privacy-faqs .border > div:last-child').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#privacy-faqs .border button i').forEach(el => {
        el.classList.remove('rotate-180');
    });
    
    if (isHidden) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    }
}
</script>

    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>


