<?php
/**
 * Prism Syntax Highlighter for Swiffy Blog
 */

$config = load_config();
$prism_options = $config['prism_options'] ?? ['theme' => 'prism', 'line_numbers' => false, 'copy_button' => false];
$theme = $prism_options['theme'];

// Initialize assets arrays
$css = ["https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/{$theme}.min.css"];
$js = ["https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"];

// Autoloader
$js[] = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js";

// Toolbar MUST be loaded before Copy Button
$js[] = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js";
$css[] = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css";

if ($prism_options['line_numbers']) {
    $css[] = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css";
    $js[] = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js";
}

if ($prism_options['copy_button']) {
    $js[] = "https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js";
}

return [
    'name' => 'Swiffy Prism.js Syntax Highlighter',
    'description' => 'Beautiful syntax highlighting with line numbers and copy button.',
    'author' => 'Swiffy Blog People',
    'version' => '1.2.1',
    'settings_url' => '../plugins/swiffy-prism/admin/settings.php',
    'assets' => [
        'css' => $css,
        'js' => $js
    ],
    'hooks' => [
        'render_content' => function($content) use ($prism_options) {
            // Clean up artifacts from Jodit and apply Prism classes
            $content = preg_replace_callback('/<pre[^>]*>.*?<code([^>]*)>(.*?)<\/code>.*?<\/pre>/is', function($matches) use ($prism_options) {
                $attrs = $matches[1];
                $inner = $matches[2];
                
                $lang = 'language-none';
                if (preg_match('/class=["\'](.*?)["\']/', $attrs, $class_match)) {
                    $classes = explode(' ', $class_match[1]);
                    foreach ($classes as $c) {
                        if (strpos($c, 'language-') === 0) {
                            $lang = $c;
                            break;
                        }
                    }
                }

                $inner = str_replace(['&nbsp;', '<br>', '<br />', '<div>', '</div>'], [" ", "\n", "\n", "", "\n"], $inner);
                $inner = html_entity_decode($inner);
                $inner = htmlspecialchars($inner);
                
                $pre_classes = ($prism_options['line_numbers'] ? 'line-numbers' : '') . ' ' . $lang;
                return "<div class=\"prism-wrapper\" style=\"position:relative;\"><pre class=\"{$pre_classes}\"><code class=\"{$lang}\">$inner</code></pre></div>";
            }, $content);
            return $content;
        }
    ]
];
