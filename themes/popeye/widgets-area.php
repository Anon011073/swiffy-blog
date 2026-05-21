<?php
$widgets = $config['widgets'] ?? [];
$sidebar_widgets = $widgets['sidebar'] ?? []; // We treat Popeye's horizontal areas as "sidebar" for simplicity or use a specific key

if (!empty($sidebar_widgets)):
?>
<aside class="widgets-container">
    <?php foreach ($sidebar_widgets as $widget_id):
        if (file_exists(__DIR__ . "/widget-{$widget_id}.php")) {
            include __DIR__ . "/widget-{$widget_id}.php";
        }
    endforeach; ?>
</aside>
<?php endif; ?>
