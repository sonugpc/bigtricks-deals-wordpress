<?php
/**
 * The template for displaying all single deals
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Bigtricks_Deals
 */

// Get deal meta data
$post_id = get_the_ID();
$fields = [
	'product_name'      => get_post_meta( $post_id, '_btdeals_product_name', true ),
	'short_description' => get_post_meta( $post_id, '_btdeals_short_description', true ),
	'offer_url'         => get_post_meta( $post_id, '_btdeals_offer_url', true ),
	'disclaimer'        => get_post_meta( $post_id, '_btdeals_disclaimer', true ),
	'offer_old_price'   => get_post_meta( $post_id, '_btdeals_offer_old_price', true ),
	'offer_sale_price'  => get_post_meta( $post_id, '_btdeals_offer_sale_price', true ),
	'coupon_code'       => get_post_meta( $post_id, '_btdeals_coupon_code', true ),
	'expiration_date'   => get_post_meta( $post_id, '_btdeals_expiration_date', true ),
	'mask_coupon'       => get_post_meta( $post_id, '_btdeals_mask_coupon', true ),
	'is_expired'        => get_post_meta( $post_id, '_btdeals_is_expired', true ),
	'verify_label'      => get_post_meta( $post_id, '_btdeals_verify_label', true ),
	'button_text'       => get_post_meta( $post_id, '_btdeals_button_text', true ),
	'thumbnail_id'      => get_post_meta( $post_id, '_btdeals_thumbnail_id', true ),
	'brand_logo_id'     => get_post_meta( $post_id, '_btdeals_brand_logo_id', true ),
	'brand_logo_url'    => get_post_meta( $post_id, '_btdeals_brand_logo_url', true ),
	'discount_tag'      => get_post_meta( $post_id, '_btdeals_discount_tag', true ),
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

// SEO Meta tags
$description = ! empty( $fields['short_description'] ) ? wp_strip_all_tags( $fields['short_description'] ) : get_the_excerpt();
$thumbnail_url = '';
if ( $fields['thumbnail_id'] ) {
	$thumbnail_data = wp_get_attachment_image_src( $fields['thumbnail_id'], 'large' );
	$thumbnail_url = $thumbnail_data ? $thumbnail_data[0] : '';
} else {
	$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'large' );
}

