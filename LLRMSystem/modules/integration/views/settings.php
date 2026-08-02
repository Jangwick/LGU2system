<?php
/**
 * Integration Webhook Settings
 *
 * Admin UI for configuring outbound webhook URLs and API keys.
 */

session_start();

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../services/IntegrationWebhookService.php';

$userRole = $_SESSION['user_role'] ?? '';
$adminRoles = ['admin', 'super_admin', 'superadmin', 'administrator'];

if (!checkAuth() || !in_array(strtolower(trim($userRole)), $adminRoles, true)) {
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integration Webhook Settings</title>
    <link href="/public/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1>Integration Webhook Settings</h1>
        <p class="text-muted">Configure callback URLs and API keys for ORTS, CMS, PHMS, and PCMS.</p>

        <form id="settings-form">
            <table class="table table-bordered bg-white">
                <thead>
                    <tr>
                        <th>Source System</th>
                        <th>Webhook URL</th>
                        <th>API Key</th>
                        <th>Enabled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($systems as $system): ?>
                        <?php $setting = $settings[$system] ?? []; ?>
                        <tr>
                            <td class="align-middle fw-bold text-uppercase"><?php echo htmlspecialchars($system); ?></td>
                            <td>
                                <input type="url" class="form-control" name="webhook_url[<?php echo $system; ?>]" value="<?php echo htmlspecialchars($setting['webhook_url'] ?? ''); ?>" placeholder="https://.../webhook">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="api_key[<?php echo $system; ?>]" value="<?php echo htmlspecialchars($setting['api_key'] ?? ''); ?>" placeholder="secret">
                            </td>
                            <td class="text-center align-middle">
                                <input type="checkbox" class="form-check-input" name="enabled[<?php echo $system; ?>]" value="1" <?php echo !empty($setting['enabled']) ? 'checked' : ''; ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Save Settings</button>
            <a href="/modules/dashboard/views/index.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>

        <div id="result" class="mt-3"></div>
    </div>

    <script>
    document.getElementById('settings-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const result = document.getElementById('result');
        const systems = ['orts', 'cms', 'phms', 'pcms'];

        result.innerHTML = '<div class="alert alert-info">Saving...</div>';

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
                const res = await fetch('/modules/integration/api/settings.php', {
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
            result.innerHTML = '<div class="alert alert-danger">' + errors.join('<br>') + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-success">All settings saved.</div>';
        }
    });
    </script>
</body>
</html>
