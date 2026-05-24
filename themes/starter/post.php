<?php $include_part('header'); ?>

<article class="post-full">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="content">
        <?php echo markdown_to_html($post['content']); ?>
    </div>
</article>

<?php
$post_comments_on = $post['comments_on'] ?? true;
$commentics_enabled = $config['commentics_enabled'] ?? false;
if (($config['comments_enabled'] ?? true) && $post_comments_on): ?>
<section class="comments-section" style="margin-top: 30px;">
    <h3>Comments</h3>
    <?php
    if ($commentics_enabled) {
        $cmtx_identifier = $post['slug'];
        $cmtx_reference = $post['title'];
        $cmtx_path = $config['commentics_path'] ?? 'commentics/';
        if (file_exists($cmtx_path . 'frontend/index.php')) {
            include $cmtx_path . 'frontend/index.php';
        }
    } else {
        echo "<p>Built-in comments are enabled. Consider using Commentics for more features.</p>";
    }
    ?>
</section>
<?php endif; ?>

<?php $include_part('footer'); ?>
