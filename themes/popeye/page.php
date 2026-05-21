<?php $include_part('header'); ?>

<article class="page-full">
    <header class="page-header">
        <h1 class="page-title"><?php echo htmlspecialchars($page['title']); ?></h1>
    </header>

    <div class="page-content">
        <?php echo markdown_to_html($page['content']); ?>
    </div>
</article>

<?php $include_part('footer'); ?>
