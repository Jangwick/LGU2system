<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$pageTitle = 'Help & Support';
$currentPage = 'help';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Help & Support']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-950 p-3 md:p-6 custom-scrollbar">
        <!-- Header Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            <div class="absolute right-8 bottom-0 opacity-10 hidden md:block">
                <i class="bi bi-question-circle text-[120px]"></i>
            </div>
            <div class="relative">
                <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">
                    <i class="bi bi-life-preserver mr-2"></i>Help & Support
                </h1>
                <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium max-w-lg">
                    Find answers to common questions, learn how to use the system, and get assistance when you need it.
                </p>
                <!-- Search Bar -->
                <div class="mt-4 max-w-xl">
                    <div class="relative">
                        <input type="text" id="helpSearch" placeholder="Search for help topics..." 
                               class="w-full px-5 py-3 rounded-xl text-gray-800 dark:text-white bg-white/95 dark:bg-gray-800/90 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 text-sm shadow-lg"
                               oninput="filterHelpTopics(this.value)">
                        <i class="bi bi-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <a href="#getting-started" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1 group block">
                <div class="flex items-center gap-3">
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-full p-2.5 group-hover:bg-red-100 dark:group-hover:bg-red-900/30 transition-colors">
                        <i class="bi bi-rocket-takeoff-fill text-red-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">Getting Started</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Quick start guide</p>
                    </div>
                </div>
            </a>
            <a href="#faq" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-blue-500 hover:shadow-lg transition-all transform hover:-translate-y-1 group block">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-full p-2.5 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors">
                        <i class="bi bi-chat-dots-fill text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">FAQ</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Common questions</p>
                    </div>
                </div>
            </a>
            <a href="#user-guide" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1 group block">
                <div class="flex items-center gap-3">
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-full p-2.5 group-hover:bg-green-100 dark:group-hover:bg-green-900/30 transition-colors">
                        <i class="bi bi-book-fill text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">User Guide</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">System documentation</p>
                    </div>
                </div>
            </a>
            <a href="#contact" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1 group block">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-full p-2.5 group-hover:bg-purple-100 dark:group-hover:bg-purple-900/30 transition-colors">
                        <i class="bi bi-headset text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">Contact</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Reach the IT team</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Getting Started Section -->
        <div id="getting-started" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up help-section">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-rocket-takeoff-fill text-red-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Getting Started</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Learn the basics of the VDM System</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-5 bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-xl border border-red-100 dark:border-red-900/20 hover:shadow-md transition-all">
                    <div class="text-2xl mb-3">🗳️</div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-2">1. Cast Your Vote</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Navigate to <strong>Cast Vote</strong> from the sidebar. Select an active session, review the document, and submit your vote (Approve, Reject, or Abstain).
                    </p>
                </div>
                <div class="p-5 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-xl border border-blue-100 dark:border-blue-900/20 hover:shadow-md transition-all">
                    <div class="text-2xl mb-3">📊</div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-2">2. View Results</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Go to <strong>Vote Results</strong> to see real-time tallies, voting breakdowns, and session outcomes once voting has concluded.
                    </p>
                </div>
                <div class="p-5 bg-gradient-to-br from-green-50 to-teal-50 dark:from-green-900/10 dark:to-teal-900/10 rounded-xl border border-green-100 dark:border-green-900/20 hover:shadow-md transition-all">
                    <div class="text-2xl mb-3">📋</div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-2">3. Manage Sessions</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Administrators and Secretaries can create, configure, and manage voting sessions via the <strong>Voting Sessions</strong> page.
                    </p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div id="faq" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-200 help-section">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-chat-dots-fill text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Frequently Asked Questions</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Quick answers to common questions</p>
                </div>
            </div>
            
            <div class="space-y-3" id="faqContainer">
                <!-- FAQ Item 1 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                        <span class="text-sm font-bold text-gray-800 dark:text-white pr-4">How do I cast a vote in a session?</span>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform faq-icon flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            To cast a vote, navigate to the <strong>"Cast Vote"</strong> section from the sidebar menu. You'll see a list of active voting sessions. 
                            Click on a session to review the legislative item, then choose your vote: <strong>Approve</strong>, <strong>Reject</strong>, or <strong>Abstain</strong>. 
                            You can also add optional remarks before submitting. Once submitted, your vote is recorded and cannot be changed. 
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                        <span class="text-sm font-bold text-gray-800 dark:text-white pr-4">Can I change my vote after submitting?</span>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform faq-icon flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            No, once a vote is submitted, it is final and cannot be changed. This is by design to maintain the integrity and transparency of the voting process. 
                            If you believe a vote was cast in error, please contact the Session Secretary or System Administrator immediately for assistance.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                        <span class="text-sm font-bold text-gray-800 dark:text-white pr-4">What are the different user roles?</span>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform faq-icon flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed space-y-2">
                            <p>The system supports several roles:</p>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li><strong>Administrator:</strong> Full system access, manages users, sessions, and all settings.</li>
                                <li><strong>Secretary:</strong> Creates and manages voting sessions, generates reports, records minutes.</li>
                                <li><strong>Councilor:</strong> Casts votes on legislative items in assigned sessions.</li>
                                <li><strong>Viewer:</strong> View-only access to public session results and reports.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                        <span class="text-sm font-bold text-gray-800 dark:text-white pr-4">How do I change my password?</span>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform faq-icon flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            Go to <strong>Settings</strong> from the profile dropdown in the navbar. Under the <strong>Security</strong> tab, 
                            you'll find the password change form. Enter your current password, followed by your new password (minimum 6 characters). 
                            Click "Update Password" to save the changes. You'll need to use the new password on your next login.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                        <span class="text-sm font-bold text-gray-800 dark:text-white pr-4">How do I switch between dark and light mode?</span>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform faq-icon flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            You can toggle dark mode from the <strong>moon/sun icon</strong> on the top navigation bar. 
                            Alternatively, go to <strong>Settings → Appearance</strong> to set your preferred theme mode (Light, Dark, or System Default).
                            Your preference is saved locally and will be remembered across sessions.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 6 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                        <span class="text-sm font-bold text-gray-800 dark:text-white pr-4">What is a quorum and why is it important?</span>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform faq-icon flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer hidden px-4 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            A quorum is the minimum number of voters/attendees required for a voting session to be valid. 
                            When creating a session, the Secretary sets the quorum requirement. The system automatically checks if quorum is met 
                            before allowing the session to proceed. If quorum is not met, voting results may be marked as unofficial.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Guide Section -->
        <div id="user-guide" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-300 help-section">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-green-50 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-book-fill text-green-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">User Guide</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Detailed guides for common tasks</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="group p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="bi bi-speedometer2 text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1">Dashboard Overview</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Your dashboard shows key statistics, recent activity, upcoming sessions, and quick action shortcuts. 
                                The widgets and charts auto-update to reflect the latest data.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="group p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="bi bi-calendar-event text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1">Managing Voting Sessions</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Secretaries and Admins can create new sessions by setting the title, date, committee, vote method, 
                                selecting legislative items, and choosing attendees. Sessions can be started, paused, and closed.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="group p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="bi bi-person-lines-fill text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1">Profile Management</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Click your avatar in the navbar and select "My Profile" to update your personal information, 
                                upload a profile picture, and view your voting history and account details.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="group p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="bi bi-graph-up text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1">Reports & Analytics</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Access detailed reports on voting patterns, session summaries, attendance records, 
                                and approval rates. Reports can be filtered by date range and exported.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="group p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="bi bi-people text-orange-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1">User Management</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Administrators can create, edit, and deactivate user accounts. New user registrations 
                                require admin approval. Roles can be assigned: Admin, Secretary, Councilor, or Viewer.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="group p-5 bg-gray-50 dark:bg-gray-800/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="bi bi-shield-lock text-teal-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-1">Security & Audit</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                All actions are logged in the audit trail for transparency. Administrators can review 
                                login history, vote records, and system changes from the Audit Log section.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Support Section -->
        <div id="contact" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 animate-fade-in-up animation-delay-400">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 help-section">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-headset text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Contact Support</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Get in touch with our IT team</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <div class="w-11 h-11 bg-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-envelope-fill text-red-600"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">ict@valenzuela.gov.ph</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-telephone-fill text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">(02) 8443-1700 local 2500</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <div class="w-11 h-11 bg-green-100 dark:bg-green-900/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-clock-fill text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Support Hours</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">Mon - Fri, 8:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <div class="w-11 h-11 bg-orange-100 dark:bg-orange-900/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-geo-alt-fill text-orange-600"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">ICT Department, City Hall Annex</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Valenzuela City, Metro Manila</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 help-section">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl flex items-center justify-center">
                        <i class="bi bi-info-circle-fill text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">System Information</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">About this application</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Application</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo APP_NAME; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Version</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo APP_VERSION; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Module Code</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo APP_MODULE_CODE; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Description</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo APP_DESCRIPTION; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Server Time</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo date('M d, Y h:i A'); ?></span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">PHP Version</span>
                        <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo phpversion(); ?></span>
                    </div>
                </div>
                
                <!-- Footer links -->
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
                    <a href="<?php echo BASE_URL; ?>/modules/help/views/privacy.php" class="text-xs font-bold text-red-600 hover:text-red-700 transition-colors">
                        <i class="bi bi-shield-check mr-1"></i>Privacy Policy
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/help/views/terms.php" class="text-xs font-bold text-red-600 hover:text-red-700 transition-colors">
                        <i class="bi bi-file-text mr-1"></i>Terms of Use
                    </a>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up animation-delay-500 help-section">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-keyboard text-yellow-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Keyboard Shortcuts</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Navigate faster with keyboard shortcuts</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Go to Dashboard</span>
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">Alt</kbd>
                        <span class="text-gray-400 text-xs">+</span>
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">D</kbd>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Toggle Sidebar</span>
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">Alt</kbd>
                        <span class="text-gray-400 text-xs">+</span>
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">S</kbd>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Toggle Dark Mode</span>
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">Alt</kbd>
                        <span class="text-gray-400 text-xs">+</span>
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">T</kbd>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Search</span>
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">Ctrl</kbd>
                        <span class="text-gray-400 text-xs">+</span>
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">K</kbd>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Scroll to Top</span>
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">Home</kbd>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Close Modal</span>
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded shadow-sm border border-gray-300 dark:border-gray-600">Esc</kbd>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
// FAQ toggle
function toggleFaq(button) {
    const answer = button.nextElementSibling;
    const icon = button.querySelector('.faq-icon');
    const isOpen = !answer.classList.contains('hidden');
    
    if (isOpen) {
        answer.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    } else {
        answer.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    }
}

// Search/filter help topics
function filterHelpTopics(query) {
    query = query.toLowerCase().trim();
    
    // Filter FAQ items
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) || query === '' ? '' : 'none';
    });
    
    // Show/hide entire sections based on content
    const sections = document.querySelectorAll('.help-section');
    sections.forEach(section => {
        if (query === '') {
            section.closest('div[id]')?.style?.removeProperty('display');
            section.style.display = '';
            return;
        }
        const text = section.textContent.toLowerCase();
        if (text.includes(query)) {
            section.style.display = '';
        } else {
            section.style.display = 'none';
        }
    });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
