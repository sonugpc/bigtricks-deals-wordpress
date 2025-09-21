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
			'id'    => 0,
			'field' => '',
		), $atts, 'loot-deal' );

		$post_id = ( 0 === $atts['id'] ) ? get_the_ID() : intval( $atts['id'] );

		if ( ! $post_id || 'deal' !== get_post_type( $post_id ) ) {
			return '';
		}

		if ( ! empty( $atts['field'] ) ) {
			return get_post_meta( $post_id, $atts['field'], true );
		}
		$deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( $post_id );
		return Bigtricks_Deals_Content_Helper::render_deal_item( $deal_data );
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
			$post_ids = wp_list_pluck( $deals_query->posts, 'ID' );
			update_post_meta_cache( $post_ids );
			update_object_term_cache( $post_ids, 'deal' );
			
			echo '<div class="rb-row rb-n20-gutter">';
			while ( $deals_query->have_posts() ) {
				$deals_query->the_post();
				$deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( get_the_ID() );
				echo Bigtricks_Deals_Content_Helper::render_deal_item( $deal_data );
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
			'shareTitle' => $product_name,
			'sharePrice' => '₹' . number_format( $sale_price, 2 ),
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
			$post_ids = wp_list_pluck( $deals_query->posts, 'ID' );
			update_post_meta_cache( $post_ids );
			update_object_term_cache( $post_ids, 'deal' );

			ob_start();
			while ( $deals_query->have_posts() ) {
				$deals_query->the_post();
				$deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( get_the_ID() );
				echo Bigtricks_Deals_Content_Helper::render_deal_item( $deal_data );
			}
			wp_send_json_success( ob_get_clean() );
		} else {
			wp_send_json_error( 'No more deals.' );
		}
	}

	/**
	 * AJAX callback to load more content.
	 *
	 * @since 1.0.0
	 */
	public function load_more_content_callback() {
		check_ajax_referer( 'store_content_nonce', 'nonce' );

		$store_id = isset( $_POST['store_id'] ) ? intval( $_POST['store_id'] ) : 0;
		$content_type = isset( $_POST['content_type'] ) ? sanitize_text_field( $_POST['content_type'] ) : '';
		$page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 2;

		if ( ! $store_id || ! $content_type ) {
			wp_send_json_error( 'Missing parameters.' );
		}

		$html = '';
		if ( 'deal' === $content_type ) {
			$html = Bigtricks_Deals_Content_Helper::get_deals_content( $store_id, 6, $page );
		}

		wp_send_json_success( $html );
	}

	/**
	 * AJAX callback to get similar deals.
	 *
	 * @since 1.0.0
	 */
	public function get_similar_deals_callback() {
		check_ajax_referer( 'bt_deals_nonce', 'nonce' );
	
		$deal_id = isset( $_POST['deal_id'] ) ? intval( $_POST['deal_id'] ) : 0;
		$limit   = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 6;
	
		if ( ! $deal_id ) {
			wp_send_json_error( 'Invalid deal ID.' );
		}
	
		$current_stores     = wp_get_post_terms( $deal_id, 'store', array( 'fields' => 'ids' ) );
		$current_categories = wp_get_post_terms( $deal_id, 'category', array( 'fields' => 'ids' ) );
	
		$args = array(
			'post_type'      => 'deal',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $deal_id ),
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
			$args['tax_query'] = $tax_query;
		}
	
		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			unset( $args['tax_query'] );
			$query = new WP_Query( $args );
		}
	
		$deals = array();
		if ( $query->have_posts() ) {
			$post_ids = wp_list_pluck( $query->posts, 'ID' );
			update_post_meta_cache( $post_ids );
			update_object_term_cache( $post_ids, 'deal' );
	
			foreach ( $query->posts as $post ) {
				$deals[] = Bigtricks_Deals_Content_Helper::get_deal_data( $post->ID );
			}
			wp_reset_postdata();
		}
	
		wp_send_json_success( $deals );
	}

	/**
	 * AJAX callback to track events.
	 *
	 * @since 1.0.0
	 */
	public function track_event_callback() {
		check_ajax_referer( 'bt_deals_nonce', 'nonce' );

		$event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
		$deal_id = isset( $_POST['deal_id'] ) ? intval( $_POST['deal_id'] ) : 0;
		$extra_data = isset( $_POST['extra_data'] ) ? sanitize_text_field( $_POST['extra_data'] ) : '';

		if ( ! $event_type || ! $deal_id ) {
			wp_send_json_error( 'Missing required parameters.' );
		}

		// Log event - you can customize this to integrate with your analytics
		$log_data = array(
			'event_type' => $event_type,
			'deal_id'    => $deal_id,
			'extra_data' => $extra_data,
			'user_ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
			'timestamp'  => current_time( 'mysql' ),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
		);

		// Store in transient for basic tracking (you can replace with database table)
		$existing_logs = get_transient( 'bt_deals_event_logs' ) ?: array();
		$existing_logs[] = $log_data;

		// Keep only last 1000 entries
		if ( count( $existing_logs ) > 1000 ) {
			$existing_logs = array_slice( $existing_logs, -1000 );
		}

		set_transient( 'bt_deals_event_logs', $existing_logs, DAY_IN_SECONDS );

		// Fire action hook for custom integrations
		do_action( 'bt_deals_event_tracked', $event_type, $deal_id, $extra_data, $log_data );

		wp_send_json_success( array( 'message' => 'Event tracked successfully' ) );
	}
}
