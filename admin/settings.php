<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('settings');

$config = load_config();
$error = '';
$success = '';
$uploads_dir = __DIR__ . '/../uploads/';


// Handle Settings Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_settings') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');

    $new_config = $config;
    $new_config['site_name'] = 'Scotts Blog';
    $new_config['footer_text'] = '';
    $new_config['comments_enabled'] = true;
    $new_config['disqus_shortname'] = '';
    $new_config['posts_per_page'] = 6;
    $new_config['sidebar_position'] = 'right';
    $new_config['back_to_top_enabled'] = false;
    $new_config['back_to_top_type'] = 'icon';
    $new_config['back_to_top_text'] = 'Top';
    $new_config['back_to_top_color'] = '#8b5cf6';
    $new_config['back_to_top_size'] = 40;
    $new_config['show_author_bio'] = true;

    if (update_config($new_config)) {
        $success = "Settings reset to defaults.";
        $config = load_config();
    } else {
        $error = "Failed to reset settings.";
    }
}

// Handle Demo Content Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_demo') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    if (import_demo_content()) {
        $success = "Demo content imported successfully! You might need to refresh to see the changes.";
        $config = load_config();
    } else {
        $error = "Failed to import demo content.";
    }
}

// --- PLUGIN SETTINGS HANDLER ---
$plugin_to_configure = $_GET['plugin'] ?? '';
$all_plugins_data = get_enabled_plugins_data();

