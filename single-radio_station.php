<?php
/**
 * The template for displaying all single radio stations
 */

get_header(); 
?>

<?php while ( have_posts() ) : the_post(); 
    $stream_url = get_post_meta( get_the_ID(), 'streaming_url', true );
    if ( ! $stream_url ) {
        $stream_url = get_post_meta( get_the_ID(), '_stream_url', true );
    }
    $website_url = get_post_meta( get_the_ID(), '_website_url', true );
    $bitrate = get_post_meta( get_the_ID(), '_bitrate', true );
    $frequency = get_post_meta( get_the_ID(), '_frequency', true );
    $owner = get_post_meta( get_the_ID(), '_owner', true );
    $play_count = (int) get_post_meta( get_the_ID(), '_play_count', true );
    $listeners = $play_count > 0 ? $play_count : 0;
    
    $genres = get_the_terms( get_the_ID(), 'genre' );
    $countries = get_the_terms( get_the_ID(), 'country' );
    $languages = get_the_terms( get_the_ID(), 'language' );
?>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="container" style="padding-bottom: 100px;">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
                <?php if ( $countries && ! is_wp_error( $countries ) ) : ?>
                <li class="breadcrumb-item"><a href="<?php echo esc_url( get_term_link( $countries[0] ) ); ?>"><?php echo esc_html( $countries[0]->name ); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
            </ol>
        </nav>

        <!-- ===== STATION HERO ===== -->
        <div class="station-hero px-4">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'full', array( 'class' => 'station-hero-bg' ) ); ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="" class="station-hero-bg">
            <?php endif; ?>
            <div class="station-hero-content">
                <div class="row align-items-center g-4">
                    <div class="col-auto">
                        <div class="position-relative">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'full', array( 'class' => 'station-large-logo station-img' ) ); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="Station Logo" class="station-large-logo station-img">
                            <?php endif; ?>
                            <span class="live-badge" style="position:absolute; bottom:10px; right:10px;">
                                <div class="live-dot"></div> Live
                            </span>
                        </div>
                    </div>
                    <div class="col">
                        <?php if ( $countries && ! is_wp_error( $countries ) ) : ?>
                        <?php if ( ! empty( $countries ) ) : 
                            $iso = liveradio_get_country_code( $countries[0]->slug );
                        ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img src="<?php echo esc_url( 'https://flagcdn.com/w40/' . $iso . '.png' ); ?>" alt="<?php echo esc_attr( $countries[0]->name ); ?> Flag" style="height:20px; border-radius:3px;">
                            <span class="text-muted small"><?php echo esc_html( $countries[0]->name ); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <h1 class="display-5 fw-bold mb-2 station-name"><?php the_title(); ?></h1>
                        
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php
                            $colors = array(
                                array('bg' => 'rgba(6,182,212,.2)', 'color' => 'var(--accent-cyan)'),
                                array('bg' => 'rgba(59,130,246,.2)', 'color' => '#3b82f6'),
                                array('bg' => 'rgba(16,185,129,.2)', 'color' => '#10b981')
                            );
                            if ( $genres && ! is_wp_error( $genres ) ) {
                                $i = 0;
                                foreach ( array_slice($genres, 0, 2) as $genre ) {
                                    $c = $colors[$i % count($colors)];
                                    echo '<span class="badge rounded-pill px-3 py-2" style="background:' . $c['bg'] . '; color:' . $c['color'] . ';">' . esc_html( $genre->name ) . '</span>';
                                    $i++;
                                }
                            }
                            if ( $languages && ! is_wp_error( $languages ) ) {
                                $c = $colors[2];
                                echo '<span class="badge rounded-pill px-3 py-2" style="background:' . $c['bg'] . '; color:' . $c['color'] . ';">' . esc_html( $languages[0]->name ) . '</span>';
                            }
                            ?>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="hero-equalizer">
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                    <div class="bar"></div>
                                </div>
                                <span class="text-muted small"><?php echo number_format_i18n( $listeners ); ?> Times Listened</span>
                            </div>
                            <div class="rating-stars">
                                <?php
                                $rating_data = liveradio_get_station_rating( get_the_ID() );
                                $avg = $rating_data['average'];
                                $count = $rating_data['count'];
                                
                                if ( $count > 0 ) :
                                    for ( $i = 1; $i <= 5; $i++ ) {
                                        if ( $i <= floor($avg) ) {
                                            echo '<i class="bi bi-star-fill"></i>';
                                        } elseif ( $i == ceil($avg) && $avg - floor($avg) >= 0.5 ) {
                                            echo '<i class="bi bi-star-half"></i>';
                                        } else {
                                            echo '<i class="bi bi-star"></i>';
                                        }
                                    }
                                    echo '<span class="text-muted small ms-1">' . esc_html( $avg ) . ' (' . esc_html( $count ) . ' reviews)</span>';
                                else :
                                    echo '<span class="text-muted small">No reviews yet</span>';
                                endif;
                                ?>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-gradient rounded-pill px-4 btn-play-trigger" data-station-id="<?php echo get_the_ID(); ?>" data-img="<?php echo has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : get_template_directory_uri() . '/assets/images/placeholder.png'; ?>">
                                <i class="bi bi-play-circle-fill me-2"></i>Play Station
                            </button>
                            <?php if ( $website_url ) : ?>
                            <a href="<?php echo esc_url( $website_url ); ?>" target="_blank" class="btn rounded-pill px-4 btn-glass-outline">
                                <i class="bi bi-globe me-2"></i>Website
                            </a>
                            <?php endif; ?>
                            <button class="btn-icon" id="btn-favorite" data-station-id="<?php echo get_the_ID(); ?>" title="Add to Favorites" aria-label="Add to Favorites">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button class="btn-icon text-danger" id="btn-report" data-station-id="<?php echo get_the_ID(); ?>" title="Report Broken Stream" aria-label="Report Broken Stream">
                                <i class="bi bi-exclamation-triangle"></i>
                            </button>
                            <button class="btn-icon" id="btn-share" data-title="<?php echo esc_attr( get_the_title() ); ?>" data-url="<?php echo esc_url( get_the_permalink() ); ?>" title="Share" aria-label="Share station">
                                <i class="bi bi-share"></i>
                            </button>
                            <button class="btn-icon" id="btn-copy-link" data-url="<?php echo esc_url( get_the_permalink() ); ?>" title="Copy link" aria-label="Copy link">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Leaderboard Ad -->
        <?php $top_ad = get_theme_mod( 'liveradio_ad_top', '' ); ?>
        <?php if ( ! empty( $top_ad ) ) : ?>
            <div class="container mb-4 text-center">
                <?php liveradio_safe_ad_output($top_ad); ?>
            </div>
        <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
            <div class="container mb-4">
                <div class="glass p-3 rounded-4 text-center" style="border:1px dashed rgba(255,255,255,.15);">
                    <p class="text-muted small mb-0">Top Leaderboard Ad Placeholder (Add code in Customizer)</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== TWO-COLUMN LAYOUT ===== -->
        <div class="row g-4 mt-1">

            <!-- ===== LEFT CONTENT ===== -->
            <div class="col-lg-8">

                <!-- Live Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span><i class="bi bi-broadcast me-1" style="color:var(--accent-cyan);"></i>Live Broadcast</span>
                        <span>HD Quality</span>
                    </div>
                    <div class="progress-live rounded"></div>
                </div>

                <!-- About Station -->
                <div class="custom-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-info-circle me-2" style="color:var(--accent-cyan);"></i>About <?php the_title(); ?></h2>
                    <div class="text-muted" style="line-height: 1.8;">
                        <?php the_content(); ?>
                    </div>
                </div>

                <!-- In-Article Ad -->
                <?php $inarticle_ad = get_theme_mod( 'liveradio_ad_inarticle', '' ); ?>
                <?php if ( ! empty( $inarticle_ad ) ) : ?>
                    <div class="mb-4 text-center">
                        <?php liveradio_safe_ad_output($inarticle_ad); ?>
                    </div>
                <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
                    <div class="glass p-3 rounded-4 text-center mb-4" style="border:1px dashed rgba(255,255,255,.15);">
                        <p class="text-muted small mb-0">In-Article Ad Placeholder (Add code in Customizer)</p>
                    </div>
                <?php endif; ?>

                <!-- Station Details -->
                <div class="custom-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-sliders me-2" style="color:var(--accent-cyan);"></i>Station Details</h2>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-globe2 me-2"></i>Country</span>
                        <span class="info-value"><?php echo $countries && ! is_wp_error( $countries ) ? esc_html( $countries[0]->name ) : 'N/A'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-music-note me-2"></i>Genre</span>
                        <span class="info-value">
                            <?php 
                            if ( $genres && ! is_wp_error( $genres ) ) {
                                echo esc_html( implode( ', ', wp_list_pluck( $genres, 'name' ) ) );
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-translate me-2"></i>Language</span>
                        <span class="info-value"><?php echo $languages && ! is_wp_error( $languages ) ? esc_html( $languages[0]->name ) : 'N/A'; ?></span>
                    </div>
                    <?php if ( $frequency ) : ?>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-broadcast me-2"></i>Frequency</span>
                        <span class="info-value"><?php echo esc_html( $frequency ); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ( $bitrate ) : ?>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-headphones me-2"></i>Bitrate</span>
                        <span class="info-value"><?php echo esc_html( $bitrate ); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-reception-4 me-2"></i>Status</span>
                        <span class="info-value"><span class="badge bg-success">Online</span></span>
                    </div>
                    <?php if ( $owner ) : ?>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-building me-2"></i>Owner</span>
                        <span class="info-value"><?php echo esc_html( $owner ); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tags -->
                <?php if ( $genres && ! is_wp_error( $genres ) ) : ?>
                <div class="custom-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-tags me-2" style="color:var(--accent-cyan);"></i>Tags</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        foreach ( $genres as $genre ) {
                            echo '<a href="' . esc_url( get_term_link( $genre ) ) . '" class="tag-pill">#' . esc_html( strtolower( $genre->name ) ) . '</a>';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="custom-card p-4">
                    <?php
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;
                    ?>
                </div>

            </div><!-- /col-lg-8 -->

            <!-- ===== RIGHT SIDEBAR ===== -->
            <div class="col-lg-4">

                <!-- Trending Now -->
                <div class="custom-card p-4 mb-4">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-fire me-2" style="color:#f59e0b;"></i>Trending Now</h2>
                    <ol class="list-unstyled mb-0">
                        <?php
                        $trending_args = array(
                            'post_type'      => 'radio_station',
                            'posts_per_page' => 3,
                            'orderby'        => 'meta_value_num',
                            'meta_key'       => '_play_count',
                            'order'          => 'DESC'
                        );
                        $trending_query = new WP_Query( $trending_args );
                        $index = 1;
                        if ( $trending_query->have_posts() ) :
                            while ( $trending_query->have_posts() ) : $trending_query->the_post();
                                $trend_count = (int) get_post_meta( get_the_ID(), '_play_count', true );
                                
                                if ($trend_count >= 1000) {
                                    $trend_list_k = round($trend_count / 1000, 1) . 'k';
                                } else {
                                    $trend_list_k = $trend_count > 0 ? $trend_count : 'New';
                                }
                                
                                $trend_countries = get_the_terms( get_the_ID(), 'country' );
                                $trend_flag = '';
                                if ( $trend_countries && ! is_wp_error( $trend_countries ) ) {
                                    $trend_flag = strtoupper(liveradio_get_country_code($trend_countries[0]->slug)) . ' ';
                                }
                                
                                $icon = $index == 1 || $index == 3 ? '<i class="bi bi-arrow-up-right-circle-fill" style="color:#10b981;"></i>' : '<i class="bi bi-dash-circle-fill" style="color:var(--accent-cyan);"></i>';
                        ?>
                        <li class="info-row">
                            <span class="text-muted fw-bold me-2" style="width:20px;"><?php echo $index; ?></span>
                            <a href="<?php the_permalink(); ?>" class="me-2 text-decoration-none">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'full', array( 'class' => 'related-img', 'style' => 'width: 45px; height: 45px; padding: 4px;' ) ); ?>
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" class="related-img" style="width: 45px; height: 45px; padding: 4px;" alt="<?php the_title_attribute(); ?>">
                                <?php endif; ?>
                            </a>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small"><a href="<?php the_permalink(); ?>" class="text-decoration-none text-primary-custom"><?php the_title(); ?></a></div>
                                <div class="text-muted" style="font-size:.75rem;"><?php echo esc_html( $trend_flag . $trend_list_k . ' Times Listened' ); ?></div>
                            </div>
                            <?php echo $icon; ?>
                        </li>
                        <?php
                            $index++;
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </ol>
                </div>

                <!-- Related Stations -->
                <div class="mb-4">
                    <h2 class="h5 fw-bold mb-3">Related Stations</h2>
                    <?php
                    $related_args = array(
                        'post_type'      => 'radio_station',
                        'posts_per_page' => 15,
                        'post__not_in'   => array( get_the_ID() ),
                        'orderby'        => 'rand'
                    );
                    if ( $countries && ! is_wp_error( $countries ) ) {
                        $related_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'country',
                                'field'    => 'term_id',
                                'terms'    => wp_list_pluck( $countries, 'term_id' ),
                            ),
                        );
                    }
                    $related_query = new WP_Query( $related_args );
                    
                    if ( $related_query->have_posts() ) :
                        while ( $related_query->have_posts() ) : $related_query->the_post();
                            $rel_genres = get_the_terms( get_the_ID(), 'genre' );
                            $rel_genre_name = $rel_genres && ! is_wp_error( $rel_genres ) ? $rel_genres[0]->name : 'Music';
                            
                            $rel_countries = get_the_terms( get_the_ID(), 'country' );
                            $rel_country_code = 'US';
                            if ( $rel_countries && ! is_wp_error( $rel_countries ) ) {
                                $rel_country_code = strtoupper(liveradio_get_country_code($rel_countries[0]->slug));
                            }
                    ?>
                    <a href="<?php the_permalink(); ?>" class="related-card text-decoration-none">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'full', array( 'class' => 'related-img' ) ); ?>
                        <?php else : ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" class="related-img" alt="Station">
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold small text-primary-custom"><?php the_title(); ?></div>
                            <div class="text-muted" style="font-size:.78rem;"><?php echo esc_html( $rel_country_code . ' • ' . $rel_genre_name ); ?></div>
                        </div>
                        <i class="bi bi-play-circle-fill related-play"></i>
                    </a>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>



                <!-- Sidebar Ad -->
                <?php $sidebar_ad = get_theme_mod( 'liveradio_ad_sidebar', '' ); ?>
                <?php if ( ! empty( $sidebar_ad ) ) : ?>
                    <div class="mb-4 text-center">
                        <?php liveradio_safe_ad_output($sidebar_ad); ?>
                    </div>
                <?php elseif ( current_user_can( 'manage_options' ) ) : ?>
                    <div class="glass p-4 rounded-4 text-center mb-4" style="border:1px dashed rgba(255,255,255,.15);">
                        <i class="bi bi-megaphone fs-2 text-muted mb-2"></i>
                        <p class="text-muted small mb-0">Advertisement Placeholder</p>
                        <p class="text-muted" style="font-size:.7rem;">Add code in Customizer</p>
                    </div>
                <?php endif; ?>

            </div><!-- /col-lg-4 -->

        </div><!-- /row -->

    </main>

<?php endwhile; ?>

<?php get_footer(); ?>
