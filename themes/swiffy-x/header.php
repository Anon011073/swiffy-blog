<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? htmlspecialchars($post['title']) . ' - ' : (isset($page) ? htmlspecialchars($page['title']) . ' - ' : ''); ?><?php echo htmlspecialchars($config['site_name']); ?></title>

    <?php
    $opts = $config['theme_options'] ?? [];
    $body_font = !empty($opts['body_font']) ? $opts['body_font'] : 'Inter';
    $title_font = !empty($opts['title_font']) ? $opts['title_font'] : 'Inter';
    $meta_font = !empty($opts['meta_font']) ? $opts['meta_font'] : 'Inter';
    $index_font = !empty($opts['index_font']) ? $opts['index_font'] : 'Inconsolata';
    $excerpt_font = !empty($opts['excerpt_font']) ? $opts['excerpt_font'] : 'Inconsolata';

    $fonts_to_load = array_unique(array_filter([$body_font, $title_font, $meta_font, $index_font, $excerpt_font]));
    $google_fonts_url = "https://fonts.googleapis.com/css2?family=" . implode('&family=', array_map(function($f) { return str_replace(' ', '+', $f) . ':wght@400;500;600;700;800;900'; }, $fonts_to_load)) . "&display=swap";
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?php echo $google_fonts_url; ?>" rel="stylesheet">

    <link rel="stylesheet" href="themes/swiffy-x/style.css">
    <?php
    $theme_assets = get_plugin_assets();
    foreach ($theme_assets['css'] as $css) echo '<link rel="stylesheet" href="'.$css.'">';
    ?>
    <style>
        :root {
            --accent-purple: <?php echo !empty($opts['primary_color']) ? $opts['primary_color'] : '#8b5cf6'; ?>;
            --accent-green: <?php echo !empty($opts['secondary_color']) ? $opts['secondary_color'] : '#22c55e'; ?>;
            --font-sans: '<?php echo $body_font; ?>', system-ui, -apple-system, sans-serif;
            --font-title: '<?php echo $title_font; ?>', sans-serif;
            --font-meta: '<?php echo $meta_font; ?>', sans-serif;
            --font-index: '<?php echo $index_font; ?>', monospace;
            --font-excerpt: '<?php echo $excerpt_font; ?>', sans-serif;

            --site-title-size: <?php echo (!empty($opts['site_title_size']) ? $opts['site_title_size'] : '1.25') . 'rem'; ?>;
            --post-title-size: <?php echo (!empty($opts['post_title_size']) ? $opts['post_title_size'] : '4.0') . 'rem'; ?>;
            --body-text-size: <?php echo (!empty($opts['body_text_size']) ? $opts['body_text_size'] : '1.25') . 'rem'; ?>;

            --container-article: <?php echo (!empty($opts['reading_max_width']) ? $opts['reading_max_width'] : 720) . 'px'; ?>;
            --reading-line-height: <?php echo !empty($opts['reading_line_height']) ? $opts['reading_line_height'] : '1.85'; ?>;
            --feed-card-spacing: <?php echo (!empty($opts['feed_card_spacing']) ? $opts['feed_card_spacing'] : 32) . 'px'; ?>;
        }

        body {
            font-family: var(--font-sans);
            font-size: var(--body-text-size);
        }

        .site-title {
            font-family: var(--font-index);
            font-size: var(--site-title-size);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Mode 1: Index Page overrides */
        .is-index .post-card-title {
            font-family: var(--font-index);
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .is-index .post-card-excerpt {
            font-family: var(--font-excerpt);
            font-weight: 500;
        }
        .is-index .post-card-meta, .is-index .read-more-link {
            font-family: var(--font-meta);
            font-weight: 500;
        }

        /* Mode 2: Article Page */
        .is-single .post-title {
            font-family: var(--font-title);
            font-size: var(--post-title-size);
        }
        .post-meta {
            font-family: var(--font-meta);
        }
        .post-full .post-content {
            font-family: var(--font-sans);
            font-size: var(--body-text-size);
            line-height: var(--reading-line-height);
        }

        <?php echo !empty($opts['custom_css']) ? $opts['custom_css'] : ''; ?>
    </style>
    <?php
    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['system_header'])) echo $p['hooks']['system_header']();
    }
    ?>

    <?php
    if (!function_exists('render_swiffy_taxonomies')) {
        function render_swiffy_taxonomies($post) {
            $categories = array_filter(array_map('trim', explode(',', (string)($post['categories'] ?? ''))));
            $tags = array_filter(array_map('trim', explode(',', (string)($post['tags'] ?? ''))));
            $all = array_merge($categories, $tags);
            if (empty($all)) return;

            $colors = ['pill-purple', 'pill-blue', 'pill-green', 'pill-orange'];
            echo '<div class="taxonomy-pills">';
            foreach ($all as $i => $tax) {
                $color_class = $colors[$i % count($colors)];
                echo '<span class="tax-pill ' . $color_class . '">' . htmlspecialchars($tax) . '</span>';
            }
            echo '</div>';
        }
    }
    ?>
</head>
<body class="<?php echo isset($post) ? 'is-single' : (isset($page) ? 'is-page is-single' : 'is-index'); ?>">
    <header class="site-header">
        <div class="container header-inner">
            <div class="site-title">
                <a href="index.php"><?php echo htmlspecialchars($config['site_name']); ?><span class="slash">/</span>Blog</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php
                    $menu = $config['menu'] ?? [['label' => 'Home', 'url' => 'index.php']];
                    foreach ($menu as $item):
                        $label = $item['label'] ?? $item['title'] ?? 'Link';
                        $url = $item['url'] ?? '#';
                    ?>
                        <li><a href="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($label); ?></a></li>
                    <?php endforeach; ?>

                    <?php if (!isset($opts['show_search_nav']) || $opts['show_search_nav'] == true): ?>
                    <li class="nav-search">
                        <form action="index.php" method="GET" style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border-radius: 20px; padding: 4px 12px; border: 1px solid rgba(255,255,255,0.1);">
                            <input type="text" name="s" placeholder="Search..." required style="background: transparent; border: none; color: white; outline: none; font-size: 0.8rem; width: 80px;">
                            <button type="submit" aria-label="Search" style="background: transparent; border: none; cursor: pointer; font-size: 0.8rem; padding: 0; opacity: 0.7;">🔍</button>
                        </form>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="site-main">
