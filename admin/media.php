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
        }
        .main-content {
            margin-left: 310px;
            margin-top: 50px;
            padding: 2rem;
        }

        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

        .view-controls { display: flex; gap: 10px; }
        .view-btn { background: #fff; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s; font-size: 0.9rem; }
        .view-btn.active { background: var(--accent-purple); color: #fff; border-color: var(--accent-purple); }

        .upload-area {
            border: 2px dashed #cbd5e1;
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            transition: 0.3s;
            cursor: pointer;
            background: #f8fafc;
        }
        .upload-area:hover { border-color: var(--accent-purple); background: #f0f4ff; }

        /* Grid View */
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-top: 2rem; }
        .media-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; position: relative; transition: 0.3s; cursor: pointer; }
        .media-item:hover { transform: translateY(-3px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .media-item.selected {
            border: 2px solid var(--accent-purple);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
            transform: scale(0.98);
        }
        .media-item.selected::after {
            content: '✓';
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--accent-purple);
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .preview-box { width: 100%; aspect-ratio: 1/1; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .file-ext-icon { font-size: 1.5rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; }

        .item-info { padding: 8px; border-top: 1px solid #f1f5f9; }
        .item-name { display: block; font-size: 0.75rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .actions { position: absolute; top: 5px; right: 5px; display: flex; gap: 4px; opacity: 0; transition: 0.2s; }
        .media-item:hover .actions { opacity: 1; }
        .action-btn { background: #fff; border: 1px solid #eee; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 0.8rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .action-btn:hover { color: var(--accent-purple); }
        .btn-del:hover { color: #ef4444; }

        /* List View */
        .media-list { display: flex; flex-direction: column; gap: 8px; margin-top: 2rem; }
        .media-list .media-item { display: flex; align-items: center; padding: 8px 12px; }
        .media-list .preview-box { width: 50px; height: 50px; aspect-ratio: 1/1; border-radius: 4px; margin-right: 15px; }
        .media-list .item-info { border: none; padding: 0; flex: 1; display: flex; align-items: center; justify-content: space-between; }
        .media-list .item-name { font-size: 0.9rem; }
        .media-list .item-meta { font-size: 0.8rem; color: #64748b; margin-left: 20px; }
        .media-list .actions { position: static; opacity: 1; margin-left: 20px; }

        /* Shortcode Helper - Refined */
        .helper-card {
            margin-top: 30px;
            border-top: 4px solid var(--accent-purple);
            background: #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
        }
        .helper-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .helper-header h3 { margin: 0; font-size: 1.1rem; color: #1e293b; }
        .helper-badge { background: #f5f3ff; color: var(--accent-purple); padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; border: 1px solid #ddd6fe; }
        .helper-preview {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            min-height: 40px;
            color: #475569;
            font-size: 0.85rem;
            line-height: 1.5;
        }
        .shortcode-box {
            background: #0f172a;
            color: #fff;
            padding: 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .shortcode-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-family: 'Cascadia Code', 'Fira Code', monospace;
            font-size: 0.95rem;
            color: #e2e8f0;
        }
        .helper-btns { display: flex; gap: 10px; }
        .helper-btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; transition: 0.2s; font-size: 0.85rem; }
        .btn-copy { background: var(--accent-green); color: #fff; }
        .btn-copy:hover { opacity: 0.9; }
        .btn-clear { background: #64748b; color: #fff; }
        .btn-clear:hover { background: #475569; }

        .error { background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .success { background: #f0fdf4; border: 1px solid #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <div class="header-row">
            <h1>Media Management</h1>
            <div class="view-controls">
                <button class="view-btn active" id="gridBtn" onclick="setView('grid')">Grid</button>
                <button class="view-btn" id="listBtn" onclick="setView('list')">List</button>
            </div>
        </div>

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
                    <div style="font-size: 1.5rem; margin-bottom: 5px;">☁️</div>
                    <strong>Click or Drag to Upload</strong>
                </div>
            </form>
        </div>

        <div class="media-grid" id="mediaContainer">
            <?php if (empty($items)): ?>
                <p style="text-align: center; color: #94a3b8; grid-column: 1 / -1; padding: 40px;">No items found.</p>
            <?php else: ?>
                <?php foreach ($items as $item):
                    $name = basename($item);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $size = format_size(filesize($item));
                    $date = date('M d, Y', filemtime($item));
                ?>
                    <div class="media-item" onclick="toggleSelect(this, '<?php echo addslashes($name); ?>')">
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
                            <div class="item-meta" style="display: none;">
                                <span><?php echo $size; ?></span> • <span><?php echo $date; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($tab === "images" && $gallery_enabled): ?>
        <div class="card helper-card">
            <div class="helper-header">
                <h3>🖼️ Gallery Shortcode Helper</h3>
                <span class="helper-badge" id="selectedCount">0 selected</span>
            </div>
            <div class="helper-preview" id="imagePreviewNames">No images selected.</div>
            <div class="shortcode-box">
                <div class="shortcode-text" id="shortcodeOutput">[swiffy-gallery images=""]</div>
                <div class="helper-btns">
                    <button class="helper-btn btn-copy" onclick="copyGalleryShortcode()">Copy</button>
                    <button class="helper-btn btn-clear" onclick="clearSelection()">Clear</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    let selectedImages = [];
    let currentView = 'grid';

    function setView(view) {
        currentView = view;
        const container = document.getElementById('mediaContainer');
        const gridBtn = document.getElementById('gridBtn');
        const listBtn = document.getElementById('listBtn');

        if (view === 'grid') {
            container.className = 'media-grid';
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');
            document.querySelectorAll('.item-meta').forEach(el => el.style.display = 'none');
        } else {
            container.className = 'media-list';
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
            document.querySelectorAll('.item-meta').forEach(el => el.style.display = 'block');
        }
    }

    function toggleSelect(el, name) {
        <?php if ($tab === "images" && $gallery_enabled): ?>
        const index = selectedImages.indexOf(name);
        if (index === -1) {
            selectedImages.push(name);
            el.classList.add("selected");
        } else {
            selectedImages.splice(index, 1);
            el.classList.remove("selected");
        }
        updateHelper();
        <?php endif; ?>
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
        alert('Copied to clipboard!');
    }
    </script>
</body>
</html>
