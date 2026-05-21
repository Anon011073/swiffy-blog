<?php
/**
 * Plugin Name: SwiffySitemap
 * Description: Generates XML sitemap.
 */

return [
    'name' => 'Swiffy Sitemap',
    'version' => '1.0.0',
    'settings_url' => 'settings.php?plugin=swiffy-sitemap',
    'hooks' => [
        'system_init' => function() {
            if (isset($_GET['sitemap'])) {
                header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                $posts = get_posts();
                foreach($posts as $p) {
                    echo '<url><loc>index.php?post='.htmlspecialchars($p['slug']).'</loc></url>';
                }
                echo '</urlset>';
                exit;
            }
        }
    ]
];
