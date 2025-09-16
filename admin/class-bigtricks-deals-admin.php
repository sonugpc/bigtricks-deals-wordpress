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
		$this->version     = $version;

		add_action( 'wp_ajax_bt_start_import', array( $this, 'start_import_callback' ) );
		add_action( 'wp_ajax_bt_process_import_chunk', array( $this, 'process_import_chunk_callback' ) );
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
	
		// Get all post meta data in a single call.
		$all_meta = get_post_meta( $post->ID );
	
		// Helper function to get meta value.
		$get_meta = function( $key ) use ( $all_meta ) {
			return isset( $all_meta[ $key ][0] ) ? $all_meta[ $key ][0] : '';
		};
	
		$fields = [
			'product_name'           => $get_meta( '_btdeals_product_name' ),
			'short_description'      => $get_meta( '_btdeals_short_description' ),
			'offer_url'              => $get_meta( '_btdeals_offer_url' ),
			'disclaimer'             => $get_meta( '_btdeals_disclaimer' ),
			'offer_old_price'        => $get_meta( '_btdeals_offer_old_price' ),
			'offer_sale_price'       => $get_meta( '_btdeals_offer_sale_price' ),
			'coupon_code'            => $get_meta( '_btdeals_coupon_code' ),
			'expiration_date'        => $get_meta( '_btdeals_expiration_date' ),
			'mask_coupon'            => $get_meta( '_btdeals_mask_coupon' ),
			'is_expired'             => $get_meta( '_btdeals_is_expired' ),
			'verify_label'           => $get_meta( '_btdeals_verify_label' ),
			'button_text'            => $get_meta( '_btdeals_button_text' ),
			'product_thumbnail_url'  => $get_meta( '_btdeals_product_thumbnail_url' ),
			'offer_thumbnail_url'    => $get_meta( '_btdeals_offer_thumbnail_url' ),
			'product_feature'        => $get_meta( '_btdeals_product_feature' ),
			'store'                  => $get_meta( '_btdeals_store' ),
			'brand_logo_url'         => $get_meta( '_btdeals_brand_logo_url' ),
			'discount_tag'           => $get_meta( '_btdeals_discount_tag' ),
			'product_id'             => $get_meta( '_btdeals_product_id' ),
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
					<p class="description"><?php _e( 'This field shows the selected store taxonomy name and is automatically updated', 'bigtricks-deals' ); ?></p></td>
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
					<th><label for="btdeals_product_id"><?php _e( 'Product ID', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_product_id" name="btdeals_product_id" value="<?php echo esc_attr( $fields['product_id'] ?? '' ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Unique product identifier for API updates and tracking', 'bigtricks-deals' ); ?></p></td>
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

				// Check for checked checkboxes in store taxonomy
				$('#store-all input[type="checkbox"]:checked, #store-pop input[type="checkbox"]:checked, #storechecklist input[type="checkbox"]:checked').each(function() {
					var termName = $(this).closest('label').text().trim();
					if (termName) {
						selectedStores.push(termName);
					}
				});

				// Also check for selected terms in the dropdown if present
				$('#store-tabs select option:selected').each(function() {
					if ($(this).val() && $(this).val() !== '-1') {
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

			// Listen for changes in store taxonomy - multiple selectors for different WordPress versions
			$(document).on('change', '#store-all input[type="checkbox"], #store-pop input[type="checkbox"], #storechecklist input[type="checkbox"]', function() {
				setTimeout(updateStoreField, 100); // Small delay to ensure DOM updates
			});

			$(document).on('change', '#store-tabs select', function() {
				setTimeout(updateStoreField, 100);
			});

			// Also listen for clicks on taxonomy links
			$(document).on('click', '#store-tabs a', function() {
				setTimeout(updateStoreField, 200);
			});

			// Initial update on page load
			setTimeout(updateStoreField, 500);

			// Also populate from existing data if available
			var existingStore = $('#btdeals_store').val();
			if (!existingStore) {
				// Try to get from post meta or taxonomy
				var postId = $('#post_ID').val();
				if (postId) {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'bt_get_post_store',
							post_id: postId,
							nonce: '<?php echo wp_create_nonce("bt_get_store_nonce"); ?>'
						},
						success: function(response) {
							if (response.success && response.data.store_name) {
								$('#btdeals_store').val(response.data.store_name);
							}
						}
					});
				}
			}

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
			'product_name', 'short_description', 'offer_url', 'offer_old_price', 'offer_sale_price', 'coupon_code',
			'expiration_date', 'verify_label', 'button_text', 'product_thumbnail_url', 'offer_thumbnail_url',
			'store', 'brand_logo_url', 'discount_tag', 'product_id'
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

		// Clear the deal data cache
		delete_transient( 'btdeal_data_' . $post_id );
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
					$all_meta = get_post_meta( $post_id );
		
					$get_meta = function( $key ) use ( $all_meta ) {
						return $all_meta[ $key ][0] ?? '';
					};
		
					if ( 'discount' === $field_name ) {
						$price = (float) $get_meta( '_btdeals_offer_sale_price' );
						$mrp   = (float) $get_meta( '_btdeals_offer_old_price' );
						return ( $mrp > 0 && $price < $mrp ) ? round( ( ( $mrp - $price ) / $mrp ) * 100 ) : 0;
					}
		
					return $get_meta( '_btdeals_' . $field_name );
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
	 * Add settings menu to admin.
	 *
	 * @since 1.0.0
	 */
	public function add_settings_menu() {
		add_submenu_page(
			'edit.php?post_type=deal',
			__( 'Settings', 'bigtricks-deals' ),
			__( 'Settings', 'bigtricks-deals' ),
			'manage_options',
			'bt-deals-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		if ( isset( $_POST['bt_save_settings'] ) && wp_verify_nonce( $_POST['bt_settings_nonce'], 'bt_save_settings' ) ) {
			$this->save_settings();
		}

		$global_disclaimer = get_option( 'btdeals_global_disclaimer', '' );
		?>
		<div class="wrap">
			<h1><?php _e( 'BigTricks Deals Settings', 'bigtricks-deals' ); ?></h1>

			<form method="post">
				<?php wp_nonce_field( 'bt_save_settings', 'bt_settings_nonce' ); ?>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="btdeals_global_disclaimer"><?php _e( 'Global Disclaimer', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<?php
								wp_editor(
									$global_disclaimer,
									'btdeals_global_disclaimer',
									array(
										'textarea_name' => 'btdeals_global_disclaimer',
										'media_buttons' => false,
										'textarea_rows' => 8,
										'tinymce' => array(
											'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,blockquote',
											'toolbar2' => '',
										),
									)
								);
								?>
								<p class="description">
									<?php _e( 'This disclaimer will be displayed on all deal pages. Individual deal disclaimers will override this global disclaimer if set.', 'bigtricks-deals' ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Save Settings', 'bigtricks-deals' ), 'primary', 'bt_save_settings' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 */
	private function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['btdeals_global_disclaimer'] ) ) {
			$global_disclaimer = wp_kses_post( $_POST['btdeals_global_disclaimer'] );
			update_option( 'btdeals_global_disclaimer', $global_disclaimer );
			echo '<div class="notice notice-success"><p>' . __( 'Settings saved successfully.', 'bigtricks-deals' ) . '</p></div>';
		}
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

		// Handle CSV preview
		$csv_headers   = array();
		$csv_preview   = array();
		$show_mapping  = false;
		$transient_key = '';

		if ( isset( $_POST['bt_preview_csv'] ) && wp_verify_nonce( $_POST['bt_preview_nonce'], 'bt_preview_csv' ) ) {
			if ( isset( $_FILES['import_file'] ) && UPLOAD_ERR_OK === $_FILES['import_file']['error'] ) {
				// Handle the file upload securely.
				$uploaded_file    = $_FILES['import_file'];
				$upload_overrides = [
					'test_form' => false,
					'mimes'     => [ 'csv' => 'text/csv' ],
				];
				$movefile         = wp_handle_upload( $uploaded_file, $upload_overrides );

				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$transient_key = 'bt_deals_import_' . md5( $movefile['file'] );
					set_transient( $transient_key, $movefile['file'], HOUR_IN_SECONDS );

					$csv_headers  = $this->get_csv_headers( $movefile['file'] );
					$csv_preview  = $this->get_csv_preview( $movefile['file'] );
					$show_mapping = true;
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html( $movefile['error'] ) . '</p></div>';
				}
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'File upload failed. Please try again.', 'bigtricks-deals' ) . '</p></div>';
			}
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'Import Deals from CSV', 'bigtricks-deals' ); ?></h1>

			<div class="notice notice-info">
				<p><?php _e( 'Upload a CSV file to import deals. You can map CSV columns to deal fields for maximum flexibility.', 'bigtricks-deals' ); ?></p>
			</div>

			<form method="post" enctype="multipart/form-data" id="bt-import-form">
				<?php wp_nonce_field( 'bt_preview_csv', 'bt_preview_nonce' ); ?>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="import_file"><?php _e( 'CSV File', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<input type="file" name="import_file" id="import_file" accept=".csv" required>
								<p class="description">
									<?php _e( 'Upload your CSV file containing deal data.', 'bigtricks-deals' ); ?>
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
								<label for="skip_first_row"><?php _e( 'Skip First Row', 'bigtricks-deals' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="skip_first_row" id="skip_first_row" value="1" checked>
								<label for="skip_first_row"><?php _e( 'Skip the first row (usually contains headers)', 'bigtricks-deals' ); ?></label>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Preview CSV & Map Fields', 'bigtricks-deals' ), 'secondary', 'bt_preview_csv', false, array( 'id' => 'bt-preview-btn' ) ); ?>
			</form>

			<?php if ( $show_mapping && !empty( $csv_headers ) ): ?>
			<div id="bt-mapping-section" style="margin-top: 30px;">
				<div id="bt-import-progress" style="display:none;">
					<h2><?php esc_html_e( 'Importing Deals...', 'bigtricks-deals' ); ?></h2>
					<progress id="bt-import-progress-bar" value="0" max="100" style="width: 100%;"></progress>
					<p id="bt-import-progress-text"></p>
				</div>

				<h2><?php _e( 'Field Mapping', 'bigtricks-deals' ); ?></h2>
				<p><?php _e( 'Map your CSV columns to the corresponding deal fields:', 'bigtricks-deals' ); ?></p>

				<?php if ( !empty( $csv_preview ) ): ?>
				<div class="bt-csv-preview" style="margin-bottom: 20px;">
					<h3><?php _e( 'CSV Preview (First 3 rows)', 'bigtricks-deals' ); ?></h3>
					<table class="widefat fixed" style="max-width: 100%;">
						<thead>
							<tr>
								<?php foreach ( $csv_headers as $header ): ?>
								<th><?php echo esc_html( $header ); ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $csv_preview, 0, 3 ) as $row ): ?>
							<tr>
								<?php foreach ( $row as $cell ): ?>
								<td><?php echo esc_html( mb_strimwidth( $cell, 0, 50, '...' ) ); ?></td>
								<?php endforeach; ?>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php endif; ?>

				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'bt_import_deals', 'bt_import_nonce' ); ?>
					<input type="hidden" name="csv_delimiter" value="<?php echo esc_attr( $_POST['csv_delimiter'] ?? ',' ); ?>">
					<input type="hidden" name="skip_first_row" value="<?php echo esc_attr( $_POST['skip_first_row'] ?? '1' ); ?>">

					<!-- Pass the transient key instead of file content -->
					<input type="hidden" name="import_transient_key" value="<?php echo esc_attr( $transient_key ); ?>">

					<table class="form-table">
						<tbody>
							<?php
							$deal_fields = array(
								'' => __( '-- Skip this column --', 'bigtricks-deals' ),
								'post_title' => __( 'Post Title', 'bigtricks-deals' ),
								'post_content' => __( 'Post Content', 'bigtricks-deals' ),
								'short_description' => __( 'Short Description', 'bigtricks-deals' ),
								'product_name' => __( 'Product Name', 'bigtricks-deals' ),
								'product_id' => __( 'Product ID', 'bigtricks-deals' ),
								'offer_url' => __( 'Offer URL', 'bigtricks-deals' ),
								'old_price' => __( 'Old Price', 'bigtricks-deals' ),
								'sale_price' => __( 'Sale Price', 'bigtricks-deals' ),
								'coupon_code' => __( 'Coupon Code', 'bigtricks-deals' ),
								'mask_coupon' => __( 'Mask Coupon (yes/no)', 'bigtricks-deals' ),
								'expiration_date' => __( 'Expiration Date', 'bigtricks-deals' ),
								'is_expired' => __( 'Is Expired (yes/no)', 'bigtricks-deals' ),
								'product_thumbnail' => __( 'Product Thumbnail URL', 'bigtricks-deals' ),
								'offer_thumbnail' => __( 'Offer Thumbnail URL', 'bigtricks-deals' ),
								'store' => __( 'Store Name', 'bigtricks-deals' ),
								'product_features' => __( 'Product Features', 'bigtricks-deals' ),
								'brand_logo' => __( 'Brand Logo URL', 'bigtricks-deals' ),
								'discount_tag' => __( 'Discount Tag', 'bigtricks-deals' ),
								'button_text' => __( 'Button Text', 'bigtricks-deals' ),
								'verify_label' => __( 'Verify Label', 'bigtricks-deals' ),
								'disclaimer' => __( 'Disclaimer', 'bigtricks-deals' ),
								'post_date' => __( 'Publish Date', 'bigtricks-deals' ),
								'post_modified' => __( 'Modified Date', 'bigtricks-deals' ),
							);

							foreach ( $csv_headers as $index => $header ):
							?>
							<tr>
								<th scope="row">
									<label><?php printf( __( 'Column %d: %s', 'bigtricks-deals' ), $index + 1, esc_html( $header ) ); ?></label>
								</th>
								<td>
									<select name="field_mapping[<?php echo $index; ?>]" class="widefat">
										<?php foreach ( $deal_fields as $field_key => $field_label ): ?>
										<option value="<?php echo esc_attr( $field_key ); ?>" <?php selected( $this->get_default_mapping( $header ), $field_key ); ?>>
											<?php echo esc_html( $field_label ); ?>
										</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<?php endforeach; ?>

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
							<tr>
								<th scope="row">
									<label for="update_existing"><?php _e( 'Update Existing Deals', 'bigtricks-deals' ); ?></label>
								</th>
								<td>
									<input type="checkbox" name="update_existing" id="update_existing" value="1">
									<label for="update_existing"><?php _e( 'Update existing deals if a matching title is found', 'bigtricks-deals' ); ?></label>
								</td>
							</tr>
						</tbody>
					</table>

					<?php submit_button( __( 'Start Import', 'bigtricks-deals' ), 'primary', 'bt_start_import_btn' ); ?>
				</form>
			</div>
			<?php endif; ?>

			<div class="bt-import-info" style="margin-top: 30px;">
				<h3><?php _e( 'Import Instructions', 'bigtricks-deals' ); ?></h3>
				<ul>
					<li><?php _e( 'Upload a CSV file with your deal data', 'bigtricks-deals' ); ?></li>
					<li><?php _e( 'Preview the CSV to see the column headers', 'bigtricks-deals' ); ?></li>
					<li><?php _e( 'Map each CSV column to the appropriate deal field', 'bigtricks-deals' ); ?></li>
					<li><?php _e( 'Choose import options like creating stores and preserving dates', 'bigtricks-deals' ); ?></li>
					<li><?php _e( 'Click Import to process the deals', 'bigtricks-deals' ); ?></li>
				</ul>

				<h4><?php _e( 'Supported Date Formats', 'bigtricks-deals' ); ?></h4>
				<ul>
					<li><code>Y-m-d H:i:s</code> (e.g., 2023-12-25 14:30:00)</li>
					<li><code>m/d/Y</code> (e.g., 12/25/2023)</li>
					<li><code>d-m-Y</code> (e.g., 25-12-2023)</li>
				</ul>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Show preview button when file is selected
			$('#import_file').on('change', function() {
				if ($(this).val()) {
					$('#bt-preview-btn').prop('disabled', false).addClass('button-primary');
				} else {
					$('#bt-preview-btn').prop('disabled', true).removeClass('button-primary');
				}
			});

			// Initially disable preview button
			$('#bt-preview-btn').prop('disabled', true);

			// Handle import via AJAX
			$('#bt_start_import_btn').on('click', function(e) {
				e.preventDefault();

				var $form = $(this).closest('form');
				var formData = new FormData($form[0]);
				formData.append('action', 'bt_start_import');
				formData.append('nonce', '<?php echo wp_create_nonce( 'bt_import_deals' ); ?>');

				$('#bt-mapping-section').hide();
				$('#bt-import-progress').show();

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							processImportChunk(response.data);
						} else {
							alert(response.data.message);
						}
					},
					error: function() {
						alert('An error occurred while starting the import.');
					}
				});
			});

			function processImportChunk(data) {
				var totalRows = data.total_rows;
				var processed = data.processed_rows || 0;

				if (processed >= totalRows) {
					$('#bt-import-progress-text').text('Import complete!');
					return;
				}

				var progress = (processed / totalRows) * 100;
				$('#bt-import-progress-bar').val(progress);
				$('#bt-import-progress-text').text('Processed ' + processed + ' of ' + totalRows + ' rows...');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'bt_process_import_chunk',
						nonce: '<?php echo wp_create_nonce( 'bt_import_deals' ); ?>',
						transient_key: data.transient_key,
						field_mapping: data.field_mapping,
						create_stores: data.create_stores,
						preserve_dates: data.preserve_dates,
						update_existing: data.update_existing,
					},
					success: function(response) {
						if (response.success) {
							processImportChunk(response.data);
						} else {
							alert(response.data.message);
						}
					},
					error: function() {
						alert('An error occurred while processing a chunk.');
					}
				});
			}
		});
		</script>
		<?php
	}

	/**
	 * Get CSV headers from uploaded file.
	 *
	 * @since 1.0.0
	 * @return array Array of CSV headers.
	 */
	private function get_csv_headers( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		$csv_delimiter = isset( $_POST['csv_delimiter'] ) ? sanitize_text_field( $_POST['csv_delimiter'] ) : ',';

		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return array();
		}

		$headers = fgetcsv( $handle, 0, $csv_delimiter );
		fclose( $handle );

		if ( ! $headers ) {
			return array();
		}

		// Clean headers
		return array_map( function( $header ) {
			return trim( $header, '"\'' );
		}, $headers );
	}

	/**
	 * Get CSV preview data.
	 *
	 * @since 1.0.0
	 * @return array Array of CSV preview rows.
	 */
	private function get_csv_preview( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		$csv_delimiter  = isset( $_POST['csv_delimiter'] ) ? sanitize_text_field( $_POST['csv_delimiter'] ) : ',';
		$skip_first_row = isset( $_POST['skip_first_row'] ) ? (bool) $_POST['skip_first_row'] : true;

		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return array();
		}

		$preview = array();
		$row_count = 0;
		$max_rows = 5; // Preview first 5 rows

		if ( $skip_first_row ) {
			fgetcsv( $handle, 0, $csv_delimiter ); // Skip header row
		}

		while ( ( $row = fgetcsv( $handle, 0, $csv_delimiter ) ) !== false && $row_count < $max_rows ) {
			$preview[] = $row;
			$row_count++;
		}

		fclose( $handle );
		return $preview;
	}

	/**
	 * Get default field mapping based on header name.
	 *
	 * @since 1.0.0
	 * @param string $header The CSV header name.
	 * @return string The default field mapping.
	 */
	private function get_default_mapping( $header ) {
		$header = strtolower( trim( $header ) );

		$mappings = array(
			'title' => 'post_title',
			'post_title' => 'post_title',
			'name' => 'post_title',
			'product_name' => 'product_name',
			'product name' => 'product_name',
			'offer_name' => 'product_name',
			'offer name' => 'product_name',
			'content' => 'post_content',
			'description' => 'post_content',
			'url' => 'offer_url',
			'offer_url' => 'offer_url',
			'offer url' => 'offer_url',
			'link' => 'offer_url',
			'price' => 'sale_price',
			'sale_price' => 'sale_price',
			'sale price' => 'sale_price',
			'new_price' => 'sale_price',
			'new price' => 'sale_price',
			'old_price' => 'old_price',
			'old price' => 'old_price',
			'original_price' => 'old_price',
			'original price' => 'old_price',
			'regular_price' => 'old_price',
			'regular price' => 'old_price',
			'coupon' => 'coupon_code',
			'coupon_code' => 'coupon_code',
			'coupon code' => 'coupon_code',
			'voucher' => 'coupon_code',
			'expiry' => 'expiration_date',
			'expiry_date' => 'expiration_date',
			'expiry date' => 'expiration_date',
			'expiration' => 'expiration_date',
			'expiration_date' => 'expiration_date',
			'expiration date' => 'expiration_date',
			'thumbnail' => 'offer_thumbnail',
			'thumbnail_url' => 'offer_thumbnail',
			'thumbnail url' => 'offer_thumbnail',
			'image' => 'offer_thumbnail',
			'image_url' => 'offer_thumbnail',
			'image url' => 'offer_thumbnail',
			'store' => 'store',
			'store_name' => 'store',
			'store name' => 'store',
			'merchant' => 'store',
			'brand' => 'store',
			'features' => 'product_features',
			'product_features' => 'product_features',
			'product features' => 'product_features',
			'specifications' => 'product_features',
			'discount' => 'discount_tag',
			'discount_tag' => 'discount_tag',
			'discount tag' => 'discount_tag',
			'savings' => 'discount_tag',
			'button' => 'button_text',
			'button_text' => 'button_text',
			'button text' => 'button_text',
			'cta' => 'button_text',
			'date' => 'post_date',
			'publish_date' => 'post_date',
			'publish date' => 'post_date',
			'created' => 'post_date',
		);

		return isset( $mappings[$header] ) ? $mappings[$header] : '';
	}

	/**
	 * Process the import of deals.
	 *
	 * @since 1.0.0
	 */
	public function process_import() {
		// This function is intentionally left blank as the import process is now handled by AJAX.
	}

	public function start_import_callback() {
		check_ajax_referer( 'bt_import_deals', 'nonce' );

		$transient_key = isset( $_POST['import_transient_key'] ) ? sanitize_text_field( $_POST['import_transient_key'] ) : '';
		$file_path     = get_transient( $transient_key );

		if ( ! $file_path || ! file_exists( $file_path ) ) {
			wp_send_json_error( [ 'message' => __( 'Import file not found or expired.', 'bigtricks-deals' ) ] );
		}

		$csv_delimiter = isset( $_POST['csv_delimiter'] ) ? sanitize_text_field( $_POST['csv_delimiter'] ) : ',';
		$field_mapping = isset( $_POST['field_mapping'] ) ? (array) $_POST['field_mapping'] : [];
		
		$deals = $this->parse_csv_data_with_mapping( $file_path, $csv_delimiter, $field_mapping );

		if ( empty( $deals ) ) {
			wp_send_json_error( [ 'message' => __( 'No deals found in the CSV file.', 'bigtricks-deals' ) ] );
		}

		set_transient( $transient_key . '_data', $deals, HOUR_IN_SECONDS );

		wp_send_json_success( [
			'total_rows'      => count( $deals ),
			'processed_rows'  => 0,
			'transient_key'   => $transient_key,
			'field_mapping'   => $field_mapping,
			'create_stores'   => isset( $_POST['create_stores'] ),
			'preserve_dates'  => isset( $_POST['preserve_dates'] ),
			'update_existing' => isset( $_POST['update_existing'] ),
		] );
	}

	public function process_import_chunk_callback() {
		check_ajax_referer( 'bt_import_deals', 'nonce' );

		$transient_key   = isset( $_POST['transient_key'] ) ? sanitize_text_field( $_POST['transient_key'] ) : '';
		$deals           = get_transient( $transient_key . '_data' );
		$field_mapping   = isset( $_POST['field_mapping'] ) ? (array) $_POST['field_mapping'] : [];
		$create_stores   = isset( $_POST['create_stores'] ) ? (bool) $_POST['create_stores'] : true;
		$preserve_dates  = isset( $_POST['preserve_dates'] ) ? (bool) $_POST['preserve_dates'] : true;
		$update_existing = isset( $_POST['update_existing'] ) ? (bool) $_POST['update_existing'] : false;

		if ( false === $deals ) {
			wp_send_json_error( [ 'message' => __( 'Import data not found.', 'bigtricks-deals' ) ] );
		}

		$chunk_size = 20;
		$chunk      = array_splice( $deals, 0, $chunk_size );

		foreach ( $chunk as $deal_data ) {
			$this->import_single_deal_with_mapping( $deal_data, $create_stores, $preserve_dates, $update_existing );
		}

		$processed_rows = count( get_transient( $transient_key . '_data' ) ) - count( $deals );
		set_transient( $transient_key . '_data', $deals, HOUR_IN_SECONDS );

		if ( empty( $deals ) ) {
			delete_transient( $transient_key );
			delete_transient( $transient_key . '_data' );
		}

		wp_send_json_success( [
			'total_rows'      => count( get_transient( $transient_key . '_data' ) ) + $processed_rows,
			'processed_rows'  => $processed_rows,
			'transient_key'   => $transient_key,
			'field_mapping'   => $field_mapping,
			'create_stores'   => $create_stores,
			'preserve_dates'  => $preserve_dates,
			'update_existing' => $update_existing,
		] );
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
	 * Parse CSV data with custom field mapping.
	 *
	 * @since 1.0.0
	 * @param string $file_path The path to the CSV file.
	 * @param string $delimiter The CSV delimiter.
	 * @param array $field_mapping The field mapping array.
	 * @return array Array of deal data.
	 */
	private function parse_csv_data_with_mapping( $file_path, $delimiter, $field_mapping ) {
		$deals = array();

		if ( ! file_exists( $file_path ) ) {
			return $deals;
		}

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return $deals;
		}

		// Skip header row if needed
		$skip_first_row = isset( $_POST['skip_first_row'] ) ? (bool) $_POST['skip_first_row'] : true;
		if ( $skip_first_row ) {
			fgetcsv( $handle, 0, $delimiter );
		}

		// Read data rows
		while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
			$deal = array();

			// Map columns to fields based on user mapping
			foreach ( $field_mapping as $column_index => $field_name ) {
				if ( ! empty( $field_name ) && isset( $row[$column_index] ) ) {
					$value = trim( $row[$column_index] );

					// Handle special date parsing
					if ( in_array( $field_name, array( 'post_date', 'post_modified', 'expiration_date' ) ) && ! empty( $value ) ) {
						$value = $this->parse_date( $value );
					}

					$deal[$field_name] = $value;
				}
			}

			// Only add if we have a title
			if ( ! empty( $deal['post_title'] ) ) {
				$deals[] = $deal;
			}
		}

		fclose( $handle );
		return $deals;
	}

	/**
	 * Parse various date formats.
	 *
	 * @since 1.0.0
	 * @param string $date_string The date string to parse.
	 * @return string Formatted date string.
	 */
	private function parse_date( $date_string ) {
		// Try different date formats
		$formats = array(
			'Y-m-d H:i:s',
			'Y-m-d',
			'm/d/Y',
			'd-m-Y',
			'd/m/Y',
			'Y/m/d',
		);

		foreach ( $formats as $format ) {
			$timestamp = strtotime( $date_string );
			if ( $timestamp !== false ) {
				if ( strpos( $format, 'H:i:s' ) !== false ) {
					return date( 'Y-m-d H:i:s', $timestamp );
				} else {
					return date( 'Y-m-d', $timestamp );
				}
			}
		}

		// Return original if parsing fails
		return $date_string;
	}

	/**
	 * Import a single deal with custom mapping.
	 *
	 * @since 1.0.0
	 * @param array $deal_data The deal data to import.
	 * @param bool $create_stores Whether to create store terms.
	 * @param bool $preserve_dates Whether to preserve original dates.
	 * @param bool $update_existing Whether to update existing deals.
	 * @return string Result status: 'imported', 'updated', 'skipped', or error message.
	 */
	private function import_single_deal_with_mapping( $deal_data, $create_stores, $preserve_dates, $update_existing ) {
		// Check if deal already exists
		$existing_deal = null;
		if ( $update_existing && ! empty( $deal_data['post_title'] ) ) {
			$existing_deal = get_page_by_title( $deal_data['post_title'], OBJECT, 'deal' );
		}

		// Prepare post data
		$post_data = array(
			'post_title'   => $deal_data['post_title'] ?? '',
			'post_content' => $deal_data['post_content'] ?? '',
			'post_status'  => 'publish',
			'post_type'    => 'deal',
		);

		// Set dates if preserving and available
		if ( $preserve_dates ) {
			if ( ! empty( $deal_data['post_date'] ) ) {
				$post_data['post_date'] = $deal_data['post_date'];
			}
			if ( ! empty( $deal_data['post_modified'] ) ) {
				$post_data['post_modified'] = $deal_data['post_modified'];
				$post_data['post_modified_gmt'] = get_gmt_from_date( $deal_data['post_modified'] );
			}
		}

		// Handle existing deal
		if ( $existing_deal ) {
			$post_data['ID'] = $existing_deal->ID;
			$post_id = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id->get_error_message();
		}

		// Set meta fields based on mapping
		$meta_field_mappings = array(
			'short_description' => '_btdeals_short_description',
			'product_name' => '_btdeals_product_name',
			'product_id' => '_btdeals_product_id',
			'offer_url' => '_btdeals_offer_url',
			'old_price' => '_btdeals_offer_old_price',
			'sale_price' => '_btdeals_offer_sale_price',
			'coupon_code' => '_btdeals_coupon_code',
			'mask_coupon' => '_btdeals_mask_coupon',
			'expiration_date' => '_btdeals_expiration_date',
			'is_expired' => '_btdeals_is_expired',
			'product_thumbnail' => '_btdeals_product_thumbnail_url',
			'offer_thumbnail' => '_btdeals_offer_thumbnail_url',
			'product_features' => '_btdeals_product_feature',
			'brand_logo' => '_btdeals_brand_logo_url',
			'discount_tag' => '_btdeals_discount_tag',
			'button_text' => '_btdeals_button_text',
			'verify_label' => '_btdeals_verify_label',
			'disclaimer' => '_btdeals_disclaimer',
		);

		foreach ( $meta_field_mappings as $source_field => $meta_key ) {
			if ( isset( $deal_data[$source_field] ) && $deal_data[$source_field] !== '' ) {
				if ( in_array( $meta_key, array( '_btdeals_offer_url', '_btdeals_product_thumbnail_url', '_btdeals_offer_thumbnail_url', '_btdeals_brand_logo_url' ) ) ) {
					update_post_meta( $post_id, $meta_key, esc_url_raw( $deal_data[$source_field] ) );
				} elseif ( in_array( $meta_key, array( '_btdeals_mask_coupon', '_btdeals_is_expired' ) ) ) {
					// Handle boolean fields
					$value = strtolower( trim( $deal_data[$source_field] ) );
					$boolean_value = in_array( $value, array( 'yes', 'true', '1', 'on', 'y' ) ) ? 'on' : 'off';
					update_post_meta( $post_id, $meta_key, $boolean_value );
				} else {
					update_post_meta( $post_id, $meta_key, sanitize_text_field( $deal_data[$source_field] ) );
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

		return $existing_deal ? 'updated' : 'imported';
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
