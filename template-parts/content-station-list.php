<?php
/**
 * Template part for displaying a station list item
 */

$stream_url = get_post_meta( get_the_ID(), '_stream_url', true );
$play_count = (int) get_post_meta( get_the_ID(), '_play_count', true );
$listeners_k = $play_count >= 1000 ? round($play_count / 1000, 1) . 'k' : ($play_count > 0 ? $play_count : 'New');

$genres = get_the_terms( get_the_ID(), 'genre' );
$genre_name = $genres && ! is_wp_error( $genres ) ? $genres[0]->name : 'Uncategorized';

$country_terms = get_the_terms( get_the_ID(), 'country' );
$flag = '';
$country_name = '';
if ( $country_terms && ! is_wp_error( $country_terms ) ) {
    $country_name = $country_terms[0]->name;
    $flag = strtoupper(liveradio_get_country_code($country_terms[0]->slug)) . ' ';
}
?>
<a href="<?php the_permalink(); ?>" class="station-list-card text-decoration-none">
    <?php if ( has_post_thumbnail() ) : ?>
        <?php the_post_thumbnail( 'thumbnail', array( 'class' => 'station-list-img', 'loading' => 'lazy' ) ); ?>
    <?php else : ?>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.png" alt="<?php the_title_attribute(); ?>" class="station-list-img" loading="lazy">
    <?php endif; ?>
    
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="fw-bold station-name text-primary-custom"><?php the_title(); ?></span>
            <span class="live-badge position-static" style="font-size:.65rem;"><div class="live-dot"></div> Live</span>
        </div>
        <div class="text-muted small"><?php echo esc_html( $flag . $country_name . ' &nbsp;&middot;&nbsp; ' . $genre_name . ' &nbsp;&middot;&nbsp; ' . $listeners_k . ' listening' ); ?></div>
        <div class="text-muted mt-1" style="font-size:.78rem;"><?php echo wp_trim_words( get_the_excerpt(), 15, '...' ); ?></div>
    </div>
    
    <div class="d-none d-md-flex align-items-center gap-2 text-muted small">
        <i class="bi bi-headphones"></i> 128 kbps
    </div>
    
    <button class="station-list-play btn-play-trigger" data-station-id="<?php echo get_the_ID(); ?>" aria-label="Play <?php the_title_attribute(); ?>">
        <i class="bi bi-play-circle-fill"></i>
    </button>
</a>