if ($plugin_to_configure && isset($all_plugins_data[$plugin_to_configure])) {
    $p_data = $all_plugins_data[$plugin_to_configure];
    $plugin_settings_file = __DIR__ . '/../plugins/' . $plugin_to_configure . '/admin/settings.php';

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><title><?php echo htmlspecialchars($p_data['name']); ?> Settings</title><link rel="stylesheet" href="style.css"></head>
    <body>
    <?php include "sidebar.php"; ?>
    <div style="margin-left:310px; padding:2rem; margin-top:50px;">
        <h1><?php echo htmlspecialchars($p_data['name']); ?> Settings</h1>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Settings saved successfully.</div><?php endif; ?>

        <?php
        if (file_exists($plugin_settings_file)) {
            include $plugin_settings_file;
        } else {
            echo "<div class='card'><p>No configurable options for this plugin.</p></div>";
        }
        ?>
        <br><a href="plugins.php" class="btn">&larr; Back to Plugins</a>
    </div>
    </body></html>
    <?php
    return; // Using return instead of exit for bash
}
// --- END PLUGIN SETTINGS HANDLER ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');
    $site_name = $_POST['site_name'] ?? $config['site_name'];
    $admin_nickname = $_POST['admin_nickname'] ?? $config['admin_nickname'] ?? 'Admin';
    $admin_about_me = $_POST['admin_about_me'] ?? $config['admin_about_me'] ?? '';
    $footer_text = $_POST['footer_text'] ?? $config['footer_text'] ?? '';
    $back_to_top_enabled = isset($_POST['back_to_top_enabled']);
    $back_to_top_type = $_POST['back_to_top_type'] ?? 'icon';
    $back_to_top_text = $_POST['back_to_top_text'] ?? 'Top';
    $back_to_top_color = $_POST['back_to_top_color'] ?? '#8b5cf6';
    $back_to_top_size = (int)($_POST['back_to_top_size'] ?? 40);
    $show_author_bio = isset($_POST['show_author_bio']);
    $comments_enabled = isset($_POST['comments_enabled']);
    $disqus_shortname = sanitize($_POST['disqus_shortname'] ?? '');
    $posts_per_page = (int)($_POST['posts_per_page'] ?? 5);
    $sidebar_position = $_POST['sidebar_position'] ?? 'right';

    $new_config = $config;
    $new_config['site_name'] = $site_name;
    $new_config['admin_nickname'] = $admin_nickname;
    $new_config['admin_about_me'] = $admin_about_me;
    $new_config['footer_text'] = $footer_text;
    $new_config['back_to_top_enabled'] = $back_to_top_enabled;
    $new_config['back_to_top_type'] = $back_to_top_type;
    $new_config['back_to_top_text'] = $back_to_top_text;
    $new_config['back_to_top_color'] = $back_to_top_color;
    $new_config['back_to_top_size'] = $back_to_top_size;
    $new_config['show_author_bio'] = $show_author_bio;
    $new_config['comments_enabled'] = $comments_enabled;
    $new_config['disqus_shortname'] = $disqus_shortname;
    $new_config['posts_per_page'] = $posts_per_page;
    $new_config['sidebar_position'] = $sidebar_position;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $filename = 'avatar_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploads_dir . $filename)) $new_config['admin_avatar'] = $filename;
        }
    }
    if (!empty($_POST['new_password'])) $new_config['admin_pass'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    if (update_config($new_config)) { $success = "Settings updated."; $config = load_config(); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Settings</title><link rel="stylesheet" href="style.css">
<style>
    .main-content { margin-left: 310px; margin-top: 50px; padding: 2rem; }
    .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc; }
    .profile-row { display: flex; gap: 20px; align-items: flex-start; }
</style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="main-content">
    <h1>General Settings</h1>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" style="background:#f2dede; color:#a94442; padding:10px; border-radius:4px; margin-bottom:15px;"><?php echo $error; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
        <div class="card">
            <h3>⚙️ Site Configuration</h3>
            <label>Site Name</label><input type="text" name="site_name" value="<?php echo htmlspecialchars($config['site_name']); ?>" required style="width:100%; padding:10px; margin-bottom:15px;">

            <label>Custom Footer Text (Copyright, etc)</label>
            <textarea name="footer_text" style="width:100%; height:60px; padding:10px; margin-bottom:15px;"><?php echo htmlspecialchars($config['footer_text'] ?? ''); ?></textarea>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:15px;">
                <div><label>Posts Per Page</label><input type="number" name="posts_per_page" value="<?php echo $config['posts_per_page']; ?>" style="width:100%; padding:10px;"></div>
                <div><label>Sidebar Position</label><select name="sidebar_position" style="width:100%; padding:10px;"><option value="left" <?php echo ($config['sidebar_position']??'')==='left'?'selected':''; ?>>Left</option><option value="right" <?php echo ($config['sidebar_position']??'')==='right'?'selected':''; ?>>Right</option></select></div>
            </div>
            <br>
            <label><input type="checkbox" name="comments_enabled" <?php echo ($config['comments_enabled']??false)?'checked':''; ?>> Enable built-in comments</label>
            <br><br>
            <label>Disqus Shortname</label><input type="text" name="disqus_shortname" value="<?php echo htmlspecialchars($config['disqus_shortname'] ?? ''); ?>" style="width:100%; padding:10px;">

            <h3 style="margin-top:30px;">👤 Admin Profile</h3>
            <div class="profile-row">
                <div>
                    <label>Avatar</label><br>
                    <?php if (!empty($config['admin_avatar'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($config['admin_avatar']); ?>" class="avatar-preview">
                    <?php else: ?>
                        <div class="avatar-preview" style="background:#eee; display:flex; align-items:center; justify-content:center;">No Image</div>
                    <?php endif; ?>
                    <input type="file" name="avatar" style="margin-top:10px;">
                </div>
                <div style="flex:1;">
                    <label>Nickname</label><input type="text" name="admin_nickname" value="<?php echo htmlspecialchars($config['admin_nickname'] ?? 'Admin'); ?>" style="width:100%; padding:10px;">
                    <label style="margin-top:10px; display:block;">About Me / Bio</label>
                    <textarea name="admin_about_me" style="width:100%; height:80px; padding:10px;"><?php echo htmlspecialchars($config['admin_about_me'] ?? ''); ?></textarea>
                    <label style="margin-top:10px; display:block; cursor:pointer;">
                        <input type="checkbox" name="show_author_bio" <?php echo ($config['show_author_bio'] ?? true) ? 'checked' : ''; ?>>
                        Show Author Bio box on single post pages
                    </label>
                </div>
            </div>

            <h3 style="margin-top:30px;">🔒 Security</h3>
            <label>New Password (leave blank to keep current)</label><input type="password" name="new_password" style="width:100%; padding:10px;">
            <br><br>

        </div>


    <div class="card" style="border-top: 5px solid #8b5cf6;">
        <h3>🔝 Back to Top Button</h3>
        <div style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="back_to_top_enabled" <?php echo ($config['back_to_top_enabled'] ?? false) ? 'checked' : ''; ?> style="margin-right: 10px;">
                Enable "Back to Top" button
            </label>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label>Type</label>
                <select name="back_to_top_type" style="width:100%; padding:10px;">
                    <option value="icon" <?php echo ($config['back_to_top_type'] ?? 'icon') === 'icon' ? 'selected' : ''; ?>>Icon Only</option>
                    <option value="text" <?php echo ($config['back_to_top_type'] ?? 'icon') === 'text' ? 'selected' : ''; ?>>Text Only</option>
                    <option value="both" <?php echo ($config['back_to_top_type'] ?? 'icon') === 'both' ? 'selected' : ''; ?>>Both</option>
                </select>
            </div>
            <div>
                <label>Button Color</label>
                <input type="color" name="back_to_top_color" value="<?php echo htmlspecialchars($config['back_to_top_color'] ?? '#8b5cf6'); ?>" style="width:100%; height:40px; padding:2px;">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
            <div>
                <label>Link Text (if applicable)</label>
                <input type="text" name="back_to_top_text" value="<?php echo htmlspecialchars($config['back_to_top_text'] ?? 'Top'); ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label>Button Size (px)</label>
                <input type="number" name="back_to_top_size" value="<?php echo htmlspecialchars($config['back_to_top_size'] ?? 40); ?>" style="width:100%; padding:10px;">
            </div>
        </div>
    <div style="margin-top: 20px; display: flex; gap: 10px;">
        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 15px; font-weight: bold;">💾 Save All Site Settings</button>
        <button type="submit" name="action" value="reset_settings" class="btn" style="background: #6c757d; color: #fff; padding: 15px; font-weight: bold;" onclick="return confirm('Reset all site settings (not content) to defaults?')">🔄 Reset to Defaults</button>
    </div></form>
<div class="card" style="margin-top: 30px; border-top: 4px solid #ffc107;">
        <h3>✨ Demo Content</h3>
        <p>New to the CMS? You can import demo content (posts, pages, and sample settings) to see how everything looks.</p>
        <div class="warning" style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffeeba;">
            <strong>Note:</strong> This will NOT delete your existing content, but it will overwrite site settings with demo defaults.
        </div>
        <form method="POST" onsubmit="return confirm('Import demo content? This will update your site settings.')">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="import_demo">
            <button type="submit" class="btn" style="background: #ffc107; color: #000; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Import Demo Content</button>
        </form>
    </div>
</div>
</body></html>
