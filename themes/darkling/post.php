<?php $include_part('header'); ?>

<?php
$img_pos = $post['featured_image_position'] ?? 'top';
$is_admin = isset($_SESSION['admin_logged_in']);
$admin_nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');

function render_native_comments($post, $config, $admin_nickname) {
    if (!($config['comments_enabled'] ?? true)) return;
    require_once __DIR__ . '/../../app/comments.php';
    $comments = get_comments($post['slug']);
    $is_admin = isset($_SESSION['admin_logged_in']);

    if (!empty($comments)):
        foreach ($comments as $comment):
            if (!($comment['approved'] ?? false)) continue;
            $is_comment_admin = ($comment['nickname'] === $admin_nickname);
?>
            <div class="comment" id="comment-<?php echo $comment['id']; ?>" style="display: flex; gap: 15px; margin-bottom: 1.5rem;">
                <?php
                $c_email = $comment['email'] ?? '';
                $c_avatar = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($c_email))) . "?s=48&d=mp";
                if ($comment['nickname'] === $admin_nickname && !empty($config['admin_avatar'])) {
                    $c_avatar = "uploads/" . $config['admin_avatar'];
                }
                ?>
                <img src="<?php echo $c_avatar; ?>" style="width: 48px; height: 48px; border-radius: 4px; object-fit: cover; flex-shrink: 0;">
                <div style="flex: 1;">
                <div class="comment-header">
                    <strong>
                        <?php if ($is_comment_admin): ?>
                            <a href="index.php?page=profile" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($comment['nickname']); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($comment['nickname']); ?>
                        <?php endif; ?>
                    </strong>
                    <span class="comment-date"><?php echo format_date($comment['date']); ?></span>
                </div>
                <div class="comment-body">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </div>
                </div>
            </div>
<?php
        endforeach;
    else:
        echo "<p>No comments yet. Be the first to comment!</p>";
    endif;
?>
    <hr>
    <h4>Leave a Comment</h4>
    <form action="app/comment_submit.php" method="POST" class="comment-form">
        <input type="hidden" name="post_slug" value="<?php echo htmlspecialchars($post['slug']); ?>">
        <?php if ($is_admin): ?>
            <p style="margin-bottom: 1rem;">Posting as <strong><a href="index.php?page=profile" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($admin_nickname); ?></a></strong></p>
            <input type="hidden" name="nickname" value="<?php echo htmlspecialchars($admin_nickname); ?>">
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="nickname">Nickname</label>
                    <input type="text" id="nickname" name="nickname" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="comment_content">Comment</label>
            <textarea id="comment_content" name="content" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Comment</button>
    </form>
<?php
}
?>

<article class="post-full">
    <header class="post-header">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">Published on <?php echo format_date($post['date']); ?></div>
    </header>

    <?php if (!empty($post['featured_image'])): ?>
        <div class="post-featured-image img-top">
            <img src="uploads/<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
        </div>
    <?php endif; ?>

    <div class="post-content">
        <?php echo markdown_to_html($post['content']); ?>
    </div>

    <?php if ($config['show_author_bio'] ?? true): ?>
    <div class="post-author">
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
            <a href="index.php?page=profile"><img src="<?php echo $avatar_url; ?>" alt="<?php echo htmlspecialchars($admin_nickname); ?>" class="author-avatar"></a>
        <?php endif; ?>
        <div class="author-info">
            <span class="written-by">Written by</span>
            <span class="author-name"><a href="index.php?page=profile" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($admin_nickname); ?></a></span>
            <?php if (!empty($config['admin_about_me'])): ?>
                <p class="author-bio"><?php echo nl2br(htmlspecialchars($config['admin_about_me'])); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <section class="comments-section">
        <h3>Comments</h3>
        <?php
        $post_comments_on = $post['comments_on'] ?? true;
        $disqus_shortname = $config['disqus_shortname'] ?? '';

        if ($post_comments_on):
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
                render_native_comments($post, $config, $admin_nickname);
            endif;
        else: ?>
            <p>Comments are closed for this post.</p>
        <?php endif; ?>
    </section>
</article>

<?php $include_part('footer'); ?>
