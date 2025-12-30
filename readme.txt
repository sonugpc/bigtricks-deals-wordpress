=== Bigtricks Deals ===
Contributors: sonugpc
Tags: deals, offers, coupons, products, shortcodes, custom post type
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 2.1.0
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

*   `id`: The ID of the deal post. If not provided, the shortcode will use the current post's ID.

**Display a Specific Deal Field**

You can also use the `[loot-deal]` shortcode to display a specific meta field from a deal.

`[loot-deal id="123" field="_btdeals_offer_url"]`

*   `id`: The ID of the deal post. If not provided, the shortcode will use the current post's ID.
*   `field`: The name of the meta field to display.

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

== API ==

The Bigtricks Deals plugin provides a REST API endpoint for publishing deals.

**Endpoint:** `POST /wp-json/bigtricks-deals/v1/publish`

**Note:** Currently unsecured for testing purposes. Authentication will be added later.

**JSON Payload:**

| Parameter             | Type    | Required | Description                                                                                              |
| --------------------- | ------- | -------- | -------------------------------------------------------------------------------------------------------- |
| `title`               | string  | Yes      | The title of the deal.                                                                                   |
| `slug`                | string  | No       | Custom URL slug for the deal post.                                                                       |
| `offer_url`           | string  | Yes      | The URL for the deal.                                                                                    |
| `content`             | string  | No       | The full description of the deal. Shortcodes are supported.                                              |
| `offer_thumbnail_url` | string  | No       | The URL of the image. Telegram URLs are uploaded to media library; others are saved as external URLs.   |
| `product_name`        | string  | No       | The name of the product.                                                                                 |
| `short_description`   | string  | No       | A short description of the product. HTML is supported.                                                   |
| `product_feature`     | string  | No       | The features of the product. HTML is supported.                                                          |
| `disclaimer`          | string  | No       | A disclaimer for the deal. HTML is supported.                                                            |
| `offer_old_price`     | string  | No       | The original price of the product.                                                                       |
| `offer_sale_price`    | string  | No       | The sale price of the product.                                                                           |
| `coupon_code`         | string  | No       | The coupon code for the deal.                                                                            |
| `expiration_date`     | string  | No       | The expiration date of the deal in `YYYY-MM-DD` format.                                                  |
| `store`               | string  | No       | The name of the store.                                                                                   |
| `categories`          | array   | No       | An array of category names.                                                                              |

**Example cURL Request:**

```bash
curl -X POST http://your-wordpress-site.com/wp-json/bigtricks-deals/v1/publish \
-H "Content-Type: application/json" \
-d '{
  "title": "My Awesome Deal with Categories",
  "content": "This is the full description of my awesome deal. [my_shortcode]",
  "offer_url": "https://example.com/deal",
  "offer_thumbnail_url": "https://example.com/deal-image.jpg",
  "product_name": "Awesome Product",
  "short_description": "<b>A short and catchy description.</b>",
  "product_feature": "<ul><li>Feature 1</li><li>Feature 2</li></ul>",
  "disclaimer": "<p><em>Disclaimer: This is a limited time offer.</em></p>",
  "offer_old_price": "100",
  "offer_sale_price": "50",
  "coupon_code": "SAVE50",
  "expiration_date": "2025-12-31",
  "store": "My Store",
  "categories": ["Electronics", "Gadgets"]
}'
```

== Changelog ==

= 2.1.0 =
* Added optional `slug` parameter to publish API endpoint for custom URL slugs
* Enhanced image handling: Telegram URLs are uploaded to media library, other URLs are saved externally
* Improved title display logic: Post titles now take precedence over product names on single deal pages and archive grids
* Updated API documentation with new parameters and behaviors

= 1.0.0 =
* Initial release.
