<?php
/**
 * The main template file
 */

get_header(); ?>

    <main class="container pb-5 mt-4">
        <h1 class="display-5 fw-bold mb-5">Latest Stations</h1>
        
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content', 'station-card' ); ?>
                <?php endwhile; ?>
                
                <div class="col-12 mt-5">
                    <?php echo paginate_links(); ?>
                </div>
            <?php else : ?>
                <div class="col-12">
                    <p>No posts found.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

<?php get_footer(); ?>

