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

<?php
$post_comments_on = $post['comments_on'] ?? true;
$commentics_enabled = $config['commentics_enabled'] ?? false;
if (($config['comments_enabled'] ?? true) && $post_comments_on): ?>
    <section class="comments-section">
        <?php
        if ($commentics_enabled) {
            $cmtx_identifier = $post['slug'];
            $cmtx_reference = $post['title'];
            $cmtx_path = $config['commentics_path'] ?? 'commentics/';
            if (file_exists($cmtx_path . 'frontend/index.php')) {
                include $cmtx_path . 'frontend/index.php';
            } else {
                echo "<p style='color:red;'>Commentics not found at: " . htmlspecialchars($cmtx_path) . "</p>";
            }
        } else {
            include __DIR__ . '/../../app/comments.php';
        }
        ?>
    </section>
<?php endif; ?>

<?php $include_part('footer'); ?>
