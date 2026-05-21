<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('backup');

$config = load_config();
$error = '';
$success = '';

if (!class_exists('ZipArchive')) {
    $error = "ZipArchive PHP extension is not enabled.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'backup') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');

    $to_backup = $_POST['items'] ?? [];
    if (empty($to_backup)) {
        $error = "Please select at least one item to backup.";
    } else {
        $zip = new ZipArchive();
        $filename = "backup_" . date('Y-m-d_H-i-s') . ".zip";
        $filepath = sys_get_temp_dir() . '/' . $filename;

        if ($zip->open($filepath, ZipArchive::CREATE) !== TRUE) {
            $error = "Cannot create zip file.";
        } else {
            if (in_array('config', $to_backup)) $zip->addFile(__DIR__ . '/../config/config.php', 'config/config.php');
            if (in_array('posts', $to_backup)) {
                foreach (glob(__DIR__ . '/../content/posts/*.json') as $f) $zip->addFile($f, 'content/posts/' . basename($f));
            }
            if (in_array('pages', $to_backup)) {
                foreach (glob(__DIR__ . '/../content/pages/*.json') as $f) $zip->addFile($f, 'content/pages/' . basename($f));
            }
            if (in_array('comments', $to_backup)) {
                foreach (glob(__DIR__ . '/../content/comments/*.json') as $f) $zip->addFile($f, 'content/comments/' . basename($f));
            }
            if (in_array('media', $to_backup)) {
                foreach (glob(__DIR__ . '/../uploads/*') as $f) if (is_file($f)) $zip->addFile($f, 'uploads/' . basename($f));
            }
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            unlink($filepath);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');

    if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload a valid backup ZIP file.";
    } else {
        $zip = new ZipArchive();
        if ($zip->open($_FILES['backup_file']['tmp_name']) === TRUE) {
            $items_to_restore = $_POST['items'] ?? [];
            if (empty($items_to_restore)) {
                $error = "Please select at least one item to restore.";
            } else {
                $base = __DIR__ . '/../';
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    // Security check: ZipSlip
                    if (strpos($name, '..') !== false || strpos($name, '/') === 0 || strpos($name, '\\') === 0) continue;

                    if (in_array('config', $items_to_restore) && $name === 'config/config.php') {
                        $zip->extractTo($base, $name);
                    } elseif (in_array('posts', $items_to_restore) && strpos($name, 'content/posts/') === 0) {
                        $zip->extractTo($base, $name);
                    } elseif (in_array('pages', $items_to_restore) && strpos($name, 'content/pages/') === 0) {
                        $zip->extractTo($base, $name);
                    } elseif (in_array('comments', $items_to_restore) && strpos($name, 'content/comments/') === 0) {
                        $zip->extractTo($base, $name);
                    } elseif (in_array('media', $items_to_restore) && strpos($name, 'uploads/') === 0) {
                        $zip->extractTo($base, $name);
                    }
                }
                $zip->close();
                $success = "Restoration successful!";
                $config = load_config();
            }
        } else {
            $error = "Failed to open ZIP file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Backup & Restore</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h2>Create Backup</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="action" value="backup">
                    <label><input type="checkbox" name="items[]" value="config" checked> Config & Settings</label><br>
                    <label><input type="checkbox" name="items[]" value="posts" checked> Posts</label><br>
                    <label><input type="checkbox" name="items[]" value="pages" checked> Pages</label><br>
                    <label><input type="checkbox" name="items[]" value="comments" checked> Comments</label><br>
                    <label><input type="checkbox" name="items[]" value="media" checked> Media</label><br><br>
                    <button type="submit" class="btn btn-primary">Download Backup ZIP</button>
                </form>
            </div>
            <div class="card">
                <h2>Restore Backup</h2>
                <div class="warning">
                    <strong>WARNING:</strong> Restoring will <strong>DELETE</strong> current data in the selected categories and replace it with the backup content. This cannot be undone.
                </div>
                <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you absolutely sure? Current content in selected categories will be PERMANENTLY DELETED.')">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="action" value="restore">
                    <input type="file" name="backup_file" accept=".zip" required><br><br>
                    <label><input type="checkbox" name="items[]" value="config"> Config</label><br>
                    <label><input type="checkbox" name="items[]" value="posts"> Posts</label><br>
                    <label><input type="checkbox" name="items[]" value="pages"> Pages</label><br>
                    <label><input type="checkbox" name="items[]" value="comments"> Comments</label><br>
                    <label><input type="checkbox" name="items[]" value="media"> Media</label><br><br>
                    <button type="submit" class="btn btn-danger">Start Restoration</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
