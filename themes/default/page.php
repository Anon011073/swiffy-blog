<?php $include_part('header'); ?>

<article class="post-full page-full">
    <header class="post-header">
        <h1 class="post-title"><?php echo htmlspecialchars($page['title']); ?></h1>
    </header>

    <div class="post-content">
        <?php echo markdown_to_html($page['content']); ?>
    </div>
</article>

<?php $include_part('footer'); ?>
