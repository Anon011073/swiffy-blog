<div class="widget widget-links">
    <h3>Links</h3>
    <ul>
        <?php
        $links = $config['widget_links'] ?? [];
        foreach ($links as $link):
        ?>
            <li><a href="<?php echo htmlspecialchars($link['url']); ?>"><?php echo htmlspecialchars($link['label']); ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
