<?php $include_part('header'); ?>

<article class="post-full">
    <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">Published on <?php echo format_date($post['date']); ?></div>

    <div class="post-content">
        <?php echo markdown_to_html($post['content']); ?>
    </div>

    <?php
    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['post_footer'])) $p['hooks']['post_footer']($post);
    }
    ?>
</article>

<?php if ($config['comments_enabled'] ?? true): ?>
    <section class="comments-section">
        <?php include __DIR__ . '/../../app/comments.php'; ?>
    </section>
<?php endif; ?>

<?php $include_part('footer'); ?>