get_header(); ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	
	<!-- Product Schema Markup -->
	<script type="application/ld+json">
	{
		"@context": "https://schema.org/",
		"@type": "Product",
		"name": "<?php echo esc_js( $product_name ); ?>",
		"description": "<?php echo esc_js( $description ); ?>",
		"image": "<?php echo esc_url( $thumbnail_url ); ?>",
		"brand": {
			"@type": "Brand",
			"name": "<?php echo esc_js( $store_name ); ?>"
		},
		"offers": {
			"@type": "Offer",
			"price": "<?php echo esc_js( $fields['offer_sale_price'] ); ?>",
			"priceCurrency": "USD",
			"availability": "<?php echo ( 'on' === $fields['is_expired'] ) ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock'; ?>",
			"url": "<?php echo esc_url( $fields['offer_url'] ); ?>"
		}
	}
	</script>
	
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="site">
	<div id="content" class="site-content">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'bt-deal-page' ); ?> itemscope itemtype="https://schema.org/Product">
	
					<!-- Hero Section - Full Width -->
					<div class="bt-deal-hero-wrapper">
		<div class="bt-deal-hero">
			<?php if ( $discount_percent > 0 || ! empty( $fields['discount_tag'] ) ) : ?>
				<div class="bt-discount-badge">
					<?php echo ! empty( $fields['discount_tag'] ) ? esc_html( $fields['discount_tag'] ) : esc_html( $discount_percent ) . '% OFF'; ?>
				</div>
			<?php endif; ?>
			
			<!-- Categories Chips -->
			<?php 
			$categories = get_the_category();
			if ( ! empty( $categories ) ) : ?>
				<div class="bt-category-chips">
					<?php foreach ( $categories as $category ) : ?>
						<span class="bt-category-chip"><?php echo esc_html( $category->name ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			
			<div class="bt-hero-content">
				<div class="bt-hero-left">
					<?php
					// Get thumbnail for left side
					if ( $fields['thumbnail_id'] ) {
						echo wp_get_attachment_image( $fields['thumbnail_id'], 'medium', false, [
							'class' => 'bt-product-image',
							'itemprop' => 'image'
						] );
					} elseif ( has_post_thumbnail() ) {
						the_post_thumbnail( 'medium', [
							'class' => 'bt-product-image',
							'itemprop' => 'image'
						] );
					}
					?>
				</div>
				
				<div class="bt-hero-center">
					<h1 class="bt-product-title" itemprop="name"><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( ! empty( $fields['short_description'] ) ) : ?>
						<p class="bt-product-description" itemprop="description"><?php echo wp_kses_post( $fields['short_description'] ); ?></p>
					<?php endif; ?>
					
					<div class="bt-price-section">
						<?php if ( ! empty( $fields['offer_sale_price'] ) ) : ?>
							<span class="bt-current-price" itemprop="price">₹<?php echo esc_html( $fields['offer_sale_price'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $fields['offer_old_price'] ) ) : ?>
							<span class="bt-original-price"><del>₹<?php echo esc_html( $fields['offer_old_price'] ); ?></del></span>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $fields['coupon_code'] ) ) : ?>
						<div class="bt-coupon-section">
							<span class="bt-coupon-label">Use Code:</span>
							<?php if ( 'on' === $fields['mask_coupon'] ) : ?>
								<button class="bt-coupon-reveal" data-coupon="<?php echo esc_attr( $fields['coupon_code'] ); ?>">
									<span class="bt-coupon-text">Click to Reveal</span>
									<div class="bt-loading" style="display: none;"></div>
								</button>
							<?php else : ?>
								<code class="bt-coupon-code"><?php echo esc_html( $fields['coupon_code'] ); ?></code>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				
				<div class="bt-hero-right">
					<?php
					// Store image - try taxonomy thumbnail first, then meta
					$store_image_url = '';
					if ( $stores && ! is_wp_error( $stores ) ) {
						$store = reset( $stores );
						$store_thumbnail = get_term_meta( $store->term_id, 'thumbnail', true );
						if ( $store_thumbnail ) {
							$store_image_url = wp_get_attachment_image_src( $store_thumbnail, 'thumbnail' );
							$store_image_url = $store_image_url ? $store_image_url[0] : '';
						}
					}
					
					// Fallback to brand logo from meta
					if ( empty( $store_image_url ) ) {
						if ( $fields['brand_logo_id'] ) {
							$brand_logo = wp_get_attachment_image_src( $fields['brand_logo_id'], 'thumbnail' );
							$store_image_url = $brand_logo ? $brand_logo[0] : '';
						} elseif ( ! empty( $fields['brand_logo_url'] ) ) {
							$store_image_url = $fields['brand_logo_url'];
						}
					}
					
					if ( $store_image_url ) : ?>
						<img src="<?php echo esc_url( $store_image_url ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" class="bt-store-image">
					<?php endif; ?>
					
					<!-- CTA Button -->
					<div class="bt-hero-cta">
						<a href="<?php echo esc_url( $fields['offer_url'] ); ?>" 
						   class="bt-main-cta<?php echo ( 'on' === $fields['is_expired'] ) ? ' bt-expired' : ''; ?>" 
						   target="_blank" 
						   rel="nofollow noopener"
						   onclick="btDeals.trackClick('<?php echo esc_js( $post_id ); ?>')">
							<?php echo esc_html( $fields['button_text'] ? $fields['button_text'] : 'Get Deal' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Main Content with Sidebar Layout -->
	<div class="bt-content-wrapper">
		<div class="bt-content-main entry-content">
			<div class="bt-deal-description">
				<?php if ( ! empty( $fields['expiration_date'] ) ) : ?>
					<div class="bt-expiry-info">
						<i class="bt-icon-clock"></i>
						<span class="bt-expiry-label">Expires on:</span>
						<time datetime="<?php echo esc_attr( $fields['expiration_date'] ); ?>">
							<?php echo esc_html( date( 'M j, Y', strtotime( $fields['expiration_date'] ) ) ); ?>
						</time>
					</div>
				<?php endif; ?>

				<?php the_content(); ?>
			</div>
			
			<?php if ( ! empty( $fields['disclaimer'] ) ) : ?>
				<div class="bt-disclaimer">
					<h3>Important Information</h3>
					<div class="bt-disclaimer-content">
						<?php echo wp_kses_post( $fields['disclaimer'] ); ?>
					</div>
				</div>
			<?php endif; ?>
			
			<!-- Plugin Hook for Extensibility -->
			<?php do_action( 'btdeals_single_deal_content', $post_id, $fields ); ?>
		</div>
		
		<div class="bt-content-sidebar">
			<div class="bt-deal-info-card">
				<?php
				// Brand logo
				if ( $fields['brand_logo_id'] ) {
					$brand_logo_url = wp_get_attachment_image_src( $fields['brand_logo_id'], 'thumbnail' );
					if ( $brand_logo_url ) {
						echo '<img src="' . esc_url( $brand_logo_url[0] ) . '" alt="Brand Logo" class="bt-brand-logo">';
					}
				} elseif ( ! empty( $fields['brand_logo_url'] ) ) {
					echo '<img src="' . esc_url( $fields['brand_logo_url'] ) . '" alt="Brand Logo" class="bt-brand-logo">';
				}
				?>
				
				<h3>Deal Information</h3>
				<div class="bt-info-item">
					<span class="bt-info-label">Store:</span>
					<span class="bt-info-value"><?php echo esc_html( $store_name ); ?></span>
				</div>
				
				<?php if ( ! empty( $fields['verify_label'] ) ) : ?>
					<div class="bt-info-item">
						<span class="bt-info-label">Status:</span>
						<span class="bt-info-value bt-verified"><?php echo esc_html( $fields['verify_label'] ); ?></span>
					</div>
				<?php endif; ?>
				
				<div class="bt-info-item">
					<span class="bt-info-label">Posted:</span>
					<span class="bt-info-value"><?php echo esc_html( get_the_date() ); ?></span>
				</div>
			</div>

			<!-- Share Buttons Card -->
			<div class="bt-share-card">
				<h4>Share this deal</h4>
				<div class="bt-share-buttons">
					<button class="bt-share-btn" data-share="facebook" title="Share on Facebook">
						<svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
					</button>
					<button class="bt-share-btn" data-share="twitter" title="Share on Twitter">
						<svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
					</button>
					<button class="bt-share-btn" data-share="whatsapp" title="Share on WhatsApp">
						<svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.89 3.685"/></svg>
					</button>
					<button class="bt-share-btn" data-share="copy" title="Copy Link">
						<svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
					</button>
				</div>
			</div>
			
			<!-- Plugin Hook for Sidebar Ads -->
			<?php do_action( 'btdeals_single_deal_sidebar', $post_id, $fields ); ?>
			
			<?php get_sidebar(); ?>
		</div>
	</div>

	<!-- Similar Deals Carousel - Full Width -->
	<div class="bt-similar-deals">
		<h2>More Deals from <?php echo esc_html( $store_name ); ?></h2>
		<div class="bt-deals-carousel" id="similarDealsCarousel">
			<div class="bt-loading-deals">
				<div class="bt-loading"></div>
				<p>Loading similar deals...</p>
			</div>
		</div>
	</div>
	
	<!-- Plugin Hook for Bottom Content -->
	<?php do_action( 'btdeals_single_deal_bottom', $post_id, $fields ); ?>
</article>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #content -->
</div><!-- #page -->

<?php
// Enqueue deal page assets
wp_enqueue_style( 'bt-deals-single', plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/bt-deals-single.css', [], BTDEALS_VERSION );
wp_enqueue_script( 'bt-deals-single', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/bt-deals-single.js', ['jquery'], BTDEALS_VERSION, true );
wp_localize_script( 'bt-deals-single', 'btDealsAjax', [
	'ajaxurl' => admin_url( 'admin-ajax.php' ),
	'nonce' => wp_create_nonce( 'bt_deals_nonce' ),
	'postId' => $post_id,
	'storeId' => $store_id,
	'shareUrl' => get_permalink(),
	'shareTitle' => $product_name . ' - ' . $discount_percent . '% Off',
	'shareText' => $description
]);

get_footer();
