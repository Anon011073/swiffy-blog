<?php $include_part('header'); ?>

<article class="post-full">
    <header class="post-header">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
            <time><?php echo format_date($post['date']); ?></time>
            <?php
            $t_options = $config['theme_options'] ?? [];
            $show_tax = $t_options['show_tax_meta'] ?? true;
            if ($show_tax): ?>
                <?php if (isset($post['category'])): ?>
                    <span class="meta-sep">&bull;</span>
                    <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
                <?php endif; ?>
                <?php if (!empty($post['tags'])): ?>
                    <div class="post-tags" style="margin-top: 10px;">
                        <?php foreach($post['tags'] as $tag): ?>
                            <span class="tag">#<?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($post['featured_image'])): ?>
        <div class="post-featured-image">
            <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="">
        </div>
    <?php endif; ?>

    <div class="post-content">
        <?php echo markdown_to_html($post['content']); ?>
    </div>

    <?php if ($t_options['show_author_bio'] ?? true):
        $author_name = $config['admin_nickname'] ?? 'Admin';
        $author_avatar = $config['admin_avatar'] ?? '';
        $author_bio = $config['admin_about_me'] ?? '';

        // If post has a custom author from AnonUsers Pro
        if (!empty($post['author'])) {
            $u_file = __DIR__ . '/../../content/users/' . $post['author'] . '.json';
            if (file_exists($u_file)) {
                $u_data = json_decode(file_get_contents($u_file), true);
                $author_name = $u_data['nickname'];
                $author_bio = $u_data['about_me'] ?? '';
                // Avatar for users not implemented yet but could be
            }
        }
    ?>
    <div class="post-author-bio" style="margin-top: 50px; padding: 30px; background: rgba(128,128,128,0.05); border-radius: 12px; display: flex; gap: 25px; align-items: center;">
        <div class="author-avatar" style="width: 80px; height: 80px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; flex-shrink: 0;">👤</div>
        <div class="author-info">
            <h4 style="margin: 0 0 5px;"><?php echo htmlspecialchars($author_name); ?></h4>
            <p style="margin: 0; font-size: 0.95rem; color: #666;"><?php echo nl2br(htmlspecialchars($author_bio)); ?></p>
        </div>
    </div>
    <?php endif; ?>
</article>

<?php $include_part('footer'); ?>
