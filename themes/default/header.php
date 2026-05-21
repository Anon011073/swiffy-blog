<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? htmlspecialchars($post['title']) . ' - ' : (isset($page) ? htmlspecialchars($page['title']) . ' - ' : ''); ?><?php echo htmlspecialchars($config['site_name']); ?></title>

    <?php
    $opts = $config['theme_options'] ?? [];
    $body_font = $opts['body_font'] ?? 'Inter';
    $title_font = $opts['title_font'] ?? 'Poppins';
    $fonts_to_load = array_unique([$body_font, $title_font]);
    $google_fonts_url = "https://fonts.googleapis.com/css2?family=" . implode('&family=', array_map(function($f) { return str_replace(' ', '+', $f) . ':wght@400;700'; }, $fonts_to_load)) . "&display=swap";
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?php echo $google_fonts_url; ?>" rel="stylesheet">

    <link rel="stylesheet" href="themes/default/style.css">
    <?php
    $theme_assets = get_plugin_assets();
    foreach ($theme_assets['css'] as $css) echo '<link rel="stylesheet" href="'.$css.'">';
    ?>
    <style>
        :root {
            --accent-color: <?php echo $opts['primary_color'] ?? '#007bff'; ?>;
            --body-font: '<?php echo $body_font; ?>', sans-serif;
            --title-font: '<?php echo $title_font; ?>', sans-serif;
            --body-font-size: <?php echo ($opts['body_font_size'] ?? 16) . 'px'; ?>;
            --title-font-size: <?php echo ($opts['title_font_size'] ?? 32) . 'px'; ?>;
            --container-width: <?php echo ($opts['container_width'] ?? 1100) . 'px'; ?>;
            --sidebar-width: <?php echo ($opts['sidebar_width'] ?? 300) . 'px'; ?>;

            --site-title-size: <?php echo ($opts['site_title_font_size'] ?? 24) . 'px'; ?>;
            --site-title-padding: <?php echo ($opts['site_title_padding'] ?? 10) . 'px'; ?>;
            --site-title-radius: <?php echo ($opts['site_title_border_radius'] ?? 8) . 'px'; ?>;
        }

        .site-title a {
            font-size: var(--site-title-size);
            <?php if (!empty($opts['site_title_border'])): ?>
            border: 2px solid var(--header-text);
            padding: var(--site-title-padding);
            border-radius: var(--site-title-radius);
            display: inline-block;
            <?php endif; ?>

            <?php if (!empty($opts['site_title_underline'])): ?>
            position: relative;
            text-decoration: none;
            <?php endif; ?>
        }

        <?php if (!empty($opts['site_title_underline'])): ?>
        .site-title a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 70%;
            height: 3px;
            background: var(--accent-color);
            transition: width 0.3s;
        }
        .site-title a:hover::after {
            width: 100%;
        }
        <?php endif; ?>

        <?php if ($opts['header_sticky'] ?? false): ?>
        .site-header { position: sticky; top: 0; z-index: 1000; }
        <?php endif; ?>

        <?php if ($opts['header_blur'] ?? false): ?>
        .site-header {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: rgba(51, 51, 51, 0.8) !important;
        }
        .dark .site-header { background: rgba(0, 0, 0, 0.8) !important; }
        <?php endif; ?>

        <?php echo $opts['custom_css'] ?? ''; ?>
        .container { max-width: var(--container-width); }
        .site-sidebar { width: var(--sidebar-width); }
    </style>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <?php
    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['system_header'])) echo $p['hooks']['system_header']();
    }
    ?>
</head>
<?php
$body_classes = [];
if (($opts['sidebar_position'] ?? 'right') === 'left') $body_classes[] = 'sidebar-left';

$is_front_page = !isset($post) && !isset($page);
$template = $opts['front_page_template'] ?? 'default';

if ($is_front_page) {
    // Index page: show sidebar only for 'default' and 'grid_sidebar'
    if ($template !== 'default' && $template !== 'grid_sidebar') {
        $body_classes[] = 'no-sidebar';
    }
} else {
    // Single post/page: show sidebar only if opted in
    if (($opts['single_post_sidebar'] ?? 'yes') === 'no') {
        $body_classes[] = 'no-sidebar';
    }
}
?>
<body class="<?php echo implode(' ', $body_classes); ?>">
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="site-title">
                    <a href="index.php"><?php echo htmlspecialchars($config['site_name']); ?></a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <?php
                        $menu = $config['menu'] ?? [['label' => 'Home', 'url' => 'index.php']];
                        foreach ($menu as $item):
                            $label = $item['label'] ?? $item['title'] ?? 'Link';
                        ?>
                            <li><a href="<?php echo htmlspecialchars($item['url'] ?? '#'); ?>"><?php echo htmlspecialchars($label); ?></a></li>
                        <?php endforeach; ?>
                        <?php if ($config['show_search_menu'] ?? false): ?>
                        <li class="menu-search">
                            <form action="index.php" method="GET">
                                <input type="text" name="s" placeholder="Search..." required>
                                <button type="submit" aria-label="Search">🔍</button>
                            </form>
                        </li>
                        <?php endif; ?>
                        <li class="theme-toggle-li">
                            <button id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode">
                                <span class="light-icon">☀️</span>
                                <span class="dark-icon">🌙</span>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="site-layout">
            <main class="site-main">
