<?php
/**
 * Template Name: Deals Archive Template
 * Description: A reusable template for displaying deals archive with all sections
 *
 * @package Bigtricks_Deals
 */

// Enqueue archive page styles
wp_enqueue_style( 'bt-deals-archive', plugin_dir_url( __FILE__ ) . '../public/css/bt-deals-archive.css', array(), '1.0.0', 'all' );
?>

<div class="bt-deals-archive-page">
    <!-- Hero Section -->


    <!-- Main Content -->
    <div class="bt-archive-main">
        <div class="pd-15">

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

            <!-- SEO Content Section -->
            <section class="bt-seo-content-section">
                <div class="bt-seo-content">
                    <div class="bt-seo-header">
                        <h1 class="bt-seo-title">Best Loot Deals & Shopping Deals Online</h1>
                        <p class="bt-seo-subtitle">Discover exclusive discounts, flash sales, and unbeatable offers from top online stores</p>
                    </div>
                    <div class="bt-seo-ctas">
                        <div class="bt-seo-stat">
                            <span class="bt-seo-stat-number">1000+</span>
                            <span class="bt-seo-stat-label">Active Deals</span>
                        </div>
                        <div class="bt-seo-stat">
                            <span class="bt-seo-stat-number">50+</span>
                            <span class="bt-seo-stat-label">Top Stores</span>
                        </div>
                        <div class="bt-seo-stat">
                            <span class="bt-seo-stat-number">24/7</span>
                            <span class="bt-seo-stat-label">Deal Updates</span>
                        </div>
                    </div>
                </div>
            </section>

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
                        <?php
                        // Display deals without top filters (sidebar filters only)
                        echo do_shortcode( '[loot-deals count="12"]' );
                        ?>
                    </main>
                </div>
            </section>

            <!-- Live Telegram Deals -->
            <section class="bt-telegram-deals-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">
                        <span class="bt-pulse-icon"></span>
                        Live Telegram Deals
                    </h2>
                    <p class="bt-section-subtitle">Latest deals shared on our Telegram channel</p>
                </div>

                <!-- CTA Block for Telegram -->
                <div class="bt-telegram-cta-block">
                    <div class="bt-telegram-cta-content">
                        <div class="bt-telegram-cta-icon">
                            <i class="rbi rbi-paperplane"></i>
                        </div>
                        <div class="bt-telegram-cta-text">
                            <h3>Join Our Telegram for Exclusive Loot Deals</h3>
                            <p>Get instant notifications for the best loot deals, flash sales, and exclusive offers before anyone else!</p>
                            <div class="bt-telegram-cta-stats">
                                <span class="bt-telegram-stat"><strong>50K+</strong> Active Members</span>
                                <span class="bt-telegram-stat"><strong>500+</strong> Daily Deals</span>
                                <span class="bt-telegram-stat"><strong>24/7</strong> Updates</span>
                            </div>
                        </div>
                        <a href="https://links.bigtricks.in/tg" class="bt-telegram-main-cta" target="_blank" rel="noopener noreferrer">
                            Join Telegram Channel
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M7 17L17 7"></path>
                                <path d="M7 7h10v10"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <div id="bt-telegram-deals-container" class="bt-telegram-deals-container">
                    <div class="bt-loading-spinner" id="bt-loading-spinner">
                        <div class="bt-spinner"></div>
                        <p>Loading latest deals...</p>
                    </div>
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

            <!-- Features Section -->
            <section class="bt-features-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">Why Choose BigTricks Deals?</h2>
                    <p class="bt-section-subtitle">Experience the best in online shopping deals</p>
                </div>

                <div class="bt-features-grid">
                    <div class="bt-feature-card">
                        <div class="bt-feature-icon">💰</div>
                        <h3 class="bt-feature-title">Best Prices Guaranteed</h3>
                        <p class="bt-feature-description">We compare prices across multiple platforms to ensure you get the absolute best deals available.</p>
                    </div>

                    <div class="bt-feature-card">
                        <div class="bt-feature-icon">⚡</div>
                        <h3 class="bt-feature-title">Lightning Fast Updates</h3>
                        <p class="bt-feature-description">New deals are added multiple times daily, so you never miss out on limited-time offers.</p>
                    </div>

                    <div class="bt-feature-card">
                        <div class="bt-feature-icon">🛡️</div>
                        <h3 class="bt-feature-title">Verified & Safe</h3>
                        <p class="bt-feature-description">All deals are verified and tested to ensure they're legitimate and safe for shopping.</p>
                    </div>

                    <div class="bt-feature-card bt-app-download-card">
                        <div class="bt-feature-icon">📱</div>
                        <h3 class="bt-feature-title">Instant Alerts on Android App</h3>
                        <p class="bt-feature-description">Download our Android app for instant notifications about the best loot deals and exclusive offers.</p>
                        <a href="https://play.google.com/store/apps/details?id=in.bigtricks" class="bt-app-download-btn" target="_blank" rel="noopener noreferrer">
                            <i class="rbi rbi-playstore"></i>
                            Download App
                        </a>
                    </div>



                </div>
            </section>

            <!-- Testimonials Section -->
            <section class="bt-testimonials-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">What Our Users Say</h2>
                    <p class="bt-section-subtitle">Real experiences from satisfied shoppers</p>
                </div>

                <div class="bt-testimonials-grid">
                    <div class="bt-testimonial-card">
                        <p class="bt-testimonial-quote">"BigTricks Deals has saved me thousands of rupees this year! The deals are genuine and the platform is so easy to use."</p>
                        <div class="bt-testimonial-author">
                            <div class="bt-testimonial-avatar">R</div>
                            <div class="bt-testimonial-info">
                                <h4>Rahul Sharma</h4>
                                <p>Mumbai, Maharashtra</p>
                            </div>
                        </div>
                    </div>

                    <div class="bt-testimonial-card">
                        <p class="bt-testimonial-quote">"I love how they update deals multiple times a day. I've found amazing offers on electronics and fashion items."</p>
                        <div class="bt-testimonial-author">
                            <div class="bt-testimonial-avatar">P</div>
                            <div class="bt-testimonial-info">
                                <h4>Priya Patel</h4>
                                <p>Ahmedabad, Gujarat</p>
                            </div>
                        </div>
                    </div>

                    <div class="bt-testimonial-card">
                        <p class="bt-testimonial-quote">"The Telegram channel is a game-changer! I get instant notifications about flash sales and never miss a good deal."</p>
                        <div class="bt-testimonial-author">
                            <div class="bt-testimonial-avatar">A</div>
                            <div class="bt-testimonial-info">
                                <h4>Amit Kumar</h4>
                                <p>Delhi, NCR</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Subscription Callouts -->
            <section class="bt-subscription-section">
                <div class="bt-section-header">
                    <h2 class="bt-section-title">Stay Updated with Latest Deals</h2>
                    <p class="bt-section-subtitle">Follow us on your favorite platforms for instant deal alerts</p>
                </div>

                <div class="bt-subscription-grid">
                    <div class="bt-subscription-card">
                        <div class="bt-subscription-icon">
                            <i class="rbi rbi-whatsapp"></i>
                        </div>
                        <h3 class="bt-subscription-title">WhatsApp Channel</h3>
                        <p class="bt-subscription-description">Get instant deal notifications on WhatsApp</p>
                        <a href="https://links.bigtricks.in/whatsapp" class="bt-subscription-btn bt-whatsapp-btn" target="_blank" rel="noopener noreferrer">
                            Join Channel
                        </a>
                    </div>

                    <div class="bt-subscription-card">
                        <div class="bt-subscription-icon">
                            <i class="rbi rbi-paperplane"></i>
                        </div>
                        <h3 class="bt-subscription-title">Telegram Channel</h3>
                        <p class="bt-subscription-description">Join our Telegram for exclusive deals</p>
                        <a href="https://links.bigtricks.in/tg" class="bt-subscription-btn bt-telegram-btn" target="_blank" rel="noopener noreferrer">
                            Join Channel
                        </a>
                    </div>

                    <div class="bt-subscription-card">
                        <div class="bt-subscription-icon">
                            <i class="rbi rbi-x"></i>
                        </div>
                        <h3 class="bt-subscription-title">X (Twitter)</h3>
                        <p class="bt-subscription-description">Follow us for real-time deal updates</p>
                        <a href="/bigtricksin" class="bt-subscription-btn bt-twitter-btn" target="_blank" rel="noopener noreferrer">
                            Follow Us
                        </a>
                    </div>

                    <div class="bt-subscription-card">
                        <div class="bt-subscription-icon">
                            <i class="rbi rbi-playstore"></i>
                        </div>
                        <h3 class="bt-subscription-title">Android App</h3>
                        <p class="bt-subscription-description">Download our app for instant deal alerts</p>
                        <a href="https://play.google.com/store/apps/details?id=in.bigtricks" class="bt-subscription-btn bt-android-btn" target="_blank" rel="noopener noreferrer">
                            Download App
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>


