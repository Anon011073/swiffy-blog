<div class="widget links-widget">
    <h3>Links</h3>
    <?php
    $links = $config['widget_links'] ?? [];
    if (empty($links)):
    ?>
        <p>No links added.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($links as $link): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>">
                        <?php echo htmlspecialchars($link['label']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
