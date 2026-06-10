<?php
/**
 * Template Name: Countries Page
 */

get_header(); ?>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white" style="padding: 80px 0; background: linear-gradient(135deg, rgba(6,182,212,0.8), rgba(59,130,246,0.8)), url('https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=1200&auto=format&fit=crop') center/cover;">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3">Browse by <span class="text-gradient">Country</span></h1>
            <p class="lead mb-4 text-light opacity-75">Explore radio stations from over 150 countries worldwide.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="search-box d-flex align-items-center">
                        <input type="hidden" name="post_type" value="radio_station">
                        <input type="text" name="s" class="form-control search-input" placeholder="Search for a country..." aria-label="Search country">
                        <button type="submit" class="btn btn-gradient rounded-pill px-4 m-1">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container mb-5 py-5">

        <!-- All Countries Grid -->
        <section class="mb-5 pb-3">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h2 class="h3 fw-bold mb-0">All Countries</h2>
                <select class="form-select w-auto" aria-label="Sort by" onchange="location = this.value;">
                    <option value="" selected>Sort by: Popular</option>
                    <option value="?orderby=name">Sort by: A-Z</option>
                </select>
            </div>
            
            <div class="row g-4">
                <?php
                $orderby = isset( $_GET['orderby'] ) && $_GET['orderby'] === 'name' ? 'name' : 'count';
                $order = isset( $_GET['orderby'] ) && $_GET['orderby'] === 'name' ? 'ASC' : 'DESC';
                
                $transient_key = 'lr_all_countries_' . $orderby . '_' . $order;
                $countries = get_transient($transient_key);
                
                if ( false === $countries ) {
                    $countries = get_terms( array(
                        'taxonomy'   => 'country',
                        'hide_empty' => true,
                        'orderby'    => $orderby,
                        'order'      => $order,
                    ) );
                    set_transient($transient_key, $countries, 12 * HOUR_IN_SECONDS);
                }
                
                foreach ( $countries as $country ) :
                    $iso_code = liveradio_get_country_code( $country->slug );
                    $flag_url = 'https://flagcdn.com/w80/' . $iso_code . '.png';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?php echo esc_url( get_term_link( $country ) ); ?>" class="custom-card country-card h-100 text-decoration-none">
                        <img src="<?php echo esc_url( $flag_url ); ?>" alt="<?php echo esc_attr( $country->name ); ?> Flag" class="country-flag" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/placeholder-flag.png'">
                        <div>
                            <h3 class="h6 mb-1 text-primary-custom"><?php echo esc_html( $country->name ); ?></h3>
                            <span class="text-muted small"><?php echo $country->count; ?> Stations</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

<?php get_footer(); ?>