</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchTelegramDeals();
});

function fetchTelegramDeals() {
    const container = document.getElementById('bt-telegram-deals-container');
    const spinner = document.getElementById('bt-loading-spinner');

    fetch('https://us-central1-bigtricks-169316.cloudfunctions.net/app/tUpdates')
        .then(response => response.json())
        .then(data => {
            spinner.style.display = 'none';

            if (data.data && data.data.length > 0) {
                // Create horizontal scrolling container
                const scrollContainer = document.createElement('div');
                scrollContainer.className = 'bt-telegram-scroll-container';

                // Create deals wrapper
                const dealsWrapper = document.createElement('div');
                dealsWrapper.className = 'bt-telegram-deals-wrapper';

                // Create deal cards - show all available deals
                data.data.forEach((deal, index) => {
                    // Extract deal information
                    const dealInfo = extractDealInfo(deal);
                    const truncatedText = dealInfo.description.length > 100 ? dealInfo.description.substring(0, 100) + '...' : dealInfo.description;

                    const dealCard = document.createElement('div');
                    dealCard.className = 'bt-telegram-deal-card';

                    dealCard.innerHTML = `
                        ${dealInfo.image ? `<div class="bt-deal-image"><img src="${dealInfo.image}" alt="${dealInfo.title}" loading="lazy"></div>` : ''}
                        <div class="bt-telegram-deal-content">
                            <h4 class="bt-deal-title">${dealInfo.title}</h4>
                            <p class="bt-telegram-deal-text">${truncatedText}</p>
                            ${dealInfo.price ? `<div class="bt-deal-price">${dealInfo.price}</div>` : ''}
                            <div class="bt-telegram-deal-meta">
                                <span class="bt-telegram-date">${formatPreciseTime(deal.date)}</span>
                                <a href="${dealInfo.url || 'https://links.bigtricks.in/tg'}" class="bt-telegram-link" target="_blank" rel="noopener noreferrer">
                                    Grab Deal
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 17L17 7"></path>
                                        <path d="M7 7h10v10"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    `;

                    dealsWrapper.appendChild(dealCard);
                });

                scrollContainer.appendChild(dealsWrapper);
                container.appendChild(scrollContainer);

            } else {
                container.innerHTML = '<p class="bt-no-deals">No deals available at the moment. Check back later!</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching Telegram deals:', error);
            spinner.style.display = 'none';
            container.innerHTML = '<p class="bt-error-message">Unable to load deals. Please try again later.</p>';
        });
}



