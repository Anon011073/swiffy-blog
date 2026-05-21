<div class="widget recent-posts-widget">
    <h3 class="widget-title">Recent Posts</h3>
    <ul style="list-style: none; padding: 0;">
        <?php
        $recent = get_posts_paginated(1, 5);
        foreach ($recent as $rp): ?>
            <li style="margin-bottom: 10px;">
                <a href="index.php?post=<?php echo $rp['slug']; ?>" style="color: #ccc;"><?php echo htmlspecialchars($rp['title']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
