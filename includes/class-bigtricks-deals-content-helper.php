<?php
/**
 * Class Bigtricks_Deals_Content_Helper
 *
 * A helper class for retrieving and preparing deal data for display.
 *
 * @package Bigtricks_Deals
 */
class Bigtricks_Deals_Content_Helper {

    /**
     * Ensure grid CSS is loaded for deal grids.
     * Call this before rendering deal grids to ensure consistent styling.
     *
     * @since 1.0.0
     */
    public static function ensure_grid_styles_loaded() {
        if ( ! wp_style_is( 'bt-deals-grid', 'enqueued' ) ) {
            wp_enqueue_style( 'bt-deals-grid', plugin_dir_url( __FILE__ ) . '../public/css/bt-deals-grid.css', array(), '1.0.0', 'all' );
        }
    }

    /**
     * Get all data for a single deal, with caching.
     *
     * @param int $post_id The ID of the deal post.
     * @return array An associative array of deal data.
     */
    public static function get_deal_data( $post_id ) {
        $cache_key = 'btdeal_data_' . $post_id;
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return [];
        }

        $meta = get_post_meta( $post_id );

        $deal_data = [
            'product_name'          => $meta['_btdeals_product_name'][0] ?? '',
            'offer_url'             => $meta['_btdeals_offer_url'][0] ?? '',
            'old_price'             => floatval( $meta['_btdeals_offer_old_price'][0] ?? 0 ),
            'sale_price'            => floatval( $meta['_btdeals_offer_sale_price'][0] ?? 0 ),
            'coupon_code'           => $meta['_btdeals_coupon_code'][0] ?? '',
            'button_text'           => $meta['_btdeals_button_text'][0] ?? '',
            'thumbnail_id'          => $meta['_btdeals_thumbnail_id'][0] ?? '',
            'discount_tag'          => $meta['_btdeals_discount_tag'][0] ?? '',
            'short_description'     => $meta['_btdeals_short_description'][0] ?? '',
            'expiry_date'           => $meta['_btdeals_expiry_date'][0] ?? '',
            'offer_thumbnail_url'   => $meta['_btdeals_offer_thumbnail_url'][0] ?? '',
            'product_thumbnail_url' => $meta['_btdeals_product_thumbnail_url'][0] ?? '',
            'is_expired'            => ( $meta['_btdeals_is_expired'][0] ?? 'off' ) === 'on',
            'verify_label'          => $meta['_btdeals_verify_label'][0] ?? '',
            'gallery_images'        => maybe_unserialize( $meta['_btdeals_gallery_images'][0] ?? [] ),
            'disclaimer'            => $meta['_btdeals_disclaimer'][0] ?? '',
            'product_feature'       => $meta['_btdeals_product_feature'][0] ?? '',
        ];

        // Calculate discount
        $deal_data['discount_percent'] = self::calculate_discount( $deal_data['old_price'], $deal_data['sale_price'] );

        // Get store info
        $store_info = self::get_store_info( $post_id );
        $deal_data['store_name'] = $store_info['name'];
        $deal_data['store_logo'] = $store_info['logo'];
        $deal_data['store_url'] = $store_info['url'];

        // Get categories
        $categories = get_the_terms( $post_id, 'category' );
        $deal_data['categories'] = $categories && ! is_wp_error( $categories ) ? $categories : [];

        // Fallbacks
        $deal_data['post_id'] = $post_id;
        $deal_data['post_date'] = $post->post_date;
        $deal_data['post_date_gmt'] = $post->post_date_gmt;
        $deal_data['title'] = ! empty( $deal_data['product_name'] ) ? $deal_data['product_name'] : $post->post_title;
        $deal_data['description'] = ! empty( $deal_data['short_description'] ) ? $deal_data['short_description'] : $post->post_excerpt;
        $deal_data['button_text'] = ! empty( $deal_data['button_text'] ) ? $deal_data['button_text'] : __( 'Get Deal', 'bigtricks-deals' );

        // Thumbnail URL
        $deal_data['thumbnail_url'] = self::get_thumbnail_url( $post_id, $deal_data );

        // Cache the data for 1 hour
        $cache_duration = apply_filters( 'btdeals_cache_duration', HOUR_IN_SECONDS );
        set_transient( $cache_key, $deal_data, $cache_duration );

