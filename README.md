# WordPress MU-Plugins Pack 
**4 lightweight, zero-config must-use plugins for cleaner dashboards and better UX**

Perfect for developers who hate bloat and love control.

---

### Repository Contents

mu-plugins/
├── disable-site-health.php          ← Removes Site Health nag forever
├── shortbread-breadcrumbs.php       ← Custom breadcrumbs with full admin styling panel
├── classic-editor-for-posts-cpts.php   ← Forces Classic Editor on Posts + ALL Custom Post Types
└── classic-editor-mini.php          ← Ultra-minimal version (single line, no comments)


All files are **true MU-plugins** – just drop them in `wp-content/mu-plugins/` and they work instantly. No activation needed.

---

## 1. disable-site-health.php
// Completely removes Site Health from the admin menu and all dashboard notifications

What it does:

Removes "Site Health" from Tools menu
Hides the Site Health widget on Dashboard
Suppresses all critical/notice banners
Zero performance impact

Use case: Agencies & production sites that already monitor health externally.

## 2. shortbread-breadcrumbs.php

// Adds custom breadcrumbs options under Settings > breadcrumbs

Admin settings allow for control of color and padding.

Embedable via shortcode provided in admin settings. 

Full settings page → Settings → Breadcrumbs
Live color pickers (link / hover / text)
Individual padding controls (top / right / bottom / left)
Font size in em (scales perfectly)
One shortcode: [shortbread_breadcrumbs]
Works everywhere: pages, posts, widgets, Elementor, Oxygen, PHP

## 3. Classic Editor For Posts and CPTs

// Activate Classic editor for posts and custom post Types

Gutenberg editor will still be used on all page editors.

Use case: Useful when you just need classic editor options for blog posts, job listings, team member profiles and other post types.

## 4. Classic Editor mini

// Has the same function as 'Classic Editor for Posts and CPTs' except this is the ultra-minimal version

Do not use both classic editor mu-plugins.

## Support
For support, feature requests, or bug reports, please visit the plugin's GitHub repository or WordPress support forums.

## License
This plugin is licensed under the GPL v2 or later.

⭐ Star this repo if you hate dashboard clutter as much as I do
https://github.com/zelkins33/mu-plugin-collection
