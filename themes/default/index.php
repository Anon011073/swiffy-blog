<?php $include_part('header'); ?>

<?php if (isset($is_search) && $is_search): ?>
    <h1 style="margin-bottom: 30px;">Search Results for: "<?php echo htmlspecialchars($search_query); ?>"</h1>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <p>No posts found.</p>
<?php else: ?>
    <?php
    $opts = $config['theme_options'] ?? [];
    $template = $opts['front_page_template'] ?? 'default';
    $img_pos = $opts['featured_image_position'] ?? 'left';

    // Force 'top' position for grid layout for better aesthetics
    if ($template === 'grid_sidebar') {
        $img_pos = 'top';
    }
    ?>

    <div class="post-list <?php echo 'template-' . $template; ?>">
        <?php foreach ($posts as $post): ?>
            <article class="post-card <?php echo 'img-' . $img_pos; ?>">
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="post-thumbnail">
                        <a href="index.php?post=<?php echo $post['slug']; ?>">
                            <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                        </a>
                    </div>
                <?php endif; ?>
                <div class="post-content">
                    <h2 class="post-title"><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <div class="post-meta">Published on <?php echo format_date($post['date']); ?></div>
                    <div class="post-excerpt">
                        <?php
                        if ($config['show_excerpts'] ?? true) {
                            $excerpt = $post['excerpt'] ?? '';
                            echo nl2br(htmlspecialchars($excerpt));
                        } else {
                            echo markdown_to_html($post['content']);
                        }
                        ?>
                    </div>
                    <a href="index.php?post=<?php echo $post['slug']; ?>" class="read-more">Read More &rarr;</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (isset($total_pages) && $total_pages > 1): ?>
        <nav class="pagination" style="margin-top: 40px; display: flex; gap: 10px;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="index.php?p=<?php echo $i; ?>" class="btn <?php echo $i === $current_page ? 'active' : ''; ?>" style="text-decoration: none; min-width: 35px; text-align: center; <?php echo $i === $current_page ? 'background: var(--accent-color);' : 'background: #eee; color: #333;'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php $include_part('footer'); ?>
