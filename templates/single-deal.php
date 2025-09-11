<?php
/**
 * Modern Single Deal Template v3.0 - Complete Redesign
 * Enhanced UX with countdown timer, wishlist, and modern design
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 * @package Bigtricks_Deals
 */

// Get deal meta data
$post_id = get_the_ID();
$fields = [
	'product_name'           => get_post_meta( $post_id, '_btdeals_product_name', true ),
	'short_description'      => get_post_meta( $post_id, '_btdeals_short_description', true ),
	'offer_url'              => get_post_meta( $post_id, '_btdeals_offer_url', true ),
	'disclaimer'             => get_post_meta( $post_id, '_btdeals_disclaimer', true ),
	'offer_old_price'        => get_post_meta( $post_id, '_btdeals_offer_old_price', true ),
	'offer_sale_price'       => get_post_meta( $post_id, '_btdeals_offer_sale_price', true ),
	'coupon_code'            => get_post_meta( $post_id, '_btdeals_coupon_code', true ),
	'expiration_date'        => get_post_meta( $post_id, '_btdeals_expiration_date', true ),
	'mask_coupon'            => get_post_meta( $post_id, '_btdeals_mask_coupon', true ),
	'is_expired'             => get_post_meta( $post_id, '_btdeals_is_expired', true ),
	'verify_label'           => get_post_meta( $post_id, '_btdeals_verify_label', true ),
	'button_text'            => get_post_meta( $post_id, '_btdeals_button_text', true ),
	'product_thumbnail_url'  => get_post_meta( $post_id, '_btdeals_product_thumbnail_url', true ),
	'offer_thumbnail_url'    => get_post_meta( $post_id, '_btdeals_offer_thumbnail_url', true ),
	'product_feature'        => get_post_meta( $post_id, '_btdeals_product_feature', true ),
	'store'                  => get_post_meta( $post_id, '_btdeals_store', true ),
	'brand_logo_url'         => get_post_meta( $post_id, '_btdeals_brand_logo_url', true ),
	'discount_tag'           => get_post_meta( $post_id, '_btdeals_discount_tag', true ),
];

// Get product name or fallback to post title
$product_name = ! empty( $fields['product_name'] ) ? $fields['product_name'] : get_the_title( $post_id );

// Calculate discount percentage
$discount_percent = 0;
if ( ! empty( $fields['offer_old_price'] ) && ! empty( $fields['offer_sale_price'] ) ) {
	$old_price = floatval( $fields['offer_old_price'] );
	$sale_price = floatval( $fields['offer_sale_price'] );
	if ( $old_price > $sale_price ) {
		$discount_percent = round( ( ( $old_price - $sale_price ) / $old_price ) * 100 );
	}
}

// Get store information
$stores = get_the_terms( $post_id, 'store' );
$store_name = '';
$store_id = 0;
if ( $stores && ! is_wp_error( $stores ) ) {
	$store = reset( $stores );
	$store_name = $store->name;
	$store_id = $store->term_id;
}

// Get thumbnail URL
$thumbnail_url = '';
if ( ! empty( $fields['product_thumbnail_url'] ) ) {
	$thumbnail_url = $fields['product_thumbnail_url'];
} elseif ( has_post_thumbnail() ) {
	$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'large' );
}

// Get store logo
$store_logo_url = '';
if ( $stores && ! is_wp_error( $stores ) ) {
	$store = reset( $stores );
	$store_thumbnail = get_term_meta( $store->term_id, 'thumbnail', true );
	if ( $store_thumbnail ) {
		$store_logo = wp_get_attachment_image_src( $store_thumbnail, 'thumbnail' );
		$store_logo_url = $store_logo ? $store_logo[0] : '';
	}
}
if ( empty( $store_logo_url ) && ! empty( $fields['brand_logo_url'] ) ) {
	$store_logo_url = $fields['brand_logo_url'];
}

