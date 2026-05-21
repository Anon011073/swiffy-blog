<div class="widget recent-posts-widget">
    <h3>Recent Posts</h3>
    <?php
    $recent_posts = get_recent_posts($limit ?? 5);
    if (empty($recent_posts)):
    ?>
        <p>No posts yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($recent_posts as $rp): ?>
                <li>
                    <a href="index.php?post=<?php echo $rp['slug']; ?>">
                        <?php echo htmlspecialchars($rp['title']); ?>
                    </a>
                    <br>
                    <small><?php echo format_date($rp['date']); ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
