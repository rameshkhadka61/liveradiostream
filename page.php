<?php
/**
 * The template for displaying all single pages (like Privacy Policy, Terms, etc.)
 */

get_header();
?>

<div class="container py-5 mt-4">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'custom-card p-4 p-md-5 mx-auto' ); ?> style="max-width: 900px;">
            <header class="entry-header text-center mb-5">
                <?php the_title( '<h1 class="entry-title fw-bold display-5 text-gradient mb-3">', '</h1>' ); ?>
                <hr class="w-25 mx-auto border-secondary opacity-50">
            </header>

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
        </article>
        <?php
    endwhile; // End of the loop.
    ?>
</div>

<?php
get_footer();
