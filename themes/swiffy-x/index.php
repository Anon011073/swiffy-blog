<?php $include_part('header'); ?>

<div class="container index-container">
    <?php if (isset($is_search) && $is_search): ?>
        <header style="margin-bottom: var(--space-lg); text-align: center;">
            <h1 style="font-size: 2rem;">Search Results: "<?php echo htmlspecialchars($search_query); ?>"</h1>
        </header>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div style="text-align: center; padding: var(--space-xl) 0;">
            <p style="color: var(--text-secondary); font-size: 1.2rem;">No posts found.</p>
        </div>
    <?php else: ?>
        <div class="post-feed">
            <?php foreach ($posts as $post): ?>
                <a href="index.php?post=<?php echo $post['slug']; ?>" class="post-card">
                    <?php if (!empty($post['featured_image'])): ?>
                        <div class="post-card-image">
                            <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy" style="object-position: <?php echo ($post['featured_image_x'] ?? 50); ?>% <?php echo ($post['featured_image_y'] ?? 50); ?>%;">
                        </div>
                    <?php endif; ?>

                    <div class="post-card-content <?php echo !empty($post['featured_image']) ? 'has-image' : ''; ?>">
                        <div class="post-card-meta">
                            <span class="update-label">Update</span>
                            <span class="date"><?php echo format_date($post['date']); ?></span>
                        </div>

                        <h2 class="post-card-title">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </h2>

                        <?php if (!empty($post['excerpt'])): ?>
                            <div class="post-card-excerpt">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        if ($config['theme_options']['show_taxonomies'] ?? true) {
                            render_swiffy_taxonomies($post);
                        }
                        ?>

                        <div class="post-card-footer">
                            <span class="read-more-link">Open Article →</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?p=<?php echo $i; ?>" class="<?php echo $i === (int)$current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $include_part('footer'); ?>
