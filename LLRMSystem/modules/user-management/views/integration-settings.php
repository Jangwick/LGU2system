<?php
session_start();

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../integration/services/IntegrationWebhookService.php';

$userRole = strtolower(trim($_SESSION['user_role'] ?? ''));
$adminRoles = ['admin', 'super_admin', 'superadmin', 'administrator'];

if (empty($_SESSION['user_id']) || !in_array($userRole, $adminRoles, true)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Admin access required.';
    exit;
}

$service = new IntegrationWebhookService();
$systems = ['orts', 'cms', 'phms', 'pcms'];
$settings = [];

$stmt = getDatabase()->query("SELECT * FROM integration_settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['source_system']] = $row;
}

$pageTitle = 'Integration Webhook Settings';
$currentPage = 'integration-settings';
require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="animate-slide-in-left">
                <h1 class="text-2xl font-bold mb-2">Integration Webhook Settings</h1>
                <p class="text-red-100">Configure callback URLs and API keys for ORTS, CMS, PHMS, and PCMS.</p>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in-up">
            <?php if (empty($settings)): ?>
                <div class="mb-4 p-4 text-sm text-blue-700 bg-blue-100 rounded-lg">No saved webhook settings yet. Fill in the form below and click Save to configure each system.</div>
            <?php endif; ?>
            <form id="settings-form">
                <div class="overflow-x-auto rounded-lg shadow mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source System</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Webhook URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Key</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enabled</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($systems as $system): ?>
                                <?php $setting = $settings[$system] ?? []; ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 uppercase"><?php echo htmlspecialchars($system); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <input type="url" class="input-field" name="webhook_url[<?php echo $system; ?>]" value="<?php echo htmlspecialchars($setting['webhook_url'] ?? ''); ?>" placeholder="https://.../webhook">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <input type="text" class="input-field" name="api_key[<?php echo $system; ?>]" value="<?php echo htmlspecialchars($setting['api_key'] ?? ''); ?>" placeholder="secret">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle">
                                        <input type="checkbox" class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500" name="enabled[<?php echo $system; ?>]" value="1" <?php echo !empty($setting['enabled']) ? 'checked' : ''; ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Save Settings</button>
                    <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="btn-secondary inline-flex items-center">Back to Dashboard</a>
                </div>
            </form>

            <div id="result" class="mt-4"></div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<script>
document.getElementById('settings-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.target;
    const result = document.getElementById('result');
    const systems = ['orts', 'cms', 'phms', 'pcms'];

    result.innerHTML = '<div class="p-4 mb-4 text-sm text-blue-700 bg-blue-100 rounded-lg">Saving...</div>';

    let errors = [];
    let ok = 0;

    for (const system of systems) {
        const payload = {
            source_system: system,
            webhook_url: form.querySelector(`[name="webhook_url[${system}]"]`).value,
            api_key: form.querySelector(`[name="api_key[${system}]"]`).value,
            enabled: form.querySelector(`[name="enabled[${system}]"]`).checked ? 1 : 0
        };

        try {
            const res = await fetch('<?php echo INTEGRATION_URL; ?>/api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!data.success) {
                errors.push(`${system}: ${data.error}`);
            } else {
                ok++;
            }
        } catch (err) {
            errors.push(`${system}: ${err.message}`);
        }
    }

    if (errors.length) {
        result.innerHTML = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">' + errors.join('<br>') + '</div>';
    } else {
        result.innerHTML = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">All settings saved.</div>';
    }
});
</script>
