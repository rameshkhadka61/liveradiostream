<?php
/**
 * LiveRadioStream functions and definitions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Theme Setup
 */
function liveradio_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title.
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support( 'post-thumbnails' );

    // Register Navigation Menus
    register_nav_menus( array(
        'primary' => esc_html__( 'Primary Menu', 'liveradio' ),
        'footer_quick_links' => esc_html__( 'Footer Quick Links', 'liveradio' ),
        'footer_genres' => esc_html__( 'Footer Genres', 'liveradio' ),
    ) );

    // HTML5 support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
}
add_action( 'after_setup_theme', 'liveradio_setup' );

/**
 * Enqueue scripts and styles.
 */
function liveradio_scripts() {
    // Bootstrap CSS
    wp_enqueue_style( 'bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2' );
    // Bootstrap Icons
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css', array(), '1.11.1' );
    // Theme CSS
    wp_enqueue_style( 'liveradio-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

    // Swup JS
    wp_enqueue_script( 'swup-js', 'https://unpkg.com/swup@4', array(), '4.0.0', true );

    // Bootstrap JS
    wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), '5.3.2', true );
    
    // Choices.js for rich selects
    wp_enqueue_style( 'choices-css', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', array(), '10.2.0' );
    wp_enqueue_script( 'choices-js', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', array(), '10.2.0', true );

    // Theme JS
    wp_enqueue_script( 'liveradio-app', get_template_directory_uri() . '/assets/js/app.v2.js', array('swup-js', 'choices-js'), wp_get_theme()->get( 'Version' ), true );

    // Pass ajax_url to script
    wp_localize_script( 'liveradio-app', 'liveradio_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'liveradio_nonce' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'liveradio_scripts' );

/**
 * Defer scripts for performance
 */
function liveradio_defer_scripts( $tag, $handle, $src ) {
    $defer_scripts = array( 'swup-js', 'bootstrap-js', 'liveradio-app' );
    if ( in_array( $handle, $defer_scripts ) ) {
        return '<script src="' . esc_url( $src ) . '" defer="defer"></script>' . "\n";
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'liveradio_defer_scripts', 10, 3 );

/**
 * Add Bootstrap 5 classes to nav menu items
 */
function liveradio_nav_menu_css_class( $classes, $item, $args ) {
    if ( 'primary' === $args->theme_location ) {
        $classes[] = 'nav-item';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'liveradio_nav_menu_css_class', 10, 3 );

function liveradio_nav_menu_link_attributes( $atts, $item, $args ) {
    if ( 'primary' === $args->theme_location ) {
        if ( isset( $atts['class'] ) ) {
            $atts['class'] .= ' nav-link';
        } else {
            $atts['class'] = 'nav-link';
        }
        if ( $item->current || $item->current_item_ancestor ) {
            $atts['class'] .= ' active';
        }
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'liveradio_nav_menu_link_attributes', 10, 3 );

// Include Custom Post Types and Taxonomies
require get_template_directory() . '/inc/cpt-radio-station.php';
require get_template_directory() . '/inc/meta-boxes.php';
require get_template_directory() . '/inc/ajax-handlers.php';

/**
 * Save comment rating
 */
function save_comment_rating( $comment_id ) {
    if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '' ) ) {
        $rating = wp_filter_nohtml_kses( $_POST['rating'] );
        add_comment_meta( $comment_id, 'rating', $rating );
    }
}
add_action( 'comment_post', 'save_comment_rating' );

/**
 * Custom Comment Callback for LiveRadioStream
 */
function liveradiostream_comment_callback( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    $rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
    if ( ! $rating ) {
        $rating = 5;
    }
    $initials = substr( get_comment_author(), 0, 2 );
    ?>
    <div <?php comment_class( empty( $args['has_children'] ) ? 'mb-4' : 'mb-4 parent' ); ?> id="comment-<?php comment_ID() ?>">
        <div class="d-flex gap-3 mb-3">
            <div class="review-avatar"><?php echo esc_html( strtoupper( $initials ) ); ?></div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?php echo get_comment_author(); ?></strong>
                        <div class="rating-stars fs-6">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <?php if ( $i <= $rating ) : ?>
                                    <i class="bi bi-star-fill"></i>
                                <?php else : ?>
                                    <i class="bi bi-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <small class="text-muted"><?php printf( esc_html__( '%s ago', 'liveradiostream' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?></small>
                </div>
                <p class="text-muted small mt-2 mb-0"><?php comment_text(); ?></p>
            </div>
        </div>
    <?php
}

/**
 * Remove Category and Tags from Nav Menus
 */
add_action( 'wp_loaded', 'liveradio_remove_taxonomies_from_menus' );
function liveradio_remove_taxonomies_from_menus() {
    global $wp_taxonomies;
    if ( isset( $wp_taxonomies['category'] ) ) {
        $wp_taxonomies['category']->show_in_nav_menus = false;
    }
    if ( isset( $wp_taxonomies['post_tag'] ) ) {
        $wp_taxonomies['post_tag']->show_in_nav_menus = false;
    }
}

/**
 * Helper to get ISO country code from slug
 */
function liveradio_get_country_code( $slug ) {
    $slug = strtolower( $slug );
    if ( strlen( $slug ) === 2 ) return $slug;
    
    $map = array(
        'afghanistan' => 'af', 'aland-islands' => 'ax', 'albania' => 'al', 'algeria' => 'dz', 'american-samoa' => 'as', 'andorra' => 'ad', 'angola' => 'ao',
        'anguilla' => 'ai', 'antarctica' => 'aq', 'antigua-and-barbuda' => 'ag', 'argentina' => 'ar', 'armenia' => 'am', 'aruba' => 'aw', 'australia' => 'au',
        'austria' => 'at', 'azerbaijan' => 'az', 'bahamas' => 'bs', 'bahrain' => 'bh', 'bangladesh' => 'bd', 'barbados' => 'bb', 'belarus' => 'by',
        'belgium' => 'be', 'belize' => 'bz', 'benin' => 'bj', 'bermuda' => 'bm', 'bhutan' => 'bt', 'bolivia' => 'bo', 'bonaire-sint-eustatius-and-saba' => 'bq',
        'bosnia-and-herzegovina' => 'ba', 'botswana' => 'bw', 'bouvet-island' => 'bv', 'brazil' => 'br', 'british-indian-ocean-territory' => 'io',
        'brunei-darussalam' => 'bn', 'bulgaria' => 'bg', 'burkina-faso' => 'bf', 'burundi' => 'bi', 'cambodia' => 'kh', 'cameroon' => 'cm', 'canada' => 'ca',
        'cape-verde' => 'cv', 'cayman-islands' => 'ky', 'central-african-republic' => 'cf', 'chad' => 'td', 'chile' => 'cl', 'china' => 'cn', 'christmas-island' => 'cx',
        'cocos-keeling-islands' => 'cc', 'colombia' => 'co', 'comoros' => 'km', 'congo' => 'cg', 'congo-democratic-republic-of-the' => 'cd', 'cook-islands' => 'ck',
        'costa-rica' => 'cr', 'cote-divoire' => 'ci', 'croatia' => 'hr', 'cuba' => 'cu', 'curacao' => 'cw', 'cyprus' => 'cy', 'czech-republic' => 'cz',
        'denmark' => 'dk', 'djibouti' => 'dj', 'dominica' => 'dm', 'dominican-republic' => 'do', 'ecuador' => 'ec', 'egypt' => 'eg', 'el-salvador' => 'sv',
        'equatorial-guinea' => 'gq', 'eritrea' => 'er', 'estonia' => 'ee', 'ethiopia' => 'et', 'falkland-islands' => 'fk', 'faroe-islands' => 'fo', 'fiji' => 'fj',
        'finland' => 'fi', 'france' => 'fr', 'french-guiana' => 'gf', 'french-polynesia' => 'pf', 'french-southern-territories' => 'tf', 'gabon' => 'ga',
        'gambia' => 'gm', 'georgia' => 'ge', 'germany' => 'de', 'ghana' => 'gh', 'gibraltar' => 'gi', 'greece' => 'gr', 'greenland' => 'gl', 'grenada' => 'gd',
        'guadeloupe' => 'gp', 'guam' => 'gu', 'guatemala' => 'gt', 'guernsey' => 'gg', 'guinea' => 'gn', 'guinea-bissau' => 'gw', 'guyana' => 'gy', 'haiti' => 'ht',
        'heard-island-and-mcdonald-islands' => 'hm', 'holy-see-vatican-city-state' => 'va', 'honduras' => 'hn', 'hong-kong' => 'hk', 'hungary' => 'hu',
        'iceland' => 'is', 'india' => 'in', 'indonesia' => 'id', 'iran' => 'ir', 'iraq' => 'iq', 'ireland' => 'ie', 'isle-of-man' => 'im', 'israel' => 'il',
        'italy' => 'it', 'jamaica' => 'jm', 'japan' => 'jp', 'jersey' => 'je', 'jordan' => 'jo', 'kazakhstan' => 'kz', 'kenya' => 'ke', 'kiribati' => 'ki',
        'north-korea' => 'kp', 'south-korea' => 'kr', 'kuwait' => 'kw', 'kyrgyzstan' => 'kg', 'lao-peoples-democratic-republic' => 'la', 'latvia' => 'lv',
        'lebanon' => 'lb', 'lesotho' => 'ls', 'liberia' => 'lr', 'libya' => 'ly', 'liechtenstein' => 'li', 'lithuania' => 'lt', 'luxembourg' => 'lu',
        'macao' => 'mo', 'macedonia' => 'mk', 'madagascar' => 'mg', 'malawi' => 'mw', 'malaysia' => 'my', 'maldives' => 'mv', 'mali' => 'ml', 'malta' => 'mt',
        'marshall-islands' => 'mh', 'martinique' => 'mq', 'mauritania' => 'mr', 'mauritius' => 'mu', 'mayotte' => 'yt', 'mexico' => 'mx', 'micronesia' => 'fm',
        'moldova' => 'md', 'monaco' => 'mc', 'mongolia' => 'mn', 'montenegro' => 'me', 'montserrat' => 'ms', 'morocco' => 'ma', 'mozambique' => 'mz', 'myanmar' => 'mm',
        'namibia' => 'na', 'nauru' => 'nr', 'nepal' => 'np', 'netherlands' => 'nl', 'new-caledonia' => 'nc', 'new-zealand' => 'nz', 'nicaragua' => 'ni',
        'niger' => 'ne', 'nigeria' => 'ng', 'niue' => 'nu', 'norfolk-island' => 'nf', 'northern-mariana-islands' => 'mp', 'norway' => 'no', 'oman' => 'om',
        'pakistan' => 'pk', 'palau' => 'pw', 'palestinian-territory' => 'ps', 'panama' => 'pa', 'papua-new-guinea' => 'pg', 'paraguay' => 'py', 'peru' => 'pe',
        'philippines' => 'ph', 'pitcairn' => 'pn', 'poland' => 'pl', 'portugal' => 'pt', 'puerto-rico' => 'pr', 'qatar' => 'qa', 'reunion' => 're', 'romania' => 'ro',
        'russia' => 'ru', 'rwanda' => 'rw', 'saint-barthelemy' => 'bl', 'saint-helena' => 'sh', 'saint-kitts-and-nevis' => 'kn', 'saint-lucia' => 'lc',
        'saint-martin' => 'mf', 'saint-pierre-and-miquelon' => 'pm', 'saint-vincent-and-the-grenadines' => 'vc', 'samoa' => 'ws', 'san-marino' => 'sm',
        'sao-tome-and-principe' => 'st', 'saudi-arabia' => 'sa', 'senegal' => 'sn', 'serbia' => 'rs', 'seychelles' => 'sc', 'sierra-leone' => 'sl',
        'singapore' => 'sg', 'sint-maarten' => 'sx', 'slovakia' => 'sk', 'slovenia' => 'si', 'solomon-islands' => 'sb', 'somalia' => 'so', 'south-africa' => 'za',
        'south-georgia' => 'gs', 'south-sudan' => 'ss', 'spain' => 'es', 'sri-lanka' => 'lk', 'sudan' => 'sd', 'suriname' => 'sr', 'svalbard' => 'sj',
        'swaziland' => 'sz', 'sweden' => 'se', 'switzerland' => 'ch', 'syria' => 'sy', 'taiwan' => 'tw', 'tajikistan' => 'tj', 'tanzania' => 'tz', 'thailand' => 'th',
        'timor-leste' => 'tl', 'togo' => 'tg', 'tokelau' => 'tk', 'tonga' => 'to', 'trinidad-and-tobago' => 'tt', 'tunisia' => 'tn', 'turkey' => 'tr',
        'turkmenistan' => 'tm', 'turks-and-caicos-islands' => 'tc', 'tuvalu' => 'tv', 'uganda' => 'ug', 'ukraine' => 'ua', 'united-arab-emirates' => 'ae',
        'united-kingdom' => 'gb', 'united-states' => 'us', 'united-states-minor-outlying-islands' => 'um', 'uruguay' => 'uy', 'uzbekistan' => 'uz',
        'vanuatu' => 'vu', 'venezuela' => 've', 'vietnam' => 'vn', 'virgin-islands-british' => 'vg', 'virgin-islands-us' => 'vi', 'wallis-and-futuna' => 'wf',
        'western-sahara' => 'eh', 'yemen' => 'ye', 'zambia' => 'zm', 'zimbabwe' => 'zw',
        // Existing aliases
        'usa' => 'us', 'uk' => 'gb', 'england' => 'gb', 'uae' => 'ae'
    );
    
    if ( isset( $map[ $slug ] ) ) {
        return $map[ $slug ];
    }
    
    return substr( $slug, 0, 2 );
}


/**
 * Helper to get average rating and total reviews for a station
 */
function liveradio_get_station_rating( $post_id ) {
    $comments_query = new WP_Comment_Query( array(
        'post_id' => $post_id,
        'status'  => 'approve',
    ) );
    $all_comments = $comments_query->comments;
    $total_ratings = 0;
    $sum_ratings = 0;
    foreach ( $all_comments as $c ) {
        $r = intval( get_comment_meta( $c->comment_ID, 'rating', true ) );
        if ( $r > 0 ) {
            $sum_ratings += $r;
            $total_ratings++;
        }
    }
    $avg_rating = $total_ratings > 0 ? round( $sum_ratings / $total_ratings, 1 ) : 0;
    
    return array(
        'average' => $avg_rating,
        'count'   => $total_ratings
    );
}

/**
 * Get user country from IP address
 */
function liveradio_get_user_country_data() {
    $ip = $_SERVER['REMOTE_ADDR'];
    // If localhost, use empty string to let the API use the requester's IP
    if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
        $ip = ''; 
    }
    
    $transient_key = 'lr_user_country_' . md5($ip);
    $country_data = get_transient($transient_key);
    
    if (false === $country_data) {
        $response = wp_remote_get('http://ip-api.com/json/' . $ip . '?fields=country,countryCode');
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        if ($data && isset($data->country)) {
            $country_data = array(
                'name' => $data->country,
                'code' => $data->countryCode
            );
            set_transient($transient_key, $country_data, 12 * HOUR_IN_SECONDS);
        } else {
            return false;
        }
    }
    return $country_data;
}

/**
 * Allow non-logged in users to leave a review with just their name (no email required)
 */
add_filter( 'pre_option_require_name_email', '__return_false' );

/**
 * Initialize the Plugin Update Checker for the theme
 */
require get_template_directory() . '/inc/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/rameshkhadka61/liveradiostream/',
	__FILE__,
	'liveradiostream-theme'
);

// Optional: If you're using a private repository, specify the access token like this:
// $myUpdateChecker->setAuthentication('your-token-here');

$myUpdateChecker->setBranch('main');

/**
 * Clear caching transients when a station is saved or deleted
 */
function liveradio_clear_transients_on_save( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'radio_station' ) return;

    delete_transient( 'lr_station_count_v4' );
    delete_transient( 'lr_country_count_v4' );
    delete_transient( 'lr_all_countries_v4' );
    delete_transient( 'lr_trending_countries_v4' );
    delete_transient( 'lr_top_countries_v4' );
    delete_transient( 'lr_genres_v4' );
    delete_transient( 'lr_all_countries_v4_name_ASC' );
    delete_transient( 'lr_all_countries_v4_count_DESC' );
}
add_action( 'save_post_radio_station', 'liveradio_clear_transients_on_save' );
add_action( 'deleted_post', 'liveradio_clear_transients_on_save' );

/**
 * Change admin URL to /admin-area
 */
// 1. Redirect direct access to wp-login.php to the homepage
function liveradio_redirect_default_login() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $parsed_url = parse_url($request_uri);
    $path = trim(isset($parsed_url['path']) ? $parsed_url['path'] : '', '/');
    
    // If the actual requested URL path is exactly wp-login.php
    if ( $path === 'wp-login.php' ) {
        wp_safe_redirect(home_url());
        exit;
    }
}
add_action('init', 'liveradio_redirect_default_login');

