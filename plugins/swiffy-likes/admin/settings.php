<?php
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/functions.php';

require_login('plugins');

$config = load_config();
$likes_config = $config['likes_options'] ?? [
    'icon_set' => 'thumbs',
];

if (isset($_GET['reset'])) {
    if (!verify_csrf_token($_GET['csrf_token'] ?? '')) die('CSRF Failed');
    $file = __DIR__ . '/../../../config/likes.json';
    file_put_contents($file, json_encode([]));
    header("Location: settings.php?success=reset");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF Failed');
    
    $new_options = [
        'icon_set' => $_POST['icon_set']
    ];
    
    update_config(['likes_options' => $new_options]);
    header("Location: settings.php?success=saved");
    die();
}

$file = __DIR__ . '/../../../config/likes.json';
$stats = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Like Stats & Settings - AnonBlog Admin</title>
    <link rel="stylesheet" href="../../../admin/style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 60px; padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stats-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .stats-table th, .stats-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .stats-table th { background: #f8f9fa; }
        .btn-danger { background: #dc3545; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; display: inline-block; border: none; cursor: pointer; }
        select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <?php include "../../../admin/sidebar.php"; ?>
    <div class="main-content">
        <h1>Like/Dislike Plugin</h1>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">Action completed successfully!</div>
        <?php endif; ?>

        <div class="card">
            <h3>Settings</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Icon Set</label>
                    <select name="icon_set">
                        <option value="thumbs" <?php echo ($likes_config['icon_set'] ?? 'thumbs') == 'thumbs' ? 'selected' : ''; ?>>👍 Thumbs Up/Down</option>
                        <option value="hearts" <?php echo ($likes_config['icon_set'] ?? '') == 'hearts' ? 'selected' : ''; ?>>❤️ Hearts / 💔 Broken Heart</option>
                        <option value="stars" <?php echo ($likes_config['icon_set'] ?? '') == 'stars' ? 'selected' : ''; ?>>⭐ Stars</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Post Statistics</h3>
                <a href="settings.php?reset=1&csrf_token=<?php echo get_csrf_token(); ?>" class="btn-danger" onclick="return confirm('Reset all stats?')">Reset All Stats</a>
            </div>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Post Slug</th>
                        <th>Positive</th>
                        <th>Negative</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 20px;">No data yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($stats as $slug => $data): 
                            $total = $data['likes'] + $data['dislikes'];
                            $ratio = $total > 0 ? round(($data['likes'] / $total) * 100) : 0;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($slug); ?></td>
                                <td><?php echo $data['likes']; ?></td>
                                <td><?php echo $data['dislikes']; ?></td>
                                <td><?php echo $ratio; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
