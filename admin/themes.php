<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('themes');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['theme_zip'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    if (!class_exists('ZipArchive')) {
        $error = "PHP ZipArchive extension is not enabled.";
    } else {
        $file = $_FILES['theme_zip'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $zip = new ZipArchive();
            if ($zip->open($file['tmp_name']) === TRUE) {
                $temp_extract = __DIR__ . '/../themes/tmp_' . uniqid();
                mkdir($temp_extract, 0755, true);

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (strpos($filename, '..') !== false || strpos($filename, '/') === 0 || strpos($filename, '\\') === 0) continue;
                    $zip->extractTo($temp_extract, $filename);
                }
                $zip->close();

                $found_path = '';
                $it = new RecursiveDirectoryIterator($temp_extract);
                foreach (new RecursiveIteratorIterator($it) as $f) {
                    if (basename($f) === 'index.php') {
                        $found_path = dirname($f);
                        break;
                    }
                }

                if ($found_path) {
                    $theme_slug = basename($found_path);
                    if (strpos($theme_slug, 'tmp_') === 0) $theme_slug = sanitize(pathinfo($file['name'], PATHINFO_FILENAME));

                    $dest = __DIR__ . '/../themes/' . $theme_slug;
                    if (!is_dir($dest)) {
                        rename($found_path, $dest);
                        $success = "Theme '" . $theme_slug . "' installed successfully!";
                    } else {
                        $error = "A theme with the folder name '$theme_slug' already exists.";
                    }
                } else {
                    $error = "Invalid theme ZIP: 'index.php' not found.";
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
    $theme_name = basename($_GET['delete']);
    if (!in_array($theme_name, ['default', 'starter', 'popeye', 'darkling'])) {
        $theme_dir = __DIR__ . '/../themes/' . $theme_name;
        if (is_dir($theme_dir)) {
            if (delete_directory($theme_dir)) redirect('themes.php?success=Deleted');
        }
    }
}

if (isset($_GET['activate']) && isset($_GET['token'])) {
    if (!verify_csrf_token($_GET['token'])) die('CSRF token validation failed.');
    $theme_name = $_GET['activate'];
    if (update_config(['theme' => $theme_name, 'theme_options' => []])) {
        redirect('themes.php?success=Theme Activated');
    }
}

$themes = [];
$theme_dirs = glob(__DIR__ . '/../themes/*', GLOB_ONLYDIR);
foreach ($theme_dirs as $dir) {
    if (strpos(basename($dir), 'tmp_') === 0) continue;
    $name = basename($dir);
    $meta_file = file_exists($dir . '/theme-config.php') ? $dir . '/theme-config.php' : (file_exists($dir . '/theme.php') ? $dir . '/theme.php' : null);
    if ($meta_file) {
        $themes[$name] = include $meta_file;
    } else {
        $themes[$name] = ['name' => ucfirst($name), 'author' => 'System'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Themes - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 50px; padding: 2rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1rem; }
        .theme-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 1rem 0; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Themes</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <div class="card">
            <h3>Add New Theme</h3>
            <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="file" name="theme_zip" accept=".zip" required>
                <button type="submit" class="btn btn-primary">Upload & Install</button>
            </form>
        </div>
        <div class="card">
            <?php if (empty($themes)): ?><p>No themes found.</p><?php else: ?>
                <?php foreach ($themes as $name => $data): $is_active = ($config['theme'] ?? 'default') === $name; ?>
                    <div class="theme-item" style="<?php echo $is_active ? 'border-left: 5px solid #28a745; background: #f9fff9;' : ''; ?>">
                        <div class="theme-info">
                            <h3><?php echo htmlspecialchars($data['name'] ?? $name); ?> <?php if ($is_active) echo '<small>(Active)</small>'; ?></h3>
                            <p><?php echo htmlspecialchars($data['description'] ?? ''); ?></p>
                        </div>
                        <div class="theme-actions">
                            <?php if (!$is_active): ?>
                                <a href="themes.php?activate=<?php echo $name; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-success">Activate</a>
                                <?php if (!in_array($name, ['default', 'starter', 'popeye', 'darkling'])): ?>
                                    <a href="themes.php?delete=<?php echo $name; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" onclick="return confirm('Delete theme?')">Delete</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
