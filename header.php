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
    <meta name="description" content="<?php bloginfo( 'description' ); ?>">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
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
