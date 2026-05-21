<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('media');

$config = load_config();
$error = '';
$success = '';
$images_dir = __DIR__ . '/../uploads/';
$files_dir = __DIR__ . '/../content/protected-uploads/';
$plugin_enabled = in_array('swiffy-download-gateway', $config['enabled_plugins'] ?? []);

if (!is_dir($files_dir)) mkdir($files_dir, 0755, true);

$tab = $_GET['tab'] ?? 'images';

// Force images tab if plugin is disabled
if ($tab === 'files' && !$plugin_enabled) $tab = 'images';

// Handle Uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');

    $files = $_FILES['files'];
    $uploaded_count = 0;
    $errors = [];
    $target_dir = ($tab === 'files') ? $files_dir : $images_dir;

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $filename = basename($files['name'][$i]);
        $target_file = $target_dir . $filename;

        $j = 1;
        while (file_exists($target_file)) {
            $parts = pathinfo($filename);
            $new_filename = $parts['filename'] . '_' . $j . '.' . ($parts['extension'] ?? '');
            $target_file = $target_dir . $new_filename;
            $j++;
        }

        if (move_uploaded_file($files['tmp_name'][$i], $target_file)) {
            $uploaded_count++;
        } else {
            $errors[] = "Failed to upload {$files['name'][$i]}.";
        }
    }

    if ($uploaded_count > 0) $success = "$uploaded_count items uploaded successfully.";
    if (!empty($errors)) $error = implode('<br>', $errors);
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $target_dir = ($tab === 'files') ? $files_dir : $images_dir;
        $file_to_delete = $target_dir . basename($_GET['delete']);
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
            $success = "File deleted.";
        }
    }
}

// Get Items
if ($tab === 'files') {
    $items = glob($files_dir . '*');
    $log_file = __DIR__ . '/../plugins/swiffy-download-gateway/logs/downloads.json';
    $log_content = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : []; $dl_logs = is_array($log_content) ? array_reverse($log_content) : [];
} else {
    $items = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
}

if ($items) {
    usort($items, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
}

function format_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) $bytes /= 1024;
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --accent-purple: #8b5cf6;
            --bg-darker: #0f172a;
            --card-bg: #ffffff;
        }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .card { background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 20px; border: 1px solid #edf2f7; }

        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .tab-link { text-decoration: none; padding: 10px 20px; border-radius: 8px; color: #666; font-weight: 600; transition: 0.3s; }
        .tab-link.active { background: var(--accent-purple); color: #fff; }

        .upload-area { border: 2px dashed #cbd5e0; padding: 40px; text-align: center; border-radius: 12px; transition: 0.3s; cursor: pointer; background: #f7fafc; }
        .upload-area:hover { border-color: var(--accent-purple); background: #f0f4ff; }

        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
        .media-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; position: relative; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .media-item:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1); border-color: var(--accent-purple); }

        .preview-box { width: 100%; aspect-ratio: 16/10; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .file-ext-icon { font-size: 2.5rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; font-family: monospace; }

        .item-info { padding: 12px; border-top: 1px solid #eee; }
        .item-name { display: block; font-size: 0.85rem; font-weight: 700; color: #1a202c; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
        .item-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #718096; }

        .actions { position: absolute; top: 10px; right: 10px; display: flex; gap: 5px; opacity: 0; transition: 0.3s; }
        .media-item:hover .actions { opacity: 1; }

        .action-btn { background: #fff; border: 1px solid #eee; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .action-btn:hover { background: #f8f9fa; color: var(--accent-purple); }
        .btn-del:hover { color: #e53e3e; }

        .log-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.85rem; }
        .log-table th, .log-table td { text-align: left; padding: 12px; border-bottom: 1px solid #edf2f7; }
        .log-table th { background: #f7fafc; color: #4a5568; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Media Management</h1>

        <div class="tabs">
            <a href="?tab=images" class="tab-link <?php echo $tab === 'images' ? 'active' : ''; ?>">🖼️ Images</a>
            <?php if ($plugin_enabled): ?>
            <a href="?tab=files" class="tab-link <?php echo $tab === 'files' ? 'active' : ''; ?>">📂 Secure Files</a>
            <?php endif; ?>
        </div>

        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>

        <div class="card">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="file" id="fileInput" name="files[]" multiple style="display: none;" onchange="this.form.submit()">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <div style="font-size: 2rem; margin-bottom: 10px;">☁️</div>
                    <strong>Click or Drag to Upload <?php echo $tab === 'files' ? 'Secure Files' : 'Images'; ?></strong>
                    <p style="color: #718096; font-size: 0.9rem; margin-top: 5px;">Supported: <?php echo $tab === 'files' ? 'ZIP, RAR, PDF, EXE, MP3, etc.' : 'JPG, PNG, WEBP, GIF'; ?></p>
                </div>
            </form>
        </div>

        <?php if ($tab === 'files' && !empty($dl_logs)): ?>
        <div class="card">
            <h3>📊 Recent Download Activity</h3>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                        <th>Referrer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($dl_logs, 0, 10) as $log): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent-purple);"><?php echo htmlspecialchars($log['file']); ?></td>
                            <td><code><?php echo htmlspecialchars($log['ip']); ?></code></td>
                            <td><?php echo date('M d, Y H:i:s', $log['time']); ?></td>
                            <td style="color: #a0aec0; font-size: 0.75rem;"><?php echo htmlspecialchars($log['ref']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="media-grid">
            <?php if (empty($items)): ?>
                <p style="text-align: center; color: #999; grid-column: 1 / -1; padding: 40px;">No <?php echo $tab; ?> found. Start by uploading some!</p>
            <?php else: ?>
                <?php foreach ($items as $item):
                    $name = basename($item);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $size = format_size(filesize($item));
                ?>
                    <div class="media-item">
                        <div class="actions">
                            <?php if ($tab === 'files'): ?>
                                <a href="#" class="action-btn" title="Copy Shortcode" onclick="copyText('[sfx-download file=&quot;<?php echo addslashes($name); ?>&quot; label=&quot;Download <?php echo addslashes($name); ?>&quot;]'); return false;">🔗</a>
                            <?php else: ?>
                                <a href="media_crop.php?img=<?php echo urlencode($name); ?>" class="action-btn" title="Crop">✂️</a>
                            <?php endif; ?>
                            <a href="media.php?tab=<?php echo $tab; ?>&delete=<?php echo urlencode($name); ?>&token=<?php echo get_csrf_token(); ?>"
                               class="action-btn btn-del"
                               onclick="return confirm('Permanently delete this item?')" title="Delete">🗑️</a>
                        </div>

                        <div class="preview-box">
                            <?php if ($tab === 'images'): ?>
                                <img src="../uploads/<?php echo $name; ?>" alt="">
                            <?php else: ?>
                                <div class="file-ext-icon"><?php echo $ext ?: 'file'; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="item-info">
                            <span class="item-name" title="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></span>
                            <div class="item-meta">
                                <span><?php echo $size; ?></span>
                                <span><?php echo date('M d, Y', filemtime($item)); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function copyText(text) {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        alert('Shortcode copied to clipboard!');
    }
    </script>
</body>
</html>
