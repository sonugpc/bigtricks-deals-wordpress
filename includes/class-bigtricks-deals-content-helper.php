<?php
/**
 * Helper class for rendering content.
 *
 * @link       https://bigtricks.in
 * @since      1.0.0
 *
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/includes
 */
class Bigtricks_Deals_Content_Helper {

	/**
	 * Render a single deal box.
	 *
	 * @since 1.0.0
	 * @param int $post_id The ID of the deal post.
	 * @return string The HTML content of a single deal box.
	 */
	public static function render_deal_box( $post_id ) {
		$fields = [
			'product_name'      => get_post_meta( $post_id, '_btdeals_product_name', true ),
			'offer_url'         => get_post_meta( $post_id, '_btdeals_offer_url', true ),
			'offer_old_price'   => get_post_meta( $post_id, '_btdeals_offer_old_price', true ),
			'offer_sale_price'  => get_post_meta( $post_id, '_btdeals_offer_sale_price', true ),
			'coupon_code'       => get_post_meta( $post_id, '_btdeals_coupon_code', true ),
			'button_text'       => get_post_meta( $post_id, '_btdeals_button_text', true ),
			'thumbnail_id'      => get_post_meta( $post_id, '_btdeals_thumbnail_id', true ),
			'discount_tag'      => get_post_meta( $post_id, '_btdeals_discount_tag', true ),
		];

		$title = ! empty( $fields['product_name'] ) ? $fields['product_name'] : get_the_title( $post_id );
		$permalink = get_permalink( $post_id );
		
		$thumbnail = '';
		if ( $fields['thumbnail_id'] ) {
			$thumbnail = wp_get_attachment_image( $fields['thumbnail_id'], 'medium' );
		} elseif ( has_post_thumbnail( $post_id ) ) {
			$thumbnail = get_the_post_thumbnail( $post_id, 'medium' );
		}

		ob_start();
		?>
		<div class="bt-deal-box">
			<a href="<?php echo esc_url( $permalink ); ?>" class="bt-deal-box-link">
				<?php if ( $thumbnail ) : ?>
					<div class="bt-deal-box-thumb">
						<?php echo $thumbnail; ?>
					</div>
				<?php endif; ?>
				<div class="bt-deal-box-content">
					<h4 class="bt-deal-box-title"><?php echo esc_html( $title ); ?></h4>
					<div class="bt-deal-box-price">
						<?php if ( ! empty( $fields['offer_sale_price'] ) ) : ?>
							<span class="bt-sale-price">₹<?php echo esc_html( $fields['offer_sale_price'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $fields['offer_old_price'] ) ) : ?>
							<span class="bt-old-price"><del>₹<?php echo esc_html( $fields['offer_old_price'] ); ?></del></span>
						<?php endif; ?>
					</div>
				</div>
			</a>
			<div class="bt-deal-box-action">
				<a href="<?php echo esc_url( $fields['offer_url'] ); ?>" class="bt-deal-button" target="_blank" rel="nofollow noopener">
					<?php echo esc_html( $fields['button_text'] ? $fields['button_text'] : 'Get Deal' ); ?>
				</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get template part.
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialized template.
	 */
	public static function get_template_part( $slug, $name = null ) {
		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = "{$slug}-{$name}.php";
		}
		$templates[] = "{$slug}.php";

		// Look for template in theme or child theme.
		$template = locate_template( $templates );

		// If not found, look in our plugin's templates directory.
		if ( ! $template ) {
			foreach ( $templates as $template_name ) {
				$plugin_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $template_name;
				if ( file_exists( $plugin_template ) ) {
					$template = $plugin_template;
					break;
				}
			}
		}

		if ( $template ) {
			load_template( $template, false );
		}
	}
}
