<?php $include_part('header'); ?>

<div class="container article-container">
    <?php
    $opts = $config['theme_options'] ?? [];
    ?>
    <article class="post-full">
        <header>
            <div class="post-meta">Build Update — <?php echo format_date($post['date']); ?></div>
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

            <?php
            if ($opts['show_taxonomies'] ?? true) {
                render_swiffy_taxonomies($post);
            }
            ?>

            <?php if (!empty($post['excerpt'])): ?>
                <div class="post-excerpt-lead" style="font-size: 1.25rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.6; max-width: 90%; margin-left: auto; margin-right: auto; margin-top: var(--space-md);">
                    <?php echo htmlspecialchars($post['excerpt']); ?>
                </div>
            <?php endif; ?>
        </header>

        <?php if (!empty($post['featured_image'])): ?>
            <div class="post-featured-image">
                <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="object-position: <?php echo ($post['featured_image_x'] ?? 50); ?>% <?php echo ($post['featured_image_y'] ?? 50); ?>%;">
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?php echo markdown_to_html($post['content']); ?>
        </div>

        <?php if ($config['show_author_bio'] ?? true): ?>
            <div class="author-bio-box" style="margin-top: 60px; padding: 40px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; display: flex; gap: 30px; align-items: center;">
                <div class="author-avatar">
                    <?php if (!empty($config['admin_avatar'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($config['admin_avatar']); ?>" alt="Author" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-purple);">
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--accent-purple); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 2rem;"><?php echo substr($config['admin_nickname'] ?? 'A', 0, 1); ?></div>
                    <?php endif; ?>
                </div>
                <div class="author-info" style="flex: 1;">
                    <h4 style="margin: 0 0 10px 0; font-size: 1.3rem; color: var(--text-main); font-weight: 700;">Written by <?php echo htmlspecialchars($config['admin_nickname'] ?? 'Admin'); ?></h4>
                    <p style="margin: 0; line-height: 1.6; color: var(--text-secondary); font-size: 1.05rem;"><?php echo nl2br(htmlspecialchars($config['admin_about_me'] ?? 'Welcome to my technical blog where I share insights on development and engineering.')); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </article>

    <?php if ($post['comments_on'] ?? true): ?>
    <section class="comments-section" style="margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--border-color);">
        <h3 style="margin-bottom: var(--space-md); font-size: 1.5rem;">Discussion</h3>
        <?php
        $disqus_shortname = $config['disqus_shortname'] ?? '';
        if ($disqus_shortname): ?>
            <div id="disqus_thread"></div>
            <script>
                var disqus_config = function () {
                    this.page.url = "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>";
                    this.page.identifier = "<?php echo htmlspecialchars($post['slug']); ?>";
                };
                (function() {
                    var d = document, s = d.createElement('script');
                    s.src = 'https://<?php echo $disqus_shortname; ?>.disqus.com/embed.js';
                    s.setAttribute('data-timestamp', +new Date());
                    (d.head || d.body).appendChild(s);
                })();
            </script>
        <?php else:
            $comments_enabled = $config['comments_enabled'] ?? true;
            if ($comments_enabled):
                $comments = [];
                if (function_exists('get_comments')) {
                    $comments = get_comments($post['slug']);
                }

                if (!empty($comments)):
                    foreach ($comments as $comment):
                        if (!($comment['approved'] ?? false)) continue;
            ?>
                        <div class="comment" style="margin-bottom: var(--space-md); padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--border-color);">
                            <strong style="color: var(--accent-green); display: block; margin-bottom: 4px;"><?php echo htmlspecialchars($comment['nickname'] ?? 'Swiffyymous'); ?></strong>
                            <div style="color: var(--text-secondary); font-size: 1rem;"><?php echo nl2br(htmlspecialchars($comment['content'] ?? '')); ?></div>
                        </div>
            <?php
                    endforeach;
                else: ?>
                    <p style="color: var(--text-muted); margin-bottom: var(--space-md); font-style: italic;">There are no comments yet. Be the first to join the discussion!</p>
                <?php endif; ?>

                <?php if (!empty($config['comment_rules'])): ?>
                    <div class="comment-rules" style="background: rgba(139, 92, 246, 0.05); border: 1px dashed var(--accent-purple); padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; color: var(--text-secondary);">
                        <strong>Notice:</strong> <?php echo nl2br(htmlspecialchars($config['comment_rules'])); ?>
                    </div>
                <?php endif; ?>

                <form action="app/comment_submit.php" method="POST" style="margin-top: var(--space-lg);">
                    <input type="hidden" name="post_slug" value="<?php echo htmlspecialchars($post['slug']); ?>">
                    <div style="display: grid; gap: 16px;">
                        <input type="text" name="nickname" placeholder="Your Name" required style="display: block; width: 100%; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: white; border-radius: 10px; font-family: inherit;">
                        <textarea name="content" placeholder="Join the discussion..." required style="display: block; width: 100%; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: white; min-height: 120px; border-radius: 10px; font-family: inherit; resize: vertical;"></textarea>
                        <div>
                            <button type="submit" style="background: var(--accent-purple); color: white; border: none; padding: 12px 32px; border-radius: 8px; cursor: pointer; font-weight: 700; transition: opacity 0.3s ease;">Post Comment</button>
                        </div>
                    </div>
                </form>
            <?php endif;
        endif; ?>
    </section>
    <?php endif; ?>
</div>

<?php $include_part('footer'); ?>
