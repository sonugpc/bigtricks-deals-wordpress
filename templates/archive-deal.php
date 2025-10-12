<?php
/**
 * Archive template for Deal post type
 *
 * @package Bigtricks_Deals
 */

get_header();

// Enqueue archive page styles
wp_enqueue_style( 'bt-deals-archive', plugin_dir_url( __FILE__ ) . '../public/css/bt-deals-archive.css', array(), '1.0.0', 'all' );
?>

<div class="bt-deals-archive-page">
    <!-- Hero Section -->
 

    <!-- Main Content -->
    <div class="bt-archive-main">
        <div class="pd-15">
            <!-- Breadcrumb -->
            <nav class="bt-breadcrumb">
                <a href="<?php echo home_url(); ?>">Home</a>
                <span class="bt-breadcrumb-separator">/</span>
                <span class="bt-breadcrumb-current">Deals</span>
            </nav>

            <!-- Archive Header -->
            <header class="">
                <div class="bt-archive-meta">
                    <!-- <span class="bt-archive-count">
                        <?php
                        $count = wp_count_posts('deal')->publish;
                        echo sprintf(_n('%s Deal Available', '%s Deals Available', $count, 'bigtricks-deals'), number_format_i18n($count));
                        ?>
                    </span> -->
                </div>
                <?php
                $description = get_the_archive_description();
                if ( $description ) {
                    echo '<div class="bt-archive-description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
                }
                ?>
            </header>

            <!-- Deals Section with Sidebar Layout -->
            <section class="bt-deals-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">Browse All Loot Deals</h2>
                    <p class="bt-section-subtitle">Get Instant loot Deals From top Online stores.</p>
                </div>

                <!-- Results Count -->
                <div class="bt-results-bar">
                    <div class="bt-results-count">
                        <span id="bt-results-text"><?php echo sprintf(_n('%s Deal', '%s Deals', wp_count_posts('deal')->publish, 'bigtricks-deals'), wp_count_posts('deal')->publish); ?></span>
                    </div>
                </div>

                <div class="bt-deals-layout">
                    <!-- Sidebar Filters -->
                    <aside class="bt-filters-sidebar">
                        <?php echo Bigtricks_Deals_Public::render_sidebar_filters(); ?>
                    </aside>

                    <!-- Main Content -->
                    <main class="bt-deals-main">
                        <div class="bt-deals-content">
                            <?php
                            // Display deals without top filters (sidebar filters only)
                            echo do_shortcode( '[loot-deals count="12"]' );
                            ?>
                        </div>
                    </main>
                </div>
            </section>

            <!-- Featured Categories -->
            <section class="bt-categories-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">Shop by Category</h2>
                    <p class="bt-section-subtitle">Explore deals in your favorite categories</p>
                </div>

                <div class="bt-categories-grid">
                    <?php
                    // Get the "Loot Deals" parent category
                    $loot_deals_parent = get_term_by('slug', 'loot-deals', 'category');
                    $parent_id = $loot_deals_parent ? $loot_deals_parent->term_id : 0;

                    $categories = get_terms([
                        'taxonomy' => 'category',
                        'hide_empty' => true,
                        'number' => 8,
                        'parent' => $parent_id
                    ]);

                    if ($categories && !is_wp_error($categories)) {
                        foreach ($categories as $category) {
                            $deal_count = $category->count;
                            echo '<div class="bt-category-card">';
                            echo '<a href="' . esc_url(get_term_link($category)) . '" class="bt-category-link">';
                            echo '<div class="bt-category-content">';
                            echo '<h3 class="bt-category-title">' . esc_html($category->name) . '</h3>';
                            echo '<span class="bt-category-count">' . sprintf(_n('%s deal', '%s deals', $deal_count, 'bigtricks-deals'), $deal_count) . '</span>';
                            echo '</div>';
                            echo '</a>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </section>

            <!-- Popular Stores -->
            <section class="bt-stores-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">Popular Stores</h2>
                    <p class="bt-section-subtitle">Shop from your favorite brands</p>
                </div>

                <div class="bt-stores-grid">
                    <?php
                    $stores = get_terms([
                        'taxonomy' => 'store',
                        'hide_empty' => true,
                        'number' => 12
                    ]);

                    if ($stores && !is_wp_error($stores)) {
                        foreach ($stores as $store) {
                            $store_logo = get_term_meta($store->term_id, 'thumb_image', true);
                            echo '<div class="bt-store-card">';
                            echo '<a href="' . esc_url(get_term_link($store)) . '" class="bt-store-link">';
                            if ($store_logo) {
                                echo '<img src="' . esc_url($store_logo) . '" alt="' . esc_attr($store->name) . '" class="bt-store-logo" />';
                            } else {
                                echo '<div class="bt-store-placeholder">' . esc_html(substr($store->name, 0, 1)) . '</div>';
                            }
                            echo '<h4 class="bt-store-name">' . esc_html($store->name) . '</h4>';
                            echo '</a>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Newsletter Section -->
    <section class="bt-newsletter-section">
        <div class="pd-15">
            <div class="bt-newsletter-content">
                <h2 class="bt-newsletter-title">Never Miss a Deal!</h2>
                <p class="bt-newsletter-subtitle">Subscribe to get the latest deals delivered to your inbox</p>
                <form class="bt-newsletter-form">
                    <input type="email" placeholder="Enter your email address" required>
                    <button type="submit" class="bt-btn bt-btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
