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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plugin_zip'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

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

                // Safe extraction to prevent ZipSlip
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (strpos($filename, '..') !== false || strpos($filename, '/') === 0 || strpos($filename, '\\') === 0) {
                        continue; // Skip dangerous paths
                    }
                    $zip->extractTo($temp_extract, $filename);
                }
                $zip->close();

                // Find plugin.php
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

if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (!verify_csrf_token($_GET['token'])) die('CSRF token validation failed.');
    $plugin_name = basename($_GET['delete']);
    $plugin_dir = __DIR__ . '/../plugins/' . $plugin_name;
    $enabled_plugins = $config['enabled_plugins'] ?? [];
    if (in_array($plugin_name, $enabled_plugins)) {
        $enabled_plugins = array_diff($enabled_plugins, [$plugin_name]);
        update_config(['enabled_plugins' => array_values($enabled_plugins)]);
    }
    if (is_dir($plugin_dir)) {
        if (delete_directory($plugin_dir)) redirect('plugins.php?success=Deleted');
    }
}

if (isset($_GET['toggle']) && isset($_GET['token'])) {
    if (!verify_csrf_token($_GET['token'])) die('CSRF token validation failed.');
    $plugin_name = $_GET['toggle'];
    $enabled_plugins = $config['enabled_plugins'] ?? [];
    if (in_array($plugin_name, $enabled_plugins)) $enabled_plugins = array_diff($enabled_plugins, [$plugin_name]);
    else $enabled_plugins[] = $plugin_name;
    if (update_config(['enabled_plugins' => array_values($enabled_plugins)])) redirect('plugins.php?success=1');
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
        .main-content { margin-left: 310px; margin-top: 50px; padding: 2rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1rem; }
        .plugin-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 1rem 0; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Plugins</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <div class="card">
            <h3>Add New Plugin</h3>
            <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="file" name="plugin_zip" accept=".zip" required>
                <button type="submit" class="btn btn-primary">Upload & Install</button>
            </form>
        </div>
        <div class="card">
            <?php if (empty($plugins)): ?><p>No plugins found.</p><?php else: ?>
                <?php foreach ($plugins as $name => $data): $is_enabled = in_array($name, $enabled_plugins); ?>
                    <div class="plugin-item">
                        <div class="plugin-info">
                            <h3><?php echo htmlspecialchars($data['name'] ?? $name); ?></h3>
                            <p><?php echo htmlspecialchars($data['description'] ?? ''); ?></p>
                            <?php if ($is_enabled && isset($data['settings_url'])): ?>
                                <a href="<?php echo $data['settings_url']; ?>" style="font-size: 0.8rem; color: #007bff;">Settings</a>
                            <?php endif; ?>
                        </div>
                        <div class="plugin-actions" style="display: flex; gap: 5px;">
                            <?php if ($is_enabled): ?>
                                <a href="plugins.php?toggle=<?php echo $name; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger">Deactivate</a>
                            <?php else: ?>
                                <a href="plugins.php?toggle=<?php echo $name; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-success">Activate</a>
                                <a href="plugins.php?delete=<?php echo $name; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" onclick="return confirm('Delete permanently?')">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
