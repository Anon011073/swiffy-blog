<?php
$comments_limit = $config['recent_comments_limit'] ?? 3;
$show_avatar = $config['show_comment_avatar'] ?? true;
$avatar_size = $config['comment_avatar_size'] ?? 40;
$show_excerpt = $config['show_comment_excerpt'] ?? true;
$excerpt_length = $config['comment_excerpt_length'] ?? 50;
$widget_title = $config['recent_comments_title'] ?? 'Recent Comments';

$disqus_shortname = $config['disqus_shortname'] ?? '';

echo '<div class="widget recent-comments-widget">';
echo '<h3>' . htmlspecialchars($widget_title) . '</h3>';

if (!empty($disqus_shortname)) {
    // Disqus Recent Comments (using their standard widget or a message)
    echo '<div id="recentcomments" class="dsq-widget">';
    echo '<script type="text/javascript" src="https://' . htmlspecialchars($disqus_shortname) . '.disqus.com/recent_comments_widget.js?num_items=' . (int)$comments_limit . '&hide_avatars=' . ($show_avatar ? '0' : '1') . '&avatar_size=' . (int)$avatar_size . '"></script>';
    echo '</div>';
} else {
    // Native Recent Comments
    $all_comments = [];
    $comment_files = glob(__DIR__ . '/../../content/comments/*.json');

    foreach ($comment_files as $file) {
        $post_slug = basename($file, '.json');
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            foreach ($data as $id => $comment) {
                if ($comment['approved'] ?? true) {
                    $comment['post_slug'] = $post_slug;
                    $comment['id'] = $id;
                    $all_comments[] = $comment;
                }
            }
        }
    }

    // Sort by date descending
    usort($all_comments, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    $recent = array_slice($all_comments, 0, $comments_limit);

    if (empty($recent)) {
        echo '<p>No comments yet.</p>';
    } else {
        echo '<ul class="recent-comments-list">';
        foreach ($recent as $c) {
            $name = htmlspecialchars($c['nickname'] ?? 'Anonymous');
            $date = date('M d, Y', strtotime($c['date']));
            $url = "post.php?post=" . urlencode($c['post_slug']) . "#comment-" . $c['id'];

            $avatar_url = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($c['email'] ?? ''))) . "?s=" . $avatar_size . "&d=mp";

            // Check if it's admin
            if ($name === ($config['admin_nickname'] ?? $config['admin_user'])) {
                 if (!empty($config['admin_avatar'])) {
                     $avatar_url = "uploads/" . $config['admin_avatar'];
                 }
            }

            echo '<li class="comment-item">';
            if ($show_avatar) {
                echo '<img src="' . $avatar_url . '" width="' . $avatar_size . '" height="' . $avatar_size . '" class="comment-avatar">';
            }
            echo '<div class="comment-meta">';
            echo '<span class="comment-author">' . $name . '</span> on ';
            echo '<a href="' . $url . '" class="comment-link">' . str_replace('-', ' ', $c['post_slug']) . '</a>';
            echo '<br><small class="comment-date">' . $date . '</small>';

            if ($show_excerpt) {
                $text = strip_tags($c['content']);
                if (strlen($text) > $excerpt_length) {
                    $text = substr($text, 0, $excerpt_length) . '...';
                }
                echo '<p class="comment-excerpt">' . htmlspecialchars($text) . '</p>';
            }
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
    }
}
echo '</div>';
?>

<style>
.recent-comments-list { list-style: none; padding: 0; margin: 0; }
.comment-item { display: flex; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; }
.comment-item:last-child { border-bottom: none; }
.comment-avatar { border-radius: 50%; object-fit: cover; }
.comment-author { font-weight: 700; color: var(--accent-purple); }
.comment-link { color: #fff; text-decoration: none; opacity: 0.8; font-size: 0.9rem; }
.comment-link:hover { opacity: 1; text-decoration: underline; }
.comment-date { color: #888; font-size: 0.75rem; }
.comment-excerpt { margin: 5px 0 0; font-size: 0.85rem; color: #ccc; line-height: 1.4; }
</style>
