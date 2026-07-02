<?php
/**
 * Template part for displaying a station card
 */

$stream_url = get_post_meta( get_the_ID(), '_stream_url', true );
$play_count = (int) get_post_meta( get_the_ID(), '_play_count', true );
if ( $play_count >= 1000 ) {
    $listeners_k = round($play_count / 1000, 1) . 'k';
} else {
    $listeners_k = $play_count > 0 ? $play_count : 'New';
}

$genres = get_the_terms( get_the_ID(), 'genre' );
$genre_name = $genres && ! is_wp_error( $genres ) ? $genres[0]->name : 'Uncategorized';

$country_terms = get_the_terms( get_the_ID(), 'country' );
$flag = '';
if ( $country_terms && ! is_wp_error( $country_terms ) ) {
    $flag = strtoupper(liveradio_get_country_code($country_terms[0]->slug)) . ' ';
}

// Random ribbon for demo purposes
$is_trending = rand(1, 10) > 8;
?>
<div class="col station-col">
    <div class="custom-card station-card h-100 position-relative">
        <?php if ($is_trending) : ?>
            <span class="ribbon">Trending</span>
        <?php endif; ?>
        
        <div class="station-img-wrapper">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'full', array( 'class' => 'station-img', 'loading' => 'lazy' ) ); ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="<?php the_title_attribute(); ?>" class="station-img" loading="lazy">
            <?php endif; ?>
            <div class="live-badge"><div class="live-dot"></div> Live</div>
            <div class="play-overlay">
                <button class="btn-play-circle btn-play-trigger" data-station-id="<?php echo get_the_ID(); ?>" aria-label="Play <?php the_title_attribute(); ?>">
                    <i class="bi bi-play-fill"></i>
                </button>
            </div>
        </div>
        
        <div class="card-body p-3 text-center">
            <h3 class="h6 mb-1 text-truncate station-name">
                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-primary-custom"><?php the_title(); ?></a>
            </h3>
            <p class="text-muted small mb-0"><?php echo esc_html( $flag . $genre_name ); ?> <span class="text-muted">• <i class="bi bi-play-circle"></i> <?php echo esc_html($listeners_k); ?></span></p>
        </div>
    </div>
</div>
