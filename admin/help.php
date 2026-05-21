<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login();

$config = load_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Documentation - AnonBlog Admin</title>
    <style>
        body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f4f4; }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; overflow-y: auto; }
        .card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); line-height: 1.6; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
        pre { background: #222; color: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 30px; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Help & Documentation</h1>

        <div class="card">
            <h2>🧩 Creating a Plugin</h2>
            <p>Plugins are stored in the <code>/plugins</code> directory. A plugin must have a <code>plugin.php</code> file that returns an array of metadata and hooks.</p>
            <pre>
&lt;?php
return [
    'name' => 'My Cool Plugin',
    'description' => 'Does something awesome.',
    'author' => 'Your Name',
    'version' => '1.0.0',
    'hooks' => [
        'render_content' => function($content) {
            return str_replace('[my-shortcode]', 'Hello World!', $content);
        }
    ]
];
?&gt;</pre>
            <p><strong>Available Hooks:</strong></p>
            <ul>
                <li><code>markdown_pre</code>: Runs before markdown is converted to HTML.</li>
                <li><code>render_content</code>: Runs after HTML generation. Use for shortcodes.</li>
            </ul>

            <h2>🎨 Creating a Theme</h2>
            <p>Themes are stored in <code>/themes</code>. A theme folder should contain:</p>
            <ul>
                <li><code>index.php</code>, <code>post.php</code>, <code>page.php</code> (Required templates)</li>
                <li><code>header.php</code>, <code>footer.php</code> (Recommended partials)</li>
                <li><code>theme-config.php</code> (Optional for custom options)</li>
            </ul>
            <p><strong>Example theme-config.php:</strong></p>
            <pre>
&lt;?php
return [
    'name' => 'My Theme',
    'options' => [
        ['name' => 'accent_color', 'label' => 'Color', 'type' => 'color', 'default' => '#000']
    ]
];
?&gt;</pre>
        </div>
    </div>
</body>
</html>
