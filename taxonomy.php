<?php
/**
 * The template for displaying taxonomy archives
 */

get_header(); 
$term = get_queried_object();
?>

    <!-- Breadcrumb -->
    <main class="container pb-5">
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="#" class="text-muted text-decoration-none"><?php echo esc_html( $term->taxonomy == 'genre' ? 'Genres' : 'Countries' ); ?></a></li>
                <li class="breadcrumb-item active" style="color:var(--accent-cyan);" aria-current="page"><?php echo esc_html( $term->name ); ?></li>
            </ol>
        </nav>

        <!-- ===== CATEGORY HERO ===== -->
        <div class="category-hero">
            <div class="category-hero-bg"></div>
            <div class="category-hero-decoration"></div>
            <div class="category-hero-content container">
                <div class="row align-items-center g-4">
                    <div class="col-auto">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:90px; height:90px; background: var(--gradient-accent); font-size:3rem; color:white; overflow:hidden;" id="hero-icon-container">
                            <?php if ($term->taxonomy == 'country'): ?>
                                <?php $iso_code = liveradio_get_country_code( $term->slug ); ?>
                                <img id="hero-country-flag" src="<?php echo esc_url('https://flagcdn.com/w320/' . $iso_code . '.png'); ?>" alt="<?php echo esc_attr( $term->name ); ?> Flag" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-mic-fill" id="hero-genre-icon"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill px-3" style="background:rgba(6,182,212,.2); color:var(--accent-cyan);"><?php echo esc_html( ucfirst($term->taxonomy) ); ?></span>
                        </div>
                        <h1 class="display-5 fw-bold mb-2"><span id="hero-term-name"><?php echo esc_html( $term->name ); ?></span> Stations</h1>
                        <?php 
                        $term_desc = term_description( $term->term_id, $term->taxonomy );
                        if ( ! empty( $term_desc ) ) : 
                            echo '<div class="term-description text-muted mb-3" id="hero-term-desc">' . wp_kses_post( $term_desc ) . '</div>';
                        else :
                        ?>
                            <p class="text-muted mb-3" id="hero-term-desc">Discover the hottest <span class="term-name-desc"><?php echo esc_html( strtolower($term->name) ); ?></span> radio stations streaming live from around the world, 24/7.</p>
                        <?php endif; ?>
                        <div class="stats-bar">
                            <div class="stat-item">
                                <div class="stat-number" id="hero-term-count"><?php echo number_format_i18n( $term->count ); ?></div>
                                <div class="text-muted small">Stations</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== ROW: CONTENT + SIDEBAR ===== -->
        <div class="row g-4">
            <!-- ===== LEFT CONTENT ===== -->
            <div class="col-lg-8">
                <!-- Top Leaderboard Ad (Taxonomy) -->
                <?php $top_ad = get_theme_mod( 'liveradio_ad_top', '' ); ?>
                <?php if ( ! empty( $top_ad ) ) : ?>
                    <div class="mb-4 text-center">
                        <?php echo $top_ad; ?>
                    </div>
                <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
                    <div class="glass p-3 rounded-4 text-center mb-4" style="border:1px dashed rgba(255,255,255,.15);">
                        <p class="text-muted small mb-0">Top Leaderboard Ad Placeholder (Add code in Customizer)</p>
                    </div>
                <?php endif; ?>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <form id="taxonomy-filter-form" class="row g-3 align-items-center">
                        <input type="hidden" name="action" value="liveradio_filter_stations">
                        <input type="hidden" name="taxonomy" value="<?php echo esc_attr( $term->taxonomy ); ?>">
                        <input type="hidden" name="term_id" value="<?php echo esc_attr( $term->term_id ); ?>">
                        
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text" style="background:var(--bg-primary); border-color:var(--glass-border); color:var(--text-secondary);">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" name="search" id="filter-search" placeholder="Search stations..." aria-label="Search stations">
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="country" id="filter-country" aria-label="Filter by country">
                                <?php 
                                $default_code = '';
                                if ($term->taxonomy == 'country') {
                                    $default_code = liveradio_get_country_code( $term->slug );
                                }
                                ?>
                                <option selected value="" data-name="<?php echo esc_attr( $term->name ); ?>" data-slug="<?php echo esc_attr( isset($term->slug) ? $term->slug : '' ); ?>" data-code="<?php echo esc_attr($default_code); ?>" data-count="<?php echo esc_attr($term->count); ?>">🌎 Country</option>
                                <?php
                                $countries = get_transient('lr_all_countries_v4');
                                if ( false === $countries ) {
                                    $countries = get_terms( array( 'taxonomy' => 'country', 'hide_empty' => false ) );
                                    set_transient('lr_all_countries_v4', $countries, 12 * HOUR_IN_SECONDS);
                                }
                                foreach ( $countries as $country ) {
                                    $code = liveradio_get_country_code( $country->slug );
                                    echo '<option value="' . esc_attr( $country->term_id ) . '" data-name="' . esc_attr($country->name) . '" data-slug="' . esc_attr($country->slug) . '" data-code="' . esc_attr($code) . '" data-count="' . esc_attr($country->count) . '">' . esc_html( $country->name ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select class="form-select" name="sort" id="filter-sort" aria-label="Sort by">
                                <option selected value="popular">Sort: Popular</option>
                                <option value="name">Sort: A–Z</option>
                                <option value="newest">Sort: Newest</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex justify-content-md-end">
                            <div class="view-toggle">
                                <button type="button" id="btn-grid-view" class="btn-view active" aria-label="Grid View" title="Grid View">
                                    <i class="bi bi-grid-3x3-gap-fill"></i>
                                </button>
                                <button type="button" id="btn-list-view" class="btn-view" aria-label="List View" title="List View">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Views Container -->
                <div id="station-list-container">
                    <!-- ===== GRID VIEW ===== -->
                    <div id="grid-view-section">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                            <?php if ( have_posts() ) : ?>
                                <?php while ( have_posts() ) : the_post(); ?>
                                    <?php get_template_part( 'template-parts/content', 'station-card' ); ?>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <div class="col-12">
                                    <div class="empty-state text-center py-5 glass rounded-4" style="border: 1px dashed rgba(148, 163, 184, 0.3);">
                                        <i class="bi bi-broadcast fs-1 text-muted mb-3 d-block"></i>
                                        <h3 class="h5 fw-bold">No Stations Found</h3>
                                        <p class="text-muted">We couldn't find any stations matching your criteria in this category.</p>
                                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-gradient rounded-pill px-4 mt-2">Explore Other Stations</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ===== LIST VIEW ===== -->
                    <div id="list-view-section" class="d-none">
                        <div class="d-flex flex-column gap-3">
                            <?php if ( have_posts() ) : ?>
                                <?php 
                                // Rewind posts so we can loop again for list view
                                rewind_posts();
                                while ( have_posts() ) : the_post(); 
                                    get_template_part( 'template-parts/content', 'station-list' );
                                endwhile; 
                                ?>
                            <?php else : ?>
                                <div class="empty-state text-center py-5 glass rounded-4" style="border: 1px dashed rgba(148, 163, 184, 0.3);">
                                    <i class="bi bi-broadcast fs-1 text-muted mb-3 d-block"></i>
                                    <h3 class="h5 fw-bold">No Stations Found</h3>
                                    <p class="text-muted">We couldn't find any stations matching your criteria in this category.</p>
                                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-gradient rounded-pill px-4 mt-2">Explore Other Stations</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <nav aria-label="Station pagination" class="mt-5 pagination-wrapper">
                    <?php
                    $links = paginate_links( array(
                        'type'      => 'array',
                        'prev_text' => '<i class="bi bi-chevron-left"></i>',
                        'next_text' => '<i class="bi bi-chevron-right"></i>',
                    ) );
                    if ( $links ) {
                        echo '<ul class="pagination justify-content-center gap-1">';
                        foreach ( $links as $link ) {
                            $class = strpos( $link, 'current' ) !== false ? 'active' : '';
                            $link = str_replace( 'page-numbers', 'page-link rounded-3', $link );
                            echo '<li class="page-item ' . esc_attr( $class ) . '">' . $link . '</li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </nav>

            </div><!-- /col-lg-8 -->

            <!-- ===== SIDEBAR ===== -->
            <div class="col-lg-4">
                <?php get_sidebar(); ?>
            </div>

        </div><!-- /row -->
    </main>

<?php get_footer(); ?>
