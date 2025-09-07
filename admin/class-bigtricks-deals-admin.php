<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://bigtricks.in
 * @since      1.0.0
 *
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bigtricks_Deals
 * @subpackage Bigtricks_Deals/admin
 * @author     Bigtricks <sonugpc@gmail.com>
 */
class Bigtricks_Deals_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the "Deal" custom post type.
	 *
	 * @since 1.0.0
	 */
	public function register_deal_cpt() {
		$labels = [
			"name" => __( "Deals", "custom-post-type-ui" ),
			"singular_name" => __( "Deal", "custom-post-type-ui" ),
			"all_items" => __( "Loot Deals", "custom-post-type-ui" ),
			"add_new_item" => __( "Add New Deal", "custom-post-type-ui" ),
			"edit_item" => __( "Edit Deal", "custom-post-type-ui" ),
		];
	
		$args = [
			"label" => __( "Deals", "custom-post-type-ui" ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "deals",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => "deals",
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => [ "slug" => "deal", "with_front" => true ],
			"query_var" => true,
			"menu_icon" => "dashicons-cart",
			"supports" => [ "title", "editor", "thumbnail", "custom-fields", "comments" ],	
			"taxonomies" => [ "category", "store" ],
		];
	
		register_post_type( "deal", $args );
	}

	/**
	 * Register the "Store" custom taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function register_store_taxonomy() {
		$labels = [
			"name" => __( "Stores", "custom-post-type-ui" ),
			"singular_name" => __( "Store", "custom-post-type-ui" ),
			"menu_name" => __( "Stores", "custom-post-type-ui" ),
			"all_items" => __( "All Stores", "custom-post-type-ui" ),
			"edit_item" => __( "Edit Store", "custom-post-type-ui" ),
			"view_item" => __( "View Store", "custom-post-type-ui" ),
			"update_item" => __( "Update Store name", "custom-post-type-ui" ),
			"add_new_item" => __( "Add new Store", "custom-post-type-ui" ),
			"new_item_name" => __( "New Store name", "custom-post-type-ui" ),
			"parent_item" => __( "Parent Store", "custom-post-type-ui" ),
			"parent_item_colon" => __( "Parent Store:", "custom-post-type-ui" ),
			"search_items" => __( "Search Stores", "custom-post-type-ui" ),
			"popular_items" => __( "Popular Stores", "custom-post-type-ui" ),
			"separate_items_with_commas" => __( "Separate Stores with commas", "custom-post-type-ui" ),
			"add_or_remove_items" => __( "Add or remove Stores", "custom-post-type-ui" ),
			"choose_from_most_used" => __( "Choose from the most used Stores", "custom-post-type-ui" ),
			"not_found" => __( "No Stores found", "custom-post-type-ui" ),
			"no_terms" => __( "No Stores", "custom-post-type-ui" ),
			"items_list_navigation" => __( "Stores list navigation", "custom-post-type-ui" ),
			"items_list" => __( "Stores list", "custom-post-type-ui" ),
		];
	
		$args = [
			"label" => __( "Stores", "custom-post-type-ui" ),
			"labels" => $labels,
			"public" => true,
			"publicly_queryable" => true,
			"hierarchical" => true,
			"show_ui" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"query_var" => true,
			"rewrite" => [ 'slug' => 'store', 'with_front' => true, ],
			"show_admin_column" => true,
			"show_in_rest" => true,
			"rest_base" => "stores",
			"rest_controller_class" => "WP_REST_Terms_Controller",
			"show_in_quick_edit" => true,
		];
		register_taxonomy( "store", [ "deal" ], $args );
	}

	/**
	 * Add the meta box for deal details.
	 *
	 * @since 1.0.0
	 */
	public function add_deal_meta_box() {
		add_meta_box(
			'btdeals_deal_details_new',
			__( 'Offer Details', 'bigtricks-deals' ),
			array( $this, 'render_deal_meta_box' ),
			'deal',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue media library scripts for deal post type
	 *
	 * @since 1.0.0
	 */
	public function enqueue_media_library() {
		global $pagenow, $post_type;
		
		if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'deal' === $post_type ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Render the meta box for deal details.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The post object.
	 */
	public function render_deal_meta_box( $post ) {
		wp_nonce_field( 'btdeals_save_deal_meta_data', 'btdeals_meta_box_nonce' );

		$fields = [
			'product_name'      => get_post_meta( $post->ID, '_btdeals_product_name', true ),
			'short_description' => get_post_meta( $post->ID, '_btdeals_short_description', true ),
			'offer_url'         => get_post_meta( $post->ID, '_btdeals_offer_url', true ),
			'disclaimer'        => get_post_meta( $post->ID, '_btdeals_disclaimer', true ),
			'offer_old_price'   => get_post_meta( $post->ID, '_btdeals_offer_old_price', true ),
			'offer_sale_price'  => get_post_meta( $post->ID, '_btdeals_offer_sale_price', true ),
			'coupon_code'       => get_post_meta( $post->ID, '_btdeals_coupon_code', true ),
			'expiration_date'   => get_post_meta( $post->ID, '_btdeals_expiration_date', true ),
			'mask_coupon'       => get_post_meta( $post->ID, '_btdeals_mask_coupon', true ),
			'is_expired'        => get_post_meta( $post->ID, '_btdeals_is_expired', true ),
			'verify_label'      => get_post_meta( $post->ID, '_btdeals_verify_label', true ),
			'button_text'       => get_post_meta( $post->ID, '_btdeals_button_text', true ),
			'thumbnail_id'      => get_post_meta( $post->ID, '_btdeals_thumbnail_id', true ),
			'brand_logo_id'     => get_post_meta( $post->ID, '_btdeals_brand_logo_id', true ),
			'brand_logo_url'    => get_post_meta( $post->ID, '_btdeals_brand_logo_url', true ),
			'discount_tag'      => get_post_meta( $post->ID, '_btdeals_discount_tag', true ),
		];
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="btdeals_product_name"><?php _e( 'Name Of Product', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_product_name" name="btdeals_product_name" value="<?php echo esc_attr( $fields['product_name'] ); ?>" class="widefat">
					<p class="description"><?php _e( 'If empty, will use post title', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_short_description"><?php _e( 'Short Description of Product', 'bigtricks-deals' ); ?></label></th>
					<td><textarea id="btdeals_short_description" name="btdeals_short_description" class="widefat" rows="3"><?php echo esc_textarea( $fields['short_description'] ); ?></textarea>
					<p class="description"><?php _e( 'Brief product description', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_offer_url"><?php _e( 'Offer URL', 'bigtricks-deals' ); ?></label></th>
					<td><input type="url" id="btdeals_offer_url" name="btdeals_offer_url" value="<?php echo esc_url( $fields['offer_url'] ); ?>" class="widefat"></td>
				</tr>
				<tr>
					<th><label for="btdeals_disclaimer"><?php _e( 'Disclaimer', 'bigtricks-deals' ); ?></label></th>
					<td><textarea id="btdeals_disclaimer" name="btdeals_disclaimer" class="widefat" rows="4"><?php echo esc_textarea( $fields['disclaimer'] ); ?></textarea>
					<p class="description"><?php _e( 'Optional. It works in deal lists. HTML and shortcodes are supported.', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_offer_old_price"><?php _e( 'Offer Old Price', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_offer_old_price" name="btdeals_offer_old_price" value="<?php echo esc_attr( $fields['offer_old_price'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="btdeals_offer_sale_price"><?php _e( 'Offer Sale Price', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_offer_sale_price" name="btdeals_offer_sale_price" value="<?php echo esc_attr( $fields['offer_sale_price'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="btdeals_coupon_code"><?php _e( 'Coupon Code', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_coupon_code" name="btdeals_coupon_code" value="<?php echo esc_attr( $fields['coupon_code'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="btdeals_expiration_date"><?php _e( 'Expiration Date', 'bigtricks-deals' ); ?></label></th>
					<td><input type="date" id="btdeals_expiration_date" name="btdeals_expiration_date" value="<?php echo esc_attr( $fields['expiration_date'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><?php _e( 'Mask Coupon Code?', 'bigtricks-deals' ); ?></th>
					<td><input type="checkbox" id="btdeals_mask_coupon" name="btdeals_mask_coupon" <?php checked( $fields['mask_coupon'], 'on' ); ?>>
					<label for="btdeals_mask_coupon"><?php _e( 'Yes', 'bigtricks-deals' ); ?></label></td>
				</tr>
				<tr>
					<th><?php _e( 'Offer is Expired?', 'bigtricks-deals' ); ?></th>
					<td><input type="checkbox" id="btdeals_is_expired" name="btdeals_is_expired" <?php checked( $fields['is_expired'], 'on' ); ?>>
					<label for="btdeals_is_expired"><?php _e( 'Yes', 'bigtricks-deals' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="btdeals_verify_label"><?php _e( 'Verify Label', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_verify_label" name="btdeals_verify_label" value="<?php echo esc_attr( $fields['verify_label'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="btdeals_button_text"><?php _e( 'Button Text', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_button_text" name="btdeals_button_text" value="<?php echo esc_attr( $fields['button_text'] ); ?>" class="regular-text" maxlength="14"></td>
				</tr>
				<tr>
					<th><label><?php _e( 'Upload Thumbnail', 'bigtricks-deals' ); ?></label></th>
					<td>
						<?php 
						$thumbnail_url = '';
						if ( $fields['thumbnail_id'] ) {
							$thumbnail_url = wp_get_attachment_image_src( $fields['thumbnail_id'], 'medium' );
							$thumbnail_url = $thumbnail_url ? $thumbnail_url[0] : '';
						}
						?>
						<input type="hidden" id="btdeals_thumbnail_id" name="btdeals_thumbnail_id" value="<?php echo esc_attr( $fields['thumbnail_id'] ); ?>">
						<input type="button" class="button" id="btdeals_thumbnail_upload" value="<?php _e( 'Upload Thumbnail', 'bigtricks-deals' ); ?>">
						<input type="button" class="button" id="btdeals_thumbnail_remove" value="<?php _e( 'Remove', 'bigtricks-deals' ); ?>" style="<?php echo $thumbnail_url ? '' : 'display:none;'; ?>">
						<div id="btdeals_thumbnail_preview" style="margin-top: 10px;">
							<?php if ( $thumbnail_url ) : ?>
								<img src="<?php echo esc_url( $thumbnail_url ); ?>" style="max-width: 150px; height: auto;">
							<?php endif; ?>
						</div>
						<p class="description"><?php _e( 'Upload a custom thumbnail image for this deal', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label><?php _e( 'Brand Logo', 'bigtricks-deals' ); ?></label></th>
					<td>
						<?php 
						$brand_logo_url = $fields['brand_logo_url'];
						if ( $fields['brand_logo_id'] ) {
							$logo_url = wp_get_attachment_image_src( $fields['brand_logo_id'], 'thumbnail' );
							if ( $logo_url ) {
								$brand_logo_url = $logo_url[0];
							}
						}
						?>
						<input type="hidden" id="btdeals_brand_logo_id" name="btdeals_brand_logo_id" value="<?php echo esc_attr( $fields['brand_logo_id'] ); ?>">
						<input type="url" id="btdeals_brand_logo_url" name="btdeals_brand_logo_url" value="<?php echo esc_url( $fields['brand_logo_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Brand Logo URL (or use upload button)', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_brand_logo_upload" value="<?php _e( 'Upload from Library', 'bigtricks-deals' ); ?>">
						<input type="button" class="button" id="btdeals_brand_logo_remove" value="<?php _e( 'Remove', 'bigtricks-deals' ); ?>" style="<?php echo $brand_logo_url ? '' : 'display:none;'; ?>">
						<div id="btdeals_brand_logo_preview" style="margin-top: 10px;">
							<?php if ( $brand_logo_url ) : ?>
								<img src="<?php echo esc_url( $brand_logo_url ); ?>" style="max-width: 100px; height: auto;">
							<?php endif; ?>
						</div>
						<p class="description"><?php _e( 'Brand logo URL or upload from media library. Falls back to store taxonomy thumbnail if empty.', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="btdeals_discount_tag"><?php _e( 'Discount Tag', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_discount_tag" name="btdeals_discount_tag" value="<?php echo esc_attr( $fields['discount_tag'] ); ?>" class="regular-text" maxlength="5">
					<p class="description"><?php _e( 'Max 5 symbols. E.g., $20 or 50%', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label><?php _e( 'Shortcode', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" readonly value="[quick_offer id="<?php echo $post->ID; ?>"]" class="widefat"></td>
				</tr>
			</tbody>
		</table>
		
		<script>
		jQuery(document).ready(function($) {
			// Thumbnail upload
			$('#btdeals_thumbnail_upload').on('click', function(e) {
				e.preventDefault();
				
				var mediaUploader = wp.media({
					title: 'Choose Thumbnail',
					button: { text: 'Choose Thumbnail' },
					multiple: false
				});

				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					$('#btdeals_thumbnail_id').val(attachment.id);
					$('#btdeals_thumbnail_preview').html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">');
					$('#btdeals_thumbnail_remove').show();
				});

				mediaUploader.open();
			});

			// Thumbnail remove
			$('#btdeals_thumbnail_remove').on('click', function(e) {
				e.preventDefault();
				$('#btdeals_thumbnail_id').val('');
				$('#btdeals_thumbnail_preview').html('');
				$(this).hide();
			});

			// Brand logo upload
			$('#btdeals_brand_logo_upload').on('click', function(e) {
				e.preventDefault();
				
				var mediaUploader = wp.media({
					title: 'Choose Brand Logo',
					button: { text: 'Choose Logo' },
					multiple: false
				});

				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					$('#btdeals_brand_logo_id').val(attachment.id);
					$('#btdeals_brand_logo_url').val(attachment.url);
					$('#btdeals_brand_logo_preview').html('<img src="' + attachment.url + '" style="max-width: 100px; height: auto;">');
					$('#btdeals_brand_logo_remove').show();
				});

				mediaUploader.open();
			});

			// Brand logo remove
			$('#btdeals_brand_logo_remove').on('click', function(e) {
				e.preventDefault();
				$('#btdeals_brand_logo_id').val('');
				$('#btdeals_brand_logo_url').val('');
				$('#btdeals_brand_logo_preview').html('');
				$(this).hide();
			});
		});
		</script>
		<?php
	}

	/**
	 * Save the meta data for the deal post type.
	 *
	 * @since 1.0.0
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_deal_meta_data( $post_id ) {
		if ( ! isset( $_POST['btdeals_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['btdeals_meta_box_nonce'], 'btdeals_save_deal_meta_data' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = [
			'product_name', 'offer_url', 'offer_old_price', 'offer_sale_price', 'coupon_code',
			'expiration_date', 'verify_label', 'button_text', 'brand_logo_url', 'discount_tag'
		];

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ 'btdeals_' . $field ] ) ) {
				update_post_meta( $post_id, '_btdeals_' . $field, sanitize_text_field( $_POST[ 'btdeals_' . $field ] ) );
			}
		}
		
		// Handle textarea fields
		if ( isset( $_POST['btdeals_short_description'] ) ) {
			update_post_meta( $post_id, '_btdeals_short_description', wp_kses_post( $_POST['btdeals_short_description'] ) );
		}
		
		if ( isset( $_POST['btdeals_disclaimer'] ) ) {
			update_post_meta( $post_id, '_btdeals_disclaimer', wp_kses_post( $_POST['btdeals_disclaimer'] ) );
		}

		// Handle media library uploads
		if ( isset( $_POST['btdeals_thumbnail_id'] ) ) {
			update_post_meta( $post_id, '_btdeals_thumbnail_id', intval( $_POST['btdeals_thumbnail_id'] ) );
		}

		if ( isset( $_POST['btdeals_brand_logo_id'] ) ) {
			update_post_meta( $post_id, '_btdeals_brand_logo_id', intval( $_POST['btdeals_brand_logo_id'] ) );
		}

		// Checkboxes
		$checkboxes = ['mask_coupon', 'is_expired'];
		foreach ( $checkboxes as $checkbox ) {
			if ( isset( $_POST[ 'btdeals_' . $checkbox ] ) ) {
				update_post_meta( $post_id, '_btdeals_' . $checkbox, 'on' );
			} else {
				update_post_meta( $post_id, '_btdeals_' . $checkbox, 'off' );
			}
		}
	}

	/**
	 * Register custom REST API fields for the "deal" post type.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_fields() {
	$meta_fields = [
		'product_name'      => 'string',
		'short_description' => 'string',
		'offer_url'         => 'string',
		'disclaimer'        => 'string',
		'offer_old_price'   => 'string',
		'offer_sale_price'  => 'string',
		'coupon_code'       => 'string',
		'expiration_date'   => 'string',
		'mask_coupon'       => 'string',
		'is_expired'        => 'string',
		'verify_label'      => 'string',
		'button_text'       => 'string',
		'thumbnail_id'      => 'integer',
		'brand_logo_id'     => 'integer',
		'brand_logo_url'    => 'string',
		'discount_tag'      => 'string',
		'discount'          => 'integer',
	];

		foreach ( $meta_fields as $field => $type ) {
			register_rest_field( 'deal', $field, array(
				'get_callback'    => function( $object, $field_name, $request ) {
					$post_id = $object['id'];
					if ( 'discount' === $field_name ) {
						$price = (float) get_post_meta( $post_id, '_btdeals_offer_sale_price', true );
						$mrp = (float) get_post_meta( $post_id, '_btdeals_offer_old_price', true );
						return ( $mrp > 0 && $price < $mrp ) ? round( ( ( $mrp - $price ) / $mrp ) * 100 ) : 0;
					}
					return get_post_meta( $post_id, '_btdeals_' . $field_name, true );
				},
				'update_callback' => function( $value, $object, $field_name ) {
					if ( 'discount' === $field_name ) {
						return new WP_Error( 'rest_cannot_update', __( 'Cannot update calculated field.', 'bigtricks-deals' ), array( 'status' => 400 ) );
					}
					return update_post_meta( $object->ID, '_btdeals_' . $field_name, sanitize_text_field( $value ) );
				},
				'schema'          => array(
					'description' => __( 'Custom meta field for deals.', 'bigtricks-deals' ),
					'type'        => $type,
					'context'     => array( 'view', 'edit' ),
				),
			) );
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
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 6;

		if ( ! $deal_id ) {
			wp_send_json_error( 'Invalid deal ID.' );
		}

		// Get current deal's store terms
		$current_stores = wp_get_post_terms( $deal_id, 'store', array( 'fields' => 'ids' ) );
		$current_categories = wp_get_post_terms( $deal_id, 'category', array( 'fields' => 'ids' ) );

		$args = array(
			'post_type'      => 'deal',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $deal_id ),
			'meta_query'     => array(
				array(
					'key'     => '_btdeals_is_expired',
					'value'   => 'off',
					'compare' => '='
				)
			)
		);

		// Prioritize deals from same store or category
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
		$deals = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$deal_data = array(
					'id'            => $post_id,
					'title'         => get_the_title(),
					'url'           => get_permalink(),
					'offer_url'     => get_post_meta( $post_id, '_btdeals_offer_url', true ),
					'old_price'     => get_post_meta( $post_id, '_btdeals_offer_old_price', true ),
					'sale_price'    => get_post_meta( $post_id, '_btdeals_offer_sale_price', true ),
					'discount_tag'  => get_post_meta( $post_id, '_btdeals_discount_tag', true ),
					'button_text'   => get_post_meta( $post_id, '_btdeals_button_text', true ) ?: 'Get Deal',
					'thumbnail'     => '',
					'store_name'    => ''
				);

				// Get thumbnail
				$thumbnail_id = get_post_meta( $post_id, '_btdeals_thumbnail_id', true );
				if ( $thumbnail_id ) {
					$thumbnail_url = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
					$deal_data['thumbnail'] = $thumbnail_url ? $thumbnail_url[0] : '';
				} else {
					$deal_data['thumbnail'] = get_the_post_thumbnail_url( $post_id, 'medium' );
				}

				// Get store name
				$stores = wp_get_post_terms( $post_id, 'store' );
				if ( ! empty( $stores ) ) {
					$deal_data['store_name'] = $stores[0]->name;
				}

				$deals[] = $deal_data;
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
