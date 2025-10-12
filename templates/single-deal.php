<?php
/**
 * Template for displaying single deal posts.
 *
 * @package Bigtricks_Deals
 */

// Ensure the helper class is loaded.
if ( ! class_exists( 'Bigtricks_Deals_Content_Helper' ) ) {
    require_once plugin_dir_path( __FILE__ ) . '../includes/class-bigtricks-deals-content-helper.php';
}

get_header();

// Ensure grid styles are loaded for similar deals section
Bigtricks_Deals_Content_Helper::ensure_grid_styles_loaded();

$post_id   = get_the_ID();
$deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( $post_id );

// Extract variables for easier use in the template
extract( $deal_data );

?>

<main id="main" class="bt-single-deal-container" role="main">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'bt-deal-article' ); ?>>
    <!-- Hero Section -->
    <section class="bt-hero-section bt-hero-compact">
        <div class="bt-container">
            <div class="bt-hero-grid">
                <!-- Product Images -->
                <div class="bt-product-gallery">
                    <?php if ( $thumbnail_url ) : ?>
                        <div class="bt-main-image">
                            <img src="<?php echo esc_url( $thumbnail_url ); ?>"
                                 alt="<?php echo esc_attr( $title ); ?>"
                                 loading="eager"
                                 width="500"
                                 height="300">
                            <?php if ( $coupon_code ) : ?>
                                <div class="bt-discount-badge">
                                    <?php echo esc_html( $coupon_code ); ?>
                                </div>
                            <?php elseif ( $discount_percent > 0 ) : ?>
                                <div class="bt-discount-badge">
                                    <?php echo esc_html( $discount_percent ); ?>% OFF
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $discount_tag ) ) : ?>
                                <div class="bt-product-tag">
                                    <?php echo esc_html( $discount_tag ); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Expired/Verify Label Flyer -->
                            <?php if ( $is_expired || $verify_label ) : ?>
                                <div class="bt-status-flyer <?php echo $is_expired ? 'bt-expired' : 'bt-verified'; ?>">
                                    <div class="bt-status-content">
                                        <?php if ( $is_expired ) : ?>
                                            <i class="rbi rbi-close-circle-line"></i>
                                            <span><?php esc_html_e( 'EXPIRED', 'bigtricks-deals' ); ?></span>
                                        <?php elseif ( $verify_label ) : ?>
                                            <i class="rbi rbi-check-circle-line"></i>
                                            <span><?php echo esc_html( $verify_label ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $gallery_images ) ) : ?>
                        <div class="bt-gallery-thumbs">
                            <?php foreach ( $gallery_images as $image_id ) : ?>
                                <?php $thumb_url = wp_get_attachment_image_url( $image_id, 'thumbnail' ); ?>
                                <?php if ( $thumb_url ) : ?>
                                    <img src="<?php echo esc_url( $thumb_url ); ?>"
                                         alt="<?php echo esc_attr( $title ); ?>"
                                         loading="lazy"
                                         width="80"
                                         height="80">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Product Info -->
                <div class="bt-product-info">
                    <h1 class="bt-product-title"><?php echo esc_html( $title ); ?></h1>

                    <!-- Deal Posted Time -->
                    <div class="bt-deal-posted-time">
                        <?php
                        $post_date = get_the_date( 'D, M j, Y g:i A' );
                        echo esc_html( sprintf( __( 'Posted on %s', 'bigtricks-deals' ), $post_date ) );
                        ?>
                    </div>

                    <?php if ( $store_name && $store_url ) : ?>
                        <a href="<?php echo esc_url( $store_url ); ?>" class="bt-store-info-link">
                            <div class="bt-store-info">
                                <?php if ( $store_logo ) : ?>
                                    <img src="<?php echo esc_url( $store_logo ); ?>"
                                         alt="<?php echo esc_attr( $store_name ); ?>"
                                         class="bt-store-logo"
                                         width="32"
                                         height="32">
                                <?php endif; ?>
                                <span class="bt-store-name"><?php echo esc_html( $store_name ); ?></span>
                            </div>
                        </a>
                    <?php endif; ?>

                    <!-- Top CTA Section -->
                    <div class="bt-top-cta-section">
                        <div class="bt-cta-content">
                            <!-- Left Side - Pricing Info -->
                            <div class="bt-pricing-info">
                                <?php if ( $old_price > 0 ) : ?>
                                    <div class="bt-mrp">MRP: ₹<?php echo esc_html( number_format( $old_price, 2 ) ); ?></div>
                                <?php endif; ?>

                                <div class="bt-offer-row">
                                    <span class="bt-offer-label">OFFER:</span>
                                    <?php if ( $sale_price > 0 ) : ?>
                                        <div class="bt-offer-price-container">
                                            <span class="bt-offer-price">₹<?php echo esc_html( number_format( $sale_price, 2 ) ); ?></span>
                                                                                       <span class="bt-discount-percent"><?php echo esc_html( $discount_percent ); ?>% Off</span>

                                            <div class="bt-price-info-wrapper">
                                                <button class="bt-price-info-btn" type="button" aria-label="Price Information">
                                                    <span>ℹ</span>
                                                </button>
                                                
                                                <div class="bt-price-tooltip">
                                                    <div class="bt-tooltip-header">
                                                        <strong>Price Information</strong>
                                                    </div>
                                                    <div class="bt-tooltip-content">
                                                        <p><strong>Price as of:</strong> <?php echo esc_html( get_the_date( 'M j, Y \a\t g:i A' ) ); ?></p>
                                                        <p>Product prices and availability are accurate as of the date/time indicated and are subject to change.</p>
                                                        <p>Any price and availability information displayed on <?php echo esc_html( $store_name ?: 'the Store' ); ?> at the time of purchase will apply to the purchase of this product.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( $discount_percent > 0 ) : ?>
                                        <span class="bt-discount-info">
                                            <?php if ( $old_price > 0 && $sale_price > 0 ) : ?>
                                                <?php if ( ! empty( $coupon_code ) ) : ?>
                                                    <span class="bt-savings bt-savings-special"> <?php echo esc_html( $coupon_code ); ?> to save ₹<?php echo esc_html( number_format( $old_price - $sale_price, 2 ) ); ?></span>
                                                <?php else : ?>
                                                    <span class="bt-savings bt-savings-default">✨ You successfully saved ₹<?php echo esc_html( number_format( $old_price - $sale_price, 2 ) ); ?>!</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Right Side - CTA Button -->
                            <div class="">
                                <?php if ( $offer_url ) : ?>
                                    <?php
                                    $store_name_for_button = !empty($store_name) ? $store_name : 'Store';
                                    $button_text_with_store = sprintf(__('Shop on %s', 'bigtricks-deals'), $store_name_for_button);
                                    ?>
                                    <a href="<?php echo esc_url( $offer_url ); ?>"
                                       class="bt-buy-now-btn"
                                       target="_blank"
                                       rel="nofollow noopener">
                                        <i class="rbi rbi-shopping-bag"></i> <?php echo esc_html( $button_text_with_store ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Social Share Buttons -->
                        <div class="bt-social-cta-section">
                            <div class="bt-social-buttons">
                                <button class="bt-social-btn bt-copy-btn" data-url="<?php echo esc_url( get_permalink() ); ?>" title="Copy Link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                                <button class="bt-social-btn bt-whatsapp-btn" data-url="<?php echo esc_url( get_permalink() ); ?>" data-title="<?php echo esc_attr( $title ); ?>" title="Share on WhatsApp">
                                    <i class="rbi rbi-whatsapp"></i>
                                </button>
                                <button class="bt-social-btn bt-facebook-btn" data-url="<?php echo esc_url( get_permalink() ); ?>" title="Share on Facebook">
                                    <i class="rbi rbi-facebook"></i>
                                </button>
                                <button class="bt-social-btn bt-twitter-btn" data-url="<?php echo esc_url( get_permalink() ); ?>" data-title="<?php echo esc_attr( $title ); ?>" title="Share on Twitter">
                                    <i class="rbi rbi-twitter"></i>
                                </button>
                                <button class="bt-social-btn bt-telegram-btn" data-url="<?php echo esc_url( get_permalink() ); ?>" data-title="<?php echo esc_attr( $title ); ?>" title="Share on Telegram">
                                    <i class="rbi rbi-telegram"></i>
                                </button>
                            </div>
                        </div>

                        <?php if ( $expiry_date ) : ?>
                            <div class="bt-countdown-timer" data-expiry="<?php echo esc_attr( $expiry_date ); ?>">
                                <div class="bt-countdown-label"><?php esc_html_e( 'Offer expires in:', 'bigtricks-deals' ); ?></div>
                                <div class="bt-countdown-display">
                                    <div class="bt-countdown-item">
                                        <span id="btDays" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label"><?php esc_html_e( 'Days', 'bigtricks-deals' ); ?></span>
                                    </div>
                                    <div class="bt-countdown-item">
                                        <span id="btHours" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label"><?php esc_html_e( 'Hours', 'bigtricks-deals' ); ?></span>
                                    </div>
                                    <div class="bt-countdown-item">
                                        <span id="btMinutes" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label"><?php esc_html_e( 'Minutes', 'bigtricks-deals' ); ?></span>
                                    </div>
                                    <div class="bt-countdown-item">
                                        <span id="btSeconds" class="bt-countdown-number">00</span>
                                        <span class="bt-countdown-label"><?php esc_html_e( 'Seconds', 'bigtricks-deals' ); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
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
                    <!-- Coupon Code (Top of Content) -->
                    <?php if ( $coupon_code ) : ?>
                        <div class="bt-coupon-section bt-coupon-top">
                            <div class="bt-coupon-label"><?php esc_html_e( 'Coupon Code:', 'bigtricks-deals' ); ?></div>
                            <div class="bt-coupon-code-display">
                                <span class="bt-coupon-code"><?php echo esc_html( $coupon_code ); ?></span>
                                <button class="bt-copy-coupon" data-coupon="<?php echo esc_attr( $coupon_code ); ?>">
                                    <?php esc_html_e( 'Copy', 'bigtricks-deals' ); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (get_the_content()): ?>
                        <div class="bt-full-description entry-content">
                            <h2><?php esc_html_e( 'Offer Details', 'bigtricks-deals' ); ?></h2>
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Product Features -->
                    <?php if ( ! empty( $product_feature ) ) : ?>
                        <div class="bt-product-features">
                            <h3><?php esc_html_e( 'Product Features', 'bigtricks-deals' ); ?></h3>
                            <div class="bt-features-content">
                                <?php echo wp_kses_post( $product_feature ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Product Features/Tags -->
                    <?php
                    $tags = get_the_terms( $post_id, 'post_tag' );
                    if ( $tags && ! is_wp_error( $tags ) ) :
                        ?>
                        <div class="bt-product-tags">
                            <h3><?php esc_html_e( 'Tags', 'bigtricks-deals' ); ?></h3>
                            <div class="bt-tags-list">
                                <?php foreach ( $tags as $tag ) : ?>
                                    <a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" class="bt-tag">
                                        <?php echo esc_html( $tag->name ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="bt-sidebar">
                    <div class="bt-card">
                        <?php echo do_shortcode( '[adinserter name="telegram"]' ); ?>
                    </div>
                    <!-- Store Info Card -->
                    <?php if ( $store_name ) : ?>
                        <div class="bt-card">
                            <h3><?php esc_html_e( 'Store Information', 'bigtricks-deals' ); ?></h3>
                            <div class="bt-store-details">
                                <?php if ( $store_logo ) : ?>
                                    <img src="<?php echo esc_url( $store_logo ); ?>"
                                         alt="<?php echo esc_attr( $store_name ); ?>"
                                         class="bt-store-logo-large"
                                         width="64"
                                         height="64">
                                <?php endif; ?>
                                <div class="bt-store-meta">
                                    <h4><?php echo esc_html( $store_name ); ?></h4>
                                    <p><?php esc_html_e( 'Reliable store with great deals', 'bigtricks-deals' ); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <div class="bt-card">
                        <h3><?php esc_html_e( 'Deal Summary', 'bigtricks-deals' ); ?></h3>
                        <div class="bt-stats-list">
                            <?php if ( $discount_percent > 0 ) : ?>
                                <div class="bt-stat-item">
                                    <span class="bt-stat-label"><?php esc_html_e( 'Discount:', 'bigtricks-deals' ); ?></span>
                                    <span class="bt-stat-value"><?php echo esc_html( $discount_percent ); ?>%</span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $old_price > 0 ) : ?>
                                <div class="bt-stat-item">
                                    <span class="bt-stat-label"><?php esc_html_e( 'You Save:', 'bigtricks-deals' ); ?></span>
                                    <span class="bt-stat-value"><?php echo esc_html( '₹' . number_format( $old_price - $sale_price, 2 ) ); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="bt-stat-item">
                                <span class="bt-stat-label"><?php esc_html_e( 'Store:', 'bigtricks-deals' ); ?></span>
                                <span class="bt-stat-value"><?php echo esc_html( $store_name ?: __( 'Various', 'bigtricks-deals' ) ); ?></span>
                            </div>

                            <?php if ( ! empty( $categories ) ) : ?>
                                <div class="bt-stat-item">
                                    <span class="bt-stat-label"><?php esc_html_e( 'Category:', 'bigtricks-deals' ); ?></span>
                                    <span class="bt-stat-value">
                                        <a href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>" class="bt-category-link">
                                            <?php echo esc_html( $categories[0]->name ); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Deals Section -->
    <section class="bt-similar-deals-section">
        <div class="bt-container">
            <div class="bt-section-header">
                <h2 class="bt-section-title"><?php esc_html_e( 'Similar Deals', 'bigtricks-deals' ); ?></h2>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'deal' ) ); ?>" class="bt-view-all-link"><?php esc_html_e( 'View All', 'bigtricks-deals' ); ?> <i class="rbi rbi-arrow-right"></i></a>
            </div>
            <div id="bt-similar-deals-container">
                <?php
                // Get similar deals based on current deal's categories and stores
                $current_stores = wp_get_post_terms( $post_id, 'store', array( 'fields' => 'ids' ) );
                $current_categories = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );

                $similar_args = array(
                    'post_type'      => 'deal',
                    'post_status'    => 'publish',
                    'posts_per_page' => 4,
                    'post__not_in'   => array( $post_id ),
                    'meta_query'     => array(
                        'relation' => 'OR',
                        array(
                            'key'     => '_btdeals_is_expired',
                            'value'   => 'off',
                            'compare' => '=',
                        ),
                        array(
                            'key'     => '_btdeals_is_expired',
                            'compare' => 'NOT EXISTS',
                        ),
                    ),
                );

                if ( ! empty( $current_stores ) || ! empty( $current_categories ) ) {
                    $tax_query = array( 'relation' => 'OR' );
                    if ( ! empty( $current_stores ) ) {
                        $tax_query[] = array(
                            'taxonomy' => 'store',
                            'field'    => 'term_id',
                            'terms'    => $current_stores,
                        );
                    }
                    if ( ! empty( $current_categories ) ) {
                        $tax_query[] = array(
                            'taxonomy' => 'category',
                            'field'    => 'term_id',
                            'terms'    => $current_categories,
                        );
                    }
                    $similar_args['tax_query'] = $tax_query;
                }

                $similar_query = new WP_Query( $similar_args );

                if ( $similar_query->have_posts() ) {
                    echo '<div class="bt-grid bt-grid-3 bt-deals-grid">';
                    while ( $similar_query->have_posts() ) {
                        $similar_query->the_post();
                        $similar_deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( get_the_ID() );
                        echo Bigtricks_Deals_Content_Helper::render_deal_item( $similar_deal_data );
                    }
                    echo '</div>';
                    wp_reset_postdata();
                } else {
                    // Fallback: show recent deals if no similar deals found
                    echo do_shortcode( '[loot-deals count="4"]' );
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Disclaimer Section -->
    <?php
    $global_disclaimer = get_option( 'btdeals_global_disclaimer', '' );
    $display_disclaimer = $disclaimer ?: $global_disclaimer;
    if ( $display_disclaimer ) :
        ?>
        <section class="bt-disclaimer-section">
            <div class="bt-container">
                <div class="bt-disclaimer-content">
                    <h3><?php esc_html_e( 'Important Disclaimer', 'bigtricks-deals' ); ?></h3>
                    <div class="bt-disclaimer-text">
                        <?php echo wp_kses_post( $display_disclaimer ); ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Comments Section -->
    <section class="bt-comments-section">
        <div class="bt-container">
            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>
        </div>
    </section>

	</article><!-- #post-<?php the_ID(); ?> -->

    <!-- Sticky Footer for Mobile -->
    <div class="bt-sticky-footer-mobile">
        <a href="#comments" class="bt-sticky-icon bt-comment-icon">
            <i class="rbi rbi-chat"></i>
            <span><?php esc_html_e( 'Comments', 'bigtricks-deals' ); ?></span>
        </a>
        <a href="#" class="bt-sticky-icon bt-share-trigger-mobile">
            <i class="rbi rbi-share"></i>
            <span><?php esc_html_e( 'Share', 'bigtricks-deals' ); ?></span>
        </a>
        <a href="<?php echo esc_url( $offer_url ); ?>" class="bt-sticky-get-deal is-btn" target="_blank" rel="nofollow noopener">
            <?php echo esc_html( $button_text_with_store ); ?>
        </a>
    </div>

    <!-- Global Deal Disclaimer -->
    <section class="bt-global-disclaimer">
        <div class="bt-container">
            <div class="bt-global-disclaimer-content">
                <p class="bt-global-disclaimer-text">
                    <small><em><strong>
                        Please note that most of the deals stay for a very short duration of few minutes to few hours. The price shown above is the deal price and may have changed on the shop website since. To ensure that you get the most out of these deals, please join our
                        <a href="/visit/?store=tg_chanel" target="_blank" rel="noopener">telegram channel</a> /
                        <a href="/visit/?store=wa_channel" target="_blank" rel="noopener">Whatsapp channel</a> or
                        <a href="/visit/?store=bigtricks_app" target="_blank" rel="noopener">download bigtricks.in app</a>
                    </strong></em></small>
                </p>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
