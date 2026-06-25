<?php
/**
 * The template for displaying all single pages (like Privacy Policy, Terms, etc.)
 */

get_header();
?>

    <?php while ( have_posts() ) : the_post(); ?>
    
    <!-- Hero Section -->
    <section class="hero-section text-center text-white" style="padding: 60px 0;">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3"><?php the_title(); ?></h1>
            <p class="lead mb-0 text-light opacity-75">
                <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
            </p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container pb-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="custom-card p-4 p-md-5 shadow-glow" style="border-top: 3px solid #06b6d4;">
                    <div class="entry-content text-muted" style="line-height: 1.8; font-size: 1.05rem;">
                        <?php
                        // Display the page content created in Gutenberg/Classic Editor
                        the_content();

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links mt-4">' . esc_html__( 'Pages:', 'liveradio' ),
                                'after'  => '</div>',
                            )
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php endwhile; // End of the loop. ?>

<?php get_footer(); ?>

