<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

checkAuth();

require_once __DIR__ . '/../controllers/NotificationController.php';
$controller = new NotificationController();
$result = $controller->getNotifications(50, 0, false);
$notifications = $result['notifications'] ?? [];
$unreadCount = $result['unread_count'] ?? 0;

$pageTitle = 'Notifications';
$currentPage = 'notifications';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Notifications']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-6 animate-fade-in">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Notifications</h1>
                    <p class="text-gray-600 dark:text-gray-400">View and manage your notifications</p>
                </div>
                <div class="flex gap-3">
                    <button id="mark-all-read" class="no-ripple inline-flex items-center justify-center px-4 py-2 !bg-white !text-red-600 border border-red-600 rounded-lg font-bold hover:shadow-lg transition-shadow duration-200 shadow-sm h-10 flex-shrink-0">
                        <i class="bi bi-check-all mr-2"></i>
                        Mark all as read
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
            <div id="notifications-container-page" class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($notifications)): ?>
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-bell-slash text-3xl mb-2"></i>
                        <p class="text-sm">No notifications</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 group" data-id="<?php echo $notification['id']; ?>">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center <?php echo NotificationController::getPriorityClass($notification['priority'] ?? 'normal'); ?>">
                                    <i class="bi <?php echo NotificationController::getNotificationIcon($notification['type'] ?? 'system'); ?> text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white <?php echo empty($notification['is_read']) ? '' : 'font-normal'; ?>">
                                            <?php echo e($notification['title'] ?? 'Notification'); ?>
                                        </h3>
                                        <?php if (empty($notification['is_read'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2"><?php echo e($notification['message'] ?? ''); ?></p>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-500">
                                        <span><i class="bi bi-clock mr-1"></i><?php echo e($notification['created_at'] ?? ''); ?></span>
                                        <span class="capitalize"><?php echo e($notification['type'] ?? 'system'); ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?php if (empty($notification['is_read'])): ?>
                                        <button class="mark-read-btn text-blue-600 dark:text-blue-400 hover:text-blue-700 p-1" title="Mark as read" data-id="<?php echo $notification['id']; ?>">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="delete-btn text-red-600 dark:text-red-400 hover:text-red-700 p-1" title="Delete" data-id="<?php echo $notification['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

<script>
    const apiBase = '<?php echo NOTIFICATIONS_URL; ?>/api/notifications.php';

    function postJson(url, body = {}) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        });
    }

    document.getElementById('mark-all-read')?.addEventListener('click', () => {
        postJson(`${apiBase}?action=read_all`)
            .then(() => location.reload());
    });

    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            postJson(`${apiBase}?action=read`, { notification_id: id })
                .then(() => location.reload());
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            fetch(`${apiBase}?action=delete&id=${id}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            }).then(() => location.reload());
        });
    });
</script>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