// 2. Serve the login page when the custom slug is accessed
function liveradio_handle_custom_login_url() {
    $custom_login_slug = 'admin-area';
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $parsed_url = parse_url($request_uri);
    
    $path = trim(isset($parsed_url['path']) ? $parsed_url['path'] : '', '/');
    $parts = explode('/', $path);
    $last_part = end($parts);
    
    if ( $last_part === $custom_login_slug ) {
        if ( ! defined( 'LIVERADIO_CUSTOM_LOGIN' ) ) {
            define( 'LIVERADIO_CUSTOM_LOGIN', true );
        }
        
        // Ensure WordPress thinks we are on the login page
        $schema = is_ssl() ? 'https://' : 'http://';
        $_SERVER['SCRIPT_NAME'] = '/wp-login.php';
        
        // Initialize variables expected by wp-login.php to prevent undefined variable warnings
        global $user_login, $error;
        $user_login = '';
        $error = '';
        
        require_once ABSPATH . 'wp-login.php';
        exit;
    }
}
add_action('init', 'liveradio_handle_custom_login_url');

// 3. Update the login URL throughout the site via site_url filter
add_filter( 'site_url', function( $url, $path, $scheme, $blog_id ) {
    if ( $path === 'wp-login.php' || strpos( $path, 'wp-login.php?' ) === 0 ) {
        return str_replace( 'wp-login.php', 'admin-area', $url );
    }
    return $url;
}, 10, 4 );

