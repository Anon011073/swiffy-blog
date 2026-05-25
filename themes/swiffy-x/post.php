<?php $include_part('header'); ?>

<?php
$img_pos = $post['featured_image_position'] ?? 'top';
$is_admin = isset($_SESSION['admin_logged_in']);
$admin_nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');

function render_native_comments_sfx($post, $config, $admin_nickname) {
    if (!($config['comments_enabled'] ?? true)) return;
    require_once __DIR__ . '/../../app/comments.php';
    $comments = get_comments($post['slug']);
    $is_admin = isset($_SESSION['admin_logged_in']);

    if (!empty($comments)):
        foreach ($comments as $comment):
            if (!($comment['approved'] ?? false)) continue;
            $is_comment_admin = ($comment['nickname'] === $admin_nickname);
?>
            <div class="comment" id="comment-<?php echo $comment['id']; ?>" style="margin-bottom: var(--space-md); padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--border-color); display: flex; gap: 15px;">
                <?php
                $c_email = $comment['email'] ?? '';
                $c_avatar = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($c_email))) . "?s=48&d=mp";
                if ($comment['nickname'] === $admin_nickname && !empty($config['admin_avatar'])) {
                    $c_avatar = "uploads/" . $config['admin_avatar'];
                }
                ?>
                <img src="<?php echo $c_avatar; ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                <div style="flex: 1;">
                <strong style="color: var(--accent-green); display: block; margin-bottom: 4px;">
                    <?php if ($is_comment_admin): ?>
                        <a href="index.php?page=profile" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($comment['nickname']); ?></a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($comment['nickname'] ?? 'Swiffyymous'); ?>
                    <?php endif; ?>
                </strong>
                <div style="color: var(--text-secondary); font-size: 1rem;"><?php echo nl2br(htmlspecialchars($comment['content'] ?? '')); ?></div>
                </div>
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
            <?php if ($is_admin): ?>
                <p style="color: var(--text-main); margin-bottom: 8px;">Posting as <strong><a href="index.php?page=profile" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($admin_nickname); ?></a></strong></p>
                <input type="hidden" name="nickname" value="<?php echo htmlspecialchars($admin_nickname); ?>">
            <?php else: ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <input type="text" name="nickname" placeholder="Your Name" required style="display: block; width: 100%; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: white; border-radius: 10px; font-family: inherit;">
                    <input type="email" name="email" placeholder="Email Address (Gravatar support)" required style="display: block; width: 100%; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: white; border-radius: 10px; font-family: inherit;">
                </div>
            <?php endif; ?>
            <textarea name="content" placeholder="Join the discussion..." required style="display: block; width: 100%; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: white; min-height: 120px; border-radius: 10px; font-family: inherit; resize: vertical;"></textarea>
            <div>
                <button type="submit" style="background: var(--accent-purple); color: white; border: none; padding: 12px 32px; border-radius: 8px; cursor: pointer; font-weight: 700; transition: opacity 0.3s ease;">Post Comment</button>
            </div>
        </div>
    </form>
<?php
}
?>

<div class="sfx-container sfx-reading-mode">
    <article class="sfx-post-full">
        <header class="sfx-post-header">
            <h1 class="sfx-post-title" style="font-size: <?php echo ($config['theme_options']['post_title_size'] ?? '3.5'); ?>rem;">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>

            <div class="sfx-post-meta">
                <span class="sfx-date"><?php echo strtoupper(format_date($post['date'])); ?></span>
            </div>

            <?php
            if ($config['theme_options']['show_taxonomies'] ?? true) {
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
                    <?php
                    $avatar_url = '';
                    if ($config['use_gravatar'] ?? false) {
                        $email_hash = md5(strtolower(trim($config['admin_email'] ?? '')));
                        $avatar_url = "https://www.gravatar.com/avatar/$email_hash?s=120&d=mp";
                    } elseif (!empty($config['admin_avatar'])) {
                        $avatar_url = "uploads/" . $config['admin_avatar'];
                    }
                    ?>
                    <?php if ($avatar_url): ?>
                        <a href="index.php?page=profile"><img src="<?php echo $avatar_url; ?>" alt="Author" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-purple);"></a>
                    <?php else: ?>
                        <a href="index.php?page=profile" style="text-decoration: none;"><div style="width: 100px; height: 100px; border-radius: 50%; background: var(--accent-purple); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 2rem;"><?php echo substr($admin_nickname, 0, 1); ?></div></a>
                    <?php endif; ?>
                </div>
                <div class="author-info" style="flex: 1;">
                    <h4 style="margin: 0 0 10px 0; font-size: 1.3rem; color: var(--text-main); font-weight: 700;">Written by <a href="index.php?page=profile" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($admin_nickname); ?></a></h4>
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
            render_native_comments_sfx($post, $config, $admin_nickname);
        endif; ?>
    </section>
    <?php endif; ?>
</div>

<?php $include_part('footer'); ?>
