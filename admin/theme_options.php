<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('theme_options');

$config = load_config();
$error = '';
$success = '';

$current_theme = $config['theme'] ?? 'default';
$theme_config_file = __DIR__ . '/../themes/' . $current_theme . '/theme-config.php';
$theme_meta = file_exists($theme_config_file) ? include $theme_config_file : null;

if (!$theme_meta) {
    die("Theme configuration not found for '{$current_theme}'.");
}

$google_fonts = [
    'Inter', 'Poppins', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Oswald',
    'Raleway', 'PT Sans', 'Merriweather', 'Noto Sans', 'Playfair Display',
    'Ubuntu', 'Lora', 'Quicksand', 'Fira Sans', 'Work Sans', 'Libre Baskerville',
    'Josefin Sans', 'Archivo', 'Inconsolata'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    if (isset($_POST['reset'])) {
        $reset_options = [];
        foreach ($theme_meta['options'] as $opt) {
            $name = $opt['name'];
            $reset_options[$name] = $opt['default'] ?? '';
        }
        update_config(['theme_options' => $reset_options]);
        $success = "Options reset to defaults.";
    } else {
        $new_options = [];
        foreach ($theme_meta['options'] as $opt) {
            $name = $opt['name'];
            if ($opt['type'] === 'checkbox') {
                $new_options[$name] = isset($_POST[$name]);
            } else {
                $new_options[$name] = $_POST[$name] ?? ($opt['default'] ?? '');
            }
        }
        update_config(['theme_options' => $new_options]);
        $success = "Options updated successfully.";
    }
    $config = load_config();
}

$sections = $theme_meta['sections'] ?? ['general' => 'General Settings'];
$active_tab = array_key_first($sections);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Options - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 50px; padding: 20px; }
        .card { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 2rem; border: 1px solid #edf2f7; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 700; color: #2d3748; }
        input[type="text"], input[type="number"], select, input[type="color"], textarea { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; background: #f7fafc; transition: 0.2s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #8b5cf6; background: #fff; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); }
        input[type="color"] { height: 50px; padding: 4px; }

        .tabs { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #edf2f7; padding-bottom: 8px; }
        .tab-link { padding: 10px 20px; border-radius: 8px; color: #718096; text-decoration: none; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .tab-link:hover { background: #f7fafc; color: #2d3748; }
        .tab-link.active { background: #8b5cf6; color: #fff; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .btn-row { display: flex; gap: 15px; margin-top: 30px; border-top: 1px solid #edf2f7; padding-top: 20px; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid transparent; }
        .alert-success { background: #f0fff4; color: #2f855a; border-color: #c6f6d5; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Theme Customization: <?php echo htmlspecialchars($theme_meta['name']); ?></h1>

        <?php if ($success): ?><div class="alert alert-success">✨ <?php echo $success; ?></div><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div class="tabs">
                <?php foreach ($sections as $id => $title): ?>
                    <div class="tab-link <?php echo $id === $active_tab ? 'active' : ''; ?>" onclick="openTab(event, 'tab-<?php echo $id; ?>')"><?php echo htmlspecialchars($title); ?></div>
                <?php endforeach; ?>
            </div>

            <?php foreach ($sections as $id => $title): ?>
                <div id="tab-<?php echo $id; ?>" class="tab-content <?php echo $id === $active_tab ? 'active' : ''; ?>">
                    <div class="card">
                        <h3><?php echo htmlspecialchars($title); ?></h3>
                        <div style="margin-top: 20px;">
                        <?php
                        foreach ($theme_meta['options'] as $opt):
                            if (($opt['section'] ?? 'general') !== $id) continue;

                            $name = $opt['name'];
                            $val = $config['theme_options'][$name] ?? ($opt['default'] ?? '');
                        ?>
                            <div class="form-group">
                                <label><?php echo $opt['label']; ?></label>
                                                                <?php if ($opt['type'] === 'text' || $opt['type'] === 'number'): ?>
                                    <input type="<?php echo $opt['type']; ?>" step="any" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($val); ?>">
                                <?php elseif ($opt['type'] === 'select'): ?>
                                    <select name="<?php echo $name; ?>">
                                        <?php foreach ($opt['options'] as $o_val => $o_label): ?>
                                            <option value="<?php echo $o_val; ?>" <?php echo $val === $o_val ? 'selected' : ''; ?>><?php echo $o_label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($opt['type'] === 'font'): ?>
                                    <select name="<?php echo $name; ?>">
                                        <?php foreach ($google_fonts as $font): ?>
                                            <option value="<?php echo $font; ?>" <?php echo $val === $font ? 'selected' : ''; ?>><?php echo $font; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($opt['type'] === 'range'): ?>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <input type="range" name="<?php echo $name; ?>" id="range-<?php echo $name; ?>" min="<?php echo $opt['min'] ?? 0; ?>" max="<?php echo $opt['max'] ?? 100; ?>" value="<?php echo htmlspecialchars($val); ?>" style="flex: 1;" oninput="document.getElementById('val-<?php echo $name; ?>').innerText = this.value + '%'">
                                        <span id="val-<?php echo $name; ?>" style="width: 50px; font-weight: 700; color: #8b5cf6;"><?php echo htmlspecialchars($val); ?>%</span>
                                    </div>
                                <?php elseif ($opt['type'] === 'color'): ?>
                                    <input type="color" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($val); ?>">
                                <?php elseif ($opt['type'] === 'checkbox'): ?>
                                    <label style="font-weight: 400; display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" name="<?php echo $name; ?>" <?php echo $val ? 'checked' : ''; ?> value="1" style="width: 20px; height: 20px;">
                                        Enable this feature
                                    </label>
                                <?php elseif ($opt['type'] === 'textarea'): ?>
                                    <textarea name="<?php echo $name; ?>" style="height: 150px; font-family: 'Fira Code', monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($val); ?></textarea>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 15px; font-weight: bold;">💾 Save Theme Settings</button>
                <button type="submit" name="reset" class="btn" style="background: #e2e8f0; color: #4a5568; padding: 15px; font-weight: bold;" onclick="return confirm('Reset these options to theme defaults?')">🔄 Reset Defaults</button>
            </div>
        </form>
    </div>

    <script>
        function openTab(evt, tabId) {
            const contents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < contents.length; i++) {
                contents[i].classList.remove("active");
            }
            const links = document.getElementsByClassName("tab-link");
            for (let i = 0; i < links.length; i++) {
                links[i].classList.remove("active");
            }
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>
