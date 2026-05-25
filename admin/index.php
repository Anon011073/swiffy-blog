<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('dashboard');

$config = load_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Swiffy Blog Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card h2 { margin-top: 0; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .news-item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f9f9f9; }
        .news-item:last-child { border-bottom: none; }
        .news-date { font-size: 0.8rem; color: #888; }
        .btn-small { padding: 5px 10px; font-size: 0.8rem; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; }
        .marketplace-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Dashboard</h1>

        <div class="dashboard-grid">
            <!-- Section 1: News & Updates -->
            <div class="card">
                <h2>📢 News & Updates</h2>
                <div id="news-feed">
                    <div class="news-item">
                        <div class="news-date">April 28, 2024</div>
                        <strong>Swiffy Blog v1.0.0-beta Released!</strong>
                        <p>Welcome to the first beta release of Swiffy Blog. Explore the new features and start blogging!</p>
                    </div>
                    <div class="news-item">
                        <div class="news-date">April 27, 2024</div>
                        <strong>Security Best Practices</strong>
                        <p>Remember to delete your <code>install.php</code> file after setup to keep your site secure.</p>
                    </div>
                </div>
                <p style="font-size: 0.9rem; color: #666;"><em>Live feed integration coming soon...</em></p>
            </div>

            <!-- Section 2: Themes Marketplace -->
            <div class="card">
                <h2>🎨 Themes Marketplace</h2>
                <div class="marketplace-item">
                    <span>Default Pro (Premium)</span>
                    <a href="#" class="btn-small">View</a>
                </div>
                <div class="marketplace-item">
                    <span>Minimalist Dark</span>
                    <a href="#" class="btn-small">Free</a>
                </div>
                <div class="marketplace-item">
                    <span>Magazine Grid</span>
                    <a href="#" class="btn-small">View</a>
                </div>
                <hr>
                <a href="#" style="font-size: 0.9rem;">Browse all themes →</a>
            </div>

            <!-- Section 3: Plugins Marketplace -->
            <div class="card">
                <h2>🧩 Featured Plugins</h2>
                <div class="marketplace-item">
                    <span>User Roles & Registration</span>
                    <span style="font-size: 0.8rem; color: #28a745;">Coming Soon</span>
                </div>
                <div class="marketplace-item">
                    <span>Advanced SEO Pack</span>
                    <a href="#" class="btn-small">Premium</a>
                </div>
                <div class="marketplace-item">
                    <span>Newsletter Pro</span>
                    <a href="#" class="btn-small">Premium</a>
                </div>
                <hr>
                <a href="#" style="font-size: 0.9rem;">Browse all plugins →</a>
            </div>
        </div>
    </div>
</body>
</html>
