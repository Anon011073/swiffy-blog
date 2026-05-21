<?php
/**
 * Installer for Lightweight Blogging CMS
 */

$config_file = __DIR__ . '/config/config.php';
if (file_exists($config_file)) die('CMS is already installed.');

$errors = [];
$success = false;

if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = "PHP version 7.4.0 or higher is required. Your version: " . PHP_VERSION;
}

if (!class_exists('ZipArchive')) $errors[] = "PHP ZipArchive extension is required for themes and plugins.";
if (!function_exists('gd_info')) $errors[] = "PHP GD extension is required for image processing/cropping.";

$dirs_to_check = ['content', 'content/posts', 'content/pages', 'content/comments', 'config', 'uploads', 'plugins'];
foreach ($dirs_to_check as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (!is_dir($full_path) && !mkdir($full_path, 0755, true)) $errors[] = "Failed to create directory: $dir";
    if (!is_writable($full_path)) $errors[] = "Directory is not writable: $dir";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $site_name = $_POST['site_name'] ?? '';
    $admin_user = $_POST['admin_user'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';

    if ($site_name && $admin_user && $admin_pass) {
        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        $config_content = "<?php\nreturn " . var_export([
            'site_name' => $site_name,
            'admin_user' => $admin_user,
            'admin_pass' => $hashed_password,
            'admin_nickname' => 'Admin',
            'theme' => 'default',
            'comments_enabled' => true,
            'show_search_menu' => false,
            'show_excerpts' => true,
            'posts_per_page' => 5,
            'sidebar_position' => 'right',
            'widget_areas' => ['sidebar' => ['search', 'recent_posts']],
            'enabled_plugins' => []
        ], true) . ";\n";

        if (file_put_contents($config_file, $config_content)) $success = true;
        else $errors[] = "Failed to write config file.";
    } else {
        $errors[] = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Installer</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .install-box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .error { color: #d9534f; background: #f2dede; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { color: #5cb85c; background: #dff0d8; padding: 1rem; border-radius: 4px; text-align: center; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 1rem; }
        button { width: 100%; padding: 0.75rem; background: #007bff; border: none; color: #fff; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="install-box">
        <h1>CMS Installation</h1>
        <?php if ($success): ?>
            <div class="success">
                <p>Installation successful!</p>
                <p><strong>Security Notice:</strong> Please delete <code>install.php</code> manually.</p>
                <p><a href="admin/login.php">Go to Admin Panel</a></p>
            </div>
        <?php else: ?>
            <?php if ($errors): ?><div class="error"><?php foreach ($errors as $e) echo "<div>$e</div>"; ?></div><?php endif; ?>
            <form method="POST">
                <label>Site Name</label><input type="text" name="site_name" required>
                <label>Admin Username</label><input type="text" name="admin_user" required>
                <label>Admin Password</label><input type="password" name="admin_pass" required>
                <button type="submit" <?php if (!empty($errors)) echo 'disabled'; ?>>Install CMS</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
