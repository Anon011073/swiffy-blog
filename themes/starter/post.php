<?php $include_part('header'); ?>

<article class="post-full">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="content">
        <?php echo markdown_to_html($post['content']); ?>
    </div>
</article>

<?php $include_part('footer'); ?>
