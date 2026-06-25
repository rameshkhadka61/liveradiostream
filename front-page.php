<?php
/**
 * The front page template
 */

get_header(); 
$station_count = get_transient('lr_station_count_v6');
if ( false === $station_count ) {
    $station_count_obj = wp_count_posts('radio_station');
    $station_count = isset($station_count_obj->publish) ? $station_count_obj->publish : 0;
    set_transient('lr_station_count_v6', $station_count, 12 * HOUR_IN_SECONDS);
}

$country_count = get_transient('lr_country_count_v6');
if ( false === $country_count ) {
    $country_count = wp_count_terms( array( 'taxonomy' => 'country', 'hide_empty' => true ) );
    set_transient('lr_country_count_v6', $country_count, 12 * HOUR_IN_SECONDS);
}

$total_plays = get_transient('lr_total_plays_v6');
if ( false === $total_plays ) {
    global $wpdb;
    $total_plays = (int) $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_play_count'");
    set_transient('lr_total_plays_v6', $total_plays, 1 * HOUR_IN_SECONDS);
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
                            $countries = get_transient('lr_all_countries_v6');
                            if ( false === $countries ) {
                                $countries = get_terms( array( 'taxonomy' => 'country', 'hide_empty' => true ) );
                                set_transient('lr_all_countries_v6', $countries, 12 * HOUR_IN_SECONDS);
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

            <!-- Surprise Me Button -->
            <div class="mt-3">
                <button id="btn-surprise-me" class="btn btn-glass-outline rounded-pill px-4 py-2" style="border: 1px solid rgba(6,182,212,0.4); color: var(--accent-cyan); backdrop-filter: blur(8px);">
                    <span id="surprise-btn-content"><i class="bi bi-shuffle me-2"></i>Surprise Me! <span class="badge rounded-pill ms-1" style="background:rgba(6,182,212,.2); color:var(--accent-cyan); font-size:.7rem;">Random Station</span></span>
                    <span id="surprise-btn-loading" class="d-none"><span class="spinner-border spinner-border-sm me-2"></span>Finding station...</span>
                </button>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php
                $trending_countries = get_transient( 'lr_trending_countries_v6' );
                if ( false === $trending_countries ) {
                    $trending_countries = get_terms( array( 'taxonomy' => 'country', 'number' => 6, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ) );
                    set_transient( 'lr_trending_countries_v6', $trending_countries, 12 * HOUR_IN_SECONDS );
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

        <!-- Top Leaderboard Ad -->
        <?php $front_top_ad = get_theme_mod( 'liveradio_ad_front_top', '' ); ?>
        <?php if ( ! empty( $front_top_ad ) ) : ?>
            <div class="mb-5 text-center mt-4">
                <?php liveradio_safe_ad_output($front_top_ad); ?>
            </div>
        <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
            <div class="mb-5 mt-4">
                <div class="glass p-3 rounded-4 text-center" style="border:1px dashed rgba(255,255,255,.15);">
                    <p class="text-muted small mb-0">Front Page Top Ad Placeholder (Add code in Customizer)</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Featured Countries -->
        <section class="mb-5 pb-3">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h2 class="h3 fw-bold mb-0">Browse by Country</h2>
                <a href="<?php echo esc_url( home_url( '/countries' ) ); ?>" class="text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-4">
                <?php
                $top_countries = get_transient( 'lr_top_countries_v6' );
                if ( false === $top_countries ) {
                    $top_countries = get_terms( array( 'taxonomy' => 'country', 'number' => 4, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ) );
                    set_transient( 'lr_top_countries_v6', $top_countries, 12 * HOUR_IN_SECONDS );
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

        <!-- Featured Station Spotlight -->
        <section class="mb-5 pb-3">
            <h2 class="h3 fw-bold mb-4">Spotlight Station</h2>
            <?php
            $featured_args = array(
                'post_type'      => 'radio_station',
                'posts_per_page' => 1,
                'orderby'        => 'rand'
            );
            $featured_query = new WP_Query( $featured_args );

            if ( $featured_query->have_posts() ) :
                while ( $featured_query->have_posts() ) : $featured_query->the_post();
                    $feat_genres = get_the_terms( get_the_ID(), 'genre' );
                    $feat_genre = $feat_genres && ! is_wp_error( $feat_genres ) ? $feat_genres[0]->name : 'Music';
            ?>
            <div class="custom-card p-0 overflow-hidden position-relative spotlight-card station-hero text-center text-md-start" style="margin-top:0; border:1px solid var(--glass-border);">
                <!-- Blurred background image like single page -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'large', array( 'class' => 'station-hero-bg' ) ); ?>
                <?php else : ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="" class="station-hero-bg">
                <?php endif; ?>
                
                <div class="station-hero-content position-relative z-1 w-100">
                    <div class="row g-0">
                        <div class="col-md-4 p-4 p-lg-5 d-flex align-items-center justify-content-center">
                            <!-- Station Logo -->
                            <div class="position-relative">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'large', array( 'class' => 'spotlight-logo station-img bg-white p-2', 'style' => 'object-fit: contain !important;' ) ); ?>
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="Station Logo" class="spotlight-logo station-img bg-white p-2" style="object-fit: contain !important;">
                                <?php endif; ?>
                                <div class="position-absolute top-0 start-0 translate-middle" style="z-index: 2;">
                                    <span class="badge bg-danger shadow-sm"><i class="bi bi-star-fill me-1"></i> Featured</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 p-4 p-lg-5 d-flex align-items-center">
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                                    <span class="tag-pill" style="font-size:0.7rem; padding:0.2rem 0.6rem;"><?php echo esc_html($feat_genre); ?></span>
                                    <?php 
                                        $play_count = (int) get_post_meta( get_the_ID(), '_play_count', true );
                                        $listeners = $play_count;
                                    ?>
                                    <span class="text-muted small"><i class="bi bi-play-circle me-1"></i><?php echo $listeners > 0 ? number_format_i18n($listeners) . ' plays' : 'New'; ?></span>
                                </div>
                                
                                <h3 class="display-6 fw-bold mb-3 station-name"><?php the_title(); ?></h3>
                                
                                <p class="text-muted mb-4 mx-auto mx-md-0" style="line-height:1.6; max-width:600px;">
                                    <?php echo wp_trim_words( wp_strip_all_tags( get_the_content() ), 45, '...' ); ?>
                                </p>
                                
                                <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                    <button class="btn btn-gradient rounded-pill px-4 btn-play-trigger" data-station-id="<?php echo get_the_ID(); ?>" data-img="<?php echo has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : get_template_directory_uri() . '/assets/images/placeholder.png'; ?>">
                                        <i class="bi bi-play-circle-fill me-2"></i>Listen Now
                                    </button>
                                    <a href="<?php the_permalink(); ?>" class="btn rounded-pill px-4 btn-glass-outline"><i class="bi bi-info-circle me-2"></i>View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </section>

        <!-- Top Radio Stations -->
        <?php
        $user_country_data = liveradio_get_user_country_data();
        $section_title = "Top Trending Stations";
        $query_args = array(
            'post_type'      => 'radio_station',
            'posts_per_page' => 10,
            'meta_key'       => '_play_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
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

        <!-- Continue Listening Section (Populated from localStorage via JS) -->
        <section class="mb-5 pb-3 d-none" id="continue-listening-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="equalizer" style="height:18px; display:inline-flex;">
                        <div class="equalizer-bar"></div>
                        <div class="equalizer-bar"></div>
                        <div class="equalizer-bar"></div>
                        <div class="equalizer-bar"></div>
                    </div>
                    <h2 class="h3 fw-bold mb-0">Continue Listening</h2>
                </div>
                <button id="btn-clear-history" class="btn btn-sm" style="color:var(--text-secondary); font-size:.78rem;">
                    <i class="bi bi-trash3 me-1"></i>Clear
                </button>
            </div>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4" id="recently-played-container">
                <!-- Populated via JS -->
            </div>
        </section>

        <!-- In-Feed Ad -->
        <?php $front_infeed_ad = get_theme_mod( 'liveradio_ad_front_infeed', '' ); ?>
        <?php if ( ! empty( $front_infeed_ad ) ) : ?>
            <div class="mb-5 text-center">
                <?php liveradio_safe_ad_output($front_infeed_ad); ?>
            </div>
        <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
            <div class="mb-5">
                <div class="glass p-3 rounded-4 text-center" style="border:1px dashed rgba(255,255,255,.15);">
                    <p class="text-muted small mb-0">Front Page In-Feed Ad Placeholder (Add code in Customizer)</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Explore Genres -->
        <section class="mb-5 pb-3">
            <h2 class="h3 fw-bold mb-4">Explore Genres</h2>
            <div class="row g-3">
                <?php
                $genres = get_transient( 'lr_genres_v6' );
                if ( false === $genres ) {
                    $genres = get_terms( array( 'taxonomy' => 'genre', 'number' => 8, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ) );
                    set_transient( 'lr_genres_v6', $genres, 12 * HOUR_IN_SECONDS );
                }
                $fallback_colors = array('#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f43f5e', '#84cc16', '#14b8a6', '#6366f1', '#d946ef', '#f97316', '#0ea5e9');
                $fallback_icons = array('bi-music-note-beamed', 'bi-mic-fill', 'bi-lightning-fill', 'bi-stars', 'bi-newspaper', 'bi-boombox-fill', 'bi-earbuds', 'bi-heart-fill', 'bi-tree-fill', 'bi-trophy-fill', 'bi-cloud-fill', 'bi-bank', 'bi-brightness-high-fill', 'bi-fire');
                
                $used_colors = array();
                $used_icons = array();

                foreach ( $genres as $genre ) :
                    $g_name = strtolower($genre->name);
                    $icon = '';
                    $bg = '';

                    // Smart mapping based on genre keywords
                    if (strpos($g_name, 'pop') !== false) { $icon = 'bi-stars'; $bg = '#ec4899'; }
                    elseif (strpos($g_name, 'rock') !== false) { $icon = 'bi-lightning-fill'; $bg = '#ef4444'; }
                    elseif (strpos($g_name, 'news') !== false) { $icon = 'bi-newspaper'; $bg = '#3b82f6'; }
                    elseif (strpos($g_name, 'talk') !== false || strpos($g_name, 'podcast') !== false) { $icon = 'bi-mic-fill'; $bg = '#f59e0b'; }
                    elseif (strpos($g_name, 'jazz') !== false) { $icon = 'bi-music-note-list'; $bg = '#8b5cf6'; }
                    elseif (strpos($g_name, 'classic') !== false) { $icon = 'bi-bank'; $bg = '#64748b'; }
                    elseif (strpos($g_name, 'hip hop') !== false || strpos($g_name, 'rap') !== false) { $icon = 'bi-boombox-fill'; $bg = '#f97316'; }
                    elseif (strpos($g_name, 'electronic') !== false || strpos($g_name, 'dance') !== false || strpos($g_name, 'edm') !== false) { $icon = 'bi-earbuds'; $bg = '#06b6d4'; }
                    elseif (strpos($g_name, 'country') !== false) { $icon = 'bi-tree-fill'; $bg = '#84cc16'; }
                    elseif (strpos($g_name, 'sports') !== false) { $icon = 'bi-trophy-fill'; $bg = '#14b8a6'; }
                    elseif (strpos($g_name, 'religious') !== false || strpos($g_name, 'christian') !== false || strpos($g_name, 'gospel') !== false || strpos($g_name, 'islamic') !== false) { $icon = 'bi-book-half'; $bg = '#0ea5e9'; }
                    elseif (strpos($g_name, 'r&b') !== false || strpos($g_name, 'soul') !== false) { $icon = 'bi-heart-fill'; $bg = '#e11d48'; }
                    elseif (strpos($g_name, 'reggae') !== false) { $icon = 'bi-brightness-high-fill'; $bg = '#22c55e'; }
                    elseif (strpos($g_name, 'latin') !== false) { $icon = 'bi-fire'; $bg = '#dc2626'; }
                    elseif (strpos($g_name, 'ambient') !== false || strpos($g_name, 'chill') !== false) { $icon = 'bi-cloud-fill'; $bg = '#6366f1'; }

                    // Enforce uniqueness for icons
                    if (empty($icon) || in_array($icon, $used_icons)) {
                        foreach ($fallback_icons as $f_icon) {
                            if (!in_array($f_icon, $used_icons)) {
                                $icon = $f_icon;
                                break;
                            }
                        }
                    }

                    // Enforce uniqueness for colors
                    if (empty($bg) || in_array($bg, $used_colors)) {
                        foreach ($fallback_colors as $f_bg) {
                            if (!in_array($f_bg, $used_colors)) {
                                $bg = $f_bg;
                                break;
                            }
                        }
                    }

                    $used_icons[] = $icon;
                    $used_colors[] = $bg;
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

        <!-- Recently Added Stations -->
        <section class="mb-5 pb-3">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h2 class="h3 fw-bold mb-0">Recently Added</h2>
            </div>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4" id="recent-station-list-container">
                <?php
                $recent_stations = new WP_Query( array(
                    'post_type'      => 'radio_station',
                    'posts_per_page' => 5,
                    'orderby'        => 'date',
                    'order'          => 'DESC'
                ) );
                if ( $recent_stations->have_posts() ) :
                    while ( $recent_stations->have_posts() ) : $recent_stations->the_post();
                        get_template_part( 'template-parts/content', 'station-card' );
                    endwhile;
                    wp_reset_postdata();
                endif;
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
                            <img id="front-playing-img" src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png"
                                alt="Station" style="width:40px;height:40px;border-radius:8px;object-fit:contain;background:#fff;padding:2px;">
                            <div>
                                <div id="front-playing-name" class="small fw-bold" style="color:var(--text-primary);">Ready to Play</div>
                                <div id="front-playing-status" style="font-size:.72rem; color:var(--text-secondary);">Pick a station</div>
                            </div>
                            <div id="front-playing-eq" class="equalizer ms-auto paused" style="height:18px;">
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                            </div>
                        </div>

                        <div class="position-absolute glass rounded-3 px-3 py-2 text-center shadow"
                            style="top:20px; right:-16px; min-width:110px; border:1px solid rgba(59,130,246,.25);">
                            <div class="fw-bold" style="font-size:1.3rem; color:var(--accent-blue);"><?php echo number_format_i18n($total_plays); ?>+</div>
                            <div style="font-size:.72rem; color:var(--text-secondary);">Times Listened</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-6 col-md-3">
                    <div class="custom-card p-3 text-center">
                        <div class="fw-bold fs-4 text-gradient"><?php echo number_format_i18n($station_count); ?>+</div>
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
                        <div class="fw-bold fs-4 text-gradient"><?php echo number_format_i18n($total_plays); ?>+</div>
                        <div class="text-muted small">Times Listened</div>
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

        <!-- SEO About Text -->
        <section class="mb-5 pb-3">
            <div class="custom-card p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h4 fw-bold mb-3">About LiveRadioStream Directory</h2>
                        <p class="text-muted" style="line-height:1.7;">
                            Welcome to LiveRadioStream, the ultimate global radio directory designed to connect you with thousands of live AM/FM broadcasts, web radios, and internet stations worldwide. Whether you are looking for the latest pop hits, local news updates, immersive talk shows, or relaxing ambient music, our meticulously curated catalog ensures you have access to high-quality audio streams 24/7. 
                        </p>
                        <p class="text-muted" style="line-height:1.7;">
                            Our platform is optimized for seamless discovery. Filter stations by country, explore diverse genres, and enjoy an uninterrupted HD streaming experience right from your browser or mobile device. Join our community of listeners and experience the world of radio without boundaries.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div class="glass p-4 rounded-4 text-center" style="background:rgba(6,182,212,.05); border:1px solid rgba(6,182,212,.1);">
                            <i class="bi bi-broadcast-pin text-gradient display-4 mb-2"></i>
                            <h3 class="h5 fw-bold text-primary-custom">100% Free</h3>
                            <p class="text-muted small mb-0">No signup required. Just click play and enjoy global broadcasts instantly.</p>
                        </div>
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
