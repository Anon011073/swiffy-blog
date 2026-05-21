<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? htmlspecialchars($post['title']) . ' - ' : ''; echo htmlspecialchars($config['site_name']); ?></title>

    <?php
    $opts = $config['theme_options'] ?? [];
    $body_font = $opts['body_font'] ?? 'Inter';
    $title_font = $opts['title_font'] ?? 'Poppins';
    ?>
    <link href="https://fonts.googleapis.com/css2?family=<?php echo urlencode($body_font); ?>:wght@400;700&family=<?php echo urlencode($title_font); ?>:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="themes/darkling/style.css">

    <style>
        :root {
            --site-width: <?php echo $opts['site_width'] ?? 800; ?>px;
            --accent-color: <?php echo $opts['primary_color'] ?? '#3498db'; ?>;
            --body-font: '<?php echo $body_font; ?>', sans-serif;
            --title-font: '<?php echo $title_font; ?>', sans-serif;
        }
    </style>

    <?php
    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['system_header'])) echo $p['hooks']['system_header']();
    }
    ?>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="site-title">
                <a href="index.php"><?php echo htmlspecialchars($config['site_name']); ?></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php
                    $menu = $config['menu'] ?? [['label' => 'Home', 'url' => 'index.php']];
                    foreach ($menu as $item): ?>
                        <li><a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo htmlspecialchars($item['label'] ?? $item['title']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <?php
            $widgets = $config['widget_areas'] ?? [];
            if (!empty($widgets['upper'])): ?>
                <div class="widget-area">
                    <?php foreach ($widgets['upper'] as $w):
                        if (file_exists(__DIR__ . "/widget-{$w}.php")) include __DIR__ . "/widget-{$w}.php";
                    endforeach; ?>
                </div>
            <?php endif; ?>
