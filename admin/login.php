<?php
require_once __DIR__ . '/../app/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        redirect('index.php');
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; text-align: center; }
        .error { color: #d9534f; background: #f2dede; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #333; border: none; color: #fff; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #000; }
        .back-link { display: block; margin-top: 1rem; text-align: center; color: #666; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <?php if (load_config()['enabled_plugins'] && in_array('anon-users', load_config()['enabled_plugins'])): ?>
            <p style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
                Don't have an account? <a href="../index.php?page=register" style="color: #007bff;">Register here</a>
            </p>
        <?php endif; ?>
        <a href="../index.php" class="back-link">← Back to Website</a>
    </div>
</body>
</html>
