<div class="widget widget-recent-posts">
    <h3>Recent Posts</h3>
    <ul>
        <?php
        if (function_exists('get_recent_posts')) {
            $recent_posts = get_recent_posts(5);
            foreach ($recent_posts as $recent):
            ?>
                <li><a href="index.php?post=<?php echo $recent['slug']; ?>"><?php echo htmlspecialchars($recent['title']); ?></a></li>
            <?php
            endforeach;
        }
        ?>
    </ul>
</div>
