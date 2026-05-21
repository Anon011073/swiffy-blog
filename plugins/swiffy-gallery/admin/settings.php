<?php
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/functions.php';

require_login('plugins');

$config = load_config();
$swiffy_gallery_options = $config['swiffy_gallery_options'] ?? [
    'layout' => 'grid', // grid or slider
    'columns' => 3,
    'gap' => 15,
    'border_radius' => 8,
    'click_to_zoom' => true
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF Failed');
    
    $new_options = [
        'layout' => $_POST['layout'],
        'columns' => (int)$_POST['columns'],
        'gap' => (int)$_POST['gap'],
        'border_radius' => (int)$_POST['border_radius'],
        'click_to_zoom' => isset($_POST['click_to_zoom'])
    ];
    
    update_config(['swiffy_gallery_options' => $new_options]);
    header("Location: settings.php?success=1");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>swiffy-gallery Settings - Swiffy Blog Admin</title>
    <link rel="stylesheet" href="../../../admin/style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 60px; padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="number"], select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .help-box { background: #e3f2fd; border-left: 5px solid #2196f3; padding: 15px; margin-bottom: 20px; color: #333; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-weight: bold; }
        .btn-primary { background: #2271b1; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include "../../../admin/sidebar.php"; ?>
    <div class="main-content">
        <h1>swiffy-gallery Plugin Settings</h1>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">Settings saved!</div>
        <?php endif; ?>

        <div class="help-box">
            <h3>📖 How to use</h3>
            <p>To add a swiffy-gallery to any post or page, use the following shortcode:</p>
            <p><code>[swiffy-gallery images="image1.jpg, image2.png, photo.webp"]</code></p>
            <p><em>Tip: You can find filenames in the <strong>Media</strong> section. Select images there to generate the code automatically.</em></p>
        </div>

        <div class="card">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                
                <div class="form-group">
                    <label>swiffy-gallery Layout</label>
                    <select name="layout">
                        <option value="grid" <?php echo ($swiffy_gallery_options['layout'] ?? 'grid') == 'grid' ? 'selected' : ''; ?>>Responsive Grid</option>
                        <option value="slider" <?php echo ($swiffy_gallery_options['layout'] ?? '') == 'slider' ? 'selected' : ''; ?>>Horizontal Slider (Arrows)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Columns (Grid Layout only)</label>
                    <select name="columns">
                        <option value="2" <?php echo ($swiffy_gallery_options['columns'] ?? 3) == 2 ? 'selected' : ''; ?>>2 Columns</option>
                        <option value="3" <?php echo ($swiffy_gallery_options['columns'] ?? 3) == 3 ? 'selected' : ''; ?>>3 Columns</option>
                        <option value="4" <?php echo ($swiffy_gallery_options['columns'] ?? 3) == 4 ? 'selected' : ''; ?>>4 Columns</option>
                        <option value="5" <?php echo ($swiffy_gallery_options['columns'] ?? 3) == 5 ? 'selected' : ''; ?>>5 Columns</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Gap between images (Pixels)</label>
                    <input type="number" name="gap" value="<?php echo $swiffy_gallery_options['gap'] ?? 15; ?>">
                </div>

                <div class="form-group">
                    <label>Border Radius (Pixels)</label>
                    <input type="number" name="border_radius" value="<?php echo $swiffy_gallery_options['border_radius'] ?? 8; ?>">
                </div>

                <div class="form-group">
                    <label><input type="checkbox" name="click_to_zoom" <?php echo ($swiffy_gallery_options['click_to_zoom'] ?? true) ? 'checked' : ''; ?>> Enable click to view full image</label>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
