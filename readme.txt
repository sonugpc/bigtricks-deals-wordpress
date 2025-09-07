=== Bigtricks Deals ===
Contributors: sonugpc
Tags: deals, offers, coupons, products, shortcodes, custom post type
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful plugin to manage and display deals, offers, and coupons on your WordPress site.

== Description ==

The Bigtricks Deals plugin provides a comprehensive solution for creating, managing, and displaying deals on your website. It features a "Deal" custom post type with a wide range of custom fields to capture all the necessary information about your offers.

**Key Features:**

*   **Deal Custom Post Type:** A dedicated post type for managing your deals.
*   **Store Taxonomy:** Organize your deals by store.
*   **Comprehensive Deal Fields:** Includes fields for offer URL, product name, short description, disclaimer, old price, sale price, coupon code, expiration date, and more.
*   **Media Library Integration:** Easily upload thumbnails and brand logos for your deals.
*   **Minimalistic Single Deal Page:** A clean, modern, and responsive design for single deal pages.
*   **AJAX-Powered Similar Deals Carousel:** Display similar deals in a touch-friendly carousel.
*   **Social Sharing:** Allow users to share deals on Facebook, Twitter, and WhatsApp.
*   **Shortcodes:** Display single deals or a grid of deals anywhere on your site.
*   **Optimized for Performance:** Efficiently loads assets and follows WordPress best practices.
*   **SEO Friendly:** Integrates with Rank Math and includes Product Schema markup.

== Installation ==

1.  Upload the `bigtricks-deals-wordpress` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Start adding deals by going to "Deals" > "Add New Deal" in the WordPress admin menu.

== Shortcodes ==

**Display a Single Deal**

Use the `[loot-deal]` shortcode to display a single deal box.

`[loot-deal id="123"]`

*   `id`: The ID of the deal post.

**Display a Grid of Deals**

Use the `[loot-deals]` shortcode to display a grid of deals with an AJAX "Load More" button.

`[loot-deals]`

You can also use the following attributes to filter the deals:

*   `category`: The ID of the category to display deals from. You can pass multiple category IDs separated by a comma.
*   `store`: The ID of the store to display deals from. You can pass multiple store IDs separated by a comma.
*   `count`: The number of deals to display per page.

Example:

`[loot-deals category="1,2" store="3" count="6"]`

== Frequently Asked Questions ==

= How do I add a new deal? =

Go to "Deals" > "Add New Deal" in the WordPress admin menu.

= How do I display a single deal on a page? =

Use the `[loot-deal id="123"]` shortcode, replacing "123" with the ID of your deal post.

= How do I display a grid of deals? =

Use the `[loot-deals]` shortcode. You can also use the `category`, `store`, and `count` attributes to customize the grid.

== Changelog ==

= 1.0.0 =
* Initial release.