get_header(); ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo esc_attr( wp_strip_all_tags( $fields['short_description'] ?: get_the_excerpt() ) ); ?>">

	<!-- Preload critical resources -->
	<?php if ( $thumbnail_url ) : ?>
		<link rel="preload" as="image" href="<?php echo esc_url( $thumbnail_url ); ?>">
	<?php endif; ?>

	<!-- Product Schema Markup -->
	<script type="application/ld+json">
	{
		"@context": "https://schema.org/",
		"@type": "Product",
		"name": "<?php echo esc_js( $product_name ); ?>",
		"description": "<?php echo esc_js( wp_strip_all_tags( $fields['short_description'] ?: get_the_excerpt() ) ); ?>",
		"image": "<?php echo esc_url( $thumbnail_url ); ?>",
		"brand": {
			"@type": "Brand",
			"name": "<?php echo esc_js( $store_name ); ?>"
		},
		"offers": {
			"@type": "Offer",
			"price": "<?php echo esc_js( $fields['offer_sale_price'] ); ?>",
			"priceCurrency": "INR",
			"availability": "<?php echo ( 'on' === $fields['is_expired'] ) ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock'; ?>",
			"url": "<?php echo esc_url( $fields['offer_url'] ); ?>"
		}
	}
	</script>

	<?php wp_head(); ?>
</head>

<body <?php body_class( 'bt-deal-single' ); ?>>

