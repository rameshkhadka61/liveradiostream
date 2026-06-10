<?php
/**
 * The sidebar containing the main widget area
 */
?>
<aside class="sticky-top" style="top: 100px; z-index: 1;">

    <!-- Popular Genres Widget -->
    <div class="custom-card p-4 mb-4">
        <h2 class="h5 fw-bold mb-3"><i class="bi bi-bar-chart-fill me-2" style="color:var(--accent-cyan);"></i>Popular Genres</h2>
        <div class="d-flex flex-wrap gap-2">
            <?php
            $genres = get_terms( array( 'taxonomy' => 'genre', 'number' => 7, 'orderby' => 'count', 'order' => 'DESC' ) );
            $colors = array( '#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899' );
            $i = 0;
            foreach ( $genres as $genre ) :
                $color = $colors[ $i % count( $colors ) ];
                $rgb = ''; // Simplified logic to get rgba for badge
                if($color == '#ef4444') $rgb = '239,68,68';
                elseif($color == '#3b82f6') $rgb = '59,130,246';
                elseif($color == '#10b981') $rgb = '16,185,129';
                elseif($color == '#f59e0b') $rgb = '245,158,11';
                elseif($color == '#8b5cf6') $rgb = '139,92,246';
                elseif($color == '#06b6d4') $rgb = '6,182,212';
                elseif($color == '#ec4899') $rgb = '236,72,153';
                else $rgb = '100,100,100';
            ?>
            <a href="<?php echo esc_url( get_term_link( $genre ) ); ?>" class="tag-pill text-decoration-none d-flex align-items-center gap-1" style="border-color: rgba(<?php echo $rgb; ?>, 0.3); color: <?php echo $color; ?>; background: rgba(<?php echo $rgb; ?>, 0.05);">
                <?php echo esc_html( $genre->name ); ?>
                <span class="badge rounded-pill ms-1" style="background:rgba(<?php echo $rgb; ?>,.15); color:<?php echo $color; ?>; font-size: 0.7rem;"><?php echo number_format_i18n( $genre->count ); ?></span>
            </a>
            <?php 
                $i++;
            endforeach; 
            ?>
        </div>
    </div>

    <!-- Top Countries Widget -->
    <div class="custom-card p-4 mb-4">
        <h2 class="h5 fw-bold mb-3"><i class="bi bi-globe2 me-2" style="color:var(--accent-cyan);"></i>Top Countries</h2>
        <div class="d-flex flex-column gap-2">
            <?php
            $countries = get_terms( array( 'taxonomy' => 'country', 'number' => 5, 'orderby' => 'count', 'order' => 'DESC' ) );
            $c_count = count($countries);
            $j = 0;
            foreach ( $countries as $country ) :
                $j++;
                $border = ($j < $c_count) ? 'border-bottom:1px solid var(--glass-border);' : '';
            ?>
            <a href="<?php echo esc_url( get_term_link( $country ) ); ?>" class="d-flex align-items-center justify-content-between text-decoration-none py-2" style="<?php echo $border; ?> color:var(--text-primary);">
                <span class="small"><?php echo esc_html( $country->name ); ?></span>
                <span class="text-muted small"><?php echo number_format_i18n( $country->count ); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Trending Stations Widget -->
    <div class="custom-card p-4 mb-4">
        <h2 class="h5 fw-bold mb-3"><i class="bi bi-fire me-2" style="color:#f59e0b;"></i>Trending Stations</h2>
        <?php
        $trending = new WP_Query( array( 'post_type' => 'radio_station', 'posts_per_page' => 3 ) );
        $k = 0;
        while ( $trending->have_posts() ) : $trending->the_post();
            $k++;
            $listeners = get_post_meta( get_the_ID(), '_listeners', true );
            if ( ! $listeners ) $listeners = rand( 1000, 15000 );
            $listeners_k = round($listeners / 1000, 1) . 'k';
            $mb = ($k < 3) ? 'mb-3' : '';
            
            // Randomly assign trending badge
            $badges = array(
                array('text' => '↑ Hot', 'bg' => 'rgba(16,185,129,.15)', 'color' => '#10b981'),
                array('text' => 'Stable', 'bg' => 'rgba(6,182,212,.15)', 'color' => 'var(--accent-cyan)'),
                array('text' => '↑ Rising', 'bg' => 'rgba(16,185,129,.15)', 'color' => '#10b981')
            );
            $b = $badges[$k % 3];
            
            $country_terms = get_the_terms( get_the_ID(), 'country' );
            $flag = '';
            if ( $country_terms && ! is_wp_error( $country_terms ) ) {
                $flag = strtoupper(liveradio_get_country_code($country_terms[0]->slug)) . ' ';
            }
        ?>
        <a href="<?php the_permalink(); ?>" class="d-flex align-items-center gap-3 text-decoration-none <?php echo $mb; ?>">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'thumbnail', array( 'style' => 'width:44px;height:44px;border-radius:8px;object-fit:contain;background:#fff;padding:2px;' ) ); ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:contain;background:#fff;padding:2px;">
            <?php endif; ?>
            <div>
                <div class="small fw-semibold text-primary-custom"><?php the_title(); ?></div>
                <div class="text-muted" style="font-size:.75rem;"><?php echo $flag . $listeners_k; ?> listening</div>
            </div>
            <span class="ms-auto badge" style="background:<?php echo $b['bg']; ?>; color:<?php echo $b['color']; ?>; font-size:.7rem;"><?php echo $b['text']; ?></span>
        </a>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <!-- Ad Placeholder -->
    <div class="glass p-4 rounded-4 text-center" style="border:1px dashed rgba(255,255,255,.15);">
        <i class="bi bi-megaphone fs-2 text-muted mb-2 d-block"></i>
        <p class="text-muted small mb-0">Advertisement Placeholder</p>
        <p class="text-muted" style="font-size:.7rem;">300 &times; 250</p>
    </div>

</aside>
