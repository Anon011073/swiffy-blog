<?php $include_part('header'); ?>

<?php
$img_pos = $config['featured_image_position'] ?? 'top';
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

    <div class="post-author">
        <?php if (!empty($config['admin_avatar'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($config['admin_avatar']); ?>" alt="<?php echo htmlspecialchars($config['admin_nickname'] ?? 'Admin'); ?>" class="author-avatar">
        <?php endif; ?>
        <div class="author-info">
            <span class="written-by">Written by</span>
            <span class="author-name"><?php echo htmlspecialchars($config['admin_nickname'] ?? 'Admin'); ?></span>
        </div>
    </div>

    <section class="comments-section">
        <h3>Comments</h3>
        <?php
        $disqus_shortname = $config['disqus_shortname'] ?? '';
        $post_comments_on = $post['comments_on'] ?? true;

        if ($disqus_shortname && $post_comments_on): ?>
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
            <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
        <?php else:
            $comments_enabled = ($config['comments_enabled'] ?? true) && $post_comments_on;
            if ($comments_enabled):
                require_once __DIR__ . '/../../app/comments.php';
                $comments = get_comments($post['slug']);

                if (!empty($comments)):
                    foreach ($comments as $comment):
                        if (!($comment['approved'] ?? false)) continue;
            ?>
                        <div class="comment">
                            <div class="comment-header">
                                <strong><?php echo htmlspecialchars($comment['nickname']); ?></strong>
                                <span class="comment-date"><?php echo format_date($comment['date']); ?></span>
                            </div>
                            <div class="comment-body">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
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
                    <div class="form-group">
                        <label for="nickname">Nickname</label>
                        <input type="text" id="nickname" name="nickname" required>
                    </div>
                    <div class="form-group">
                        <label for="comment_content">Comment</label>
                        <textarea id="comment_content" name="content" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Comment</button>
                </form>
            <?php else: ?>
                <p>Comments are closed for this post.</p>
            <?php endif;
        endif; ?>
    </section>
</article>

<?php $include_part('footer'); ?>