        return $deal_data;
    }

    /**
     * Calculate discount percentage.
     *
     * @param float $old_price  The original price.
     * @param float $sale_price The sale price.
     * @return int The discount percentage.
     */
    public static function calculate_discount( $old_price, $sale_price ) {
        if ( $old_price > $sale_price && $old_price > 0 ) {
            return round( ( ( $old_price - $sale_price ) / $old_price ) * 100 );
        }
        return 0;
    }

    /**
     * Get CSS class for discount color based on percentage.
     *
     * @param int $discount_percent The discount percentage.
     * @return string The CSS class name.
     */
    public static function get_discount_color_class( $discount_percent ) {
        if ( $discount_percent >= 50 ) {
            return 'bt-discount-high';
        } elseif ( $discount_percent >= 20 ) {
            return 'bt-discount-medium';
        } else {
            return 'bt-discount-low';
        }
    }

    /**
     * Get store information for a deal.
     *
     * @param int $post_id The ID of the deal post.
     * @return array An array containing the store name and logo URL.
     */
    public static function get_store_info( $post_id ) {
        $stores = get_the_terms( $post_id, 'store' );
        if ( $stores && ! is_wp_error( $stores ) ) {
            $store = reset( $stores );
            return [
                'name' => $store->name,
                'logo' => get_term_meta( $store->term_id, 'thumb_image', true ),
                'url'  => get_term_link( $store ),
            ];
        }
        return [ 'name' => '', 'logo' => '', 'url' => '' ];
    }

    /**
     * Get the primary thumbnail URL for a deal.
     *
     * @param int   $post_id   The ID of the deal post.
     * @param array $deal_data The deal data array.
     * @return string The thumbnail URL.
     */
    public static function get_thumbnail_url( $post_id, $deal_data ) {
        if ( ! empty( $deal_data['offer_thumbnail_url'] ) ) {
            return $deal_data['offer_thumbnail_url'];
        }
        if ( ! empty( $deal_data['product_thumbnail_url'] ) ) {
            return $deal_data['product_thumbnail_url'];
        }
        if ( ! empty( $deal_data['thumbnail_id'] ) ) {
            return wp_get_attachment_image_url( $deal_data['thumbnail_id'], 'large' );
        }
        if ( has_post_thumbnail( $post_id ) ) {
            return get_the_post_thumbnail_url( $post_id, 'large' );
        }
        return ''; // Return empty string if no thumbnail is found
    }

    /**
     * Get deals content for a specific store.
     *
     * @param int $store_id The ID of the store term.
     * @param int $posts_per_page The number of deals to retrieve.
     * @param int $page The page number.
     * @return string The HTML content for the deals.
     */
    public static function get_deals_content( $store_id, $posts_per_page = 6, $page = 1 ) {
        // Ensure grid styles are loaded
        self::ensure_grid_styles_loaded();

        $args = [
            'post_type'      => 'deal',
            'posts_per_page' => $posts_per_page,
            'paged'          => $page,
            'tax_query'      => [
                [
                    'taxonomy' => 'store',
                    'field'    => 'term_id',
                    'terms'    => $store_id,
                ],
            ],
        ];

        $query = new WP_Query( $args );
        $html = '';

        if ( $query->have_posts() ) {
			$post_ids = wp_list_pluck( $query->posts, 'ID' );
			update_meta_cache( 'post', $post_ids );
			update_object_term_cache( $post_ids, 'deal' );

            while ( $query->have_posts() ) {
                $query->the_post();
				$deal_data = self::get_deal_data( get_the_ID() );
                $html .= self::render_deal_item( $deal_data );
            }
            wp_reset_postdata();
        }

        return $html;
    }

    /**
     * Render a single deal item.
     *
     * @param array $deal_data The data of the deal post.
     * @return string The HTML for a single deal item.
     */
    public static function render_deal_item( $deal_data ) {
        ob_start();
        $post_id = isset( $deal_data['post_id'] ) ? $deal_data['post_id'] : 0;

        // Get time ago from cached data
        $post_date = isset( $deal_data['post_date_gmt'] ) ? strtotime( $deal_data['post_date_gmt'] ) : time();
        $time_diff = current_time( 'timestamp' ) - $post_date;

        // Format time ago with icons
        if ($time_diff < 60) {
            $time_ago = $time_diff . 's ago';
        } elseif ($time_diff < 3600) {
            $time_ago = floor($time_diff / 60) . 'm ago';
        } elseif ($time_diff < 86400) {
            $time_ago = floor($time_diff / 3600) . 'H ago';
        } elseif ($time_diff < 2592000) {
            $time_ago = floor($time_diff / 86400) . 'D ago';
        } else {
            $time_ago = floor($time_diff / 2592000) . 'M ago';
        }
        ?>
        <article class="bt-deal-card">
            <?php if ( ! empty( $deal_data['coupon_code'] ) ) : ?>
                <div class="bt-deal-badge">
                    <?php echo esc_html( $deal_data['coupon_code'] ); ?>
                </div>
            <?php endif; ?>

            <div class="bt-deal-image">
                <a href="<?php echo esc_url( $deal_data['offer_url'] ); ?>" target="_blank" rel="noopener">
                    <img src="<?php echo esc_url( $deal_data['thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $deal_data['title'] ); ?>" loading="lazy">
                    <?php if ( $deal_data['is_expired'] ) : ?>
                        <div class="bt-expired-overlay">
                            <div class="bt-expired-label"><?php esc_html_e( 'EXPIRED', 'bigtricks-deals' ); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ( $deal_data['is_expired'] || ! empty( $deal_data['verify_label'] ) ) : ?>
                        <div class="bt-status-flyer <?php echo $deal_data['is_expired'] ? 'bt-expired' : 'bt-verified'; ?>">
                            <div class="bt-status-content">
                                <?php if ( $deal_data['is_expired'] ) : ?>
                                    <i class="rbi rbi-close-circle-line"></i>
                                    <span><?php esc_html_e( 'EXPIRED', 'bigtricks-deals' ); ?></span>
                                <?php elseif ( ! empty( $deal_data['verify_label'] ) ) : ?>
                                    <i class="rbi rbi-check-circle-line"></i>
                                    <span><?php echo esc_html( $deal_data['verify_label'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $deal_data['discount_tag'] ) ) : ?>
                        <div class="bt-product-tag">
                            <?php echo esc_html( $deal_data['discount_tag'] ); ?>
                        </div>
                    <?php endif; ?>
                </a>
            </div>

            <div class="bt-deal-content">
                <div class="bt-deal-title">
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( $deal_data['title'] ); ?></a>
                </div>

                <div class="bt-deal-meta">
                    <?php if ( ! empty( $deal_data['store_name'] ) && ! empty( $deal_data['store_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $deal_data['store_url'] ); ?>" class="bt-deal-store"><?php echo esc_html( $deal_data['store_name'] ); ?></a>
                    <?php endif; ?>
                    <?php if ( ! empty( $deal_data['categories'] ) ) : ?>
                        <span class="bt-deal-category">
                            <a href="<?php echo esc_url( get_term_link( $deal_data['categories'][0] ) ); ?>" class="bt-category-link-small">
                                <?php echo esc_html( $deal_data['categories'][0]->name ); ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="bt-deal-pricing">
                    <div class="bt-price-row">
                        <?php if ( $deal_data['old_price'] > 0 ) : ?>
                            <span class="bt-deal-old-price">₹<?php echo esc_html( number_format( $deal_data['old_price'] ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( $deal_data['sale_price'] > 0 ) : ?>
                            <span class="bt-deal-sale-price">₹<?php echo esc_html( number_format( $deal_data['sale_price'] ) ); ?></span>

                            <div class="bt-price-info-wrapper">
                                <button class="bt-price-info-btn" type="button" aria-label="Price Information">
                                    <span>ℹ</span>
                                </button>

                                <div class="bt-price-tooltip">
                                    <div class="bt-tooltip-header">
                                        <strong>Price Information</strong>
                                    </div>
                                    <div class="bt-tooltip-content">
                                        <p><strong>Price as of:</strong> <?php echo esc_html( get_the_date( 'M j, Y \a\t g:i A', $post_id ) ); ?></p>
                                        <p>Product prices and availability are accurate as of the date/time indicated and are subject to change.</p>
                                        <p>Any price and availability information displayed on <?php echo esc_html( $deal_data['store_name'] ?: 'the Store' ); ?> at the time of purchase will apply to the purchase of this product.</p>
                                    </div>
                                </div>
                            </div>

                            <?php if ( $deal_data['discount_percent'] > 0 ) : ?>
                                <span class="bt-deal-discount <?php echo self::get_discount_color_class( $deal_data['discount_percent'] ); ?>">
                                    <?php echo esc_html( $deal_data['discount_percent'] ); ?>% OFF
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <span class="bt-deal-time">
                        <i class="rbi rbi-clock-round"></i>
                        <?php echo esc_html( $time_ago ); ?>
                    </span>
                </div>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }
}
