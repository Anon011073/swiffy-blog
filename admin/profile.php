<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login();

$config = load_config();
$error = '';
$success = '';
$uploads_dir = __DIR__ . '/../uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');

    $admin_nickname = sanitize($_POST['admin_nickname'] ?? '');
    $admin_email = sanitize($_POST['admin_email'] ?? '');
    $admin_about_me = sanitize($_POST['admin_about_me'] ?? '');
    $use_gravatar = isset($_POST['use_gravatar']);

    $new_config = $config;
    $new_config['admin_nickname'] = $admin_nickname;
    $new_config['admin_email'] = $admin_email;
    $new_config['admin_about_me'] = $admin_about_me;
    $new_config['use_gravatar'] = $use_gravatar;

    // Handle Avatar Upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $filename = 'avatar_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploads_dir . $filename)) {
                $new_config['admin_avatar'] = $filename;
            }
        }
    }

    // Handle Password Reset
    if (!empty($_POST['current_password'])) {
        if (password_verify($_POST['current_password'], $config['admin_pass'])) {
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            if (!empty($new_pass)) {
                if ($new_pass === $confirm_pass) {
                    $new_config['admin_pass'] = password_hash($new_pass, PASSWORD_DEFAULT);
                } else {
                    $error = "New passwords do not match.";
                }
            } else {
                $error = "Please enter a new password.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    } elseif (!empty($_POST['new_password'])) {
        $error = "Please enter your current password to change it.";
    }

    if (empty($error)) {
        if (update_config($new_config)) {
            $success = "Profile updated successfully.";
            $config = load_config();
        } else {
            $error = "Failed to update profile.";
        }
    }
}

$avatar_url = '';
if ($config['use_gravatar'] ?? false) {
    $email_hash = md5(strtolower(trim($config['admin_email'] ?? '')));
    $avatar_url = "https://www.gravatar.com/avatar/$email_hash?s=150&d=mp";
} elseif (!empty($config['admin_avatar'])) {
    $avatar_url = "../uploads/" . $config['admin_avatar'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - Swiffy Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 70px; padding: 2rem; }
        .card { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; border: 1px solid #e2e8f0; }
        .profile-header { display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #edf2f7; }
        .avatar-container { position: relative; width: 120px; height: 120px; }
        .avatar-preview { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #4a5568; }
        input[type="text"], input[type="email"], input[type="password"], textarea {
            width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 1rem;
        }
        .btn { padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 700; cursor: pointer; border: none; transition: 0.2s; }
        .btn-primary { background: #8b5cf6; color: #fff; }
        .btn-primary:hover { background: #7c3aed; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .alert-success { background: #def7ec; color: #03543f; }
        .alert-danger { background: #fde8e8; color: #9b1c1c; }
        .section-title { font-size: 1.25rem; font-weight: 700; margin: 2rem 0 1rem; color: #1a202c; border-left: 4px solid #8b5cf6; padding-left: 1rem; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>👤 Admin Profile</h1>

        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div class="card">
                <div class="profile-header">
                    <div class="avatar-container">
                        <?php if ($avatar_url): ?>
                            <img src="<?php echo $avatar_url; ?>" class="avatar-preview" id="avatarPreview">
                        <?php else: ?>
                            <div class="avatar-preview" style="background:#f7fafc; display:flex; align-items:center; justify-content:center; color:#a0aec0;">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 style="margin:0;"><?php echo htmlspecialchars($config['admin_nickname'] ?? 'Admin'); ?></h2>
                        <p style="color:#718096; margin:5px 0 15px;"><?php echo htmlspecialchars($config['admin_user']); ?></p>
                        <input type="file" name="avatar" id="avatarInput" style="display:none;" onchange="previewImage(this)">
                        <button type="button" class="btn" style="background:#edf2f7; color:#4a5568; font-size:0.875rem;" onclick="document.getElementById('avatarInput').click()">Upload New Photo</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="use_gravatar" <?php echo ($config['use_gravatar'] ?? false) ? 'checked' : ''; ?>>
                        Use Gravatar instead of local upload
                    </label>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group">
                        <label>Nickname</label>
                        <input type="text" name="admin_nickname" value="<?php echo htmlspecialchars($config['admin_nickname'] ?? ''); ?>" placeholder="Used for comments">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="admin_email" value="<?php echo htmlspecialchars($config['admin_email'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Bio / About Me</label>
                    <textarea name="admin_about_me" rows="4"><?php echo htmlspecialchars($config['admin_about_me'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="section-title">🔒 Change Password</div>
            <div class="card">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password to make changes">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password">
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width:100%; padding:1rem;">💾 Save Profile Changes</button>
            </div>
        </form>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>
