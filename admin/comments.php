<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('comments');

$config = load_config();
$comments_dir = __DIR__ . '/../content/comments/';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF');
    $comment_rules = $_POST['comment_rules'] ?? '';
    if (update_config(['comment_rules' => $comment_rules])) {
        $success = "Comment settings updated.";
        $config = load_config();
    }
}

if (isset($_GET['action']) && isset($_GET['post']) && isset($_GET['id']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $post_slug = basename($_GET['post']);
        $comment_id = $_GET['id'];
        $comment_file = $comments_dir . $post_slug . '.json';

        if (file_exists($comment_file)) {
            $comments = json_decode(file_get_contents($comment_file), true);
            if (isset($comments[$comment_id])) {
                if ($_GET['action'] === 'approve') {
                    $comments[$comment_id]['approved'] = true;
                    $success = "Comment approved.";
                } elseif ($_GET['action'] === 'delete') {
                    unset($comments[$comment_id]);
                    $success = "Comment deleted.";
                }
                file_put_contents($comment_file, json_encode($comments, JSON_PRETTY_PRINT));
            }
        }
    }
}

$all_comments = [];
$comment_files = glob($comments_dir . '*.json');
foreach ($comment_files as $file) {
    $post_slug = basename($file, '.json');
    $comments = json_decode(file_get_contents($file), true);
    if ($comments) {
        foreach ($comments as $id => $comment) {
            $comment['id'] = $id;
            $comment['post_slug'] = $post_slug;
            $all_comments[] = $comment;
        }
    }
}

usort($all_comments, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; }
        table th, table td { text-align: left; padding: 1rem; border-bottom: 1px solid var(--border-color); }
        table th { background: #f8fafc; font-weight: 700; color: #475569; }
        .comment-text { color: #4b5563; font-size: 0.9rem; line-height: 1.5; }
        .status-pending { color: var(--warning); background-color: #fef3c7; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
        .status-approved { color: var(--success); background-color: #f0fdf4; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Comments Management</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>💬 Commenting Rules</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">This text will appear above the comment box on post pages.</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="action" value="update_settings">
                <textarea name="comment_rules" style="width: 100%; height: 80px; padding: 10px; margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 6px;"><?php echo htmlspecialchars($config['comment_rules'] ?? ''); ?></textarea>
                <button type="submit" class="btn btn-primary">Save Rules</button>
            </form>
        </div>

        <div class="card">
            <h3>Recent Comments</h3>
            <?php if (empty($all_comments)): ?>
                <p>No comments found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Author</th>
                            <th>Comment</th>
                            <th>Post</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_comments as $c): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($c['nickname']); ?></td>
                                <td class="comment-text"><?php echo htmlspecialchars(substr($c['content'], 0, 80)) . (strlen($c['content']) > 80 ? '...' : ''); ?></td>
                                <td><a href="../index.php?post=<?php echo $c['post_slug']; ?>" target="_blank" style="color: var(--primary); text-decoration: none;"><?php echo htmlspecialchars($c['post_slug']); ?></a></td>
                                <td>
                                    <?php if ($c['approved'] ?? false): ?>
                                        <span class="status-approved">Approved</span>
                                    <?php else: ?>
                                        <span class="status-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <?php if (!($c['approved'] ?? false)): ?>
                                            <a href="comments.php?action=approve&id=<?php echo $c['id']; ?>&post=<?php echo $c['post_slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-success" style="padding: 4px 8px; font-size: 0.8rem;">Approve</a>
                                        <?php endif; ?>
                                        <a href="comments.php?action=delete&id=<?php echo $c['id']; ?>&post=<?php echo $c['post_slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.8rem;" onclick="return confirm('Delete this comment?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
