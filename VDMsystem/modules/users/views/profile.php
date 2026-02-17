<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Fetch full user data
$db = getDatabase();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['flash_error'] = 'User not found.';
    redirectToDashboard();
}

// Get user voting activity stats
$votesStmt = $db->prepare("SELECT COUNT(*) as total_votes FROM votes WHERE councilor_id = ?");
$votesStmt->execute([$_SESSION['user_id']]);
$userVotes = $votesStmt->fetch(PDO::FETCH_ASSOC);

$sessionsStmt = $db->prepare("SELECT COUNT(DISTINCT session_id) as sessions_attended FROM votes WHERE councilor_id = ?");
$sessionsStmt->execute([$_SESSION['user_id']]);
$userSessions = $sessionsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent activity from audit logs
$recentActivityStmt = $db->prepare("
    SELECT event_type, action, details, created_at 
    FROM audit_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentActivityStmt->execute([$_SESSION['user_id']]);
$recentActivity = $recentActivityStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent votes
$recentVotesStmt = $db->prepare("
    SELECT v.vote, v.cast_at, d.title as document_title, s.title as session_title 
    FROM votes v 
    LEFT JOIN documents d ON v.document_id = d.id 
    LEFT JOIN voting_sessions s ON v.session_id = s.id 
    WHERE v.councilor_id = ? 
    ORDER BY v.cast_at DESC 
    LIMIT 5
");
$recentVotesStmt->execute([$_SESSION['user_id']]);
$recentVotes = $recentVotesStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate last active
$lastActive = 'Just now';
if (!empty($user['last_login'])) {
    $diff = time() - strtotime($user['last_login']);
    if ($diff < 3600) $lastActive = floor($diff / 60) . 'm ago';
    elseif ($diff < 86400) $lastActive = floor($diff / 3600) . 'h ago';
    else $lastActive = floor($diff / 86400) . 'd ago';
}

$pageTitle = 'My Profile';
$currentPage = 'profile';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'My Profile']
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
        <!-- Container -->
        <div class="max-w-6xl mx-auto">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4 animate-fade-in">
                    <div class="flex items-center">
                        <i class="bi bi-check-circle text-green-500 mr-2 text-lg"></i>
                        <span class="text-green-700 dark:text-green-300 font-medium"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4 animate-fade-in">
                    <div class="flex items-center">
                        <i class="bi bi-exclamation-circle text-red-500 mr-2 text-lg"></i>
                        <span class="text-red-700 dark:text-red-300 font-medium"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Profile Header Banner -->
            <div class="vdm-welcome-banner rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 mb-5 text-white animate-fade-in relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-56 h-56 bg-white opacity-5 rounded-full blur-3xl"></div>
                <div class="absolute right-0 bottom-0 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl"></div>
                <div class="relative flex flex-col md:flex-row items-center gap-5 md:gap-6">
                    <!-- Profile Picture -->
                    <div class="relative group flex-shrink-0">
                        <div class="w-24 h-24 md:w-28 md:h-28 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white/30 flex items-center justify-center overflow-hidden shadow-2xl transition-all duration-300 group-hover:border-white/50">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo BASE_URL; ?>/storage/profiles/<?php echo e($user['profile_picture']); ?>" 
                                     alt="Profile" class="w-full h-full object-cover" id="profile-preview">
                            <?php else: ?>
                                <i class="bi bi-person-fill text-5xl text-white/80" id="profile-icon"></i>
                                <img src="" alt="Profile" class="w-full h-full object-cover hidden" id="profile-preview">
                            <?php endif; ?>
                        </div>
                        <label for="profile-picture-input" class="absolute bottom-1 right-1 bg-white text-red-600 rounded-full w-8 h-8 flex items-center justify-center cursor-pointer shadow-lg hover:bg-red-50 transition-all duration-200 hover:scale-110 border-2 border-white">
                            <i class="bi bi-camera-fill text-xs"></i>
                        </label>
                        <input type="file" id="profile-picture-input" accept="image/*" class="hidden" onchange="previewProfilePicture(this)">
                    </div>
                    
                    <!-- User Info -->
                    <div class="text-center md:text-left flex-1">
                        <h1 class="text-2xl md:text-3xl font-black tracking-tight"><?php echo e($user['full_name']); ?></h1>
                        <p class="text-red-100 text-sm mt-0.5 opacity-90"><?php echo e($user['email']); ?></p>
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mt-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500/40 backdrop-blur-sm border border-red-300/30">
                                <i class="bi bi-shield-check mr-1.5"></i>
                                <?php echo ucfirst(e($user['role'])); ?>
                            </span>
                            <?php if (!empty($user['department'])): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/30 backdrop-blur-sm border border-blue-300/20">
                                <i class="bi bi-building mr-1.5"></i>
                                <?php echo e($user['department']); ?>
                            </span>
                            <?php endif; ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo $user['is_active'] ? 'bg-green-500/30 border-green-300/20' : 'bg-red-500/30 border-red-300/20'; ?> backdrop-blur-sm border">
                                <i class="bi bi-circle-fill mr-1.5 text-[7px]"></i>
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Edit Profile Button -->
                    <div class="flex-shrink-0">
                        <button onclick="toggleEditMode()" id="editProfileBtn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/30 rounded-xl text-sm font-bold transition-all hover:shadow-lg">
                            <i class="bi bi-pencil-square"></i>
                            <span>Edit Profile</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5 animate-fade-in-up">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-hand-thumbs-up-fill text-red-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Votes</p>
                            <p class="text-xl font-black text-gray-800 dark:text-white"><?php echo number_format($userVotes['total_votes'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calendar-check-fill text-blue-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Sessions</p>
                            <p class="text-xl font-black text-gray-800 dark:text-white"><?php echo number_format($userSessions['sessions_attended'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-50 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-calendar3 text-green-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Member Since</p>
                            <p class="text-xl font-black text-gray-800 dark:text-white"><?php echo formatDate($user['created_at'], 'M Y'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clock-fill text-purple-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Last Active</p>
                            <p class="text-xl font-black text-gray-800 dark:text-white"><?php echo $lastActive; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 animate-fade-in-up">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Personal Information Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                            <i class="bi bi-person-vcard text-red-500 text-lg"></i>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Personal Information</h2>
                        </div>
                        <div class="p-6">
                            <!-- View Mode -->
                            <div id="viewMode">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Full Name</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['full_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Username</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['username'] ?? '—'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Email Address</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['email']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Phone Number</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['phone'] ?? '—'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Position</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['position'] ?? '—'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Department</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['department'] ?? '—'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Edit Mode (hidden by default) -->
                            <div id="editMode" class="hidden">
                                <form id="profileForm" onsubmit="saveProfile(event)">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Full Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="full_name" value="<?php echo e($user['full_name']); ?>" required
                                                   class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Username</label>
                                            <input type="text" value="<?php echo e($user['username'] ?? ''); ?>" disabled
                                                   class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 rounded-lg bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Email Address <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" value="<?php echo e($user['email']); ?>" required
                                                   class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Phone Number</label>
                                            <input type="text" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>"
                                                   class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm"
                                                   placeholder="+63 XXX XXX XXXX">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Position</label>
                                            <input type="text" name="position" value="<?php echo e($user['position'] ?? ''); ?>"
                                                   class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm"
                                                   placeholder="e.g. Councilor">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Department</label>
                                            <input type="text" name="department" value="<?php echo e($user['department'] ?? ''); ?>"
                                                   class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm"
                                                   placeholder="e.g. Legislative Affairs">
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                                        <button type="button" onclick="toggleEditMode()" class="px-5 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 rounded-lg font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                                            Cancel
                                        </button>
                                        <button type="submit" id="saveProfileBtn" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                                            <i class="bi bi-check-lg"></i>
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-clock-history text-orange-500 text-lg"></i>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">Recent Activity</h2>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/modules/audit/views/index.php" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors">View All</a>
                        </div>
                        <div class="p-6">
                            <?php if (empty($recentActivity) && empty($recentVotes)): ?>
                                <div class="text-center py-6">
                                    <i class="bi bi-clock text-3xl text-gray-200 dark:text-gray-700 block mb-2"></i>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">No recent activity</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-1">
                                    <?php 
                                    // Combine and sort activities
                                    $allActivity = [];
                                    foreach ($recentActivity as $act) {
                                        $allActivity[] = [
                                            'type' => 'audit',
                                            'label' => ucwords(str_replace('_', ' ', $act['event_type'])),
                                            'detail' => $act['action'] ?? '',
                                            'time' => $act['created_at']
                                        ];
                                    }
                                    foreach ($recentVotes as $rv) {
                                        $allActivity[] = [
                                            'type' => 'vote',
                                            'label' => 'Voted ' . ucfirst($rv['vote']),
                                            'detail' => $rv['document_title'] ?? 'Document',
                                            'time' => $rv['cast_at'],
                                            'vote' => $rv['vote']
                                        ];
                                    }
                                    // Sort by time descending
                                    usort($allActivity, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
                                    $allActivity = array_slice($allActivity, 0, 6);
                                    ?>
                                    <?php foreach ($allActivity as $act): ?>
                                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all">
                                            <?php if (($act['type'] ?? '') === 'vote'): ?>
                                                <?php
                                                $vColor = match(strtolower($act['vote'] ?? '')) {
                                                    'approve' => 'text-green-500 bg-green-50 dark:bg-green-900/20',
                                                    'reject' => 'text-red-500 bg-red-50 dark:bg-red-900/20',
                                                    default => 'text-gray-400 bg-gray-50 dark:bg-gray-800'
                                                };
                                                ?>
                                                <div class="w-8 h-8 rounded-full <?php echo $vColor; ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    <i class="bi bi-hand-thumbs-up-fill text-xs"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    <i class="bi bi-activity text-gray-400 text-xs"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    <?php echo e($act['label']); ?>
                                                    <?php if (!empty($act['detail'])): ?>
                                                        <span class="text-gray-400 dark:text-gray-500">: <?php echo e($act['detail']); ?></span>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                                    <?php echo formatDateTime($act['time'], 'M d, Y h:i A'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-5">
                    <!-- Account Security Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                            <i class="bi bi-shield-lock text-green-500 text-lg"></i>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Account Security</h2>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            <a href="<?php echo BASE_URL; ?>/modules/users/views/settings.php" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Change Password</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Update your password</p>
                                </div>
                                <i class="bi bi-chevron-right text-gray-300 dark:text-gray-600 group-hover:text-red-500 transition-colors"></i>
                            </a>
                            <div class="flex items-center justify-between px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white">Two-Factor Auth</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Not enabled</p>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500">Soon</span>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/modules/audit/views/index.php" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Login History</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">View recent logins</p>
                                </div>
                                <i class="bi bi-chevron-right text-gray-300 dark:text-gray-600 group-hover:text-red-500 transition-colors"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Account Details Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                            <i class="bi bi-info-circle text-blue-500 text-lg"></i>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Account Details</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">User ID</span>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">#<?php echo $user['id']; ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">Role</span>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300"><?php echo ucfirst(e($user['role'])); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">Status</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">Joined</span>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300"><?php echo formatDate($user['created_at']); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">Last Updated</span>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300"><?php echo formatDate($user['updated_at'] ?? $user['created_at']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                            <i class="bi bi-lightning text-yellow-500 text-lg"></i>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Quick Links</h2>
                        </div>
                        <div class="p-4 space-y-1">
                            <a href="<?php echo BASE_URL; ?>/modules/users/views/settings.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group">
                                <i class="bi bi-gear text-gray-400 group-hover:text-red-500 transition-colors"></i>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-white transition-colors">Account Settings</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/modules/voting/views/cast-vote.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group">
                                <i class="bi bi-check2-square text-gray-400 group-hover:text-red-500 transition-colors"></i>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-white transition-colors">Cast Vote</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/modules/help/views/index.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all group">
                                <i class="bi bi-question-circle text-gray-400 group-hover:text-red-500 transition-colors"></i>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-white transition-colors">Help Center</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
// Toggle between view and edit mode
function toggleEditMode() {
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const editBtn = document.getElementById('editProfileBtn');
    
    const isEditing = !editMode.classList.contains('hidden');
    
    if (isEditing) {
        editMode.classList.add('hidden');
        viewMode.classList.remove('hidden');
        editBtn.innerHTML = '<i class="bi bi-pencil-square"></i><span>Edit Profile</span>';
    } else {
        viewMode.classList.add('hidden');
        editMode.classList.remove('hidden');
        editBtn.innerHTML = '<i class="bi bi-x-lg"></i><span>Cancel</span>';
    }
}

// Preview profile picture before upload
function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('profile-preview');
            const icon = document.getElementById('profile-icon');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) icon.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
        
        // Auto-save profile picture
        uploadProfilePicture(input.files[0]);
    }
}

function uploadProfilePicture(file) {
    const formData = new FormData();
    formData.append('profile_picture', file);
    
    // Include current profile data
    const form = document.getElementById('profileForm');
    if (form) {
        const inputs = form.querySelectorAll('input[name]:not([disabled])');
        inputs.forEach(input => {
            formData.append(input.name, input.value);
        });
    } else {
        // If in view mode, use data attributes
        formData.append('full_name', '<?php echo e($user['full_name']); ?>');
        formData.append('email', '<?php echo e($user['email']); ?>');
    }

    fetch(App.apiUrl('users', 'update-profile.php'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Profile picture updated!', 'success');
        } else {
            showToast('Error: ' + (data.error || 'Upload failed'), 'error');
        }
    })
    .catch(error => {
        showToast('Network error: ' + error.message, 'error');
    });
}

function saveProfile(event) {
    event.preventDefault();
    
    const btn = document.getElementById('saveProfileBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Saving...';
    
    const formData = new FormData(event.target);
    
    // Include profile picture if selected
    const pictureInput = document.getElementById('profile-picture-input');
    if (pictureInput.files.length > 0) {
        formData.append('profile_picture', pictureInput.files[0]);
    }

    fetch(App.apiUrl('users', 'update-profile.php'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Profile updated successfully!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error: ' + (data.error || 'Update failed'), 'error');
        }
    })
    .catch(error => {
        showToast('Network error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Changes';
    });
}
</script>
