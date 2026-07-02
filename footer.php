<?php
/**
 * The footer for our theme
 */
?>
    </div> <!-- /#swup -->

    <!-- Footer -->
    <footer class="site-footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand text-gradient fs-4 fw-bold mb-3 d-inline-block" href="<?php echo esc_url( home_url( '/' ) ); ?>"><i
                            class="bi bi-broadcast"></i> <?php bloginfo( 'name' ); ?></a>
                    <p class="text-muted mb-4"><?php bloginfo( 'description' ); ?></p>
                    <div class="d-flex gap-3">
                        <?php
                        $fb = get_theme_mod('facebook_url', '');
                        $tw = get_theme_mod('twitter_url', '');
                        $ig = get_theme_mod('instagram_url', '');
                        ?>
                        <?php if ( $fb ) : ?><a href="<?php echo esc_url($fb); ?>" class="btn-icon" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a><?php endif; ?>
                        <?php if ( $tw ) : ?><a href="<?php echo esc_url($tw); ?>" class="btn-icon" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
                        <?php if ( $ig ) : ?><a href="<?php echo esc_url($ig); ?>" class="btn-icon" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="bi bi-instagram"></i></a><?php endif; ?>
                    </div>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <?php
                    wp_nav_menu( array(
                        'theme_location'  => 'footer_quick_links',
                        'container'       => false,
                        'menu_class'      => 'list-unstyled',
                        'fallback_cb'     => '__return_false',
                    ) );
                    ?>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="fw-bold mb-3">Top Genres</h5>
                    <?php
                    wp_nav_menu( array(
                        'theme_location'  => 'footer_genres',
                        'container'       => false,
                        'menu_class'      => 'list-unstyled',
                        'fallback_cb'     => '__return_false',
                    ) );
                    ?>
                </div>
                <div class="col-lg-3">
                    <h5 class="fw-bold mb-3">Newsletter</h5>
                    <p class="text-muted small">Subscribe to get latest updates and top stations delivered to your inbox.</p>
                    <form id="newsletter-form" class="input-group">
                        <input type="email" id="newsletter-email" name="email" class="form-control bg-transparent" placeholder="Email address" required>
                        <button class="btn btn-outline-info" type="submit" id="newsletter-submit" aria-label="Subscribe"><i class="bi bi-send-fill"></i></button>
                    </form>
                    <div id="newsletter-msg" class="mt-2 small d-none"></div>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="text-center text-muted small">
                &copy; <?php echo date('Y'); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                    class="text-muted text-decoration-none hover-cyan"><?php bloginfo( 'name' ); ?></a>. All rights reserved.
            </div>
        </div>
    </footer>

<?php
$player_title = 'Select a Station';
$player_thumb = get_template_directory_uri() . '/assets/images/placeholder.png';
$is_single_station = is_singular( 'radio_station' );
if ( $is_single_station ) {
    $player_title = get_the_title();
    $player_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : $player_thumb;
}
?>
    <!-- Sticky Audio Player -->
    <div id="sticky-player" class="sticky-player <?php echo $is_single_station ? 'show' : ''; ?>">
        <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img id="player-thumbnail" src="<?php echo esc_url( $player_thumb ); ?>"
                    alt="Thumbnail" class="player-thumbnail">
                <div>
                    <h5 id="player-station-name" class="mb-0 fs-6 fw-bold"><?php echo esc_html( $player_title ); ?></h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="live-dot" style="width:5px;height:5px;"></span>
                        <small class="text-muted" id="player-station-details">Live Radio</small>
                    </div>
                </div>
            </div>

            <div class="player-controls d-flex align-items-center gap-3">
                <button class="btn p-0" aria-label="Previous station"><i class="bi bi-skip-backward-fill fs-5"></i></button>
                <button id="global-play-btn" class="btn btn-play shadow-glow" aria-label="Play or Pause"><i id="global-play-icon"
                        class="bi bi-play-fill"></i></button>
                <button class="btn p-0" aria-label="Next station"><i class="bi bi-skip-forward-fill fs-5"></i></button>
            </div>

            <div class="d-flex align-items-center gap-4">
                <div id="player-equalizer" class="equalizer paused d-none d-md-flex">
                    <div class="equalizer-bar"></div>
                    <div class="equalizer-bar"></div>
                    <div class="equalizer-bar"></div>
                    <div class="equalizer-bar"></div>
                </div>
                <div class="d-none d-md-flex align-items-center gap-2">
                    <i class="bi bi-volume-up text-muted"></i>
                    <input type="range" class="volume-slider" min="0" max="100" value="80" id="player-volume">
                </div>
            </div>
        </div>
        <audio id="global-audio-player" preload="none"></audio>
    </div>

    <!-- Back to top -->
    <button id="btn-back-to-top" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

    <?php wp_footer(); ?>
</body>
</html>
