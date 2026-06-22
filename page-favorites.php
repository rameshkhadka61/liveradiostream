<?php
/**
 * Template Name: Favorites Page
 *
 * The template for displaying the user's favorite stations.
 */

get_header();
?>

    <?php while ( have_posts() ) : the_post(); ?>
    
    <!-- Hero Section -->
    <section class="hero-section text-center text-white" style="padding: 60px 0;">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3"><i class="bi bi-heart-fill text-danger me-2"></i><?php the_title(); ?></h1>
            <p class="lead mb-0 text-light opacity-75">
                Your personal collection of favorite radio stations.
            </p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container pb-5 mt-4" style="min-height: 50vh;">
        
        <?php 
        // Optional: display page content if they added any text in the editor
        if ( get_the_content() ) : 
        ?>
            <div class="mb-4 text-muted text-center" style="font-size: 1.05rem;">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>

        <!-- Favorites Grid Container -->
        <div id="favorites-container" class="position-relative">
            <!-- Loading State -->
            <div id="favorites-loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading your favorites...</p>
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="favorites-empty" class="col-12 d-none">
                <div class="empty-state text-center py-5 glass rounded-4" style="border: 1px dashed rgba(148, 163, 184, 0.3);">
                    <i class="bi bi-heart fs-1 text-muted mb-3 d-block"></i>
                    <h3 class="h5 fw-bold">No Favorites Yet</h3>
                    <p class="text-muted">You haven't added any stations to your favorites. Explore and click the heart icon on a station to add it here!</p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-gradient rounded-pill px-4 mt-2">Explore Stations</a>
                </div>
            </div>

            <!-- The Grid -->
            <div id="favorites-grid" class="row g-4 d-none">
                <!-- Stations will be populated here via AJAX -->
            </div>
        </div>

    </main>

    <?php endwhile; // End of the loop. ?>

<?php get_footer(); ?>