<div id="bt-deal-container" class="bt-deal-wrapper">

	<!-- Hero Section - Compact and Wide -->
	<section class="bt-hero-section">
		<div class="bt-container">

			<!-- Breadcrumb -->
			<nav class="bt-breadcrumb" aria-label="Breadcrumb">
				<div class="bt-container">
					<a href="<?php echo esc_url( home_url() ); ?>">Home</a>
					<span class="bt-separator">›</span>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'deal' ) ); ?>">Deals</a>
					<?php if ( $store_name ) : ?>
						<span class="bt-separator">›</span>
						<a href="<?php echo esc_url( get_term_link( $store_id, 'store' ) ); ?>"><?php echo esc_html( $store_name ); ?></a>
					<?php endif; ?>
					<span class="bt-separator">›</span>
					<span class="bt-current"><?php echo esc_html( $product_name ); ?></span>
				</div>
			</nav>

			<!-- Compact Hero Content -->
			<div class="bt-hero-content">
				<div class="bt-hero-main">

					<!-- Product Image -->
					<div class="bt-product-image-wrapper">
						<?php if ( $thumbnail_url ) : ?>
							<img src="<?php echo esc_url( $thumbnail_url ); ?>"
								 alt="<?php echo esc_attr( $product_name ); ?>"
								 class="bt-product-image"
								 loading="eager"
								 width="300"
								 height="300">
						<?php endif; ?>

						<!-- Discount Badge -->
						<?php if ( $discount_percent > 0 || ! empty( $fields['discount_tag'] ) ) : ?>
							<div class="bt-discount-badge">
								<?php echo ! empty( $fields['discount_tag'] ) ? esc_html( $fields['discount_tag'] ) : esc_html( $discount_percent ) . '% OFF'; ?>
							</div>
						<?php endif; ?>
					</div>

					<!-- Product Info -->
					<div class="bt-product-details">

						<!-- Action Bar -->
						<div class="bt-action-bar">
							<!-- Wishlist Button -->
							<button class="bt-wishlist-btn" data-deal-id="<?php echo esc_attr( $post_id ); ?>" title="Add to Wishlist">
								<svg class="bt-wishlist-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
								</svg>
								<span class="bt-wishlist-text">Save Deal</span>
							</button>

							<!-- Share Button -->
							<button class="bt-share-trigger" title="Share Deal">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
									<polyline points="16,6 12,2 8,6"/>
									<line x1="12" y1="2" x2="12" y2="15"/>
								</svg>
								<span>Share</span>
							</button>
						</div>

						<!-- Store Badge -->
						<?php if ( $store_name ) : ?>
							<div class="bt-store-badge">
								<?php if ( $store_logo_url ) : ?>
									<img src="<?php echo esc_url( $store_logo_url ); ?>"
										 alt="<?php echo esc_attr( $store_name ); ?>"
										 class="bt-store-logo"
										 loading="lazy"
										 width="20"
										 height="20">
								<?php else : ?>
									<!-- Default Store SVG -->
									<svg class="bt-store-logo" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
										<path d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
									</svg>
								<?php endif; ?>
								<span><?php echo esc_html( $store_name ); ?></span>
							</div>
						<?php endif; ?>

						<!-- Product Title -->
						<h1 class="bt-product-title"><?php echo esc_html( $product_name ); ?></h1>

						<!-- Product Description -->
						<?php if ( ! empty( $fields['short_description'] ) ) : ?>
							<p class="bt-product-description"><?php echo wp_kses_post( $fields['short_description'] ); ?></p>
						<?php endif; ?>

						<!-- Price Section -->
						<div class="bt-price-section">
							<div class="bt-price-current">
								₹<?php echo esc_html( $fields['offer_sale_price'] ); ?>
							</div>
							<?php if ( ! empty( $fields['offer_old_price'] ) ) : ?>
								<div class="bt-price-original">
									₹<?php echo esc_html( $fields['offer_old_price'] ); ?>
								</div>
							<?php endif; ?>
							<?php if ( $discount_percent > 0 ) : ?>
								<div class="bt-price-savings">
									<span class="bt-savings-amount">Save ₹<?php echo esc_html( $fields['offer_old_price'] - $fields['offer_sale_price'] ); ?></span>
									<span class="bt-savings-percent">(<?php echo esc_html( $discount_percent ); ?>% off)</span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Countdown Timer -->
						<?php if ( ! empty( $fields['expiration_date'] ) && 'on' !== $fields['is_expired'] ) : ?>
							<div class="bt-countdown-section">
								<div class="bt-countdown-label">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<circle cx="12" cy="12" r="10"/>
										<path d="M12 6v6l4 2"/>
									</svg>
									<span>Deal expires in:</span>
								</div>
								<div class="bt-countdown-timer" data-expiry="<?php echo esc_attr( $fields['expiration_date'] ); ?>">
									<div class="bt-countdown-item">
										<span class="bt-countdown-value" id="btDays">--</span>
										<span class="bt-countdown-label">Days</span>
									</div>
									<div class="bt-countdown-item">
										<span class="bt-countdown-value" id="btHours">--</span>
										<span class="bt-countdown-label">Hours</span>
									</div>
									<div class="bt-countdown-item">
										<span class="bt-countdown-value" id="btMinutes">--</span>
										<span class="bt-countdown-label">Min</span>
									</div>
									<div class="bt-countdown-item">
										<span class="bt-countdown-value" id="btSeconds">--</span>
										<span class="bt-countdown-label">Sec</span>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<!-- Deal Meta -->
						<div class="bt-deal-meta">
							<div class="bt-meta-item">
								<svg class="bt-meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
								</svg>
								<span>Posted: <?php echo esc_html( get_the_date() ); ?></span>
							</div>

							<div class="bt-meta-item">
								<svg class="bt-meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
									<path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
								</svg>
								<span><?php echo esc_html( get_post_meta( $post_id, '_btdeals_views', true ) ?: '0' ); ?> views</span>
							</div>

							<?php if ( ! empty( $fields['verify_label'] ) ) : ?>
								<div class="bt-meta-item bt-verified">
									<svg class="bt-meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
									</svg>
									<span><?php echo esc_html( $fields['verify_label'] ); ?></span>
								</div>
							<?php endif; ?>
						</div>

					</div>

				</div>

				<!-- CTA Section - Smaller and Less Prominent -->
				<div class="bt-hero-actions">

					<!-- Coupon Section -->
					<?php if ( ! empty( $fields['coupon_code'] ) ) : ?>
						<div class="bt-coupon-section">
							<div class="bt-coupon-label">Coupon Code</div>
							<?php if ( 'on' === $fields['mask_coupon'] ) : ?>
								<button class="bt-coupon-reveal" data-coupon="<?php echo esc_attr( $fields['coupon_code'] ); ?>">
									<span class="bt-coupon-text">Click to Reveal</span>
								</button>
							<?php else : ?>
								<div class="bt-coupon-code"><?php echo esc_html( $fields['coupon_code'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- CTA Button - Smaller -->
					<a href="<?php echo esc_url( $fields['offer_url'] ); ?>"
					   class="bt-cta-button<?php echo ( 'on' === $fields['is_expired'] ) ? ' bt-expired' : ''; ?>"
					   target="_blank"
					   rel="nofollow noopener"
					   onclick="btDeals.trackClick('<?php echo esc_js( $post_id ); ?>')">
						<?php echo esc_html( $fields['button_text'] ?: 'Get Deal' ); ?>
						<svg class="bt-arrow-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M7 17L17 7M17 7H7M17 7V17"/>
						</svg>
					</a>

				</div>
			</div>

		</div>
	</section>

	<!-- Content Section - Wide with Sidebar -->
	<section class="bt-content-section">
		<div class="bt-container">
			<div class="bt-content-layout">

				<!-- Main Content - Wider -->
				<div class="bt-main-content">

					<!-- Product Features -->
					<?php if ( ! empty( $fields['product_feature'] ) ) : ?>
						<div class="bt-features-section">
							<h2>Product Features</h2>
							<div class="bt-features-content">
								<?php echo wp_kses_post( $fields['product_feature'] ); ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Deal Description -->
					<div class="bt-description-section">
						<h2>Deal Details</h2>
						<div class="bt-description-content">
							<?php the_content(); ?>
						</div>
					</div>

					<!-- Disclaimer -->
					<?php if ( ! empty( $fields['disclaimer'] ) ) : ?>
						<div class="bt-disclaimer-section">
							<h3>Important Information</h3>
							<div class="bt-disclaimer-content">
								<?php echo wp_kses_post( $fields['disclaimer'] ); ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Social Share -->
					<div class="bt-share-section">
						<h3>Share this Deal</h3>
						<div class="bt-share-buttons">
							<button class="bt-share-btn" data-platform="facebook" title="Share on Facebook">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
									<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
								</svg>
								<span>Facebook</span>
							</button>
							<button class="bt-share-btn" data-platform="twitter" title="Share on Twitter">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
									<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
								</svg>
								<span>Twitter</span>
							</button>
							<button class="bt-share-btn" data-platform="whatsapp" title="Share on WhatsApp">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
									<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.89 3.685"/>
								</svg>
								<span>WhatsApp</span>
							</button>
							<button class="bt-share-btn" data-platform="copy" title="Copy Link">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
								</svg>
								<span>Copy Link</span>
							</button>
						</div>
					</div>

				</div>

				<!-- Sidebar -->
				<div class="bt-sidebar">

					<!-- Deal Summary Card -->
					<div class="bt-sidebar-card">
						<h3 class="bt-sidebar-title">Deal Summary</h3>
						<div class="bt-summary-content">

							<!-- Price Summary -->
							<div class="bt-summary-item">
								<span class="bt-summary-label">Current Price</span>
								<span class="bt-summary-value bt-price">₹<?php echo esc_html( $fields['offer_sale_price'] ); ?></span>
							</div>

							<?php if ( ! empty( $fields['offer_old_price'] ) ) : ?>
								<div class="bt-summary-item">
									<span class="bt-summary-label">Original Price</span>
									<span class="bt-summary-value bt-price-old">₹<?php echo esc_html( $fields['offer_old_price'] ); ?></span>
								</div>

								<div class="bt-summary-item">
									<span class="bt-summary-label">You Save</span>
									<span class="bt-summary-value bt-savings">
										₹<?php echo esc_html( $fields['offer_old_price'] - $fields['offer_sale_price'] ); ?>
										(<?php echo esc_html( $discount_percent ); ?>%)
									</span>
								</div>
							<?php endif; ?>

							<!-- Store Info -->
							<div class="bt-summary-item">
								<span class="bt-summary-label">Store</span>
								<span class="bt-summary-value"><?php echo esc_html( $store_name ); ?></span>
							</div>

							<!-- Deal Status -->
							<div class="bt-summary-item">
								<span class="bt-summary-label">Status</span>
								<span class="bt-summary-value <?php echo ( 'on' === $fields['is_expired'] ) ? 'bt-expired' : 'bt-active'; ?>">
									<?php echo ( 'on' === $fields['is_expired'] ) ? 'Expired' : 'Active'; ?>
								</span>
							</div>

							<!-- Expiration -->
							<?php if ( ! empty( $fields['expiration_date'] ) ) : ?>
								<div class="bt-summary-item">
									<span class="bt-summary-label">Expires</span>
									<span class="bt-summary-value"><?php echo esc_html( date( 'M j, Y', strtotime( $fields['expiration_date'] ) ) ); ?></span>
								</div>
							<?php endif; ?>

						</div>
					</div>

					<!-- Quick Actions Card -->
					<div class="bt-sidebar-card">
						<h3 class="bt-sidebar-title">Quick Actions</h3>
						<div class="bt-quick-actions">

							<!-- Coupon Code -->
							<?php if ( ! empty( $fields['coupon_code'] ) ) : ?>
								<div class="bt-coupon-section">
									<div class="bt-coupon-label">Coupon Code</div>
									<?php if ( 'on' === $fields['mask_coupon'] ) : ?>
										<button class="bt-coupon-reveal" data-coupon="<?php echo esc_attr( $fields['coupon_code'] ); ?>">
											<span class="bt-coupon-text">Click to Reveal</span>
										</button>
									<?php else : ?>
										<div class="bt-coupon-code"><?php echo esc_html( $fields['coupon_code'] ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<!-- CTA Button -->
							<a href="<?php echo esc_url( $fields['offer_url'] ); ?>"
							   class="bt-cta-button<?php echo ( 'on' === $fields['is_expired'] ) ? ' bt-expired' : ''; ?>"
							   target="_blank"
							   rel="nofollow noopener"
							   onclick="btDeals.trackClick('<?php echo esc_js( $post_id ); ?>')">
								<?php echo esc_html( $fields['button_text'] ?: 'Get Deal Now' ); ?>
								<svg class="bt-arrow-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M7 17L17 7M17 7H7M17 7V17"/>
								</svg>
							</a>

						</div>
					</div>

					<!-- Deal Meta Card -->
					<div class="bt-sidebar-card">
						<h3 class="bt-sidebar-title">Deal Information</h3>
						<div class="bt-meta-list">

							<div class="bt-meta-item">
								<svg class="bt-meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
								</svg>
								<span>Posted: <?php echo esc_html( get_the_date() ); ?></span>
							</div>

							<div class="bt-meta-item">
								<svg class="bt-meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
									<path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
								</svg>
								<span><?php echo esc_html( get_post_meta( $post_id, '_btdeals_views', true ) ?: '0' ); ?> views</span>
							</div>

							<?php if ( ! empty( $fields['verify_label'] ) ) : ?>
								<div class="bt-meta-item bt-verified">
									<svg class="bt-meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
									</svg>
									<span><?php echo esc_html( $fields['verify_label'] ); ?></span>
								</div>
							<?php endif; ?>

						</div>
					</div>

					<!-- Plugin Hook for Sidebar Ads -->
					<?php do_action( 'btdeals_single_deal_sidebar', $post_id, $fields ); ?>

				</div>

			</div>
		</div>
	</section>

	<!-- Similar Deals Section -->
	<section class="bt-similar-deals-section">
		<div class="bt-similar-container">
			<h2 class="bt-similar-title">
				<svg class="bt-section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
				</svg>
				More Great Deals
			</h2>

			<div class="bt-similar-carousel" id="btSimilarCarousel">
				<div class="bt-loading-similar">
					<div class="bt-loading-spinner"></div>
					<p>Loading similar deals...</p>
				</div>
			</div>

			<!-- Carousel Navigation -->
			<div class="bt-carousel-nav">
				<button class="bt-nav-btn bt-prev-btn" id="btPrevBtn" disabled>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M15 18l-6-6 6-6"/>
					</svg>
				</button>
				<div class="bt-carousel-dots" id="btCarouselDots"></div>
				<button class="bt-nav-btn bt-next-btn" id="btNextBtn" disabled>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 18l6-6-6-6"/>
					</svg>
				</button>
			</div>
		</div>
	</section>

	<!-- Plugin Hooks -->
	<?php do_action( 'btdeals_single_deal_bottom', $post_id, $fields ); ?>

</div>

<!-- Enqueue optimized assets -->
<?php
wp_enqueue_style( 'bt-deals-single', plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/bt-deals-single.css', [], BTDEALS_VERSION );
wp_enqueue_script( 'bt-deals-single', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/bt-deals-single.js', ['jquery'], BTDEALS_VERSION, true );

// Localize script with data
wp_localize_script( 'bt-deals-single', 'btDealsData', [
	'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
	'nonce'        => wp_create_nonce( 'bt_deals_nonce' ),
	'postId'       => $post_id,
	'storeId'      => $store_id,
	'shareUrl'     => get_permalink(),
	'shareTitle'   => $product_name,
	'shareText'    => wp_strip_all_tags( $fields['short_description'] ?: get_the_excerpt() ),
	'currency'     => '₹',
	'isExpired'    => $fields['is_expired'] === 'on'
]);

get_footer();
