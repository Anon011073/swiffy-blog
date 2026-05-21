<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/posts.php';
require_once __DIR__ . '/../app/functions.php';

require_login('posts');

$posts = get_posts(true); // Include pending for admin
$config = load_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <style>
        body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar { width: 250px; background: #333; color: #fff; padding: 1rem; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 2rem; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 1rem; }
        .sidebar ul li a { color: #ccc; text-decoration: none; display: block; padding: 0.5rem; border-radius: 4px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #444; color: #fff; }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; cursor: pointer; border: none; font-size: 0.9rem; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-danger { background: #d9534f; color: #fff; }
        .card { background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table th, table td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #eee; }
        .actions { display: flex; gap: 0.5rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <div class="header">
            <h1>Posts</h1>
            <a href="post_edit.php" class="btn btn-primary">Create New Post</a>
        </div>

        <div class="card">
            <?php if (empty($posts)): ?>
                <p>No posts found. Create your first post!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['date']); ?></td>
                                <td>
                                    <?php if (isset($post['status']) && $post['status'] === 'pending'): ?>
                                        <span style="color: #f0ad4e;">Pending</span>
                                    <?php else: ?>
                                        <span style="color: #5cb85c;">Published</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="post_edit.php?post=<?php echo $post['slug']; ?>" class="btn btn-primary">Edit</a>
                                    <a href="post_delete.php?slug=<?php echo $post['slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
