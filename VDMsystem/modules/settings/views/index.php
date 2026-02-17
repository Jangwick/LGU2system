<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/SettingsController.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}
if (!isAdmin()) {
    $_SESSION['flash_error'] = 'Access denied. Admin privileges required.';
    redirectToDashboard();
}

$controller = new SettingsController();

// Handle form submission
$flashMessage = '';
$flashType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->updateSettings($_POST, $_SESSION['user_id']);
    $flashMessage = $result['message'];
    $flashType = $result['success'] ? 'success' : 'error';
}

$data = $controller->index();

$pageTitle = 'System Settings';
$currentPage = 'settings';
$breadcrumbs = [
    ['label' => 'Administration', 'url' => '#'],
    ['label' => 'Settings']
];

include_once __DIR__ . '/../../core/layouts/header.php';

// Friendly group labels and icons
$groupLabels = [
    'general'       => ['label' => 'General Settings', 'icon' => 'bi-gear-wide-connected', 'desc' => 'Basic system configuration'],
    'security'      => ['label' => 'Security Settings', 'icon' => 'bi-shield-lock', 'desc' => 'Password, session, and access control'],
    'voting'        => ['label' => 'Voting Settings', 'icon' => 'bi-hand-thumbs-up', 'desc' => 'Voting session behavior'],
    'notifications' => ['label' => 'Notification Settings', 'icon' => 'bi-bell', 'desc' => 'Email and in-app notifications'],
    'audit'         => ['label' => 'Audit Settings', 'icon' => 'bi-journal-text', 'desc' => 'Activity logging and integrity'],
];
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
            
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">System Settings</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Configure system-wide preferences and security options.</p>
                </div>
                <div class="shrink-0 hidden md:flex">
                    <div class="bg-white/20 backdrop-blur rounded-xl p-3 text-center border border-white/10">
                        <i class="bi bi-gear-wide-connected text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-xl shadow-md <?php echo $flashType === 'success' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800'; ?> animate-fade-in">
                <i class="bi <?php echo $flashType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'; ?> mr-2"></i>
                <?php echo e($flashMessage); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Settings Navigation (Desktop) -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 sticky top-6 animate-fade-in-up">
                    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Sections</h3>
                    <nav class="space-y-1">
                        <?php foreach ($groupLabels as $groupKey => $groupInfo): ?>
                            <?php if (isset($data['grouped'][$groupKey])): ?>
                                <a href="#section-<?php echo $groupKey; ?>" 
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 transition-colors font-medium group">
                                    <i class="bi <?php echo $groupInfo['icon']; ?> text-gray-400 group-hover:text-red-600 transition-colors"></i>
                                    <?php echo $groupInfo['label']; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <hr class="my-2 border-gray-100 dark:border-gray-800">
                        <a href="#section-sysinfo" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 transition-colors font-medium group">
                            <i class="bi bi-info-circle text-gray-400 group-hover:text-red-600 transition-colors"></i>
                            System Information
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="lg:col-span-3">
                <form method="POST" id="settingsForm">
                    <?php foreach ($data['grouped'] as $group => $settings): ?>
                        <?php $gInfo = $groupLabels[$group] ?? ['label' => ucfirst($group), 'icon' => 'bi-gear', 'desc' => '']; ?>
                        
                        <div id="section-<?php echo $group; ?>" class="bg-white dark:bg-gray-900 rounded-xl shadow-md mb-6 overflow-hidden animate-fade-in-up">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/80">
                                <h2 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="bi <?php echo $gInfo['icon']; ?> text-red-600"></i>
                                    <?php echo $gInfo['label']; ?>
                                </h2>
                                <?php if ($gInfo['desc']): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo $gInfo['desc']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-6 space-y-5">
                                <?php foreach ($settings as $setting): ?>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                                        <div class="sm:w-1/2">
                                            <label for="setting-<?php echo $setting['setting_key']; ?>" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                                            </label>
                                            <?php if ($setting['description']): ?>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?php echo e($setting['description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="sm:w-1/2">
                                            <?php if ($setting['setting_type'] === 'boolean'): ?>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="hidden" name="<?php echo $setting['setting_key']; ?>" value="0">
                                                    <input type="checkbox" 
                                                           id="setting-<?php echo $setting['setting_key']; ?>" 
                                                           name="<?php echo $setting['setting_key']; ?>" 
                                                           value="1"
                                                           <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>
                                                           class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                                    <span class="ml-3 text-sm font-medium text-gray-500 dark:text-gray-400"><?php echo $setting['setting_value'] == '1' ? 'Enabled' : 'Disabled'; ?></span>
                                                </label>
                                            <?php elseif ($setting['setting_type'] === 'number'): ?>
                                                <input type="number" 
                                                       id="setting-<?php echo $setting['setting_key']; ?>" 
                                                       name="<?php echo $setting['setting_key']; ?>" 
                                                       value="<?php echo e($setting['setting_value']); ?>" 
                                                       class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                                            <?php else: ?>
                                                <input type="text" 
                                                       id="setting-<?php echo $setting['setting_key']; ?>" 
                                                       name="<?php echo $setting['setting_key']; ?>" 
                                                       value="<?php echo e($setting['setting_value']); ?>" 
                                                       class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Save Button -->
                    <div class="flex justify-end mb-6">
                        <button type="submit" class="bg-red-700 dark:bg-red-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-red-800 transition-all shadow-lg shadow-red-600/30 flex items-center gap-2 transform hover:-translate-y-0.5">
                            <i class="bi bi-check-lg text-lg"></i>
                            Save All Settings
                        </button>
                    </div>
                </form>

                <!-- System Information -->
                <div id="section-sysinfo" class="bg-white dark:bg-gray-900 rounded-xl shadow-md overflow-hidden animate-fade-in-up mb-6">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/80">
                        <h2 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-info-circle text-red-600"></i>
                            System Information
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Server and environment details (read-only)</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $infoLabels = [
                                'php_version' => ['PHP Version', 'bi-filetype-php'],
                                'db_version' => ['Database Version', 'bi-database'],
                                'server_software' => ['Server Software', 'bi-hdd-rack'],
                                'os' => ['Operating System', 'bi-pc-display'],
                                'memory_limit' => ['Memory Limit', 'bi-memory'],
                                'max_upload' => ['Max Upload Size', 'bi-cloud-upload'],
                                'max_post' => ['Max POST Size', 'bi-send'],
                                'max_execution_time' => ['Max Execution Time', 'bi-clock'],
                                'timezone' => ['Server Timezone', 'bi-globe-americas'],
                                'disk_free' => ['Disk Free Space', 'bi-hdd'],
                                'disk_total' => ['Disk Total Space', 'bi-hdd-fill'],
                                'document_root' => ['Document Root', 'bi-folder'],
                            ];
                            foreach ($data['systemInfo'] as $key => $value):
                                $info = $infoLabels[$key] ?? [ucwords(str_replace('_', ' ', $key)), 'bi-info'];
                            ?>
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
                                    <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg flex items-center justify-center shrink-0">
                                        <i class="bi <?php echo $info[1]; ?> text-red-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider"><?php echo $info[0]; ?></p>
                                        <p class="text-sm text-gray-800 dark:text-white font-medium truncate" title="<?php echo e($value); ?>"><?php echo e($value); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
// Toggle label text on boolean switches
document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', function() {
        const label = this.closest('label').querySelector('span');
        if (label) {
            label.textContent = this.checked ? 'Enabled' : 'Disabled';
        }
    });
});

// Smooth scroll for nav links
document.querySelectorAll('a[href^="#section-"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
