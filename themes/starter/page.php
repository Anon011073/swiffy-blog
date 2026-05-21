<?php $include_part('header'); ?>

<main class="starter-main">
    <div class="starter-container">
        <article class="starter-page">
            <h1 class="starter-page-title"><?php echo htmlspecialchars($page['title']); ?></h1>
            <div class="starter-page-content">
                <?php echo markdown_to_html($page['content']); ?>
            </div>
        </article>
    </div>
</main>

<?php $include_part('footer'); ?>
