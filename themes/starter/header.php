<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($post) ? $post['title'] . ' - ' : ''; ?><?php echo htmlspecialchars($config['site_name']); ?></title>

    <?php
    $heading_font = $config['theme_options']['heading_font'] ?? 'Montserrat';
    ?>
    <link href="https://fonts.googleapis.com/css2?family=<?php echo str_replace(' ', '+', $heading_font); ?>:wght@700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="themes/starter/style.css">

    <style>
        :root {
            --accent-color: <?php echo $config['theme_options']['accent_color'] ?? '#e91e63'; ?>;
            --heading-font: '<?php echo $heading_font; ?>', sans-serif;
        }

        h1, h2, h3 { font-family: var(--heading-font); }
        a { color: var(--accent-color); }
        .btn { background: var(--accent-color); color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
    </style>
    <?php
    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['system_header'])) echo $p['hooks']['system_header']();
    }
    ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1><a href="index.php"><?php echo htmlspecialchars($config['site_name']); ?></a></h1>
            <nav class="main-nav">
                <ul>
                    <?php
                    $menu = $config['menu'] ?? [['label' => 'Home', 'url' => 'index.php']];
                    foreach ($menu as $item):
                        $label = $item['label'] ?? $item['title'] ?? 'Link';
                    ?>
                        <li><a href="<?php echo htmlspecialchars($item['url'] ?? '#'); ?>"><?php echo htmlspecialchars($label); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container site-layout">
        <main class="site-main">
