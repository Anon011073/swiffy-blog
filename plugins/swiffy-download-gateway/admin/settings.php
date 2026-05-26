<?php
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/functions.php';

require_login('plugins');

if (!defined('SFX_DL_GATEWAY_DIR')) {
    define('SFX_DL_GATEWAY_DIR', dirname(__DIR__));
}

$config = load_config();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sfx_save_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF Failed');

    $new_config = [
        'sfx_dl_timer' => (int)$_POST['sfx_dl_timer'],
        'sfx_dl_ads' => isset($_POST['sfx_dl_ads']),
        'sfx_dl_msg' => sanitize($_POST['sfx_dl_msg']),
        'sfx_dl_unlock_mode' => sanitize($_POST['sfx_dl_unlock_mode'])
    ];

    if (update_config($new_config)) {
        $success = 'Settings saved successfully!';
        $config = load_config();
    }
}

$timer = $config['sfx_dl_timer'] ?? 5;
$ads = $config['sfx_dl_ads'] ?? true;
$msg = $config['sfx_dl_msg'] ?? 'Preparing secure link...';
$unlock_mode = $config['sfx_dl_unlock_mode'] ?? 'none';

// Load logs
$log_file = SFX_DL_GATEWAY_DIR . '/logs/downloads.json';
$logs = file_exists($log_file) ? array_reverse(json_decode(file_get_contents($log_file), true)) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Gateway Settings - Swiffy Blog</title>
    <link rel="stylesheet" href="../../../admin/style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 70px; padding: 2rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
<?php include "../../../admin/sidebar.php"; ?>
<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>🛡️ Download Gateway Settings</h1>
        <a href="../../../admin/plugins.php" class="btn" style="background:#6c757d; color:#fff; padding:8px 16px; text-decoration:none; border-radius:4px;">&larr; Back to Plugins</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success" style="background:#def7ec; color:#03543f; padding:15px; border-radius:4px; margin-bottom:15px;"><?php echo $success; ?></div><?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <div class="card">
                <h3>⚙️ Gateway Configuration</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                    <div class="form-group">
                        <label>Countdown Timer (seconds)</label>
                        <input type="number" name="sfx_dl_timer" value="<?php echo $timer; ?>" min="0" max="60">
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="sfx_dl_ads" <?php echo $ads ? 'checked' : ''; ?> style="margin-right: 10px;"> Enable Ad Slots on Gateway
                        </label>
                    </div>

                    <div class="form-group">
                        <label>Unlock Requirement</label>
                        <select name="sfx_dl_unlock_mode">
                            <option value="none" <?php echo ($unlock_mode === 'none') ? 'selected' : ''; ?>>None (Timer Only)</option>
                            <option value="like" <?php echo ($unlock_mode === 'like') ? 'selected' : ''; ?>>Like Post to Unlock</option>
                            <option value="comment" <?php echo ($unlock_mode === 'comment') ? 'selected' : ''; ?>>Comment to Unlock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Default Status Message</label>
                        <input type="text" name="sfx_dl_msg" value="<?php echo htmlspecialchars($msg); ?>">
                    </div>

                    <button type="submit" name="sfx_save_settings" class="btn btn-primary" style="width: 100%; margin-top: 10px; background:#8b5cf6; color:#fff; padding:12px; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">💾 Save Settings</button>
                </form>
            </div>
        </div>

        <div>
            <div class="card" style="max-height: 500px; overflow-y: auto;">
                <h3>📊 Recent Downloads</h3>
                <table style="width: 100%; margin-top: 15px; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #eee;">
                            <th style="padding: 10px 5px;">File</th>
                            <th style="padding: 10px 5px;">IP</th>
                            <th style="padding: 10px 5px;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="3" style="padding: 20px; text-align: center; color: #999;">No downloads recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($logs, 0, 20) as $log): ?>
                                <tr style="border-bottom: 1px solid #f9f9f9;">
                                    <td style="padding: 10px 5px; font-weight: 600;"><?php echo htmlspecialchars($log['file']); ?></td>
                                    <td style="padding: 10px 5px; font-family: monospace;"><?php echo htmlspecialchars($log['ip']); ?></td>
                                    <td style="padding: 10px 5px; color: #666;"><?php echo date('M d, H:i', $log['time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px; border-top: 4px solid #8b5cf6;">
        <h3>📖 Shortcode Documentation</h3>
        <p>To embed a secure download button in any post or page, use the following shortcode:</p>
        <div style="background: #f7fafc; padding: 1.25rem; border-radius: 8px; margin: 15px 0; border: 1px dashed #cbd5e0; font-family: monospace;">
            [sfx-download file="your-file.zip" label="Download Asset Pack"]
        </div>
        <p style="font-size: 0.9rem; color: #666;">Replace <code>your-file.zip</code> with the exact filename from the <strong>Secure Files</strong> tab in <strong>Media Management</strong>.</p>
    </div>
</div>
</body>
</html>
