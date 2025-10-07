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
		// Enqueue the grid stylesheet.
		wp_enqueue_style( 'bt-deals-grid', plugin_dir_url( __FILE__ ) . 'css/bt-deals-grid.css', array(), $this->version, 'all' );

		$atts = shortcode_atts( array(
			'category'    => '',
			'store'       => '',
			'count'       => 12,
			'show_filters' => 'false',
			'same_day'    => 'false',
		), $atts, 'loot-deals' );

		// Enqueue archive script if filters are enabled
		if ( 'true' === $atts['show_filters'] ) {
			wp_enqueue_script( 'bt-deals-archive', plugin_dir_url( __FILE__ ) . 'js/bt-deals-archive.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( 'bt-deals-archive', 'btDealsAjax', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bt_deals_nonce' ),
			) );
		}

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

		// Filter by same day if requested
		if ( 'true' === $atts['same_day'] ) {
			$args['date_query'] = array(
				array(
					'year'  => date( 'Y' ),
					'month' => date( 'm' ),
					'day'   => date( 'd' ),
				),
			);
		}

		$deals_query = new WP_Query( $args );

		ob_start();

		// Output filters if enabled
		if ( 'true' === $atts['show_filters'] ) {
			echo $this->render_deals_filters( $atts );
		}

		if ( $deals_query->have_posts() ) {
			$post_ids = wp_list_pluck( $deals_query->posts, 'ID' );
			update_meta_cache( 'post', $post_ids );
			update_object_term_cache( $post_ids, 'deal' );

			echo '<div class="bt-grid bt-grid-3 bt-deals-grid" id="bt-deals-grid">';
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
			echo '<div class="bt-grid bt-grid-3 bt-deals-grid" id="bt-deals-grid"><p>No deals found.</p></div>';
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
	 * Load custom template for deal archive page.
	 *
	 * @since    1.0.0
	 * @param    string    $template    The path of the template to include.
	 * @return   string    The path of the template to include.
	 */
	public function load_deal_archive_template( $template ) {
		if ( is_post_type_archive( 'deal' ) ) {
			$new_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/archive-deal.php';
			if ( file_exists( $new_template ) ) {
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
	 * AJAX handler for filtering deals.
	 *
	 * @since 1.0.0
	 */
	public function filter_deals_ajax_handler() {
		check_ajax_referer( 'bt_deals_nonce', 'nonce' );

		$filters = isset( $_POST['filters'] ) ? $_POST['filters'] : array();
		$count = isset( $filters['count'] ) ? intval( $filters['count'] ) : 12;

		$args = array(
			'post_type'      => 'deal',
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'paged'          => 1,
		);

		$tax_query = array( 'relation' => 'AND' );
		$meta_query = array( 'relation' => 'AND' );

		// Category filter
		if ( ! empty( $filters['categories'] ) && is_array( $filters['categories'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $filters['categories'] ),
			);
		}

		// Store filter
		if ( ! empty( $filters['stores'] ) && is_array( $filters['stores'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'store',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $filters['stores'] ),
			);
		}

		// Price filters
		if ( ! empty( $filters['min_price'] ) ) {
			$meta_query[] = array(
				'key'     => '_btdeals_offer_sale_price',
				'value'   => floatval( $filters['min_price'] ),
				'compare' => '>=',
				'type'    => 'DECIMAL',
			);
		}

		if ( ! empty( $filters['max_price'] ) ) {
			$meta_query[] = array(
				'key'     => '_btdeals_offer_sale_price',
				'value'   => floatval( $filters['max_price'] ),
				'compare' => '<=',
				'type'    => 'DECIMAL',
			);
		}

		// Search filter
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = sanitize_text_field( $filters['search'] );
		}

		// Add tax and meta queries
		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}

		if ( count( $meta_query ) > 1 ) {
			$args['meta_query'] = $meta_query;
		}

		// Exclude expired deals
		$args['meta_query'][] = array(
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
		);

		$deals_query = new WP_Query( $args );

		if ( $deals_query->have_posts() ) {
			$post_ids = wp_list_pluck( $deals_query->posts, 'ID' );
			update_meta_cache( 'post', $post_ids );
			update_object_term_cache( $post_ids, 'deal' );

			ob_start();
			while ( $deals_query->have_posts() ) {
				$deals_query->the_post();
				$deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( get_the_ID() );
				echo Bigtricks_Deals_Content_Helper::render_deal_item( $deal_data );
			}
			$deals_html = ob_get_clean();

			$load_more_html = '';
			if ( $deals_query->max_num_pages > 1 ) {
				$load_more_html = '<button class="bt-deals-load-more" data-page="2" data-max-pages="' . esc_attr( $deals_query->max_num_pages ) . '" data-atts="' . esc_attr( json_encode( array_merge( array( 'count' => $count ), $filters ) ) ) . '">Load More Deals</button>';
			}

			wp_send_json_success( array(
				'html'      => $deals_html,
				'load_more' => $load_more_html,
			) );
		} else {
			wp_send_json_error( 'No deals found.' );
		}
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
		$filters = isset( $_POST['filters'] ) ? $_POST['filters'] : array();

		$args = array(
			'post_type'      => 'deal',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['count'] ),
			'paged'          => $page,
		);

		$tax_query = array( 'relation' => 'AND' );
		$meta_query = array( 'relation' => 'AND' );

		// Category filter
		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $atts['category'] ) ),
			);
		}

		if ( ! empty( $filters['categories'] ) && is_array( $filters['categories'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $filters['categories'] ),
			);
		}

		// Store filter
		if ( ! empty( $atts['store'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'store',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', explode( ',', $atts['store'] ) ),
			);
		}

		if ( ! empty( $filters['stores'] ) && is_array( $filters['stores'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'store',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $filters['stores'] ),
			);
		}

		// Price filters
		if ( ! empty( $filters['min_price'] ) ) {
			$meta_query[] = array(
				'key'     => '_btdeals_offer_sale_price',
				'value'   => floatval( $filters['min_price'] ),
				'compare' => '>=',
				'type'    => 'DECIMAL',
			);
		}

		if ( ! empty( $filters['max_price'] ) ) {
			$meta_query[] = array(
				'key'     => '_btdeals_offer_sale_price',
				'value'   => floatval( $filters['max_price'] ),
				'compare' => '<=',
				'type'    => 'DECIMAL',
			);
		}

		// Search filter
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = sanitize_text_field( $filters['search'] );
		}

		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}

		if ( count( $meta_query ) > 1 ) {
			$args['meta_query'] = $meta_query;
		}

		// Exclude expired deals
		$args['meta_query'][] = array(
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
		);

		$deals_query = new WP_Query( $args );

		if ( $deals_query->have_posts() ) {
			$post_ids = wp_list_pluck( $deals_query->posts, 'ID' );
			update_meta_cache( 'post', $post_ids );
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
			update_meta_cache( 'post', $post_ids );
			update_object_term_cache( $post_ids, 'deal' );
	
			foreach ( $query->posts as $post ) {
				$deals[] = Bigtricks_Deals_Content_Helper::get_deal_data( $post->ID );
			}
			wp_reset_postdata();
		}
	
		wp_send_json_success( $deals );
	}

	/**
	 * Render the deals filters UI.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string Filter HTML.
	 */
	public function render_deals_filters( $atts ) {
		ob_start();
		?>
		<div class="bt-deals-filters">
			<div class="bt-filters-row">
				<div class="bt-filter-group">
					<label for="bt-search">Search Deals:</label>
					<input type="text" id="bt-search" placeholder="Search by title or description..." />
				</div>

				<div class="bt-filter-group">
					<label for="bt-category">Category:</label>
					<select id="bt-category">
						<option value="">All Categories</option>
						<?php
						$categories = get_terms( array(
							'taxonomy' => 'category',
							'hide_empty' => true,
						) );
						foreach ( $categories as $category ) {
							echo '<option value="' . esc_attr( $category->term_id ) . '">' . esc_html( $category->name ) . '</option>';
						}
						?>
					</select>
				</div>

				<div class="bt-filter-group">
					<label for="bt-store">Store:</label>
					<select id="bt-store">
						<option value="">All Stores</option>
						<?php
						$stores = get_terms( array(
							'taxonomy' => 'store',
							'hide_empty' => true,
						) );
						foreach ( $stores as $store ) {
							echo '<option value="' . esc_attr( $store->term_id ) . '">' . esc_html( $store->name ) . '</option>';
						}
						?>
					</select>
				</div>

				<div class="bt-filter-group">
					<label for="bt-min-price">Min Price:</label>
					<input type="number" id="bt-min-price" placeholder="0" min="0" />
				</div>

				<div class="bt-filter-group">
					<label for="bt-max-price">Max Price:</label>
					<input type="number" id="bt-max-price" placeholder="10000" min="0" />
				</div>

				<div class="bt-filter-group">
					<button id="bt-apply-filters" class="bt-btn bt-btn-primary">Apply Filters</button>
					<button id="bt-clear-filters" class="bt-btn bt-btn-secondary">Clear</button>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the sidebar filters UI (Amazon-style).
	 *
	 * @since 1.0.0
	 * @return string Sidebar filter HTML.
	 */
	public static function render_sidebar_filters() {
		ob_start();
		?>
		<div class="bt-sidebar-filters">
			<!-- Search -->
			<div class="bt-filter-panel">
				<div class="bt-filter-header">
					<h3>Search</h3>
				</div>
				<div class="bt-filter-content">
					<div class="bt-search-box">
						<input type="text" id="bt-sidebar-search" placeholder="Search deals..." />
						<button type="button" id="bt-search-btn">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="11" cy="11" r="8"></circle>
								<path d="m21 21-4.35-4.35"></path>
							</svg>
						</button>
					</div>
				</div>
			</div>

			<!-- Categories -->
			<div class="bt-filter-panel">
				<div class="bt-filter-header">
					<h3>Categories</h3>
					<button class="bt-filter-toggle" data-target="categories">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="6,9 12,15 18,9"></polyline>
						</svg>
					</button>
				</div>
				<div class="bt-filter-content bt-filter-categories" id="categories-content">
					<?php
					$categories = get_terms([
						'taxonomy' => 'category',
						'hide_empty' => true,
						'number' => 10
					]);

					if ($categories && !is_wp_error($categories)) {
						foreach ($categories as $category) {
							$count = $category->count;
							echo '<label class="bt-filter-option">';
							echo '<input type="checkbox" name="bt-category-filter" value="' . esc_attr($category->term_id) . '" />';
							echo '<span class="bt-option-text">' . esc_html($category->name) . '</span>';
							echo '<span class="bt-option-count">(' . $count . ')</span>';
							echo '</label>';
						}
					}
					?>
				</div>
			</div>

			<!-- Stores -->
			<div class="bt-filter-panel">
				<div class="bt-filter-header">
					<h3>Stores</h3>
					<button class="bt-filter-toggle" data-target="stores">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="6,9 12,15 18,9"></polyline>
						</svg>
					</button>
				</div>
				<div class="bt-filter-content bt-filter-stores" id="stores-content">
					<?php
					$stores = get_terms([
						'taxonomy' => 'store',
						'hide_empty' => true,
						'number' => 15
					]);

					if ($stores && !is_wp_error($stores)) {
						foreach ($stores as $store) {
							$count = $store->count;
							$logo = get_term_meta($store->term_id, 'thumb_image', true);
							echo '<label class="bt-filter-option bt-store-option">';
							echo '<input type="checkbox" name="bt-store-filter" value="' . esc_attr($store->term_id) . '" />';
							if ($logo) {
								echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr($store->name) . '" class="bt-store-thumb" />';
							}
							echo '<span class="bt-option-text">' . esc_html($store->name) . '</span>';
							echo '<span class="bt-option-count">(' . $count . ')</span>';
							echo '</label>';
						}
					}
					?>
				</div>
			</div>

			<!-- Price Range -->
			<div class="bt-filter-panel">
				<div class="bt-filter-header">
					<h3>Price Range</h3>
					<button class="bt-filter-toggle" data-target="price">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="6,9 12,15 18,9"></polyline>
						</svg>
					</button>
				</div>
				<div class="bt-filter-content bt-filter-price" id="price-content">
					<div class="bt-price-inputs">
						<div class="bt-price-group">
							<label for="bt-sidebar-min-price">Min Price</label>
							<input type="number" id="bt-sidebar-min-price" placeholder="0" min="0" />
						</div>
						<div class="bt-price-group">
							<label for="bt-sidebar-max-price">Max Price</label>
							<input type="number" id="bt-sidebar-max-price" placeholder="10000" min="0" />
						</div>
					</div>
					<button id="bt-apply-price-filter" class="bt-apply-price-btn">Apply</button>
				</div>
			</div>

			<!-- Clear Filters -->
			<div class="bt-filter-actions">
				<button id="bt-sidebar-clear-filters" class="bt-clear-all-btn">Clear All Filters</button>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Apply sorting parameters to WP_Query args.
	 *
	 * @since 1.0.0
	 * @param array  $args   Query arguments.
	 * @param string $sort_by Sort option.
	 */
	private function apply_sorting_to_args( &$args, $sort_by ) {
		switch ( $sort_by ) {
			case 'date_asc':
				$args['orderby'] = 'date';
				$args['order'] = 'ASC';
				break;
			case 'price_asc':
				$args['meta_key'] = '_btdeals_offer_sale_price';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'ASC';
				break;
			case 'price_desc':
				$args['meta_key'] = '_btdeals_offer_sale_price';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'DESC';
				break;
			case 'discount_desc':
				$args['meta_key'] = '_btdeals_discount_percent';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'DESC';
				break;
			case 'title_asc':
				$args['orderby'] = 'title';
				$args['order'] = 'ASC';
				break;
			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order'] = 'DESC';
				break;
			case 'date_desc':
			default:
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
				break;
		}
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
