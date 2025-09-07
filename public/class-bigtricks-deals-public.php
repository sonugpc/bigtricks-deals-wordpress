<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://bigtricks.in
 * @since      1.0.0
 *
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and shortcode functionality.
 *
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/public
 * @author     Bigtricks <sonugpc@gmail.com>
 */
class Bigtricks_Deals_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the shortcodes for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_shortcodes() {
		add_shortcode( 'loot-deal', array( $this, 'render_loot_deal_shortcode' ) );
		add_shortcode( 'loot-deals', array( $this, 'render_loot_deals_archive_shortcode' ) );
	}

	/**
	 * Render the single loot deal shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function render_loot_deal_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts, 'loot-deal' );

		$post_id = intval( $atts['id'] );

		if ( ! $post_id || 'deal' !== get_post_type( $post_id ) ) {
			return '';
		}

		return Bigtricks_Deals_Content_Helper::render_deal_box( $post_id );
	}

	/**
	 * Render the loot deals archive shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function render_loot_deals_archive_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'category' => '',
			'store'    => '',
			'count'    => 12,
		), $atts, 'loot-deals' );

		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		$args = array(
			'post_type'      => 'deal',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['count'] ),
			'paged'          => $paged,
		);

		$tax_query = array( 'relation' => 'AND' );

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $atts['category'] ) ),
			);
		}

		if ( ! empty( $atts['store'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'store',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $atts['store'] ) ),
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}

		$deals_query = new WP_Query( $args );

		ob_start();

		if ( $deals_query->have_posts() ) {
			echo '<div class="bt-deals-archive-grid">';
			while ( $deals_query->have_posts() ) {
				$deals_query->the_post();
				echo Bigtricks_Deals_Content_Helper::render_deal_box( get_the_ID() );
			}
			echo '</div>';

			if ( $deals_query->max_num_pages > 1 ) {
				echo '<div class="bt-deals-load-more-wrapper">';
				echo '<button class="bt-deals-load-more" data-page="2" data-max-pages="' . esc_attr( $deals_query->max_num_pages ) . '" data-atts="' . esc_attr( json_encode( $atts ) ) . '">Load More Deals</button>';
				echo '</div>';
			}
		} else {
			echo '<p>No deals found.</p>';
		}

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Load custom template for single deal posts and enqueue assets.
	 *
	 * @since    1.0.0
	 * @param    string    $template    The path of the template to include.
	 * @return   string    The path of the template to include.
	 */
	public function load_single_deal_template( $template ) {
		if ( is_singular( 'deal' ) ) {
			$this->enqueue_single_deal_assets();
			$new_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/single-deal.php';
			if ( '' != $new_template ) {
				return $new_template;
			}
		}
		return $template;
	}

	/**
	 * Enqueue scripts and styles for the single deal page.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_single_deal_assets() {
		wp_enqueue_style( 'bt-deals-single', plugin_dir_url( __FILE__ ) . 'css/bt-deals-single.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'bt-deals-single', plugin_dir_url( __FILE__ ) . 'js/bt-deals-single.js', array( 'jquery' ), $this->version, true );

		$post_id = get_the_ID();
		$product_name = get_post_meta( $post_id, '_btdeals_product_name', true ) ?: get_the_title();
		$description = get_post_meta( $post_id, '_btdeals_short_description', true ) ?: get_the_excerpt();
		
		$old_price = floatval( get_post_meta( $post_id, '_btdeals_offer_old_price', true ) );
		$sale_price = floatval( get_post_meta( $post_id, '_btdeals_offer_sale_price', true ) );
		$discount_percent = 0;
		if ( $old_price > $sale_price ) {
			$discount_percent = round( ( ( $old_price - $sale_price ) / $old_price ) * 100 );
		}

		$stores = get_the_terms( $post_id, 'store' );
		$store_id = 0;
		if ( $stores && ! is_wp_error( $stores ) ) {
			$store_id = reset( $stores )->term_id;
		}

		wp_localize_script( 'bt-deals-single', 'btDealsAjax', [
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'bt_deals_nonce' ),
			'postId'     => $post_id,
			'storeId'    => $store_id,
			'shareUrl'   => get_permalink(),
			'shareTitle' => $product_name . ' - ' . $discount_percent . '% Off',
			'shareText'  => wp_strip_all_tags( $description ),
		]);
	}

	/**
	 * AJAX handler for loading more deals.
	 *
	 * @since 1.0.0
	 */
	public function load_more_deals_ajax_handler() {
		check_ajax_referer( 'bt_deals_nonce', 'nonce' );

		$page = intval( $_POST['page'] );
		$atts = json_decode( stripslashes( $_POST['atts'] ), true );

		$args = array(
			'post_type'      => 'deal',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['count'] ),
			'paged'          => $page,
		);

		$tax_query = array( 'relation' => 'AND' );

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $atts['category'] ) ),
			);
		}

		if ( ! empty( $atts['store'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'store',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $atts['store'] ) ),
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}

		$deals_query = new WP_Query( $args );

		if ( $deals_query->have_posts() ) {
			ob_start();
			while ( $deals_query->have_posts() ) {
				$deals_query->the_post();
				echo Bigtricks_Deals_Content_Helper::render_deal_box( get_the_ID() );
			}
			wp_send_json_success( ob_get_clean() );
		} else {
			wp_send_json_error( 'No more deals.' );
		}
	}
}
