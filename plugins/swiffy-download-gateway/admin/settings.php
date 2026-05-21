<?php
/**
 * Settings for Swiffy Download Gateway
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sfx_save_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF Failed');

    update_config([
        'sfx_dl_timer' => (int)$_POST['sfx_dl_timer'],
        'sfx_dl_ads' => isset($_POST['sfx_dl_ads']),
        'sfx_dl_msg' => $_POST['sfx_dl_msg'],
        'sfx_dl_unlock_mode' => $_POST['sfx_dl_unlock_mode']
    ]);

    echo '<div class="alert alert-success">Settings saved!</div>';
    $config = load_config();
}

$timer = $config['sfx_dl_timer'] ?? 5;
$ads = $config['sfx_dl_ads'] ?? true;
$msg = $config['sfx_dl_msg'] ?? 'Preparing secure link...';
$unlock_mode = $config['sfx_dl_unlock_mode'] ?? 'none';

// Load logs
$log_file = SFX_DL_GATEWAY_DIR . '/logs/downloads.json';
$logs = file_exists($log_file) ? array_reverse(json_decode(file_get_contents($log_file), true)) : [];
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div>
        <div class="card">
            <h3>⚙️ Gateway Configuration</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="form-group" style="margin-top: 20px;">
                    <label>Countdown Timer (seconds)</label>
                    <input type="number" name="sfx_dl_timer" value="<?php echo $timer; ?>" min="0" max="60" style="width: 100%; padding: 10px;">
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>
                        <input type="checkbox" name="sfx_dl_ads" <?php echo $ads ? 'checked' : ''; ?>> Enable Ad Slots on Gateway
                    </label>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>Default Status Message</label>
                <div class="form-group" style="margin-top: 20px;">
                    <label>Unlock Requirement</label>
                    <select name="sfx_dl_unlock_mode" style="width: 100%; padding: 10px;">
                        <option value="none" <?php echo ($unlock_mode === 'none') ? 'selected' : ''; ?>>None (Timer Only)</option>
                        <option value="like" <?php echo ($unlock_mode === 'like') ? 'selected' : ''; ?>>Like Post to Unlock</option>
                        <option value="comment" <?php echo ($unlock_mode === 'comment') ? 'selected' : ''; ?>>Comment to Unlock</option>
                    </select>
                </div>
                    <input type="text" name="sfx_dl_msg" value="<?php echo htmlspecialchars($msg); ?>" style="width: 100%; padding: 10px;">
                </div>

                <button type="submit" name="sfx_save_settings" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Save Settings</button>
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

<div class="card" style="margin-top: 20px;">
    <h3>📖 Shortcode Documentation</h3>
    <p>To embed a secure download button in any post or page, use the following shortcode:</p>
    <code style="display: block; background: #f4f4f4; padding: 15px; border-radius: 8px; margin: 10px 0; font-family: 'Fira Code', monospace;">
        [sfx-download file="your-file.zip" label="Download Asset Pack"]
    </code>
    <p style="font-size: 0.9rem; color: #666;">Replace <code>your-file.zip</code> with the exact filename from the <strong>Secure Files</strong> tab in Media Management.</p>
</div>
