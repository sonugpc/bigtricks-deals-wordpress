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

$post_id   = get_the_ID();
$deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( $post_id );

// Extract variables for easier use in the template
extract( $deal_data );

?>

<main id="main" class="bt-single-deal-container" role="main">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'bt-deal-article' ); ?>>
    <!-- Hero Section -->
    <section class="bt-hero-section">
        <div class="bt-container">
            <div class="bt-hero-grid">
                <!-- Product Images -->
                <div class="bt-product-gallery">
                    <?php if ( $thumbnail_url ) : ?>
                        <div class="bt-main-image">
                            <img src="<?php echo esc_url( $thumbnail_url ); ?>"
                                 alt="<?php echo esc_attr( $title ); ?>"
                                 loading="lazy"
                                 width="600"
                                 height="400">
                            <?php if ( $discount_percent > 0 ) : ?>
                                <div class="bt-discount-badge">
                                    <?php echo esc_html( $discount_percent ); ?>% OFF
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

                    <?php if ( $store_name ) : ?>
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
                    <?php endif; ?>

                    <!-- Pricing -->
                    <div class="bt-pricing-section">
                        <div class="bt-price-row">
                            <?php if ( $sale_price > 0 ) : ?>
                                <span class="bt-sale-price"><?php echo esc_html( '₹' . number_format( $sale_price, 2 ) ); ?></span>
                            <?php endif; ?>

                            <?php if ( $old_price > 0 ) : ?>
                                <span class="bt-old-price"><?php echo esc_html( '₹' . number_format( $old_price, 2 ) ); ?></span>
                            <?php endif; ?>

                            <?php if ( $discount_percent > 0 ) : ?>
                                <span class="bt-discount-percent"><?php echo esc_html( $discount_percent ); ?>% off</span>
                            <?php endif; ?>
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

                    <!-- Action Buttons -->
                    <div class="bt-action-buttons">
                        <?php if ( $offer_url ) : ?>
                            <a href="<?php echo esc_url( $offer_url ); ?>"
                               class="bt-primary-btn is-btn"
                               target="_blank"
                               rel="nofollow noopener">
                                <?php echo esc_html( $button_text ); ?>
                            </a>
                        <?php endif; ?>

                        <a href="#" class="bt-share-trigger is-btn">
                            <i class="rbi rbi-share"></i>
                            <?php esc_html_e( 'Share', 'bigtricks-deals' ); ?>
                        </a>
                    </div>

                    <!-- Coupon Code -->
                    <?php if ( $coupon_code ) : ?>
                        <div class="bt-coupon-section">
                            <div class="bt-coupon-label"><?php esc_html_e( 'Coupon Code:', 'bigtricks-deals' ); ?></div>
                            <div class="bt-coupon-code-container">
                                <button class="bt-coupon-reveal" data-coupon="<?php echo esc_attr( $coupon_code ); ?>">
                                    <?php esc_html_e( 'Click to Reveal Code', 'bigtricks-deals' ); ?>
                                </button>
                                <div class="bt-coupon-code" style="display: none;">
                                    <?php echo esc_html( $coupon_code ); ?>
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
                        <div class="bt-full-description entry-content">
                            <h2><?php esc_html_e( 'Product Details', 'bigtricks-deals' ); ?></h2>
                            <?php the_content(); ?>
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
            <div id="btSimilarDealsGrid" class="bt-deals-grid">
                <div class="bt-loading-similar">
                    <div class="bt-spinner"></div>
                    <p><?php esc_html_e( 'Loading similar deals...', 'bigtricks-deals' ); ?></p>
                </div>
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
            <?php echo esc_html( $button_text ); ?>
        </a>
    </div>
</main>

<?php get_footer(); ?>
