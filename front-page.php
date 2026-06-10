<?php
/**
 * The front page template
 */

get_header(); 
$station_count = get_transient('lr_station_count');
if ( false === $station_count ) {
    $station_count_obj = wp_count_posts('radio_station');
    $station_count = isset($station_count_obj->publish) ? $station_count_obj->publish : 0;
    set_transient('lr_station_count', $station_count, 12 * HOUR_IN_SECONDS);
}

$country_count = get_transient('lr_country_count_v2');
if ( false === $country_count ) {
    $country_count = wp_count_terms( array( 'taxonomy' => 'country', 'hide_empty' => false ) );
    set_transient('lr_country_count_v2', $country_count, 12 * HOUR_IN_SECONDS);
}
?>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3">Listen to Radio Stations <span class="text-gradient">Worldwide</span></h1>
            <p class="lead mb-5 text-light opacity-75">Discover music, news, and talk shows from over <?php echo $country_count; ?>+ countries.</p>

            <div class="row justify-content-center mb-4">
                <div class="col-md-8 col-lg-6">
                    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-box d-flex align-items-center">
                        <select name="country" id="hero-country-select" class="search-select border-0 w-auto" aria-label="Select Country" onchange="if(this.options[this.selectedIndex].dataset.link) { if(typeof swup !== 'undefined') swup.navigate(this.options[this.selectedIndex].dataset.link); else window.location.href = this.options[this.selectedIndex].dataset.link; }">
                            <option value="">🌎 All</option>
                            <?php
                            $countries = get_transient('lr_all_countries_v2');
                            if ( false === $countries ) {
                                $countries = get_terms( array( 'taxonomy' => 'country', 'hide_empty' => false ) );
                                set_transient('lr_all_countries_v2', $countries, 12 * HOUR_IN_SECONDS);
                            }
                            foreach ( $countries as $country ) {
                                echo '<option value="' . esc_attr( $country->slug ) . '" data-link="' . esc_url( get_term_link( $country ) ) . '">' . esc_html( $country->name ) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="text" name="s" class="form-control search-input" placeholder="Search for stations, genres..." aria-label="Search stations">
                        <button type="submit" class="btn btn-gradient rounded-pill px-4 m-1">Search</button>
                    </form>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php
                $trending_countries = get_transient( 'lr_trending_countries_v3' );
                if ( false === $trending_countries ) {
                    $trending_countries = get_terms( array( 'taxonomy' => 'country', 'number' => 6, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false ) );
                    set_transient( 'lr_trending_countries_v3', $trending_countries, 12 * HOUR_IN_SECONDS );
                }
                foreach ( $trending_countries as $country ) {
                    echo '<a href="' . esc_url( get_term_link( $country ) ) . '" class="tag-btn">' . esc_html( $country->name ) . '</a>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container mb-5">

        <!-- Featured Countries -->
        <section class="mb-5 pb-3">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h2 class="h3 fw-bold mb-0">Browse by Country</h2>
                <a href="<?php echo esc_url( home_url( '/countries' ) ); ?>" class="text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-4">
                <?php
                $top_countries = get_transient( 'lr_top_countries_v3' );
                if ( false === $top_countries ) {
                    $top_countries = get_terms( array( 'taxonomy' => 'country', 'number' => 4, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false ) );
                    set_transient( 'lr_top_countries_v3', $top_countries, 12 * HOUR_IN_SECONDS );
                }
                foreach ( $top_countries as $country ) :
                    $iso_code = liveradio_get_country_code( $country->slug );
                    $flag_url = 'https://flagcdn.com/w80/' . $iso_code . '.png';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?php echo esc_url( get_term_link( $country ) ); ?>" class="custom-card country-card h-100 text-decoration-none">
                        <img src="<?php echo esc_url( $flag_url ); ?>" alt="<?php echo esc_attr( $country->name ); ?> Flag" class="country-flag" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/placeholder-flag.png'">
                        <div>
                            <h3 class="h6 mb-1 text-primary-custom"><?php echo esc_html( $country->name ); ?></h3>
                            <span class="text-muted small"><?php echo number_format_i18n( $country->count ); ?> Stations</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Top Radio Stations -->
        <?php
        $user_country_data = liveradio_get_user_country_data();
        $section_title = "Top Trending Stations";
        $query_args = array(
            'post_type'      => 'radio_station',
            'posts_per_page' => 8,
        );
        $has_country_filter = false;

        if ( $user_country_data && isset($user_country_data['name']) ) {
            $country_name = $user_country_data['name'];
            $term = get_term_by('name', $country_name, 'country');
            
            if (!$term) {
                $slug = sanitize_title($country_name);
                $term = get_term_by('slug', $slug, 'country');
            }

            if ($term) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'country',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ),
                );
                $section_title = "Top trending Stations from " . esc_html($term->name);
                $has_country_filter = true;
            }
        }

        $top_stations = new WP_Query( $query_args );

        // Fallback if no stations in user country
        if ( !$top_stations->have_posts() && $has_country_filter ) {
            unset($query_args['tax_query']);
            $section_title = "Top Trending Stations";
            $top_stations = new WP_Query( $query_args );
        }
        ?>
        <section class="mb-5 pb-3">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h2 class="h3 fw-bold mb-0"><?php echo $section_title; ?></h2>
            </div>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4" id="station-list-container">
                <?php
                if ( $top_stations->have_posts() ) :
                    while ( $top_stations->have_posts() ) : $top_stations->the_post();
                        get_template_part( 'template-parts/content', 'station-card' );
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>No stations found.</p>';
                endif;
                ?>
            </div>
        </section>

        <!-- Genres Section -->
        <section class="mb-5 pb-3">
            <h2 class="h3 fw-bold mb-4">Explore Genres</h2>
            <div class="row g-3">
                <?php
                $genres = get_transient( 'lr_genres_v3' );
                if ( false === $genres ) {
                    $genres = get_terms( array( 'taxonomy' => 'genre', 'number' => 4, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false ) );
                    set_transient( 'lr_genres_v3', $genres, 12 * HOUR_IN_SECONDS );
                }
                $colors = array('#ef4444', '#3b82f6', '#10b981', '#f59e0b');
                $icons = array('bi-mic-fill', 'bi-newspaper', 'bi-lightning-fill', 'bi-music-note-beamed');
                $i = 0;
                foreach ( $genres as $genre ) :
                    $bg = $colors[$i % count($colors)];
                    $icon = $icons[$i % count($icons)];
                ?>
                <div class="col-6 col-md-3">
                    <a href="<?php echo esc_url( get_term_link( $genre ) ); ?>" class="text-decoration-none">
                        <div class="genre-card" style="background-color: <?php echo $bg; ?>;">
                            <i class="bi <?php echo $icon; ?> genre-icon"></i>
                            <h3 class="h5 mb-0"><?php echo esc_html( $genre->name ); ?></h3>
                        </div>
                    </a>
                </div>
                <?php 
                $i++;
                endforeach; 
                ?>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="mb-5 why-section">
            <div class="text-center mb-5">
                <span class="badge rounded-pill px-3 py-2 mb-3 d-inline-block"
                    style="background:rgba(6,182,212,.15); color:var(--accent-cyan); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase;">Why Us</span>
                <h2 class="display-6 fw-bold mb-3">Why Choose <span class="text-gradient">LiveRadioStream</span>?</h2>
                <p class="text-muted mx-auto" style="max-width:520px;">Everything you need to discover, stream, and
                    enjoy radio from every corner of the world &mdash; beautifully designed and built for speed.</p>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="custom-card p-4 h-100" style="border-top: 3px solid #06b6d4;">
                                <div class="mb-3 d-flex align-items-center justify-content-center rounded-3"
                                    style="width:52px;height:52px;background:rgba(6,182,212,.15);">
                                    <i class="bi bi-globe2 fs-4" style="color:#06b6d4;"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">Worldwide Coverage</h3>
                                <p class="text-muted mb-0" style="font-size:.82rem; line-height:1.6;">Access <?php echo number_format_i18n($station_count); ?>+
                                    stations from <?php echo $country_count; ?>+ countries in one click.</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="custom-card p-4 h-100" style="border-top: 3px solid #3b82f6;">
                                <div class="mb-3 d-flex align-items-center justify-content-center rounded-3"
                                    style="width:52px;height:52px;background:rgba(59,130,246,.15);">
                                    <i class="bi bi-soundwave fs-4" style="color:#3b82f6;"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">HD Streaming</h3>
                                <p class="text-muted mb-0" style="font-size:.82rem; line-height:1.6;">Crystal clear
                                    audio up to 320 kbps, no buffering.</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="custom-card p-4 h-100" style="border-top: 3px solid #10b981;">
                                <div class="mb-3 d-flex align-items-center justify-content-center rounded-3"
                                    style="width:52px;height:52px;background:rgba(16,185,129,.15);">
                                    <i class="bi bi-search-heart fs-4" style="color:#10b981;"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">Smart Search</h3>
                                <p class="text-muted mb-0" style="font-size:.82rem; line-height:1.6;">Find stations by
                                    country, genre, name or language instantly.</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="custom-card p-4 h-100" style="border-top: 3px solid #f59e0b;">
                                <div class="mb-3 d-flex align-items-center justify-content-center rounded-3"
                                    style="width:52px;height:52px;background:rgba(245,158,11,.15);">
                                    <i class="bi bi-phone fs-4" style="color:#f59e0b;"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">Mobile Friendly</h3>
                                <p class="text-muted mb-0" style="font-size:.82rem; line-height:1.6;">Listen anywhere,
                                    anytime with our fully responsive design.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="position-relative ps-lg-3">
                        <img src="https://images.unsplash.com/photo-1611532736597-de2d4265fba3?q=80&w=900&auto=format&fit=crop"
                            class="img-fluid rounded-4 shadow-lg w-100" style="object-fit:cover; max-height:420px;"
                            alt="LiveRadioStream app experience">

                        <div class="position-absolute glass rounded-3 px-3 py-2 d-flex align-items-center gap-3 shadow"
                            style="bottom:24px; left:-16px; min-width:220px; border:1px solid rgba(6,182,212,.25); backdrop-filter:blur(12px);">
                            <img src="https://images.unsplash.com/photo-1619983081563-430f63602796?w=80&q=80"
                                alt="Station" style="width:40px;height:40px;border-radius:8px;object-fit:contain;background:#fff;padding:2px;">
                            <div>
                                <div class="small fw-bold" style="color:var(--text-primary);">Capital FM</div>
                                <div style="font-size:.72rem; color:var(--text-secondary);">Now playing &middot; Pop</div>
                            </div>
                            <div class="equalizer ms-auto" style="height:18px;">
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                            </div>
                        </div>

                        <div class="position-absolute glass rounded-3 px-3 py-2 text-center shadow"
                            style="top:20px; right:-16px; min-width:110px; border:1px solid rgba(59,130,246,.25);">
                            <div class="fw-bold" style="font-size:1.3rem; color:var(--accent-blue);">48k+</div>
                            <div style="font-size:.72rem; color:var(--text-secondary);">Listening Now</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-6 col-md-3">
                    <div class="custom-card p-3 text-center">
                        <div class="fw-bold fs-4 text-gradient"><?php echo number_format_i18n($station_count); ?></div>
                        <div class="text-muted small">Radio Stations</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="custom-card p-3 text-center">
                        <div class="fw-bold fs-4 text-gradient"><?php echo $country_count; ?>+</div>
                        <div class="text-muted small">Countries</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="custom-card p-3 text-center">
                        <div class="fw-bold fs-4 text-gradient">48k</div>
                        <div class="text-muted small">Live Listeners</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="custom-card p-3 text-center">
                        <div class="fw-bold fs-4 text-gradient">100%</div>
                        <div class="text-muted small">Free to Use</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="text-center py-5 glass rounded-4 mb-5 border">
            <h2 class="display-6 fw-bold mb-3">Couldn't Find Your Radio Station?</h2>
            <p class="lead text-muted mb-4">Don't worry, we will include it in our next update. Click below to submit your radio station.</p>
            <a href="<?php echo esc_url( home_url( '/submit' ) ); ?>" class="btn btn-gradient btn-lg rounded-pill px-5 d-inline-block text-decoration-none text-white shadow-glow">Submit Here!</a>
        </section>

    </main>

<?php get_footer(); ?>
