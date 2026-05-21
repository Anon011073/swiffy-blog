# AnonBlog CMS v1.0.0-beta

AnonBlog is a lightweight, fast, and secure flat-file blogging CMS built with vanilla PHP. No database, no heavy frameworks—just pure performance and simplicity.

![Admin Dashboard](docs/screenshots/admin_dashboard.png)

## 🚀 Features

- **Flat-File System**: No MySQL/Database required. Uses JSON for lightning-fast storage.
- **Interactive Installer**: Set up your blog in seconds.
- **Responsive Default Theme**: Beautiful, clean design with built-in Light/Dark mode.
- **Theme Customization**: Change colors, typography (20+ Google Fonts), and layout widths directly from the admin panel.
- **Post & Page Management**: Create and edit content with the Jodit Rich Text Editor.
- **Media Manager**: Easy image uploads and featured image selection.
- **Built-in Comments**: Moderate guest comments or switch to **Disqus** with one click.
- **Widget & Plugin System**: Modular sidebar/footer widgets and simple plugin hooks.
- **Backup & Restore**: Export your entire site to a ZIP file and restore it anytime.
- **Demo Content**: One-click import to populate your site with sample posts and settings.

## 🛠️ Requirements

- **PHP**: 7.4 or higher.
- **Extensions**: `json`, `gd`, `ZipArchive` (for backups).
- **Permissions**: Write access to `content/`, `config/`, `uploads/`, and `plugins/`.

## 📦 Installation

1. Upload the files to your web server (Laragon, XAMPP, or production).
2. Navigate to your site URL (e.g., `http://localhost/install.php`).
3. Follow the on-screen instructions to set your site name and admin credentials.
4. Delete `install.php` after completion.

## 🔮 Planned Features (Premium & Core)

We are constantly evolving AnonBlog. Here is what is on the roadmap:

- **Advanced User Roles & Registration**: A powerful plugin system to handle Admin, Author, and Subscriber roles. Authors can draft posts for admin review, and regular users get a personal profile and comment history.
- **Premium Themes**: More highly optimized and niche-specific templates.
- **Newsletter Plugin**: Simple subscriber management and email notifications for new posts.
- **Advanced SEO Suite**: Sitemaps, OpenGraph meta tags, and schema markup.
- **AnonTax (Categories & Tags)**: Group your posts for better navigation.
- **Multi-language Support**: Translate your blog into multiple languages.

---

*AnonBlog - Keeping blogging simple.*
