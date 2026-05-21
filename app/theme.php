<?php
/**
 * Theme and template handling
 */

require_once __DIR__ . '/functions.php';

function render_theme($template, $data = []) {
    $config = load_config();
    $theme = $config['theme'] ?? 'default';
    $theme_path = __DIR__ . '/../themes/' . $theme . '/';

    // Define helper to include parts
    $include_part = function($part, $part_data = []) use ($theme_path, $data, &$include_part) {
        $merged_data = array_merge($data, $part_data);
        $merged_data['include_part'] = $include_part;
        extract($merged_data);
        include $theme_path . $part . '.php';
    };

    if (file_exists($theme_path . $template . '.php')) {
        $data['include_part'] = $include_part;
        extract($data);
        include $theme_path . $template . '.php';
    } else {
        die("Template $template not found in theme $theme.");
    }
}

/**
 * Basic Markdown to HTML converter (Simplified)
 */
function markdown_to_html($content) {
    $plugins_data = get_enabled_plugins_data();

    // 1. Pre-process markdown (hooks that act on raw markdown)
    foreach ($plugins_data as $plugin_data) {
        if (isset($plugin_data['hooks']['markdown_pre'])) {
            $content = $plugin_data['hooks']['markdown_pre']($content);
        }
    }

    // 2. If it contains HTML (likely from Jodit), we skip basic markdown to avoid breaking tags
    // But we still want to support some basic markdown in-between if it is pure markdown
    $is_html = (strpos($content, '<p>') !== false || strpos($content, '<div>') !== false);

    if (!$is_html) {
        $content = htmlspecialchars($content, ENT_NOQUOTES);
        // Bold
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        // Italic
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        // Headers
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
        // Links
        $content = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $content);
        // Line breaks
        $content = nl2br($content);
    }

    // 3. Post-process (hooks for shortcodes, prism, etc)
    foreach ($plugins_data as $plugin_data) {
        if (isset($plugin_data['hooks']['render_content'])) {
            $content = $plugin_data['hooks']['render_content']($content);
        }
    }

    return $content;
}

/**
 * Get plugin assets (CSS/JS)
 */
function get_plugin_assets() {
    $assets = ['css' => [], 'js' => []];
    $plugins_data = get_enabled_plugins_data();

    foreach ($plugins_data as $plugin_data) {
        if (isset($plugin_data['assets']['css'])) {
            foreach ($plugin_data['assets']['css'] as $css) $assets['css'][] = $css;
        }
        if (isset($plugin_data['assets']['js'])) {
            foreach ($plugin_data['assets']['js'] as $js) $assets['js'][] = $js;
        }
    }
    return $assets;
}
