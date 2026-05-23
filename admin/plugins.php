<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('plugins');

$config = load_config();
$error = '';
$success = '';

// Recursive delete helper
function delete_directory($dir) {
    if (!is_dir($dir)) return false;
    $items = array_diff(scandir($dir), array('.', '..'));
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? delete_directory($path) : unlink($path);
    }
    return rmdir($dir);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    if (isset($_FILES['plugin_zip'])) {
        if (!class_exists('ZipArchive')) {
            $error = "PHP ZipArchive extension is not enabled on this server.";
        } else {
            $file = $_FILES['plugin_zip'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $zip = new ZipArchive();
                if ($zip->open($file['tmp_name']) === TRUE) {
                    $temp_id = uniqid('tmp_');
                    $temp_extract = __DIR__ . '/../plugins/' . $temp_id;
                    mkdir($temp_extract, 0755, true);

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);
                        if (strpos($filename, '..') !== false || strpos($filename, '/') === 0 || strpos($filename, '\\') === 0) {
                            continue;
                        }
                        $zip->extractTo($temp_extract, $filename);
                    }
                    $zip->close();

                    $found_path = '';
                    $it = new RecursiveDirectoryIterator($temp_extract);
                    foreach (new RecursiveIteratorIterator($it) as $f) {
                        if (basename($f) === 'plugin.php') {
                            $found_path = dirname($f);
                            break;
                        }
                    }

                    if ($found_path) {
                        $plugin_slug = basename($found_path);
                        if (strpos($plugin_slug, 'tmp_') === 0) {
                            $plugin_slug = sanitize(pathinfo($file['name'], PATHINFO_FILENAME));
                        }
                        $dest = __DIR__ . '/../plugins/' . $plugin_slug;
                        if (!is_dir($dest)) {
                            rename($found_path, $dest);
                            $success = "Plugin '" . $plugin_slug . "' installed successfully!";
                        } else {
                            $error = "A plugin with the folder name '$plugin_slug' already exists.";
                        }
                    } else {
                        $error = "Invalid plugin ZIP: 'plugin.php' not found.";
                    }
                    delete_directory($temp_extract);
                } else {
                    $error = "Failed to open ZIP file.";
                }
            }
        }
    }

    if (isset($_POST['action'])) {
        $plugin_name = $_POST['plugin'];
        $enabled_plugins = $config['enabled_plugins'] ?? [];

        if ($_POST['action'] === 'toggle') {
            if (in_array($plugin_name, $enabled_plugins)) {
                $enabled_plugins = array_diff($enabled_plugins, [$plugin_name]);
                $msg = "Deactivated";
            } else {
                $enabled_plugins[] = $plugin_name;
                $msg = "Activated";
            }
            if (update_config(['enabled_plugins' => array_values($enabled_plugins)])) {
                redirect('plugins.php?success=' . $msg);
            }
        }

        if ($_POST['action'] === 'delete') {
            $plugin_dir = __DIR__ . '/../plugins/' . basename($plugin_name);
            if (in_array($plugin_name, $enabled_plugins)) {
                $enabled_plugins = array_diff($enabled_plugins, [$plugin_name]);
                update_config(['enabled_plugins' => array_values($enabled_plugins)]);
            }
            if (is_dir($plugin_dir)) {
                if (delete_directory($plugin_dir)) {
                    redirect('plugins.php?success=Deleted');
                }
            }
        }
    }
}

$plugins = [];
$plugin_dirs = glob(__DIR__ . '/../plugins/*', GLOB_ONLYDIR);
foreach ($plugin_dirs as $dir) {
    if (strpos(basename($dir), 'tmp_') === 0) continue;
    $plugin_file = $dir . '/plugin.php';
    if (file_exists($plugin_file)) {
        $plugins[basename($dir)] = include $plugin_file;
    }
}
$enabled_plugins = $config['enabled_plugins'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plugins - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .plugin-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 1.5rem 0; }
        .plugin-info h3 { margin: 0 0 0.5rem 0; color: #1e293b; }
        .plugin-info p { margin: 0; color: #64748b; font-size: 0.9rem; }
        .plugin-status { margin-top: 0.5rem; }
        .status-badge { font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #f1f5f9; color: #475569; }
        .plugin-actions { display: flex; gap: 10px; align-items: center; }
        .settings-link { font-size: 0.85rem; color: #2271b1; text-decoration: none; font-weight: 600; }
        .settings-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Plugins</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Plugin <?php echo htmlspecialchars($_GET['success']); ?> successfully!</div><?php endif; ?>

        <div class="card">
            <h3>Add New Plugin</h3>
            <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="file" name="plugin_zip" accept=".zip" required>
                <button type="submit" class="btn btn-primary">Upload & Install</button>
            </form>
        </div>

        <div class="card">
            <?php if (empty($plugins)): ?>
                <p>No plugins found.</p>
            <?php else: ?>
                <?php foreach ($plugins as $name => $data): $is_enabled = in_array($name, $enabled_plugins); ?>
                    <div class="plugin-item">
                        <div class="plugin-info">
                            <h3><?php echo htmlspecialchars($data['name'] ?? $name); ?></h3>
                            <p><?php echo htmlspecialchars($data['description'] ?? 'No description provided.'); ?></p>
                            <div class="plugin-status">
                                <?php if ($is_enabled): ?>
                                    <span class="status-badge status-active">Active</span>
                                    <?php if (isset($data['settings_url'])): ?>
                                        &nbsp; | &nbsp; <a href="<?php echo $data['settings_url']; ?>" class="settings-link">Settings</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="plugin-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="plugin" value="<?php echo htmlspecialchars($name); ?>">
                                <input type="hidden" name="action" value="toggle">
                                <?php if ($is_enabled): ?>
                                    <button type="submit" class="btn btn-warning">Deactivate</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-success">Activate</button>
                                <?php endif; ?>
                            </form>

                            <?php if (!$is_enabled): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? This action is irreversible and will delete all plugin files.');">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="plugin" value="<?php echo htmlspecialchars($name); ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
