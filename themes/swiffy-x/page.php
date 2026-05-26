<?php $include_part('header'); ?>

<div class="container article-container">
    <article class="post-full">
        <?php if (empty($page['hide_title'])): ?>
        <header>
            <h1 class="post-title"><?php echo htmlspecialchars($page['title']); ?></h1>
        </header>
        <?php endif; ?>

        <?php if (!empty($page['featured_image'])): ?>
            <div class="post-featured-image">
                <img src="uploads/<?php echo htmlspecialchars($page['featured_image']); ?>" alt="<?php echo htmlspecialchars($page['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?php echo markdown_to_html($page['content']); ?>
        </div>
    </article>
</div>

<?php $include_part('footer'); ?>
