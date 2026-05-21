<?php $include_part('header'); ?>

<?php if (isset($is_search) && $is_search): ?>
    <h1>Search Results for: "<?php echo htmlspecialchars($search_query); ?>"</h1>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <p>No posts found.</p>
<?php else: ?>
    <?php
    $template = $config['front_page_template'] ?? 'default';
    $img_pos = $config['featured_image_position'] ?? 'top';

    // Grid template forces top image
    if ($template === 'grid') $img_pos = 'top';
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
                            echo nl2br(htmlspecialchars($post['excerpt']));
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
        <nav class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="index.php?p=<?php echo $i; ?>" class="btn <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php $include_part('footer'); ?>
