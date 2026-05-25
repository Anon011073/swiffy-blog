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
    <title>Help & Documentation - Swiffy Blog Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 50px; padding: 2rem; }
        .card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); line-height: 1.6; margin-bottom: 2rem; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; font-family: monospace; color: #d63384; }
        pre { background: #1a202c; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto; margin: 15px 0; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 30px; color: var(--primary); }
        .toc { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .toc ul { list-style: none; padding: 0; display: flex; gap: 20px; }
        .toc a { text-decoration: none; font-weight: 600; color: #475569; }
        .toc a:hover { color: var(--primary); }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Help & Documentation</h1>

        <div class="toc">
            <ul>
                <li><a href="#comments">💬 Comments</a></li>
                <li><a href="#plugins">🧩 Plugins</a></li>
                <li><a href="#themes">🎨 Themes</a></li>
                <li><a href="#shortcodes">📝 Shortcodes</a></li>
            </ul>
        </div>

        <div class="card" id="comments">
            <h2>💬 Comment System</h2>
            <p>Swiffy Blog features a built-in JSON-based comment system. You can also integrate Disqus by providing your shortname in the settings.</p>
            <p><strong>Native Comments:</strong> Stored locally in <code>content/comments/</code>. Supports Gravatar and admin labels.</p>
            <p><strong>Disqus:</strong> To use Disqus, create an account at <a href="https://disqus.com" target="_blank">disqus.com</a>, create a "site", and enter your "Shortname" in the <strong>Settings</strong> panel.</p>
        </div>

        <div class="card" id="plugins">
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
        </div>

        <div class="card" id="themes">
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

        <div class="card" id="shortcodes">
            <h2>📝 Standard Shortcodes</h2>
            <ul>
                <li><code>[youtube]https://youtube.com/watch?v=...[/youtube]</code>: Embed a YouTube video.</li>
                <li><code>[swiffy-gallery images="img1.jpg, img2.jpg"]</code>: Display an image gallery.</li>
                <li><code>[sfx-download file="file.zip" label="Download Now"]</code>: Create a secure download button.</li>
            </ul>
        </div>
    </div>
</body>
</html>
