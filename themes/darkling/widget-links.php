<div class="widget links-widget">
    <h3 class="widget-title">Links</h3>
    <ul style="list-style: none; padding: 0;">
        <?php
        $links = $config['widget_links'] ?? [];
        foreach ($links as $link): ?>
            <li style="margin-bottom: 5px;">
                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" style="color: #ccc;"><?php echo htmlspecialchars($link['label']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
