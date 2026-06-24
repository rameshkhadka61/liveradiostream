<?php
/**
 * The header for our theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="dark">

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    $meta_desc = get_bloginfo( 'description', 'display' );
    if ( is_singular() ) {
        global $post;
        $excerpt = get_the_excerpt();
        if ( ! empty( $excerpt ) ) {
            $meta_desc = wp_strip_all_tags( $excerpt );
        } elseif ( isset( $post->post_content ) && ! empty( $post->post_content ) ) {
            $meta_desc = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, '...' );
        }
    } elseif ( is_archive() ) {
        $term_desc = get_the_archive_description();
        if ( ! empty( $term_desc ) ) {
            $meta_desc = wp_strip_all_tags( $term_desc );
        }
    }

    // Guaranteed fallback if still empty
    if ( empty( $meta_desc ) ) {
        $meta_desc = get_bloginfo( 'name', 'display' ) . ' - Welcome to our website.';
    }
    ?>
    <meta name="description" content="<?php echo esc_attr( $meta_desc ); ?>">

    <?php
    // Remove default WordPress canonical tag to prevent duplicates
    remove_action( 'wp_head', 'rel_canonical' );
    
    // Generate comprehensive canonical URL
    $canonical_url = wp_get_canonical_url();
    if ( ! $canonical_url ) {
        if ( is_front_page() ) {
            $canonical_url = home_url( '/' );
        } elseif ( is_home() && $page_for_posts = get_option( 'page_for_posts' ) ) {
            $canonical_url = get_permalink( $page_for_posts );
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $canonical_url = get_term_link( get_queried_object() );
        } elseif ( is_post_type_archive() ) {
            $canonical_url = get_post_type_archive_link( get_query_var( 'post_type' ) );
        } elseif ( is_author() ) {
            $canonical_url = get_author_posts_url( get_queried_object_id() );
        } else {
            // Fallback to current URL without query parameters
            $canonical_url = home_url( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
        }
    }
    ?>
    <?php if ( ! is_wp_error( $canonical_url ) && $canonical_url ) : ?>
    <link rel="canonical" href="<?php echo esc_url( $canonical_url ); ?>">
    <?php endif; ?>

    <?php
    // Open Graph Data
    $og_title = is_singular() ? get_the_title() : get_bloginfo( 'name' );
    $og_type  = is_singular() ? 'article' : 'website';
    $og_image = '';
    
    if ( is_singular() && has_post_thumbnail() ) {
        $og_image = get_the_post_thumbnail_url( null, 'large' );
    } else {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $og_image = wp_get_attachment_image_url( $custom_logo_id, 'full' );
        } else {
            $og_image = get_theme_file_uri( 'screenshot.jpg' );
        }
    }
    ?>
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $meta_desc ); ?>">
    <meta property="og:url" content="<?php echo esc_url( $canonical_url ); ?>">
    <meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>">
    <meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>">
    <?php if ( $og_image ) : ?>
    <meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">
    <?php endif; ?>

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $meta_desc ); ?>">
    <?php if ( $og_image ) : ?>
    <meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
    <?php endif; ?>


    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="google-adsense-anchor-ad-position" content="top">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

    <!-- Sticky Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand text-gradient d-flex align-items-center gap-2" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <i class="bi bi-broadcast"></i> <?php bloginfo( 'name' ); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php
                wp_nav_menu( array(
                    'theme_location'  => 'primary',
                    'container'       => false,
                    'menu_class'      => 'navbar-nav mx-auto',
                    'fallback_cb'     => '__return_false',
                    'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'depth'           => 2,
                ) );
                ?>
                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" class="text-secondary fs-5 hover-cyan" aria-label="Search"><i class="bi bi-search"></i></a>
                    <button id="theme-toggle" class="btn-theme-toggle" aria-label="Toggle Theme">
                        <i id="theme-icon" class="bi bi-sun-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Swup Container -->
    <div id="swup" class="transition-fade">
