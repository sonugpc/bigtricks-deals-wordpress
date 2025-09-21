<?php
/**
 * Template part for displaying single deal posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Bigtricks_Deals
 */

?>

<header class="entry-header">
    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
</header><!-- .entry-header -->

<div class="entry-content">
    <?php
    the_content();

    $deal_data = Bigtricks_Deals_Content_Helper::get_deal_data( get_the_ID() );
    ?>
    <div class="quick-offer-cta">
        <div class="deal-info">
            <h3><?php echo esc_html( $deal_data['title'] ); ?></h3>
            <div class="price-wrapper">
                <span class="sale-price"><?php echo esc_html( $deal_data['sale_price'] ); ?></span>
                <?php if ( $deal_data['old_price'] > 0 ) : ?>
                    <span class="old-price"><del><?php echo esc_html( $deal_data['old_price'] ); ?></del></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="deal-actions">
            <a href="<?php echo esc_url( $deal_data['offer_url'] ); ?>" class="button btn-deal" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $deal_data['button_text'] ); ?></a>
            <?php if ( ! empty( $deal_data['store_name'] ) && ! empty( $deal_data['store_url'] ) ) : ?>
                <div class="deal-store">
                    <a href="<?php echo esc_url( $deal_data['store_url'] ); ?>">
                        <?php if ( ! empty( $deal_data['store_logo'] ) ) : ?>
                            <img src="<?php echo esc_url( $deal_data['store_logo'] ); ?>" alt="<?php echo esc_attr( $deal_data['store_name'] ); ?>" class="store-logo">
                        <?php else : ?>
                            <?php echo esc_html( $deal_data['store_name'] ); ?>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div><!-- .entry-content -->

<footer class="entry-footer">
    <?php
    // Display store taxonomy terms
    $stores = get_the_term_list( get_the_ID(), 'store', '<strong>Stores:</strong> ', ', ', '' );
    if ( ! is_wp_error( $stores ) && ! empty( $stores ) ) {
        echo '<span class="stores-links">' . $stores . '</span>';
    }
    ?>
</footer><!-- .entry-footer -->
