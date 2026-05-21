<?php
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/functions.php';

require_login('plugins');

$config = load_config();
$prism_config = $config['prism_options'] ?? [
    'theme' => 'prism',
    'line_numbers' => false,
    'copy_button' => false
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF Failed');
    
    $new_prism = [
        'theme' => $_POST['theme'],
        'line_numbers' => isset($_POST['line_numbers']),
        'copy_button' => isset($_POST['copy_button'])
    ];
    
    update_config(['prism_options' => $new_prism]);
    header("Location: settings.php?success=1");
    die();
}

$google_fonts = [
    'Inter', 'Poppins', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Oswald', 
    'Raleway', 'PT Sans', 'Merriweather', 'Noto Sans', 'Playfair Display', 
    'Ubuntu', 'Lora', 'Quicksand', 'Fira Sans', 'Work Sans', 'Libre Baskerville', 
    'Josefin Sans', 'Archivo'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prism Settings - Swiffy Blog Admin</title>
    <link rel="stylesheet" href="../../../admin/style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 60px; padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .help-box { background: #e3f2fd; border-left: 5px solid #2196f3; padding: 15px; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        select, .btn { padding: 0.5rem 1rem; border-radius: 4px; border: 1px solid #ddd; }
        .btn-primary { background: #2271b1; color: #fff; border: none; cursor: pointer; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; color: #333; }
    </style>
</head>
<body>
    <?php include "../../../admin/sidebar.php"; ?>
    <div class="main-content">
        <h1>Prism Syntax Highlighter Settings</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">Settings saved!</div>
        <?php endif; ?>

        <div class="help-box">
            <h3>📖 How to use Prism</h3>
            <p>To highlight code in your posts, use the <strong>Source Code</strong> mode in the editor and wrap your code like this:</p>
            <pre><code>&lt;pre&gt;&lt;code class="language-php"&gt;
&lt;?php
echo "Hello World";
?&gt;
&lt;/code&gt;&lt;/pre&gt;</code></pre>
            <p>Common language classes: <code>language-php</code>, <code>language-css</code>, <code>language-javascript</code>, <code>language-markup</code> (for HTML).</p>
        </div>

        <div class="card">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="form-group">
                    <label>Prism Theme</label>
                    <select name="theme" style="width: 100%;">
                        <option value="prism" <?php echo ($prism_config['theme'] ?? 'prism') == 'prism' ? 'selected' : ''; ?>>Default</option>
                        <option value="prism-dark" <?php echo ($prism_config['theme'] ?? '') == 'prism-dark' ? 'selected' : ''; ?>>Dark</option>
                        <option value="prism-okaidia" <?php echo ($prism_config['theme'] ?? '') == 'prism-okaidia' ? 'selected' : ''; ?>>Okaidia (Black)</option>
                        <option value="prism-tomorrow" <?php echo ($prism_config['theme'] ?? '') == 'prism-tomorrow' ? 'selected' : ''; ?>>Tomorrow Night</option>
                        <option value="prism-twilight" <?php echo ($prism_config['theme'] ?? '') == 'prism-twilight' ? 'selected' : ''; ?>>Twilight</option>
                        <option value="prism-solarizedlight" <?php echo ($prism_config['theme'] ?? '') == 'prism-solarizedlight' ? 'selected' : ''; ?>>Solarized Light</option>
                        <option value="prism-coy" <?php echo ($prism_config['theme'] ?? '') == 'prism-coy' ? 'selected' : ''; ?>>Coy</option>
                        <option value="prism-funky" <?php echo ($prism_config['theme'] ?? '') == 'prism-funky' ? 'selected' : ''; ?>>Funky</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="line_numbers" <?php echo ($prism_config['line_numbers'] ?? false) ? 'checked' : ''; ?>> Show Line Numbers</label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="copy_button" <?php echo ($prism_config['copy_button'] ?? false) ? 'checked' : ''; ?>> Enable Copy Button</label>
                </div>
                <button type="submit" class="btn btn-primary">Save Prism Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
