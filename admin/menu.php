<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('menu');

$config = load_config();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $labels = $_POST['label'] ?? [];
    $urls = $_POST['url'] ?? [];
    $menu = [];

    for ($i = 0; $i < count($labels); $i++) {
        if (!empty($labels[$i])) {
            $menu[] = [
                'label' => $labels[$i],
                'url' => $urls[$i]
            ];
        }
    }

    if (update_config(['menu' => $menu])) {
        $success = "Menu updated successfully.";
        $config = load_config();
    } else {
        $error = "Failed to update menu.";
    }
}

$menu = $config['menu'] ?? [
    ['label' => 'Home', 'url' => 'index.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin Panel</title>
    <style>
        body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar { width: 250px; background: #333; color: #fff; padding: 1rem; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 2rem; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 1rem; }
        .sidebar ul li a { color: #ccc; text-decoration: none; display: block; padding: 0.5rem; border-radius: 4px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #444; color: #fff; }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .form-group { display: flex; gap: 10px; margin-bottom: 1rem; align-items: center; }
        input[type="text"] { flex: 1; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; cursor: pointer; border: none; font-size: 1rem; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-danger { background: #d9534f; color: #fff; }
        .error { color: #d9534f; background: #f2dede; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { color: #5cb85c; background: #dff0d8; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Menu Management</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" id="menu-form">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div id="menu-items">
                    <?php foreach ($menu as $item): ?>
                        <div class="form-group">
                            <input type="text" name="label[]" value="<?php echo htmlspecialchars($item['label']); ?>" placeholder="Label">
                            <input type="text" name="url[]" value="<?php echo htmlspecialchars($item['url']); ?>" placeholder="URL (e.g. index.php?page=about)">
                            <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="btn" onclick="addMenuItem()">Add Item</button>
                <hr>
                <button type="submit" class="btn btn-primary">Save Menu</button>
            </form>
        </div>
    </div>

    <script>
        function addMenuItem() {
            const container = document.getElementById('menu-items');
            const div = document.createElement('div');
            div.className = 'form-group';
            div.innerHTML = `
                <input type="text" name="label[]" placeholder="Label">
                <input type="text" name="url[]" placeholder="URL">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Remove</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