// 4. Update network_site_url as well
add_filter( 'network_site_url', function( $url, $path, $scheme ) {
    if ( $path === 'wp-login.php' || strpos( $path, 'wp-login.php?' ) === 0 ) {
        return str_replace( 'wp-login.php', 'admin-area', $url );
    }
    return $url;
}, 10, 3 );

/**
 * Custom Login Page Theme
 */
// Custom Logo Text
add_filter( 'login_headertext', 'liveradio_custom_login_logo_text' );
function liveradio_custom_login_logo_text() {
    return '<i class="bi bi-broadcast"></i> ' . get_bloginfo( 'name' );
}

// Custom Logo URL
add_filter( 'login_headerurl', 'liveradio_custom_login_logo_url' );
function liveradio_custom_login_logo_url() {
    return home_url( '/' );
}

// Custom Login Styles
add_action( 'login_enqueue_scripts', 'liveradio_custom_login_scripts' );
function liveradio_custom_login_scripts() {
    wp_enqueue_style( 'google-font-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap', false );
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css', array(), '1.11.1' );
    ?>
    <style type="text/css">
        body.login {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        body.login #login {
            padding: 2rem 0;
            margin: 0;
            width: 100%;
            max-width: 400px;
        }
        body.login #login h1 a {
            background-image: none;
            width: auto;
            height: auto;
            font-size: 2rem;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-indent: 0;
            background: linear-gradient(135deg, #06b6d4, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }
        body.login #login h1 a i::before {
            background: linear-gradient(135deg, #06b6d4, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        body.login form {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-top: 0;
        }
        body.login label {
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        body.login input[type="text"], 
        body.login input[type="password"], 
        body.login input[type="email"] {
            background-color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            box-shadow: none;
            transition: all 0.3s ease;
        }
        body.login input[type="text"]:focus, 
        body.login input[type="password"]:focus, 
        body.login input[type="email"]:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 0.25rem rgba(6, 182, 212, 0.25);
            outline: none;
            color: #f8fafc;
        }
        body.login .submit .button-primary {
            background: linear-gradient(135deg, #06b6d4, #3b82f6) !important;
            border: none !important;
            border-radius: 50px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            color: white !important;
            text-shadow: none !important;
            transition: all 0.3s ease !important;
            font-weight: 600 !important;
            padding: 0 2rem !important;
            height: 42px !important;
            line-height: 42px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 1rem;
        }
        body.login .submit .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.3) !important;
        }
        body.login #nav, body.login #backtoblog {
            text-align: center;
            padding: 0;
            margin-top: 1.5rem;
        }
        body.login #nav a, body.login #backtoblog a {
            color: #94a3b8 !important;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }
        body.login #nav a:hover, body.login #backtoblog a:hover {
            color: #06b6d4 !important;
        }
        body.login .message, body.login #login_error {
            background: rgba(30, 41, 59, 0.7);
            border-left: 4px solid #06b6d4;
            color: #f8fafc;
            border-radius: 0.5rem;
            box-shadow: none;
            margin-bottom: 1.5rem;
        }
        body.login #login_error {
            border-left-color: #ef4444;
        }
        body.login input[type="checkbox"] {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.25rem;
            margin-right: 8px;
        }
        body.login input[type="checkbox"]:checked {
            background-color: #06b6d4;
            border-color: #06b6d4;
        }
        body.login input[type="checkbox"]:checked::before {
            content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath d='M14.83 4.89l1.34.94-5.81 8.38H9.02L5.78 9.67l1.34-1.25 2.57 2.4z' fill='%23ffffff'/%3E%3C/svg%3E");
            margin: -0.25rem 0 0 -0.25rem;
            height: 1.5rem;
            width: 1.5rem;
            display: inline-block;
        }
        body.login .privacy-policy-page-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        body.login .privacy-policy-page-link a {
            color: #94a3b8;
            text-decoration: none;
        }
        body.login .privacy-policy-page-link a:hover {
            color: #06b6d4;
        }
        
        /* Language switcher */
        body.login .language-switcher {
            margin-top: 1.5rem;
            background: transparent;
            color: #94a3b8;
        }
        body.login .language-switcher select {
            background-color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: #f8fafc;
        }
        
        /* Hide remember me if it looks ugly */
        .forgetmenot {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }
    </style>
    <?php
}
