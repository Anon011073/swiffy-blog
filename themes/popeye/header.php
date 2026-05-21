<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? htmlspecialchars($post['title']) . ' - ' : (isset($page) ? htmlspecialchars($page['title']) . ' - ' : ''); echo htmlspecialchars($config['site_name']); ?></title>

    <?php
    $options = $config['theme_options'] ?? [];
    $site_width = $options['site_width'] ?? 650;
    $body_font = $options['body_font'] ?? 'Inter';
    $title_font = $options['title_font'] ?? 'Playfair Display';
    $primary_color = $options['primary_color'] ?? '#000000';
    $header_blur = $options['header_blur'] ?? true;
    $header_sticky = $options['header_sticky'] ?? true;
    $line_height = $options['line_height'] ?? 1.6;
    ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?php echo urlencode($body_font); ?>:wght@400;700&family=<?php echo urlencode($title_font); ?>:wght@700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="themes/popeye/style.css">

    <?php
    $assets = get_plugin_assets();
    foreach ($assets['css'] as $css) echo '<link rel="stylesheet" href="'.$css.'">';

    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['system_header'])) echo $p['hooks']['system_header']();
    }
    ?>

    <style>
        :root {
            --site-width: <?php echo $site_width; ?>px;
            --body-font: '<?php echo $body_font; ?>', sans-serif;
            --title-font: '<?php echo $title_font; ?>', serif;
            --primary-color: <?php echo $primary_color; ?>;
            --line-height: <?php echo $line_height; ?>;
        }
        <?php if ($header_sticky): ?>
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        <?php else: ?>
        .site-header { position: static !important; }
        <?php endif; ?>

        <?php if ($header_blur): ?>
        .site-header {
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            background: rgba(255, 255, 255, 0.7) !important;
        }
        [data-theme="dark"] .site-header,
        body[data-theme="dark"] .site-header {
            background: rgba(18, 18, 18, 0.7) !important;
        }
        <?php else: ?>
        .site-header {
            background: var(--bg-color) !important;
        }
        <?php endif; ?>
        <?php echo $options['custom_css'] ?? ''; ?>
    </style>
    <script>
        // Set initial theme correctly from cookie
        (function() {
            const cookieTheme = document.cookie.split('; ').find(row => row.startsWith('dark_mode='));
            if (cookieTheme) {
                const isDark = cookieTheme.split('=')[1] === '1';
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            }
        })();
    </script>
</head>
<body data-theme="<?php echo (isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1') ? 'dark' : 'light'; ?>">
    <header class="site-header">
        <div class="container header-inner">
            <div class="site-branding">
                <a href="index.php" class="site-title"><?php echo htmlspecialchars($config['site_name']); ?></a>
            </div>
            <nav class="site-nav">
                <?php
                $menu = $config['menu'] ?? [['label' => 'Home', 'url' => 'index.php']];
                foreach ($menu as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo htmlspecialchars($item['label'] ?? $item['title']); ?></a>
                <?php endforeach; ?>

                <?php if ($config['show_search_menu'] ?? false): ?>
                    <form action="index.php" method="GET" class="nav-search">
                        <input type="text" name="s" placeholder="Search..." style="background: rgba(128,128,128,0.1); border: none; padding: 5px 10px; border-radius: 4px; color: inherit; font-size: 0.9rem; width: 100px;">
                    </form>
                <?php endif; ?>

                <button id="theme-toggle" class="theme-toggle" title="Toggle Dark Mode">
                    <span class="sun-icon">☀️</span>
                    <span class="moon-icon">🌙</span>
                </button>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <?php
            $widgets = $config['widget_areas'] ?? [];
            if (!empty($widgets['upper'])): ?>
                <div class="widgets-container upper-widgets">
                    <?php foreach ($widgets['upper'] as $w):
                        if (file_exists(__DIR__ . "/widget-{$w}.php")) include __DIR__ . "/widget-{$w}.php";
                    endforeach; ?>
                </div>
            <?php endif; ?>
