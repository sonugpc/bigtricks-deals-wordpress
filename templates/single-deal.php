<?php
/**
 * Template for displaying single deal posts
 *
 * @package Bigtricks_Deals
 */

get_header();

// Get deal data
$post_id = get_the_ID();
$deal_data = [
    'product_name' => get_post_meta($post_id, '_btdeals_product_name', true),
    'offer_url' => get_post_meta($post_id, '_btdeals_offer_url', true),
    'old_price' => floatval(get_post_meta($post_id, '_btdeals_offer_old_price', true)),
    'sale_price' => floatval(get_post_meta($post_id, '_btdeals_offer_sale_price', true)),
    'coupon_code' => get_post_meta($post_id, '_btdeals_coupon_code', true),
    'button_text' => get_post_meta($post_id, '_btdeals_button_text', true),
    'thumbnail_id' => get_post_meta($post_id, '_btdeals_thumbnail_id', true),
    'discount_tag' => get_post_meta($post_id, '_btdeals_discount_tag', true),
    'short_description' => get_post_meta($post_id, '_btdeals_short_description', true),
    'expiry_date' => get_post_meta($post_id, '_btdeals_expiry_date', true),
    'offer_thumbnail_url' => get_post_meta($post_id, '_btdeals_offer_thumbnail_url', true),
    'product_thumbnail_url' => get_post_meta($post_id, '_btdeals_product_thumbnail_url', true),
];

// Calculate discount percentage
$discount_percent = 0;
if ($deal_data['old_price'] > $deal_data['sale_price'] && $deal_data['old_price'] > 0) {
    $discount_percent = round((($deal_data['old_price'] - $deal_data['sale_price']) / $deal_data['old_price']) * 100);
}

// Get store information
$stores = get_the_terms($post_id, 'store');
$store_name = '';
$store_logo = '';
if ($stores && !is_wp_error($stores)) {
    $store = reset($stores);
    $store_name = $store->name;
    $store_logo = get_term_meta($store->term_id, '_btdeals_store_logo', true);
}

// Get product images
$gallery_images = get_post_meta($post_id, '_btdeals_gallery_images', true) ?: [];

// Get disclaimer
$disclaimer = get_post_meta($post_id, '_btdeals_disclaimer', true);

// Fallback values
$title = !empty($deal_data['product_name']) ? $deal_data['product_name'] : get_the_title();
$description = !empty($deal_data['short_description']) ? $deal_data['short_description'] : get_the_excerpt();
$button_text = !empty($deal_data['button_text']) ? $deal_data['button_text'] : 'Get Deal';

// Priority: Offer Thumbnail > Product Thumbnail > Post Thumbnail
$thumbnail_url = '';
if (!empty($deal_data['offer_thumbnail_url'])) {
    $thumbnail_url = $deal_data['offer_thumbnail_url'];
} elseif (!empty($deal_data['product_thumbnail_url'])) {
    $thumbnail_url = $deal_data['product_thumbnail_url'];
} elseif ($deal_data['thumbnail_id']) {
    $thumbnail_url = wp_get_attachment_image_url($deal_data['thumbnail_id'], 'large');
} elseif (has_post_thumbnail()) {
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'large');
}

// Check if deal is expired or has verify label
$is_expired = get_post_meta($post_id, '_btdeals_is_expired', true) === 'on';
$verify_label = get_post_meta($post_id, '_btdeals_verify_label', true);
?>