function extractDealInfo(deal) {
    const content = deal.text || deal.caption || '';
    const entities = deal.entities || deal.caption_entities || [];

    // Extract URLs from entities
    let dealUrl = '';
    entities.forEach(entity => {
        if (entity.type === 'url') {
            const url = content.substring(entity.offset, entity.offset + entity.length);
            if (url.includes('links.bigtricks.in') || url.includes('fkrt.cc')) {
                dealUrl = url;
            }
        }
    });

    // Extract title from content
    let title = 'Latest Deal';
    let price = '';
    let description = content;

    // Try to extract meaningful title
    const lines = content.split('\n').filter(line => line.trim());

    // Look for product names, prices, or key phrases
    for (const line of lines) {
        const trimmed = line.trim();

        // Check for price patterns
        const priceMatch = trimmed.match(/[@₹]\s*[\d,]+/);
        if (priceMatch && !price) {
            price = priceMatch[0];
        }

        // Check for product names (usually the first meaningful line)
        if (trimmed.length > 10 && trimmed.length < 100 && !trimmed.includes('http') && !trimmed.includes('@') && !priceMatch) {
            // Remove emojis and clean up
            const cleanTitle = trimmed.replace(/[^\w\s₹@\-.,&()]/g, '').trim();
            if (cleanTitle.length > 5) {
                title = cleanTitle;
                break;
            }
        }
    }

    // Clean up description
    description = content
        .replace(/https?:\/\/[^\s]+/g, '') // Remove URLs
        .replace(/[^\w\s₹@\-.,&()]/g, ' ') // Remove emojis/special chars
        .replace(/\s+/g, ' ') // Normalize spaces
        .trim();

    if (description.length > 150) {
        description = description.substring(0, 150) + '...';
    }

    // Extract image if available
    let image = '';
    if (deal.photo && deal.photo.length > 0) {
        // Use the largest available image
        const largestPhoto = deal.photo.reduce((prev, current) =>
            (prev.file_size > current.file_size) ? prev : current
        );
        // Note: In a real implementation, you'd need to construct the actual image URL
        // For now, we'll skip images as they require additional API calls
    }

    return {
        title: title || 'Latest Deal',
        description: description || 'Check out this amazing deal!',
        price: price,
        url: dealUrl,
        image: image
    };
}

function formatPreciseTime(timestamp) {
    if (!timestamp) return 'Just now';

    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffMins < 1) {
        return 'Just now';
    } else if (diffMins < 60) {
        return `${diffMins}m ago`;
    } else if (diffHours < 24) {
        return `${diffHours}h ago`;
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays}d ago`;
    } else {
        return date.toLocaleDateString();
    }
}
</script>
