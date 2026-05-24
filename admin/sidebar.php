<?php
require_once __DIR__ . '/../app/functions.php';
$config = load_config();
$admin_base = (strpos($_SERVER['PHP_SELF'], '/plugins/') !== false) ? '../../../admin/' : '';

// Role-based access control for Sidebar
$user_role = 'Admin';
if (isset($_SESSION['swiffy_user'])) {
    $user_role = $_SESSION['swiffy_user']['role'] ?? 'Subscriber';
}
$is_admin = isset($_SESSION['admin_logged_in']);

$nav_avatar_url = '';
if ($is_admin) {
    if ($config['use_gravatar'] ?? false) {
        $email_hash = md5(strtolower(trim($config['admin_email'] ?? '')));
        $nav_avatar_url = "https://www.gravatar.com/avatar/$email_hash?s=64&d=mp";
    } elseif (!empty($config['admin_avatar'])) {
        $nav_avatar_url = $admin_base . "../uploads/" . $config['admin_avatar'];
    }
}
?>
<div style="height: 50px; background: #000; color: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; font-family: sans-serif;">
    <div style="font-weight: bold;"><?php echo htmlspecialchars($config['site_name'] ?? 'Swiffy Blog'); ?> Admin</div>
    <div style="display: flex; gap: 20px; font-size: 0.9rem; align-items: center;">
        <a href="<?php echo $admin_base; ?>../index.php" target="_blank" style="color: #fff; text-decoration: none;">🌐 View Site</a>
        <a href="<?php echo $admin_base; ?>backup.php" style="color: #fff; text-decoration: none;">💾 Backup</a>
        <a href="<?php echo $admin_base; ?>help.php" style="color: #fff; text-decoration: none;">❓ Help</a>
        <?php if ($is_admin): ?>
            <a href="<?php echo $admin_base; ?>profile.php" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <?php if ($nav_avatar_url): ?>
                    <img src="<?php echo $nav_avatar_url; ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    👤
                <?php endif; ?>
                Profile
            </a>
        <?php endif; ?>
        <a href="<?php echo $admin_base; ?>logout.php" style="color: #fff; text-decoration: none;">🚪 Logout</a>
    </div>
</div>
<div class="sidebar" style="background: #222;  flex-shrink: 0; color: #fff; padding: 1rem; position: fixed; top: 50px; left: 0; bottom: 0; z-index: 999; overflow-y: auto;">
    <ul style="list-style: none; padding: 0;">
        <?php if (has_permission('dashboard')): ?>
            <li style=""><a href="<?php echo $admin_base; ?>index.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <?php endif; ?>

        <?php if (has_permission('posts')): ?>
            <li style=""><a href="<?php echo $admin_base; ?>posts.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' || basename($_SERVER['PHP_SELF']) == 'post_edit.php' ? 'active' : ''; ?>">Posts</a></li>
        <?php endif; ?>

        <?php if (has_permission('pages')): ?>
            <li style=""><a href="<?php echo $admin_base; ?>pages.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pages.php' || basename($_SERVER['PHP_SELF']) == 'page_edit.php' ? 'active' : ''; ?>">Pages</a></li>
        <?php endif; ?>

        <?php if (has_permission('media')): ?>
            <li style=""><a href="<?php echo $admin_base; ?>media.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>">Media</a></li>
        <?php endif; ?>

        <?php if (has_permission('comments')): ?>
            <li style=""><a href="<?php echo $admin_base; ?>comments.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>">Comments</a></li>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <?php
            if (isset($config['enabled_plugins']) && in_array('swiffy-users', $config['enabled_plugins'])):
            ?>
                <li style=""><a href="<?php echo $admin_base; ?>../plugins/swiffy-users/admin/manage.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo strpos($_SERVER['PHP_SELF'], 'swiffy-users/admin/manage.php') !== false ? 'active' : ''; ?>">Users</a></li>
            <?php endif; ?>
            <li style=""><a href="<?php echo $admin_base; ?>settings.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">Settings</a></li>
            <li style=""><a href="<?php echo $admin_base; ?>themes.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'themes.php' ? 'active' : ''; ?>">Themes</a></li>
            <li style=""><a href="<?php echo $admin_base; ?>theme_options.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'theme_options.php' ? 'active' : ''; ?>">Theme Options</a></li>
            <li style=""><a href="<?php echo $admin_base; ?>menu.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>">Menu</a></li>
            <li style=""><a href="<?php echo $admin_base; ?>widgets.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'widgets.php' ? 'active' : ''; ?>">Widgets</a></li>
            <li style=""><a href="<?php echo $admin_base; ?>plugins.php" style="color: #ccc; text-decoration: none; display: block;  border-radius: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'plugins.php' ? 'active' : ''; ?>">Plugins</a></li>
        <?php endif; ?>
    </ul>
    <style>
        .sidebar a.active { background: #444; color: #fff !important; }
        .sidebar a:hover { background: #333; color: #fff !important; }
    </style>
    <div style=" font-size: 0.8rem; color: #666; border-top: 1px solid #333; margin-top: 20px;">
        Version: <?php echo SWIFFYBLOG_VERSION; ?>
    </div>
</div>
