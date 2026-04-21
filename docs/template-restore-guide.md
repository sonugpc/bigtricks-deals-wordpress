# Archive and Single Template Restore Guide

This plugin currently uses theme templates for deal single and archive views.

If you need plugin-managed templates again, use one of the methods below.

## Method 1: Restore From Git History (Recommended)

If these files existed in your repository history, restore them from a previous commit:

```bash
git checkout <commit_sha> -- includes/class-bigtricks-deals.php
git checkout <commit_sha> -- public/class-bigtricks-deals-public.php
git checkout <commit_sha> -- templates/
git checkout <commit_sha> -- public/css/bt-deals-archive.css
git checkout <commit_sha> -- public/css/bt-deals-single.css
git checkout <commit_sha> -- public/js/bt-deals-single.js
```

After restoring files:

1. Confirm hooks are present in `includes/class-bigtricks-deals.php`.
2. Confirm template methods are present in `public/class-bigtricks-deals-public.php`.
3. Visit Settings > Permalinks and click Save once to refresh rewrite rules.

## Method 2: Recreate Manually

### 1) Re-add public hook registrations

In `includes/class-bigtricks-deals.php` inside `define_public_hooks()` add:

```php
$this->loader->add_filter( 'single_template', $plugin_public, 'load_single_deal_template' );
$this->loader->add_filter( 'archive_template', $plugin_public, 'load_deal_archive_template' );
```

### 2) Re-add template loader methods

In `public/class-bigtricks-deals-public.php` re-create these methods:

- `load_single_deal_template( $template )`
- `load_deal_archive_template( $template )`
- `enqueue_single_deal_assets()`

Expected behavior:

1. If `is_singular( 'deal' )`, return plugin template path `templates/single-deal.php` and enqueue single assets.
2. If `is_post_type_archive( 'deal' )`, return plugin template path `templates/archive-deal.php` and enqueue archive assets.

### 3) Recreate template files

Create these files:

- `templates/single-deal.php`
- `templates/archive-deal.php`
- `templates/template-deals-archive.php`
- `templates/template-parts/content-deal.php` (optional, if your template uses it)

### 4) Recreate template-specific assets

Create these assets if your templates reference them:

- `public/css/bt-deals-single.css`
- `public/css/bt-deals-archive.css`
- `public/js/bt-deals-single.js`

Note: Keep `public/css/bt-deals-grid.css` untouched. It is used by shortcode/grid rendering.

### 5) Verify after restore

1. Open a single `deal` post URL and confirm the plugin template is loaded.
2. Open the `deal` archive URL and confirm layout/styles are loaded.
3. Check browser console/network for missing CSS/JS files.
4. Test `[loot-deal]` and `[loot-deals]` shortcodes on a normal page.

## Safer Alternative (No Plugin Template Hooks)

If possible, prefer theme overrides for long-term maintainability:

1. Add `single-deal.php` and `archive-deal.php` to your active theme.
2. Keep plugin focused on data, shortcodes, and helper rendering.
3. Avoid coupling display logic tightly to the plugin lifecycle.
