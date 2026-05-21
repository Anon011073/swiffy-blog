<?php $include_part('header'); ?>

<div class="posts-list">
    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php
        $t_options = $config['theme_options'] ?? [];
        $img_style = $t_options['featured_img_style'] ?? 'full';
        $titles_only = $t_options['titles_only'] ?? false;

        foreach ($posts as $post): ?>
            <article class="post-entry <?php echo $img_style === 'thumb' ? 'has-thumb' : ''; ?> <?php echo $titles_only ? 'titles-only' : ''; ?>">
                <?php if (!$titles_only && $img_style === 'full' && !empty($post['featured_image'])): ?>
                    <div class="entry-featured-image">
                        <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="">
                    </div>
                <?php endif; ?>

                <div class="entry-content-wrapper">
                    <?php if (!$titles_only && $img_style === 'thumb' && !empty($post['featured_image'])): ?>
                        <div class="entry-thumb">
                            <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="">
                        </div>
                    <?php endif; ?>

                    <div class="entry-text">
                        <header class="entry-header">
                            <h2 class="entry-title"><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                            <div class="entry-meta">
                                <time><?php echo format_date($post['date']); ?></time>
                                <?php
                                if (($t_options['show_tax_meta'] ?? true) && isset($post['category'])): ?>
                                    <span class="meta-sep">&bull;</span>
                                    <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
                                <?php endif; ?>
                            </div>
                        </header>

                        <?php if (!$titles_only): ?>
                        <div class="entry-excerpt">
                            <?php echo htmlspecialchars($post['excerpt'] ?? ''); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?p=<?php echo $current_page - 1; ?>" class="prev">&larr; Newer</a>
                <?php endif; ?>
                <span class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                <?php if ($current_page < $total_pages): ?>
                    <a href="?p=<?php echo $current_page + 1; ?>" class="next">Older &rarr;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $include_part('footer'); ?>
