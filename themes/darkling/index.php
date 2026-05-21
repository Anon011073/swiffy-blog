<?php $include_part('header'); ?>

<div class="post-list">
    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <h2 class="post-title"><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                <div class="post-meta">Published on <?php echo format_date($post['date']); ?></div>
                <div class="post-excerpt">
                    <?php echo nl2br(htmlspecialchars($post['excerpt'])); ?>
                </div>
                <div style="margin-top: 15px;">
                    <a href="index.php?post=<?php echo $post['slug']; ?>" class="read-more">Read Full Story &rarr;</a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php $include_part('footer'); ?>