<main class="bt-single-deal-container">
    <!-- Hero Section -->
    <section class="bt-hero-section">
        <div class="bt-container">
            <div class="bt-hero-grid">
                <!-- Product Images -->
                <div class="bt-product-gallery">
                    <?php if ($thumbnail_url): ?>
                        <div class="bt-main-image">
                            <img src="<?php echo esc_url($thumbnail_url); ?>"
                                 alt="<?php echo esc_attr($title); ?>"
                                 loading="lazy"
                                 width="600"
                                 height="400">
                            <?php if ($discount_percent > 0): ?>
                                <div class="bt-discount-badge">
                                    <?php echo esc_html($discount_percent); ?>% OFF
                                </div>
                            <?php endif; ?>

                            <!-- Expired/Verify Label Flyer -->
                            <?php if ($is_expired || $verify_label): ?>
                                <div class="bt-status-flyer <?php echo $is_expired ? 'bt-expired' : 'bt-verified'; ?>">
                                    <div class="bt-status-content">
                                        <?php if ($is_expired): ?>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                            <span>EXPIRED</span>
                                        <?php elseif ($verify_label): ?>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                            <span><?php echo esc_html($verify_label); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($gallery_images)): ?>
                        <div class="bt-gallery-thumbs">
                            <?php foreach ($gallery_images as $image_id): ?>
                                <?php $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail'); ?>
                                <?php if ($thumb_url): ?>
                                    <img src="<?php echo esc_url($thumb_url); ?>"
                                         alt="<?php echo esc_attr($title); ?>"
                                         loading="lazy"
                                         width="80"
                                         height="80">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Mobile Pricing and Buy Section -->
                    <div class="bt-mobile-pricing-section">
                        <div class="bt-price-row">
                            <?php if ($deal_data['sale_price'] > 0): ?>
                                <span class="bt-sale-price">₹<?php echo number_format($deal_data['sale_price'], 2); ?></span>
                            <?php endif; ?>

                            <?php if ($deal_data['old_price'] > 0): ?>
                                <span class="bt-old-price">₹<?php echo number_format($deal_data['old_price'], 2); ?></span>
                            <?php endif; ?>

                            <?php if ($discount_percent > 0): ?>
                                <span class="bt-discount-percent"><?php echo esc_html($discount_percent); ?>% off</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Mobile Buy Button -->
                    <div class="bt-mobile-buy-section">
                        <?php if ($deal_data['offer_url']): ?>
                            <a href="<?php echo esc_url($deal_data['offer_url']); ?>"
                               class="bt-primary-btn bt-mobile-buy-btn"
                               target="_blank"
                               rel="nofollow noopener">
                                <?php echo esc_html($button_text); ?>
                            </a>
                        <?php endif; ?>

                        <button class="bt-share-trigger bt-mobile-share-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                                <polyline points="16,6 9,12 8,6"/>
                                <line x1="12" y1="2" x2="12" y2="15"/>
                            </svg>
                            Share
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="bt-product-info">
                    <h1 class="bt-product-title"><?php echo esc_html($title); ?></h1>

                    <?php if ($store_name): ?>
                        <div class="bt-store-info">
                            <?php if ($store_logo): ?>
                                <img src="<?php echo esc_url($store_logo); ?>"
                                     alt="<?php echo esc_attr($store_name); ?>"
                                     class="bt-store-logo"
                                     width="32"
                                     height="32">
                            <?php endif; ?>
                            <span class="bt-store-name"><?php echo esc_html($store_name); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Pricing -->
                    <div class="bt-pricing-section">
                        <div class="bt-price-row">
                            <?php if ($deal_data['sale_price'] > 0): ?>
                                <span class="bt-sale-price">₹<?php echo number_format($deal_data['sale_price'], 2); ?></span>
                            <?php endif; ?>

                            <?php if ($deal_data['old_price'] > 0): ?>
                                <span class="bt-old-price">₹<?php echo number_format($deal_data['old_price'], 2); ?></span>
                            <?php endif; ?>

                            <?php if ($discount_percent > 0): ?>
                                <span class="bt-discount-percent"><?php echo esc_html($discount_percent); ?>% off</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($deal_data['expiry_date']): ?>
                            <div class="bt-countdown-timer" data-expiry="<?php echo esc_attr($deal_data['expiry_date']); ?>">
                                <div class="bt-countdown-label">Offer expires in:</div>
                                <div class="bt-countdown-display">
                                    <div class="bt-countdown-item">
                                        <span id="btDays" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label">Days</span>
                                    </div>
                                    <div class="bt-countdown-item">
                                        <span id="btHours" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label">Hours</span>
                                    </div>
                                    <div class="bt-countdown-item">
                                        <span id="btMinutes" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label">Minutes</span>
                                    </div>
                                    <div class="bt-countdown-item">
                                        <span id="btSeconds" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label">Seconds</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bt-action-buttons">
                        <?php if ($deal_data['offer_url']): ?>
                            <a href="<?php echo esc_url($deal_data['offer_url']); ?>"
                               class="bt-primary-btn"
                               target="_blank"
                               rel="nofollow noopener">
                                <?php echo esc_html($button_text); ?>
                            </a>
                        <?php endif; ?>

                        <button class="bt-share-trigger">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                                <polyline points="16,6 9,12 8,6"/>
                                <line x1="12" y1="2" x2="12" y2="15"/>
                            </svg>
                            Share
                        </button>
                    </div>

                    <!-- Coupon Code -->
                    <?php if ($deal_data['coupon_code']): ?>
                        <div class="bt-coupon-section">
                            <div class="bt-coupon-label">Coupon Code:</div>
                            <div class="bt-coupon-code-container">
                                <button class="bt-coupon-reveal" data-coupon="<?php echo esc_attr($deal_data['coupon_code']); ?>">
                                    Click to Reveal Code
                                </button>
                                <div class="bt-coupon-code" style="display: none;">
                                    <?php echo esc_html($deal_data['coupon_code']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="bt-content-section">
        <div class="bt-container">
            <div class="bt-content-grid">
                <!-- Main Content -->
                <div class="bt-main-content">
                    <?php if (get_the_content()): ?>
                        <div class="bt-full-description">
                            <h2>Product Details</h2>
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Product Features/Tags -->
                    <?php
                    $tags = get_the_terms($post_id, 'post_tag');
                    if ($tags && !is_wp_error($tags)): ?>
                        <div class="bt-product-tags">
                            <h3>Tags</h3>
                            <div class="bt-tags-list">
                                <?php foreach ($tags as $tag): ?>
                                    <a href="<?php echo get_term_link($tag); ?>" class="bt-tag">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="bt-sidebar">
                    <!-- Store Info Card -->
                    <?php if ($store_name): ?>
                        <div class="bt-store-card">
                            <h3>Store Information</h3>
                            <div class="bt-store-details">
                                <?php if ($store_logo): ?>
                                    <img src="<?php echo esc_url($store_logo); ?>"
                                         alt="<?php echo esc_attr($store_name); ?>"
                                         class="bt-store-logo-large"
                                         width="64"
                                         height="64">
                                <?php endif; ?>
                                <div class="bt-store-meta">
                                    <h4><?php echo esc_html($store_name); ?></h4>
                                    <p>Reliable store with great deals</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <div class="bt-quick-stats">
                        <h3>Deal Summary</h3>
                        <div class="bt-stats-list">
                            <?php if ($discount_percent > 0): ?>
                                <div class="bt-stat-item">
                                    <span class="bt-stat-label">Discount:</span>
                                    <span class="bt-stat-value"><?php echo esc_html($discount_percent); ?>%</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($deal_data['old_price'] > 0): ?>
                                <div class="bt-stat-item">
                                    <span class="bt-stat-label">You Save:</span>
                                    <span class="bt-stat-value">₹<?php echo number_format($deal_data['old_price'] - $deal_data['sale_price'], 2); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="bt-stat-item">
                                <span class="bt-stat-label">Store:</span>
                                <span class="bt-stat-value"><?php echo esc_html($store_name ?: 'Various'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Deals Section -->
    <section class="bt-similar-deals-section">
        <div class="bt-container">
            <h2>Similar Deals</h2>
            <div class="bt-similar-carousel">
                <div id="btSimilarCarousel" class="bt-carousel-container">
                    <div class="bt-loading-similar">
                        <div class="bt-spinner"></div>
                        <p>Loading similar deals...</p>
                    </div>
                </div>
                <button id="btPrevBtn" class="bt-carousel-nav bt-prev" disabled>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,18 9,12 15,6"/>
                    </svg>
                </button>
                <button id="btNextBtn" class="bt-carousel-nav bt-next" disabled>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"/>
                    </svg>
                </button>
                <div id="btCarouselDots" class="bt-carousel-dots"></div>
            </div>
        </div>
    </section>

    <!-- Disclaimer Section -->
    <?php
    $global_disclaimer = get_option('btdeals_global_disclaimer', '');
    $display_disclaimer = $disclaimer ?: $global_disclaimer;
    if ($display_disclaimer):
    ?>
        <section class="bt-disclaimer-section">
            <div class="bt-container">
                <div class="bt-disclaimer-content">
                    <h3>Important Disclaimer</h3>
                    <div class="bt-disclaimer-text">
                        <?php echo wp_kses_post($display_disclaimer); ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
