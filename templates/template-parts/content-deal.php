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

    echo do_shortcode( '[quick_offer id="' . get_the_ID() . '"]' );
    ?>
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
