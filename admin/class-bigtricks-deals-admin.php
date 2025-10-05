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
		register_taxonomy( "store", [ "deal", "post" ], $args );
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
	
		// Get store from taxonomy
		$store_name = '';
		$store_terms = wp_get_post_terms( $post->ID, 'store', array( 'fields' => 'names' ) );
		if ( ! empty( $store_terms ) && ! is_wp_error( $store_terms ) ) {
			$store_name = $store_terms[0];
		}

	
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
			'store'                  => $get_meta( '_btdeals_store' ) ?: $store_name,
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
					<p class="description"><?php _e( 'If empty, will use post title. Meta key: <code>_btdeals_product_name</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_short_description"><?php _e( 'Short Description of Product', 'bigtricks-deals' ); ?></label></th>
					<td><textarea id="btdeals_short_description" name="btdeals_short_description" class="widefat" rows="3"><?php echo esc_textarea( $fields['short_description'] ); ?></textarea>
					<p class="description"><?php _e( 'Brief product description. Meta key: <code>_btdeals_short_description</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_offer_url"><?php _e( 'Offer URL', 'bigtricks-deals' ); ?></label></th>
					<td><input type="url" id="btdeals_offer_url" name="btdeals_offer_url" value="<?php echo esc_url( $fields['offer_url'] ); ?>" class="widefat">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_offer_url</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_disclaimer"><?php _e( 'Disclaimer', 'bigtricks-deals' ); ?></label></th>
					<td><textarea id="btdeals_disclaimer" name="btdeals_disclaimer" class="widefat" rows="4"><?php echo esc_textarea( $fields['disclaimer'] ); ?></textarea>
					<p class="description"><?php _e( 'Optional. It works in deal lists. HTML and shortcodes are supported. Meta key: <code>_btdeals_disclaimer</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_offer_old_price"><?php _e( 'Offer Old Price', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_offer_old_price" name="btdeals_offer_old_price" value="<?php echo esc_attr( $fields['offer_old_price'] ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_offer_old_price</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_offer_sale_price"><?php _e( 'Offer Sale Price', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_offer_sale_price" name="btdeals_offer_sale_price" value="<?php echo esc_attr( $fields['offer_sale_price'] ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_offer_sale_price</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_coupon_code"><?php _e( 'Coupon Code', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_coupon_code" name="btdeals_coupon_code" value="<?php echo esc_attr( $fields['coupon_code'] ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_coupon_code</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_expiration_date"><?php _e( 'Expiration Date', 'bigtricks-deals' ); ?></label></th>
					<td><input type="date" id="btdeals_expiration_date" name="btdeals_expiration_date" value="<?php echo esc_attr( $fields['expiration_date'] ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_expiration_date</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><?php _e( 'Mask Coupon Code?', 'bigtricks-deals' ); ?></th>
					<td><input type="checkbox" id="btdeals_mask_coupon" name="btdeals_mask_coupon" <?php checked( $fields['mask_coupon'], 'on' ); ?>>
					<label for="btdeals_mask_coupon"><?php _e( 'Yes', 'bigtricks-deals' ); ?></label>
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_mask_coupon</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><?php _e( 'Offer is Expired?', 'bigtricks-deals' ); ?></th>
					<td><input type="checkbox" id="btdeals_is_expired" name="btdeals_is_expired" <?php checked( $fields['is_expired'], 'on' ); ?>>
					<label for="btdeals_is_expired"><?php _e( 'Yes', 'bigtricks-deals' ); ?></label>
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_is_expired</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_verify_label"><?php _e( 'Verify Label', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_verify_label" name="btdeals_verify_label" value="<?php echo esc_attr( $fields['verify_label'] ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_verify_label</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_button_text"><?php _e( 'Button Text', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_button_text" name="btdeals_button_text" value="<?php echo esc_attr( $fields['button_text'] ); ?>" class="regular-text" maxlength="14">
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_button_text</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label><?php _e( 'Product Thumbnail', 'bigtricks-deals' ); ?></label></th>
					<td>
						<input type="url" id="btdeals_product_thumbnail_url" name="btdeals_product_thumbnail_url" value="<?php echo esc_url( $fields['product_thumbnail_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Product Thumbnail URL', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_product_thumbnail_upload" value="<?php _e( 'Upload from Media Library', 'bigtricks-deals' ); ?>">
						<p class="description"><?php _e( 'Upload or enter URL for product thumbnail. Meta key: <code>_btdeals_product_thumbnail_url</code>', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label><?php _e( 'Offer Thumbnail', 'bigtricks-deals' ); ?></label></th>
					<td>
						<input type="url" id="btdeals_offer_thumbnail_url" name="btdeals_offer_thumbnail_url" value="<?php echo esc_url( $fields['offer_thumbnail_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Offer Thumbnail URL', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_offer_thumbnail_upload" value="<?php _e( 'Upload from Media Library', 'bigtricks-deals' ); ?>">
						<p class="description"><?php _e( 'Upload or enter URL for offer thumbnail. Meta key: <code>_btdeals_offer_thumbnail_url</code>', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="btdeals_product_feature"><?php _e( 'Product Feature', 'bigtricks-deals' ); ?></label></th>
					<td><?php wp_editor( $fields['product_feature'], 'btdeals_product_feature', array( 'textarea_name' => 'btdeals_product_feature', 'media_buttons' => false, 'textarea_rows' => 5 ) ); ?>
					<p class="description"><?php _e( 'Meta key: <code>_btdeals_product_feature</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_store"><?php _e( 'Store', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_store" name="btdeals_store" value="<?php echo esc_attr( $fields['store'] ); ?>" class="widefat" readonly>
					<p class="description"><?php _e( 'This field shows the selected store taxonomy name and is automatically updated. Meta key: <code>_btdeals_store</code>', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label><?php _e( 'Brand Logo', 'bigtricks-deals' ); ?></label></th>
					<td>
						<input type="url" id="btdeals_brand_logo_url" name="btdeals_brand_logo_url" value="<?php echo esc_url( $fields['brand_logo_url'] ); ?>" class="widefat" placeholder="<?php _e( 'Brand Logo URL', 'bigtricks-deals' ); ?>">
						<br><br>
						<input type="button" class="button" id="btdeals_brand_logo_upload" value="<?php _e( 'Upload from Media Library', 'bigtricks-deals' ); ?>">
						<p class="description"><?php _e( 'Brand logo URL. Falls back to store taxonomy thumbnail if empty. Meta key: <code>_btdeals_brand_logo_url</code>', 'bigtricks-deals' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="btdeals_discount_tag"><?php _e( 'Discount Tag', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_discount_tag" name="btdeals_discount_tag" value="<?php echo esc_attr( $fields['discount_tag'] ); ?>" class="regular-text" maxlength="5">
					<p class="description"><?php _e( 'Max 5 symbols. E.g., $20 or 50%. Meta key: _btdeals_discount_tag', 'bigtricks-deals' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="btdeals_product_id"><?php _e( 'Product ID', 'bigtricks-deals' ); ?></label></th>
					<td><input type="text" id="btdeals_product_id" name="btdeals_product_id" value="<?php echo esc_attr( $fields['product_id'] ?? '' ); ?>" class="regular-text">
					<p class="description"><?php _e( 'Unique product identifier for API updates and tracking. Meta key: <code>_btdeals_product_id</code>', 'bigtricks-deals' ); ?></p></td>
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
	
		$meta_fields = [
			'product_name'        => 'sanitize_text_field',
			'offer_url'           => 'esc_url_raw',
			'offer_old_price'     => 'sanitize_text_field',
			'offer_sale_price'    => 'sanitize_text_field',
			'coupon_code'         => 'sanitize_text_field',
			'expiration_date'     => 'sanitize_text_field',
			'verify_label'        => 'sanitize_text_field',
			'button_text'         => 'sanitize_text_field',
			'product_thumbnail_url' => 'esc_url_raw',
			'offer_thumbnail_url' => 'esc_url_raw',
			'store'               => 'sanitize_text_field',
			'brand_logo_url'      => 'esc_url_raw',
			'discount_tag'        => 'sanitize_text_field',
			'product_id'          => 'sanitize_text_field',
			'short_description'   => 'wp_kses_post',
			'disclaimer'          => 'wp_kses_post',
			'product_feature'     => 'wp_kses_post',
		];
	
		foreach ( $meta_fields as $key => $sanitize_callback ) {
			if ( isset( $_POST[ 'btdeals_' . $key ] ) ) {
				$value = call_user_func( $sanitize_callback, $_POST[ 'btdeals_' . $key ] );
				update_post_meta( $post_id, '_btdeals_' . $key, $value );
			}
		}
	
		// Checkboxes
		$checkboxes = ['mask_coupon', 'is_expired'];
		foreach ( $checkboxes as $checkbox ) {
			$value = isset( $_POST[ 'btdeals_' . $checkbox ] ) ? 'on' : 'off';
			update_post_meta( $post_id, '_btdeals_' . $checkbox, $value );
		}
	
		// Clear the deal data cache
		delete_transient( 'btdeal_data_' . $post_id );

		// Update category taxonomy
		if ( isset( $_POST['tax_input']['category'] ) ) {
			$term_ids = array_map( 'intval', $_POST['tax_input']['category'] );
			wp_set_post_categories( $post_id, $term_ids );
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
	
		register_rest_field( 'deal', 'deal_meta', array(
			'get_callback'    => function( $object, $field_name, $request ) {
				$post_id = $object['id'];
				$all_meta_raw = get_post_meta( $post_id );
				$all_meta = array_map( function( $value ) {
					return $value[0];
				}, $all_meta_raw );
	
				$deal_meta = [];
				foreach ( $meta_fields as $field => $type ) {
					if ( 'discount' === $field ) {
						continue;
					}
					$meta_key = '_btdeals_' . $field;
					$deal_meta[ $field ] = $all_meta[ $meta_key ] ?? '';
				}
	
				// Calculate discount
				$price = (float) ( $all_meta['_btdeals_offer_sale_price'] ?? 0 );
				$mrp   = (float) ( $all_meta['_btdeals_offer_old_price'] ?? 0 );
				$deal_meta['discount'] = ( $mrp > 0 && $price < $mrp ) ? round( ( ( $mrp - $price ) / $mrp ) * 100 ) : 0;
	
				return $deal_meta;
			},
			'update_callback' => function( $value, $object, $field_name ) {
				if ( ! is_array( $value ) ) {
					return new WP_Error( 'rest_invalid_param', __( 'Invalid data format for deal_meta.', 'bigtricks-deals' ), array( 'status' => 400 ) );
				}
	
				foreach ( $value as $key => $field_value ) {
					if ( array_key_exists( $key, $meta_fields ) && 'discount' !== $key ) {
						update_post_meta( $object->ID, '_btdeals_' . $key, sanitize_text_field( $field_value ) );
					}
				}
				return true;
			},
			'schema'          => array(
				'description' => __( 'All custom meta fields for deals.', 'bigtricks-deals' ),
				'type'        => 'object',
				'properties'  => array_reduce( array_keys($meta_fields), function($acc, $field) use ($meta_fields) {
					$acc[$field] = ['type' => $meta_fields[$field]];
					return $acc;
				}, []),
				'context'     => array( 'view', 'edit' ),
			),
		) );
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
		}

		echo '<div class="notice notice-success"><p>' . __( 'Settings saved successfully.', 'bigtricks-deals' ) . '</p></div>';
	}

	/**
	 * Register the REST API endpoint for creating a deal.
	 *
	 * @since 1.0.0
	 */
	public function register_api_routes() {
		register_rest_route( 'bigtricks-deals/v1', '/publish', array(
			'methods' => 'POST',
			'callback' => array( $this, 'create_deal_from_api' ),
			'permission_callback' => array( $this, 'check_if_admin' ),
		) );
	}

	/**
	 * Permission callback - currently unsecured for testing.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function check_if_admin() {
		return true; // Unsecured for now
	}

	/**
	 * Create a deal from an API request.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_deal_from_api( $request ) {
		$params = $request->get_json_params();

		$required_params = ['title', 'offer_url'];
		foreach ( $required_params as $param ) {
			if ( empty( $params[ $param ] ) ) {
				return new WP_Error( 'missing_param', "Missing required parameter: {$param}", array( 'status' => 400 ) );
			}
		}

		$post_data = array(
			'post_title'   => sanitize_text_field( $params['title'] ),
			'post_content' => ! empty( $params['content'] ) ? wp_kses_post( $params['content'] ) : '',
			'post_status'  => 'publish',
			'post_type'    => 'deal',
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$meta_fields_to_sanitize = [
			'product_name'        => 'sanitize_text_field',
			'offer_url'           => 'esc_url_raw',
			'offer_old_price'     => 'sanitize_text_field',
			'offer_sale_price'    => 'sanitize_text_field',
			'coupon_code'         => 'sanitize_text_field',
			'expiration_date'     => 'sanitize_text_field',
			'verify_label'        => 'sanitize_text_field',
			'button_text'         => 'sanitize_text_field',
			'product_thumbnail_url' => 'esc_url_raw',
			'offer_thumbnail_url' => 'esc_url_raw',
			'store'               => 'sanitize_text_field',
			'brand_logo_url'      => 'esc_url_raw',
			'discount_tag'        => 'sanitize_text_field',
			'product_id'          => 'sanitize_text_field',
			'short_description'   => 'wp_kses_post',
			'disclaimer'          => 'wp_kses_post',
			'product_feature'     => 'wp_kses_post',
			'mask_coupon'         => 'sanitize_text_field',
			'is_expired'          => 'sanitize_text_field',
		];

		foreach ( $meta_fields_to_sanitize as $field => $sanitize_callback ) {
			if ( isset( $params[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, $params[ $field ] );
				update_post_meta( $post_id, '_btdeals_' . $field, $value );
			}
		}

		// If a store is provided, set it as a taxonomy term
		if ( ! empty( $params['store'] ) ) {
			wp_set_object_terms( $post_id, sanitize_text_field( $params['store'] ), 'store', false );
		}

		// Handle categories
		if ( ! empty( $params['categories'] ) && is_array( $params['categories'] ) ) {
			$category_ids = [];
			foreach ( $params['categories'] as $category_name ) {
				$category = get_term_by( 'name', $category_name, 'category' );
				if ( $category ) {
					$category_ids[] = $category->term_id;
				}
			}
			if ( ! empty( $category_ids ) ) {
				wp_set_post_categories( $post_id, $category_ids );
			}
		}

		// Handle the offer thumbnail
		if ( ! empty( $params['offer_thumbnail_url'] ) ) {
			// These files are needed for media_sideload_image()
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$image_url = esc_url_raw( $params['offer_thumbnail_url'] );
			$image_id = media_sideload_image( $image_url, $post_id, null, 'id' );

			if ( ! is_wp_error( $image_id ) ) {
				set_post_thumbnail( $post_id, $image_id );
			}
		}

		return new WP_REST_Response( array( 'url' => get_permalink( $post_id ) ), 200 );
	}
}
