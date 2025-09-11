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
			'product_name'           => get_post_meta( $post->ID, '_btdeals_product_name', true ),
			'short_description'      => get_post_meta( $post->ID, '_btdeals_short_description', true ),
			'offer_url'              => get_post_meta( $post->ID, '_btdeals_offer_url', true ),
			'disclaimer'             => get_post_meta( $post->ID, '_btdeals_disclaimer', true ),
			'offer_old_price'        => get_post_meta( $post->ID, '_btdeals_offer_old_price', true ),
			'offer_sale_price'       => get_post_meta( $post->ID, '_btdeals_offer_sale_price', true ),
			'coupon_code'            => get_post_meta( $post->ID, '_btdeals_coupon_code', true ),
			'expiration_date'        => get_post_meta( $post->ID, '_btdeals_expiration_date', true ),
			'mask_coupon'            => get_post_meta( $post->ID, '_btdeals_mask_coupon', true ),
			'is_expired'             => get_post_meta( $post->ID, '_btdeals_is_expired', true ),
			'verify_label'           => get_post_meta( $post->ID, '_btdeals_verify_label', true ),
			'button_text'            => get_post_meta( $post->ID, '_btdeals_button_text', true ),
			'product_thumbnail_url'  => get_post_meta( $post->ID, '_btdeals_product_thumbnail_url', true ),
			'offer_thumbnail_url'    => get_post_meta( $post->ID, '_btdeals_offer_thumbnail_url', true ),
			'product_feature'        => get_post_meta( $post->ID, '_btdeals_product_feature', true ),
			'store'                  => get_post_meta( $post->ID, '_btdeals_store', true ),
			'brand_logo_url'         => get_post_meta( $post->ID, '_btdeals_brand_logo_url', true ),
			'discount_tag'           => get_post_meta( $post->ID, '_btdeals_discount_tag', true ),
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
					<th><label><?php _e( 'Product Thumbnail', 'bigtricks-deals' ); ?></label></th>
					<td>
						<input type="url" id="btdeals_product_thumbnail_url" name="btdeals_product_thumbnail_url" value="<?php echo esc_url( $fields['product_thumbnail_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Product Thumbnail URL', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_product_thumbnail_upload" value="<?php _e( 'Upload from Media Library', 'bigtricks-deals' ); ?>">
						<p class="description"><?php _e( 'Upload or enter URL for product thumbnail', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label><?php _e( 'Offer Thumbnail', 'bigtricks-deals' ); ?></label></th>
					<td>
						<input type="url" id="btdeals_offer_thumbnail_url" name="btdeals_offer_thumbnail_url" value="<?php echo esc_url( $fields['offer_thumbnail_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Offer Thumbnail URL', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_offer_thumbnail_upload" value="<?php _e( 'Upload from Media Library', 'bigtricks-deals' ); ?>">
						<p class="description"><?php _e( 'Upload or enter URL for offer thumbnail', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="btdeals_product_feature"><?php _e( 'Product Feature', 'bigtricks-deals' ); ?></label></th>
					<td><?php wp_editor( $fields['product_feature'], 'btdeals_product_feature', array( 'textarea_name' => 'btdeals_product_feature', 'media_buttons' => false, 'textarea_rows' => 5 ) ); ?></td>
				</tr>
				<tr>
					<th><label for="btdeals_store"><?php _e( 'Store', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_store" name="btdeals_store" value="<?php echo esc_attr( $fields['store'] ); ?>" class="widefat" readonly>
					<p class="description"><?php _e( 'Automatically filled from selected store taxonomy', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label><?php _e( 'Brand Logo', 'bigtricks-deals' ); ?></label></th>
					<td>
						<input type="url" id="btdeals_brand_logo_url" name="btdeals_brand_logo_url" value="<?php echo esc_url( $fields['brand_logo_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Brand Logo URL', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_brand_logo_upload" value="<?php _e( 'Upload from Media Library', 'bigtricks-deals' ); ?>">
						<p class="description"><?php _e( 'Brand logo URL. Falls back to store taxonomy thumbnail if empty.', 'bigtricks-deals' ); ?></p>
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
			// Function to update store field from taxonomy
			function updateStoreField() {
				var selectedStores = [];
				$('#taxonomy-store input[type="checkbox"]:checked').each(function() {
					var termName = $(this).closest('label').text().trim();
					selectedStores.push(termName);
				});

				// Also check for selected terms in the dropdown if present
				$('#taxonomy-store select option:selected').each(function() {
					if ($(this).val()) {
						selectedStores.push($(this).text().trim());
					}
				});

				// Update the store field with the first selected store
				if (selectedStores.length > 0) {
					$('#btdeals_store').val(selectedStores[0]);
				} else {
					$('#btdeals_store').val('');
				}
			}

			// Listen for changes in store taxonomy
			$(document).on('change', '#taxonomy-store input[type="checkbox"]', function() {
				updateStoreField();
			});

			$(document).on('change', '#taxonomy-store select', function() {
				updateStoreField();
			});

			// Initial update
			updateStoreField();

			// Product thumbnail upload
			$('#btdeals_product_thumbnail_upload').on('click', function(e) {
				e.preventDefault();

				var mediaUploader = wp.media({
					title: 'Choose Product Thumbnail',
					button: { text: 'Choose Thumbnail' },
					multiple: false
				});

				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					$('#btdeals_product_thumbnail_url').val(attachment.url);
				});

				mediaUploader.open();
			});

			// Offer thumbnail upload
			$('#btdeals_offer_thumbnail_upload').on('click', function(e) {
				e.preventDefault();

				var mediaUploader = wp.media({
					title: 'Choose Offer Thumbnail',
					button: { text: 'Choose Thumbnail' },
					multiple: false
				});

				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					$('#btdeals_offer_thumbnail_url').val(attachment.url);
				});

				mediaUploader.open();
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
					$('#btdeals_brand_logo_url').val(attachment.url);
				});

				mediaUploader.open();
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
			'expiration_date', 'verify_label', 'button_text', 'product_thumbnail_url', 'offer_thumbnail_url',
			'store', 'brand_logo_url', 'discount_tag'
		];

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ 'btdeals_' . $field ] ) ) {
				if ( in_array( $field, ['offer_url', 'product_thumbnail_url', 'offer_thumbnail_url', 'brand_logo_url'] ) ) {
					update_post_meta( $post_id, '_btdeals_' . $field, esc_url_raw( $_POST[ 'btdeals_' . $field ] ) );
				} else {
					update_post_meta( $post_id, '_btdeals_' . $field, sanitize_text_field( $_POST[ 'btdeals_' . $field ] ) );
				}
			}
		}

		// Handle textarea fields
		if ( isset( $_POST['btdeals_short_description'] ) ) {
			update_post_meta( $post_id, '_btdeals_short_description', wp_kses_post( $_POST['btdeals_short_description'] ) );
		}

		if ( isset( $_POST['btdeals_disclaimer'] ) ) {
			update_post_meta( $post_id, '_btdeals_disclaimer', wp_kses_post( $_POST['btdeals_disclaimer'] ) );
		}

		if ( isset( $_POST['btdeals_product_feature'] ) ) {
			update_post_meta( $post_id, '_btdeals_product_feature', wp_kses_post( $_POST['btdeals_product_feature'] ) );
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
		'product_name'           => 'string',
		'short_description'      => 'string',
		'offer_url'              => 'string',
		'disclaimer'             => 'string',
		'offer_old_price'        => 'string',
		'offer_sale_price'       => 'string',
		'coupon_code'            => 'string',
		'expiration_date'        => 'string',
		'mask_coupon'            => 'string',
		'is_expired'             => 'string',
		'verify_label'           => 'string',
		'button_text'            => 'string',
		'product_thumbnail_url'  => 'string',
		'offer_thumbnail_url'    => 'string',
		'product_feature'        => 'string',
		'store'                  => 'string',
		'brand_logo_url'         => 'string',
		'discount_tag'           => 'string',
		'discount'               => 'integer',
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
				$product_thumbnail_url = get_post_meta( $post_id, '_btdeals_product_thumbnail_url', true );
				if ( $product_thumbnail_url ) {
					$deal_data['thumbnail'] = $product_thumbnail_url;
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

	/**
	 * Add import menu to admin.
	 *
	 * @since 1.0.0
	 */
	public function add_import_menu() {
		add_submenu_page(
			'edit.php?post_type=deal',
			__( 'Import Deals', 'bigtricks-deals' ),
			__( 'Import Deals', 'bigtricks-deals' ),
			'manage_options',
			'bt-deals-import',
			array( $this, 'render_import_page' )
		);
	}

	/**
	 * Render the import page.
	 *
	 * @since 1.0.0
	 */
	public function render_import_page() {
		if ( isset( $_POST['bt_import_deals'] ) && wp_verify_nonce( $_POST['bt_import_nonce'], 'bt_import_deals' ) ) {
			$this->process_import();
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'Import Deals from Another Theme', 'bigtricks-deals' ); ?></h1>

			<div class="notice notice-info">
				<p><?php _e( 'This tool helps you import deals exported from other themes (like ReHub). Paste your exported deal data below and click Import.', 'bigtricks-deals' ); ?></p>
			</div>

			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'bt_import_deals', 'bt_import_nonce' ); ?>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="import_file"><?php _e( 'CSV File', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<input type="file" name="import_file" id="import_file" accept=".csv" required>
								<p class="description">
									<?php _e( 'Upload your exported CSV file from the previous theme.', 'bigtricks-deals' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="csv_delimiter"><?php _e( 'CSV Delimiter', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<select name="csv_delimiter" id="csv_delimiter">
									<option value=","><?php _e( 'Comma (,)', 'bigtricks-deals' ); ?></option>
									<option value=";"><?php _e( 'Semicolon (;)', 'bigtricks-deals' ); ?></option>
									<option value="\t"><?php _e( 'Tab', 'bigtricks-deals' ); ?></option>
								</select>
								<p class="description">
									<?php _e( 'Select the delimiter used in your CSV file.', 'bigtricks-deals' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="create_stores"><?php _e( 'Create Store Terms', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="create_stores" id="create_stores" value="1" checked>
								<label for="create_stores"><?php _e( 'Automatically create store taxonomy terms if they don\'t exist', 'bigtricks-deals' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="preserve_dates"><?php _e( 'Preserve Original Dates', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="preserve_dates" id="preserve_dates" value="1" checked>
								<label for="preserve_dates"><?php _e( 'Keep the original publish and modified dates from the imported data', 'bigtricks-deals' ); ?></label>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Import Deals', 'bigtricks-deals' ), 'primary', 'bt_import_deals' ); ?>
			</form>

			<div class="bt-import-info" style="margin-top: 30px;">
				<h3><?php _e( 'Field Mapping', 'bigtricks-deals' ); ?></h3>
				<p><?php _e( 'The following field mappings will be used for CSV imports:', 'bigtricks-deals' ); ?></p>
				<table class="widefat fixed" style="max-width: 600px;">
					<thead>
						<tr>
							<th><?php _e( 'CSV Header', 'bigtricks-deals' ); ?></th>
							<th><?php _e( 'Target Field', 'bigtricks-deals' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><code>Title</code></td><td><?php _e( 'Post Title', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>Content</code></td><td><?php _e( 'Post Content', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>btdeal_offer_name</code></td><td><?php _e( 'Product Name', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>rehub_offer_product_url</code></td><td><?php _e( 'Offer URL', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>rehub_offer_product_price_old</code></td><td><?php _e( 'Old Price', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>rehub_offer_product_price</code></td><td><?php _e( 'Sale Price', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>rehub_offer_product_thumb</code></td><td><?php _e( 'Product Thumbnail URL', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>thumbnail_url</code></td><td><?php _e( 'Offer Thumbnail URL', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>store</code></td><td><?php _e( 'Store Taxonomy', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>rehub_prod_feature</code></td><td><?php _e( 'Product Features', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>Date</code></td><td><?php _e( 'Publish Date', 'bigtricks-deals' ); ?></td></tr>
						<tr><td><code>Post Modified Date</code></td><td><?php _e( 'Modified Date', 'bigtricks-deals' ); ?></td></tr>
					</tbody>
				</table>
				<p class="description" style="margin-top: 10px;">
					<?php _e( 'Note: The system automatically handles case-insensitive matching and removes quotes from headers.', 'bigtricks-deals' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Process the import of deals.
	 *
	 * @since 1.0.0
	 */
	private function process_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'bigtricks-deals' ) );
		}

		$csv_delimiter = isset( $_POST['csv_delimiter'] ) ? sanitize_text_field( $_POST['csv_delimiter'] ) : ',';
		$create_stores = isset( $_POST['create_stores'] ) ? (bool) $_POST['create_stores'] : true;
		$preserve_dates = isset( $_POST['preserve_dates'] ) ? (bool) $_POST['preserve_dates'] : true;

		// Handle file upload
		if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
			echo '<div class="notice notice-error"><p>' . __( 'Please upload a valid CSV file.', 'bigtricks-deals' ) . '</p></div>';
			return;
		}

		$file = $_FILES['import_file'];

		// Check file type
		$allowed_types = array( 'text/csv', 'application/csv', 'text/plain' );
		if ( ! in_array( $file['type'], $allowed_types ) && ! preg_match( '/\.csv$/i', $file['name'] ) ) {
			echo '<div class="notice notice-error"><p>' . __( 'Please upload a valid CSV file.', 'bigtricks-deals' ) . '</p></div>';
			return;
		}

		// Parse the CSV data
		$deals = $this->parse_csv_data( $file['tmp_name'], $csv_delimiter );

		if ( empty( $deals ) ) {
			echo '<div class="notice notice-error"><p>' . __( 'No valid deals found in the CSV file.', 'bigtricks-deals' ) . '</p></div>';
			return;
		}

		$imported = 0;
		$skipped = 0;
		$errors = array();

		foreach ( $deals as $deal_data ) {
			$result = $this->import_single_deal( $deal_data, $create_stores, $preserve_dates );
			if ( $result === true ) {
				$imported++;
			} elseif ( $result === false ) {
				$skipped++;
			} else {
				$errors[] = $result;
			}
		}

		// Display results
		if ( $imported > 0 ) {
			echo '<div class="notice notice-success"><p>' . sprintf( __( 'Successfully imported %d deals.', 'bigtricks-deals' ), $imported ) . '</p></div>';
		}

		if ( $skipped > 0 ) {
			echo '<div class="notice notice-warning"><p>' . sprintf( __( 'Skipped %d deals (already exist or invalid data).', 'bigtricks-deals' ), $skipped ) . '</p></div>';
		}

		if ( ! empty( $errors ) ) {
			echo '<div class="notice notice-error"><p>' . __( 'Import completed with errors:', 'bigtricks-deals' ) . '</p><ul>';
			foreach ( $errors as $error ) {
				echo '<li>' . esc_html( $error ) . '</li>';
			}
			echo '</ul></div>';
		}
	}

	/**
	 * Parse the CSV data into individual deals.
	 *
	 * @since 1.0.0
	 * @param string $file_path The path to the CSV file.
	 * @param string $delimiter The CSV delimiter.
	 * @return array Array of deal data.
	 */
	private function parse_csv_data( $file_path, $delimiter ) {
		$deals = array();

		if ( ! file_exists( $file_path ) ) {
			return $deals;
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return $deals;
		}

		// Read header row
		$headers = fgetcsv( $handle, 0, $delimiter );
		if ( ! $headers ) {
			fclose( $handle );
			return $deals;
		}

		// Clean headers and create mapping
		$header_mapping = array();
		foreach ( $headers as $header ) {
			$clean_header = trim( $header, '"\'' ); // Remove quotes
			$header_mapping[] = strtolower( $clean_header );
		}

		// Read data rows
		while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
			if ( count( $row ) === count( $header_mapping ) ) {
				$deal = array();

				// Map headers to standardized field names
				foreach ( $header_mapping as $index => $mapped_header ) {
					$value = isset( $row[$index] ) ? trim( $row[$index] ) : '';

					// Map CSV headers to our expected field names
					switch ( $mapped_header ) {
						case 'title':
							$deal['title'] = $value;
							break;
						case 'content':
							$deal['content'] = $value;
							break;
						case 'btdeal_offer_name':
							$deal['btdeal_offer_name'] = $value;
							break;
						case 'rehub_offer_product_url':
							$deal['rehub_offer_product_url'] = $value;
							break;
						case 'rehub_offer_product_price_old':
							$deal['rehub_offer_product_price_old'] = $value;
							break;
						case 'rehub_offer_product_price':
							$deal['rehub_offer_product_price'] = $value;
							break;
						case 'rehub_offer_product_thumb':
							$deal['rehub_offer_product_thumb'] = $value;
							break;
						case 'thumbnail_url':
							$deal['thumbnail_url'] = $value;
							break;
						case 'store':
							$deal['store'] = $value;
							break;
						case 'rehub_prod_feature':
							$deal['rehub_prod_feature'] = $value;
							break;
						case 'date':
							$deal['date'] = $value;
							break;
						case 'post modified date':
							$deal['postmodifieddate'] = $value;
							break;
						// Handle other fields if needed
						default:
							$deal[$mapped_header] = $value;
							break;
					}
				}

				// Only add if we have a title (check both 'title' and 'Title')
				if ( ! empty( $deal['title'] ) ) {
					$deals[] = $deal;
				}
			}
		}

		fclose( $handle );
		return $deals;
	}

	/**
	 * Import a single deal.
	 *
	 * @since 1.0.0
	 * @param array $deal_data The deal data to import.
	 * @param bool $create_stores Whether to create store terms.
	 * @param bool $preserve_dates Whether to preserve original dates.
	 * @return bool|string True on success, false on skip, error message on failure.
	 */
	private function import_single_deal( $deal_data, $create_stores, $preserve_dates ) {
		// Check if deal already exists
		$existing_deal = get_page_by_title( $deal_data['title'], OBJECT, 'deal' );
		if ( $existing_deal ) {
			return false; // Skip existing deals
		}

		// Prepare post data
		$post_data = array(
			'post_title'   => isset( $deal_data['title'] ) ? $deal_data['title'] : '',
			'post_content' => isset( $deal_data['content'] ) ? $deal_data['content'] : '',
			'post_status'  => 'publish',
			'post_type'    => 'deal',
		);

		// Set dates if preserving
		if ( $preserve_dates ) {
			if ( isset( $deal_data['date'] ) ) {
				$post_data['post_date'] = date( 'Y-m-d H:i:s', strtotime( $deal_data['date'] ) );
			}
			if ( isset( $deal_data['postmodifieddate'] ) ) {
				$post_data['post_modified'] = date( 'Y-m-d H:i:s', strtotime( $deal_data['postmodifieddate'] ) );
				$post_data['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', strtotime( $deal_data['postmodifieddate'] ) );
			}
		}

		// Insert the post
		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			return $post_id->get_error_message();
		}

		// Set meta fields
		$meta_mappings = array(
			'btdeal_offer_name'           => '_btdeals_product_name',
			'rehub_offer_product_url'    => '_btdeals_offer_url',
			'rehub_offer_product_price_old' => '_btdeals_offer_old_price',
			'rehub_offer_product_price'  => '_btdeals_offer_sale_price',
			'rehub_offer_product_thumb'  => '_btdeals_product_thumbnail_url',
			'thumbnail_url'              => '_btdeals_offer_thumbnail_url',
			'rehub_prod_feature'         => '_btdeals_product_feature',
		);

		foreach ( $meta_mappings as $source => $target ) {
			if ( isset( $deal_data[$source] ) ) {
				if ( in_array( $target, array( '_btdeals_offer_url', '_btdeals_product_thumbnail_url', '_btdeals_offer_thumbnail_url' ) ) ) {
					update_post_meta( $post_id, $target, esc_url_raw( $deal_data[$source] ) );
				} else {
					update_post_meta( $post_id, $target, sanitize_text_field( $deal_data[$source] ) );
				}
			}
		}

		// Handle store taxonomy
		if ( isset( $deal_data['store'] ) && ! empty( $deal_data['store'] ) ) {
			$store_name = sanitize_text_field( $deal_data['store'] );

			// Check if store term exists
			$store_term = get_term_by( 'name', $store_name, 'store' );

			if ( ! $store_term && $create_stores ) {
				// Create the store term
				$store_term = wp_insert_term( $store_name, 'store' );
				if ( ! is_wp_error( $store_term ) ) {
					$store_term_id = $store_term['term_id'];
				}
			} elseif ( $store_term ) {
				$store_term_id = $store_term->term_id;
			}

			if ( isset( $store_term_id ) ) {
				wp_set_post_terms( $post_id, array( $store_term_id ), 'store' );
				// Also save to meta field for the store name
				update_post_meta( $post_id, '_btdeals_store', $store_name );
			}
		}

		// Set default values for required fields
		if ( ! get_post_meta( $post_id, '_btdeals_button_text', true ) ) {
			update_post_meta( $post_id, '_btdeals_button_text', 'Get Deal' );
		}

		return true;
	}

}
