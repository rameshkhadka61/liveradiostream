<?php
/**
 * LiveRadioStream Theme Customizer
 */

function liveradio_customize_register( $wp_customize ) {

    // Social Media Links Section
    $wp_customize->add_section( 'liveradio_social_section', array(
        'title'       => __( 'Footer Social Links', 'liveradio' ),
        'priority'    => 120,
        'description' => __( 'Add your social media profile URLs here. Leave empty to hide the icon in the footer.', 'liveradio' ),
    ) );

    // Facebook URL
    $wp_customize->add_setting( 'facebook_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'facebook_url', array(
        'label'   => __( 'Facebook Profile/Page URL', 'liveradio' ),
        'section' => 'liveradio_social_section',
        'type'    => 'url',
    ) );

    // Twitter / X URL
    $wp_customize->add_setting( 'twitter_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'twitter_url', array(
        'label'   => __( 'Twitter / X Profile URL', 'liveradio' ),
        'section' => 'liveradio_social_section',
        'type'    => 'url',
    ) );

    // Instagram URL
    $wp_customize->add_setting( 'instagram_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'instagram_url', array(
        'label'   => __( 'Instagram Profile URL', 'liveradio' ),
        'section' => 'liveradio_social_section',
        'type'    => 'url',
    ) );

    // Ads & Integrations Section
    $wp_customize->add_section( 'liveradio_ads_section', array(
        'title'       => __( 'AdSense & Integrations', 'liveradio' ),
        'priority'    => 130,
        'description' => __( 'Manage your Google AdSense verification, Ad units, and Cookie Consent banner here.', 'liveradio' ),
    ) );

    // Header AdSense Script (Verification / Auto Ads)
    $wp_customize->add_setting( 'liveradio_adsense_header', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post', // allows script tags
    ) );
    $wp_customize->add_control( 'liveradio_adsense_header', array(
        'label'       => __( 'Header Script (AdSense Verification/Auto Ads)', 'liveradio' ),
        'description' => __( 'Paste your Google AdSense <script> tag here. It will be placed in the <head> of your site.', 'liveradio' ),
        'section'     => 'liveradio_ads_section',
        'type'        => 'textarea',
    ) );

    // Sidebar Ad Unit
    $wp_customize->add_setting( 'liveradio_ad_sidebar', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'liveradio_ad_sidebar', array(
        'label'       => __( 'Sidebar Ad Unit (300x250)', 'liveradio' ),
        'description' => __( 'Paste your Google AdSense <ins> code here. It will appear in the right sidebar of single station pages.', 'liveradio' ),
        'section'     => 'liveradio_ads_section',
        'type'        => 'textarea',
    ) );

    // Enable Cookie Consent Banner
    $wp_customize->add_setting( 'liveradio_enable_cookie_banner', array(
        'default'           => false,
        'sanitize_callback' => 'liveradio_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'liveradio_enable_cookie_banner', array(
        'label'       => __( 'Enable Cookie Consent Banner', 'liveradio' ),
        'description' => __( 'Required for AdSense in many regions (GDPR/CCPA compliance).', 'liveradio' ),
        'section'     => 'liveradio_ads_section',
        'type'        => 'checkbox',
    ) );
    
    // Cookie Banner Text
    $wp_customize->add_setting( 'liveradio_cookie_text', array(
        'default'           => 'We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Accept", you consent to our use of cookies.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'liveradio_cookie_text', array(
        'label'       => __( 'Cookie Banner Text', 'liveradio' ),
        'section'     => 'liveradio_ads_section',
        'type'        => 'textarea',
    ) );

}
add_action( 'customize_register', 'liveradio_customize_register' );

// Sanitization for checkbox
function liveradio_sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Output Customizer Settings into Theme
 */
function liveradio_output_adsense_header() {
    $script = get_theme_mod( 'liveradio_adsense_header', '' );
    if ( ! empty( $script ) ) {
        echo "<!-- AdSense Header Integration -->\n";
        echo $script . "\n";
    }
}
add_action( 'wp_head', 'liveradio_output_adsense_header', 99 );

function liveradio_output_cookie_banner() {
    $enabled = get_theme_mod( 'liveradio_enable_cookie_banner', false );
    if ( $enabled ) {
        $text = get_theme_mod( 'liveradio_cookie_text', 'We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Accept", you consent to our use of cookies.' );
        ?>
        <!-- Cookie Consent Banner -->
        <div id="cookie-consent-banner" class="cookie-banner" style="display:none;">
            <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <p class="mb-0 text-white small" style="flex:1;">
                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                    <?php echo esc_html( $text ); ?>
                    <a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>" class="text-info text-decoration-underline ms-1">Learn more</a>
                </p>
                <div class="d-flex gap-2">
                    <button id="btn-cookie-decline" class="btn btn-sm btn-outline-light rounded-pill px-3">Decline</button>
                    <button id="btn-cookie-accept" class="btn btn-sm btn-info rounded-pill px-4 fw-bold">Accept</button>
                </div>
            </div>
        </div>
        <style>
            .cookie-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background-color: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(10px);
                border-top: 1px solid rgba(255,255,255,0.1);
                padding: 15px 0;
                z-index: 9999;
                box-shadow: 0 -5px 20px rgba(0,0,0,0.5);
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const banner = document.getElementById('cookie-consent-banner');
                const btnAccept = document.getElementById('btn-cookie-accept');
                const btnDecline = document.getElementById('btn-cookie-decline');
                
                if (!localStorage.getItem('liveradio_cookie_consent')) {
                    banner.style.display = 'block';
                }
                
                if (btnAccept) {
                    btnAccept.addEventListener('click', function() {
                        localStorage.setItem('liveradio_cookie_consent', 'accepted');
                        banner.style.display = 'none';
                    });
                }
                
                if (btnDecline) {
                    btnDecline.addEventListener('click', function() {
                        localStorage.setItem('liveradio_cookie_consent', 'declined');
                        banner.style.display = 'none';
                    });
                }
            });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'liveradio_output_cookie_banner', 100 );
