<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

require_once __DIR__ . '/../../core/config/database.php';

// Get user preferences (create table if it doesn't exist)
$db = getDatabase();
$stmt = $db->prepare("
    SELECT * FROM user_preferences 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);

// Default preferences if none exist
if (!$preferences) {
    $preferences = [
        'theme' => 'light',
        'notifications_email' => 1,
        'notifications_browser' => 1,
        'language' => 'en',
        'timezone' => 'Asia/Manila',
        'items_per_page' => 20
    ];
}

$pageTitle = 'Settings';
$currentPage = 'settings';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Account Settings</h1>
                <p class="text-gray-600">Manage your account preferences and settings</p>
            </div>
            
            <!-- Settings Sections -->
            <div class="space-y-6">
                <!-- General Settings -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-sliders mr-2 text-blue-600"></i>
                        General Settings
                    </h2>
                    
                    <form id="generalSettingsForm" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                <select name="language" class="input-field">
                                    <option value="en" <?php echo ($preferences['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="fil" <?php echo ($preferences['language'] ?? '') == 'fil' ? 'selected' : ''; ?>>Filipino</option>
                                    <option value="es" <?php echo ($preferences['language'] ?? '') == 'es' ? 'selected' : ''; ?>>Spanish</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select name="timezone" class="input-field">
                                    <option value="Asia/Manila" <?php echo ($preferences['timezone'] ?? 'Asia/Manila') == 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (PHT)</option>
                                    <option value="UTC" <?php echo ($preferences['timezone'] ?? '') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    <option value="America/New_York" <?php echo ($preferences['timezone'] ?? '') == 'America/New_York' ? 'selected' : ''; ?>>America/New York (EST)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Items Per Page</label>
                                <select name="items_per_page" class="input-field">
                                    <option value="10" <?php echo ($preferences['items_per_page'] ?? 20) == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="20" <?php echo ($preferences['items_per_page'] ?? 20) == 20 ? 'selected' : ''; ?>>20</option>
                                    <option value="50" <?php echo ($preferences['items_per_page'] ?? 20) == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo ($preferences['items_per_page'] ?? 20) == 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                                <select name="theme" class="input-field">
                                    <option value="light" <?php echo ($preferences['theme'] ?? 'light') == 'light' ? 'selected' : ''; ?>>Light</option>
                                    <option value="dark" <?php echo ($preferences['theme'] ?? '') == 'dark' ? 'selected' : ''; ?>>Dark</option>
                                    <option value="auto" <?php echo ($preferences['theme'] ?? '') == 'auto' ? 'selected' : ''; ?>>Auto (System)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="btn-primary">
                                <i class="bi bi-check-circle mr-2"></i>Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Notification Settings -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-bell mr-2 text-blue-600"></i>
                        Notification Preferences
                    </h2>
                    
                    <form id="notificationSettingsForm" class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">Email Notifications</p>
                                    <p class="text-sm text-gray-600">Receive notifications via email</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notifications_email" class="sr-only peer" <?php echo ($preferences['notifications_email'] ?? 1) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">Browser Notifications</p>
                                    <p class="text-sm text-gray-600">Receive push notifications in browser</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notifications_browser" class="sr-only peer" <?php echo ($preferences['notifications_browser'] ?? 1) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">Document Updates</p>
                                    <p class="text-sm text-gray-600">Notify when documents are updated</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notifications_documents" class="sr-only peer" <?php echo ($preferences['notifications_documents'] ?? 1) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">System Alerts</p>
                                    <p class="text-sm text-gray-600">Receive important system notifications</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notifications_system" class="sr-only peer" <?php echo ($preferences['notifications_system'] ?? 1) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="btn-primary">
                                <i class="bi bi-check-circle mr-2"></i>Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Privacy & Security -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-shield-lock mr-2 text-blue-600"></i>
                        Privacy & Security
                    </h2>
                    
                    <div class="space-y-3">
                        <button onclick="window.location.href='<?php echo USERS_URL; ?>/views/profile.php'" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800">Change Password</p>
                                    <p class="text-sm text-gray-600">Update your account password</p>
                                </div>
                                <i class="bi bi-chevron-right text-gray-400"></i>
                            </div>
                        </button>
                        
                        <button class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800">Two-Factor Authentication</p>
                                    <p class="text-sm text-gray-600">Add an extra layer of security</p>
                                </div>
                                <span class="badge badge-warning text-xs">Coming Soon</span>
                            </div>
                        </button>
                        
                        <button class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800">Active Sessions</p>
                                    <p class="text-sm text-gray-600">Manage your active login sessions</p>
                                </div>
                                <i class="bi bi-chevron-right text-gray-400"></i>
                            </div>
                        </button>
                        
                        <button onclick="confirmDataExport()" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800">Export My Data</p>
                                    <p class="text-sm text-gray-600">Download all your account data</p>
                                </div>
                                <i class="bi bi-chevron-right text-gray-400"></i>
                            </div>
                        </button>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <div class="bg-white rounded-xl shadow-md border-2 border-red-200 p-6">
                    <h2 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                        <i class="bi bi-exclamation-triangle mr-2"></i>
                        Danger Zone
                    </h2>
                    
                    <div class="space-y-3">
                        <div class="p-4 bg-red-50 rounded-lg">
                            <p class="font-medium text-gray-800 mb-2">Deactivate Account</p>
                            <p class="text-sm text-gray-600 mb-3">Temporarily disable your account. You can reactivate it anytime.</p>
                            <button onclick="confirmDeactivate()" class="btn-warning text-sm">
                                <i class="bi bi-pause-circle mr-2"></i>Deactivate Account
                            </button>
                        </div>
                        
                        <div class="p-4 bg-red-50 rounded-lg">
                            <p class="font-medium text-gray-800 mb-2">Delete Account</p>
                            <p class="text-sm text-gray-600 mb-3">Permanently delete your account and all associated data. This action cannot be undone.</p>
                            <button onclick="confirmDelete()" class="btn-danger text-sm">
                                <i class="bi bi-trash mr-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// General Settings Form Handler
document.getElementById('generalSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch(App.apiUrl('users', 'update-settings.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert('General settings updated successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
});

// Notification Settings Form Handler
document.getElementById('notificationSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Convert checkboxes to 1/0
    const checkboxes = this.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        formData.set(checkbox.name, checkbox.checked ? '1' : '0');
    });
    
    try {
        const response = await fetch(App.apiUrl('users', 'update-settings.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Notification settings updated successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
});

function confirmDataExport() {
    if (confirm('Export all your account data? This may take a few moments.')) {
        window.location.href = App.apiUrl('users', 'export-data.php');
    }
}

function confirmDeactivate() {
    if (confirm('Are you sure you want to deactivate your account? You can reactivate it anytime by logging in.')) {
        // Handle account deactivation
        alert('Account deactivation feature coming soon.');
    }
}

function confirmDelete() {
    const confirmation = prompt('Type "DELETE" to confirm permanent account deletion:');
    if (confirmation === 'DELETE') {
        if (confirm('This action is irreversible. Are you absolutely sure?')) {
            // Handle account deletion
            alert('Account deletion feature coming soon. Please contact the administrator.');
        }
    }
}
</script>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
