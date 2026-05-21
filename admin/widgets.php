<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('widgets');

$config = load_config();
$error = '';
$success = '';

$available_widgets = [
    'search' => 'Search Bar',
    'recent_posts' => 'Recent Posts',
    'links' => 'Custom Links'
];

$areas = ['sidebar', 'footer1', 'footer2', 'footer3', 'upper', 'lower'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $widget_areas = [];
    foreach ($areas as $area) {
        $widget_areas[$area] = $_POST['area_' . $area] ?? [];
    }

    // Save links widget data
    $links_labels = $_POST['links_label'] ?? [];
    $links_urls = $_POST['links_url'] ?? [];
    $links_data = [];
    for ($i = 0; $i < count($links_labels); $i++) {
        if (!empty($links_labels[$i])) {
            $links_data[] = ['label' => $links_labels[$i], 'url' => $links_urls[$i]];
        }
    }

    if (update_config(['widget_areas' => $widget_areas, 'widget_links' => $links_data])) {
        $success = "Widgets updated successfully.";
        $config = load_config();
    } else {
        $error = "Failed to update widgets.";
    }
}

$widget_areas = $config['widget_areas'] ?? [];
// Ensure all areas exist as arrays to prevent foreach errors
foreach ($areas as $a) {
    if (!isset($widget_areas[$a]) || !is_array($widget_areas[$a])) {
        $widget_areas[$a] = [];
    }
}
$widget_links = $config['widget_links'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Management - Admin Panel</title>
    <style>
        body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar { width: 250px; background: #333; color: #fff; padding: 1rem; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 2rem; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 1rem; }
        .sidebar ul li a { color: #ccc; text-decoration: none; display: block; padding: 0.5rem; border-radius: 4px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #444; color: #fff; }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1rem; }
        .area-box { border: 1px dashed #ccc; padding: 10px; margin-bottom: 10px; min-height: 50px; background: #fafafa; }
        .btn { padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; cursor: pointer; border: none; font-size: 0.9rem; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-danger { background: #d9534f; color: #fff; }
        .error { color: #d9534f; background: #f2dede; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { color: #5cb85c; background: #dff0d8; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .widget-row { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; background: #fff; border: 1px solid #ddd; padding: 5px; border-radius: 3px; }
        .link-row { display: flex; gap: 10px; margin-bottom: 5px; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Widget Management</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <?php
                    $active_theme = $config['theme'] ?? 'default';
                    foreach ($areas as $area):
                        // Show footer areas for Swiffy and Default
                        if (strpos($area, 'footer') === 0 && ($active_theme !== 'default' && $active_theme !== 'swiffy-x')) continue;
                        // Hide upper/lower if default theme or Swiffy
                        if (($active_theme === 'default' || $active_theme === 'swiffy-x') && ($area === 'upper' || $area === 'lower')) continue;
                        // Hide sidebar for themes that don't use it
                        if (($active_theme === 'popeye' || $active_theme === 'darkling') && $area === 'sidebar') continue;
                    ?>
                        <div class="card">
                            <h3><?php echo ucfirst($area); ?> Area</h3>
                            <div class="area-box" id="area-<?php echo $area; ?>">
                                <?php foreach ($widget_areas[$area] as $w): ?>
                                    <div class="widget-row">
                                        <span><?php echo $available_widgets[$w] ?? $w; ?></span>
                                        <input type="hidden" name="area_<?php echo $area; ?>[]" value="<?php echo $w; ?>">
                                        <button type="button" class="btn-danger btn" style="padding: 2px 5px;" onclick="this.parentElement.remove()">X</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <select onchange="if(this.value) addWidget('<?php echo $area; ?>', this.value, this.options[this.selectedIndex].text); this.value=''">
                                <option value="">Add Widget...</option>
                                <?php foreach ($available_widgets as $id => $label): ?>
                                    <option value="<?php echo $id; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h3>Custom Links Widget Settings</h3>
                    <div id="links-container">
                        <?php foreach ($widget_links as $link): ?>
                            <div class="link-row">
                                <input type="text" name="links_label[]" value="<?php echo htmlspecialchars($link['label']); ?>" placeholder="Label">
                                <input type="text" name="links_url[]" value="<?php echo htmlspecialchars($link['url']); ?>" placeholder="URL">
                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">X</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn" onclick="addLink()">Add Link</button>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Save Widget Layout</button>
            </div>
        </form>
    </div>

    <script>
        function addWidget(area, id, label) {
            const container = document.getElementById('area-' + area);
            const div = document.createElement('div');
            div.className = 'widget-row';
            div.innerHTML = `
                <span>${label}</span>
                <input type="hidden" name="area_${area}[]" value="${id}">
                <button type="button" class="btn btn-danger" style="padding: 2px 5px;" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(div);
        }

        function addLink() {
            const container = document.getElementById('links-container');
            const div = document.createElement('div');
            div.className = 'link-row';
            div.innerHTML = `
                <input type="text" name="links_label[]" placeholder="Label">
                <input type="text" name="links_url[]" placeholder="URL">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
