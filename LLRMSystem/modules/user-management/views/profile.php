<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

require_once __DIR__ . '/../../core/config/database.php';

// Get user data
$db = getDatabase();
$stmt = $db->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM legislative_documents WHERE uploaded_by = u.id) as document_count,
           (SELECT COUNT(*) FROM activity_logs WHERE user_id = u.id) as activity_count,
           (SELECT MAX(created_at) FROM activity_logs WHERE user_id = u.id) as last_activity
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    require_once __DIR__ . '/../../core/config/config.php';
    redirectToLogin();
}

// Get recent activity
$activityStmt = $db->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$activityStmt->execute([$_SESSION['user_id']]);
$recentActivities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'My Profile';
$currentPage = 'profile';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <!-- Avatar -->
                    <div class="relative">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img id="profile-avatar" src="<?php echo BASE_URL; ?>/storage/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile Picture" 
                                 class="w-32 h-32 bg-white rounded-full object-cover shadow-lg border-4 border-white">
                        <?php else: ?>
                            <div id="profile-avatar" class="w-32 h-32 bg-white rounded-full flex items-center justify-center text-red-600 text-5xl font-bold shadow-lg">
                                <?php echo strtoupper(substr($user['full_name'] ?? $user['email'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <button onclick="document.getElementById('avatar-upload').click()" 
                                class="absolute bottom-0 right-0 bg-red-500 hover:bg-red-600 rounded-full p-3 shadow-lg transition-all transform hover:scale-110 active:scale-95"
                                title="Upload profile picture">
                            <i class="bi bi-camera text-white"></i>
                        </button>
                        <input type="file" id="avatar-upload" class="hidden" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="uploadProfilePicture(this)">
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex-1 text-center md:text-left">
                        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></h1>
                        <p class="text-red-100 text-lg mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                            <span class="px-3 py-1 bg-red-500 rounded-full text-sm font-medium">
                                <i class="bi bi-person-badge mr-1"></i>
                                <?php echo ucfirst($user['role'] ?? 'User'); ?>
                            </span>
                            <span class="px-3 py-1 bg-red-500 rounded-full text-sm font-medium">
                                <i class="bi bi-building mr-1"></i>
                                <?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?>
                            </span>
                            <?php if (($user['status'] ?? '') === 'active'): ?>
                            <span class="px-3 py-1 bg-green-500 rounded-full text-sm font-medium">
                                <i class="bi bi-check-circle mr-1"></i>Active
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex gap-3">
                        <button onclick="openEditModal()" class="btn-outline border-white text-white hover:bg-white hover:text-red-600">
                            <i class="bi bi-pencil mr-2"></i>Edit Profile
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-file-earmark-text-fill text-red-600 text-4xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm text-gray-600">Documents</div>
                            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($user['document_count'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-activity text-green-600 text-4xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm text-gray-600">Activities</div>
                            <div class="text-2xl font-bold text-gray-900"><?php echo number_format($user['activity_count'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check text-purple-600 text-4xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm text-gray-600">Member Since</div>
                            <div class="text-lg font-bold text-gray-900">
                                <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history text-amber-600 text-4xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm text-gray-600">Last Active</div>
                            <div class="text-sm font-bold text-gray-900">
                                <?php 
                                if ($user['last_activity']) {
                                    $diff = time() - strtotime($user['last_activity']);
                                    if ($diff < 60) echo 'Just now';
                                    elseif ($diff < 3600) echo floor($diff/60) . 'm ago';
                                    elseif ($diff < 86400) echo floor($diff/3600) . 'h ago';
                                    else echo date('M d', strtotime($user['last_activity']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-person-circle mr-2 text-red-600"></i>
                            Personal Information
                        </h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Department</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Position</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($user['position'] ?? 'Not set'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-800 flex items-center">
                                <i class="bi bi-clock-history mr-2 text-red-600"></i>
                                Recent Activity
                            </h3>
                            <a href="<?php echo AUDIT_URL; ?>/views/index.php" class="text-sm text-red-600 hover:text-red-700">
                                View All
                            </a>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($recentActivities)): ?>
                                <p class="text-gray-500 text-center py-4">No recent activity</p>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                        <div class="flex-shrink-0 mt-1">
                                            <?php
                                            $iconClass = match($activity['action']) {
                                                'create' => 'bi-plus-circle text-green-600',
                                                'update' => 'bi-pencil-square text-red-600',
                                                'delete' => 'bi-trash text-red-600',
                                                'login' => 'bi-box-arrow-in-right text-indigo-600',
                                                'view' => 'bi-eye text-gray-600',
                                                default => 'bi-circle text-gray-400'
                                            };
                                            ?>
                                            <i class="bi <?php echo $iconClass; ?>"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-800"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <?php echo date('M d, Y h:i A', strtotime($activity['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Account Security -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-shield-check mr-2 text-red-600"></i>
                            Account Security
                        </h2>
                        <div class="space-y-3">
                            <button onclick="openPasswordModal()" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Change Password</p>
                                        <p class="text-xs text-gray-500">Update your password</p>
                                    </div>
                                    <i class="bi bi-chevron-right text-gray-400"></i>
                                </div>
                            </button>
                            
                            <button class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Two-Factor Auth</p>
                                        <p class="text-xs text-gray-500">Not enabled</p>
                                    </div>
                                    <i class="bi bi-chevron-right text-gray-400"></i>
                                </div>
                            </button>
                            
                            <button class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Login History</p>
                                        <p class="text-xs text-gray-500">View recent logins</p>
                                    </div>
                                    <i class="bi bi-chevron-right text-gray-400"></i>
                                </div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-link-45deg mr-2 text-red-600"></i>
                            Quick Links
                        </h2>
                        <div class="space-y-2">
                            <a href="<?php echo USERS_URL; ?>/views/settings.php" class="block px-4 py-2 hover:bg-red-50 rounded-lg transition text-sm">
                                <i class="bi bi-gear mr-2 text-gray-600"></i>Account Settings
                            </a>
                            <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="block px-4 py-2 hover:bg-red-50 rounded-lg transition text-sm">
                                <i class="bi bi-file-earmark-text mr-2 text-gray-600"></i>My Documents
                            </a>
                            <a href="<?php echo HELP_URL; ?>/views/index.php" class="block px-4 py-2 hover:bg-red-50 rounded-lg transition text-sm">
                                <i class="bi bi-question-circle mr-2 text-gray-600"></i>Help Center
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Edit Profile Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Edit Profile</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="editProfileForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                    <input type="text" name="position" value="<?php echo htmlspecialchars($user['position'] ?? ''); ?>" class="input-field">
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div id="passwordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Change Password</h3>
            <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="changePasswordForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <input type="password" name="current_password" class="input-field" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input type="password" name="new_password" class="input-field" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" class="input-field" required>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closePasswordModal()" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-shield-check mr-2"></i>Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function openPasswordModal() {
    document.getElementById('passwordModal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

// Edit Profile Form Handler
document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch(App.apiUrl('users', 'update-profile.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Profile updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
});

// Change Password Form Handler
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        alert('New passwords do not match!');
        return;
    }
    
    try {
        const response = await fetch(App.apiUrl('users', 'change-password.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert('Password changed successfully!');
            closePasswordModal();
            this.reset();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
});

// Profile Picture Upload Handler
async function uploadProfilePicture(input) {
    if (!input.files || !input.files[0]) {
        return;
    }
    
    const file = input.files[0];
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File is too large. Maximum size is 5MB.');
        input.value = '';
        return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Invalid file type. Please upload a JPG, PNG, GIF, or WEBP image.');
        input.value = '';
        return;
    }
    
    // Show loading state
    const avatar = document.getElementById('profile-avatar');
    const originalContent = avatar.outerHTML;
    avatar.outerHTML = '<div id="profile-avatar" class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center shadow-lg"><div class="spinner"></div></div>';
    
    const formData = new FormData();
    formData.append('profile_picture', file);
    
    try {
        const response = await fetch(App.apiUrl('users', 'upload-profile-picture.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            // Update avatar with new image
            const newAvatar = document.getElementById('profile-avatar');
            newAvatar.outerHTML = `<img id="profile-avatar" src="${result.image_url}?t=${Date.now()}" alt="Profile Picture" class="w-32 h-32 bg-white rounded-full object-cover shadow-lg border-4 border-white">`;
            
            // Show success message
            showNotification('Profile picture updated successfully!', 'success');
        } else {
            // Restore original avatar
            document.getElementById('profile-avatar').outerHTML = originalContent;
            alert('Error: ' + result.message);
        }
    } catch (error) {
        // Restore original avatar
        document.getElementById('profile-avatar').outerHTML = originalContent;
        alert('An error occurred while uploading. Please try again.');
    } finally {
        input.value = ''; // Reset file input
    }
}

// Show notification helper
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg animate-slide-in-right ${
        type === 'success' ? 'bg-green-600' : 
        type === 'error' ? 'bg-red-600' : 
        'bg-blue-600'
    } text-white`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="bi bi-${
                type === 'success' ? 'check-circle' : 
                type === 'error' ? 'x-circle' : 
                'info-circle'
            } text-xl"></i>
            <p class="font-medium">${message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('animate-fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
