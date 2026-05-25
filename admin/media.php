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
$gallery_enabled = in_array('swiffy-gallery', $config['enabled_plugins'] ?? []);

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
$items = [];
if ($tab === 'files') {
    $items = glob($files_dir . '*');
    $log_file = __DIR__ . '/../plugins/swiffy-download-gateway/logs/downloads.json';
    $log_content = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];
    $dl_logs = is_array($log_content) ? array_reverse($log_content) : [];
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
    <title>Media Management - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --accent-purple: #8b5cf6;
            --accent-green: #22c55e;
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .main-content {
            margin-left: 310px;
            margin-top: 50px;
            padding: 2.5rem;
            min-height: calc(100vh - 50px);
        }
        h1 { font-size: 2rem; font-weight: 800; margin-bottom: 2rem; letter-spacing: -0.02em; }

        .tabs { display: flex; gap: 8px; margin-bottom: 30px; background: rgba(255,255,255,0.03); padding: 6px; border-radius: 12px; width: fit-content; border: 1px solid var(--border); }
        .tab-link { text-decoration: none; padding: 10px 24px; border-radius: 8px; color: var(--text-muted); font-weight: 600; transition: 0.2s; font-size: 0.95rem; }
        .tab-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .tab-link.active { background: var(--accent-purple); color: #fff; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3); }

        .card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            margin-bottom: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }

        .upload-area {
            border: 2px dashed var(--border);
            padding: 50px 20px;
            text-align: center;
            border-radius: 16px;
            transition: 0.3s;
            cursor: pointer;
            background: rgba(255,255,255,0.01);
        }
        .upload-area:hover { border-color: var(--accent-purple); background: rgba(139, 92, 246, 0.04); }
        .upload-icon { font-size: 3rem; margin-bottom: 15px; display: block; }

        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 2.5rem; }
        .media-item {
            background: #1a2234;
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .media-item:hover { transform: translateY(-6px); border-color: var(--accent-purple); box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.3); }

        .preview-box { width: 100%; aspect-ratio: 1/1; background: #0b1120; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .file-ext-icon { font-size: 2.5rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.05em; }

        .item-info { padding: 16px; background: rgba(0,0,0,0.2); }
        .item-name { display: block; font-size: 0.9rem; font-weight: 600; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 6px; }
        .item-meta { display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; }

        .actions { position: absolute; top: 12px; right: 12px; display: flex; gap: 8px; opacity: 0; transition: 0.2s; z-index: 10; }
        .media-item:hover .actions { opacity: 1; }

        .action-btn { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #fff; font-size: 1.1rem; }
        .action-btn:hover { background: var(--accent-purple); border-color: var(--accent-purple); transform: scale(1.1); }
        .btn-del:hover { background: #ef4444; border-color: #ef4444; }

        .error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; }
        .success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #4ade80; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; }

        .log-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.85rem; }
        .log-table th, .log-table td { text-align: left; padding: 12px; border-bottom: 1px solid var(--border); }
        .log-table th { background: rgba(255,255,255,0.03); color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }

        /* Modern Glass Helper */
        .helper-card {
            margin-top: 4rem;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.8) 100%) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-top: 4px solid var(--accent-purple) !important;
            padding: 2.5rem !important;
        }
        .helper-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .helper-header h3 { margin: 0; font-size: 1.5rem; }
        .helper-badge { background: rgba(139, 92, 246, 0.2); color: var(--accent-purple); border: 1px solid rgba(139, 92, 246, 0.3); padding: 6px 16px; border-radius: 30px; font-size: 0.85rem; font-weight: 700; }
        .helper-preview { background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 14px; margin: 20px 0; color: #94a3b8; font-size: 0.95rem; line-height: 1.6; }

        .shortcode-box { background: #0b1120; border: 1px solid rgba(255,255,255,0.1); padding: 12px 12px 12px 24px; border-radius: 16px; display: flex; align-items: center; gap: 20px; }
        .shortcode-text { flex: 1; font-family: "Fira Code", "JetBrains Mono", monospace; color: var(--accent-purple); font-size: 1.1rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .helper-btns { display: flex; gap: 12px; }
        .helper-btn { padding: 12px 24px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; transition: 0.2s; font-size: 0.95rem; }
        .btn-copy { background: var(--accent-green); color: #fff; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3); }
        .btn-copy:hover { transform: translateY(-2px); opacity: 0.9; }
        .btn-clear { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); }
        .btn-clear:hover { background: rgba(255,255,255,0.1); }

        .media-item.selected { border: 2px solid var(--accent-purple); transform: translateY(-8px); box-shadow: 0 0 30px rgba(139, 92, 246, 0.3); }
        .media-item.selected::after { content: "✓"; position: absolute; top: 12px; left: 12px; background: var(--accent-purple); color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem; border: 2px solid #fff; }
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
                    <span class="upload-icon">☁️</span>
                    <strong>Click or Drag to Upload <?php echo $tab === 'files' ? 'Secure Files' : 'Images'; ?></strong>
                    <p style="color: #64748b; font-size: 0.9rem; margin-top: 8px;">Supported: <?php echo $tab === 'files' ? 'ZIP, RAR, PDF, EXE, MP3, etc.' : 'JPG, PNG, WEBP, GIF'; ?></p>
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
                <p style="text-align: center; color: #475569; grid-column: 1 / -1; padding: 60px; font-size: 1.1rem;">No <?php echo $tab; ?> found. Start by uploading some!</p>
            <?php else: ?>
                <?php foreach ($items as $item):
                    $name = basename($item);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $size = format_size(filesize($item));
                ?>
                    <div class="media-item" <?php echo ($tab === "images" && $gallery_enabled) ? "onclick=\"toggleSelect(this, '".addslashes($name)."')\"" : ""; ?>>
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
                                <span><?php echo date('M d', filemtime($item)); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($tab === "images" && $gallery_enabled): ?>
        <div class="card helper-card" id="shortcodeHelper">
            <div class="helper-header">
                <h3>🖼️ Gallery Shortcode Helper</h3>
                <span class="helper-badge" id="selectedCount">0 selected</span>
            </div>
            <p style="margin-bottom: 10px; font-size: 1rem; color: #94a3b8;">Select images from the grid above to build your gallery. The shortcode updates automatically.</p>

            <div class="helper-preview" id="imagePreviewNames">No images selected.</div>

            <div class="shortcode-box">
                <div class="shortcode-text" id="shortcodeOutput">[swiffy-gallery images=""]</div>
                <div class="helper-btns">
                    <button class="helper-btn btn-copy" onclick="copyGalleryShortcode()">Copy Shortcode</button>
                    <button class="helper-btn btn-clear" onclick="clearSelection()">Clear</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    let selectedImages = [];

    function toggleSelect(el, name) {
        const index = selectedImages.indexOf(name);
        if (index === -1) {
            selectedImages.push(name);
            el.classList.add("selected");
        } else {
            selectedImages.splice(index, 1);
            el.classList.remove("selected");
        }
        updateHelper();
    }

    function updateHelper() {
        const countEl = document.getElementById("selectedCount");
        const previewEl = document.getElementById("imagePreviewNames");
        const outputEl = document.getElementById("shortcodeOutput");

        if (countEl) countEl.innerText = `${selectedImages.length} selected`;

        if (selectedImages.length > 0) {
            if (previewEl) previewEl.innerText = selectedImages.join(", ");
            if (outputEl) outputEl.innerText = `[swiffy-gallery images="${selectedImages.join(", ")}"]`;
        } else {
            if (previewEl) previewEl.innerText = "No images selected.";
            if (outputEl) outputEl.innerText = "[swiffy-gallery images=\"\"]";
        }
    }

    function clearSelection() {
        selectedImages = [];
        document.querySelectorAll(".media-item.selected").forEach(el => el.classList.remove("selected"));
        updateHelper();
    }

    function copyGalleryShortcode() {
        const text = document.getElementById("shortcodeOutput").innerText;
        if (selectedImages.length === 0) {
            alert("Please select at least one image first.");
            return;
        }
        copyText(text);
    }

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
