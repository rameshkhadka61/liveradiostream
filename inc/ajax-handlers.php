<?php
/**
 * AJAX Handlers
 */

// Filter Stations
function liveradio_filter_stations() {
    $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    $country = isset( $_POST['country'] ) ? intval( $_POST['country'] ) : 0;
    $sort = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'popular';
    
    // Taxonomy archive base
    $taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
    $term_id = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;

    $args = array(
        'post_type'      => 'radio_station',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    $tax_query = array();

    if ( ! empty( $taxonomy ) && ! empty( $term_id ) ) {
        // If we are on a country taxonomy page but selected a different country from dropdown, override it
        if ( $taxonomy === 'country' && ! empty( $country ) ) {
            // Skip adding the base taxonomy, we will add the dropdown country below
        } else {
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $term_id,
            );
        }
    }

    if ( ! empty( $country ) ) {
        $tax_query[] = array(
            'taxonomy' => 'country',
            'field'    => 'term_id',
            'terms'    => $country,
        );
    }

    if ( count( $tax_query ) > 1 ) {
        $tax_query['relation'] = 'AND';
    }

    if ( ! empty( $tax_query ) ) {
        $args['tax_query'] = $tax_query;
    }

    if ( $sort === 'name' ) {
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
    } elseif ( $sort === 'newest' ) {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    } else {
        // popular
        $args['meta_key'] = '_listeners';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    }

    $query_hash = md5( serialize( $args ) );
    $transient_key = 'lr_filter_' . $query_hash;
    $response_data = get_transient( $transient_key );

    if ( false === $response_data ) {
        $query = new WP_Query( $args );

        // Generate Grid View HTML
        ob_start();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                get_template_part( 'template-parts/content', 'station-card' );
            }
        } else {
            echo '<div class="col-12">
                <div class="empty-state text-center py-5 glass rounded-4" style="border: 1px dashed rgba(148, 163, 184, 0.3);">
                    <i class="bi bi-broadcast fs-1 text-muted mb-3 d-block"></i>
                    <h3 class="h5 fw-bold">No Stations Found</h3>
                    <p class="text-muted">We couldn\'t find any stations matching your criteria in this category.</p>
                    <a href="' . esc_url( home_url( '/' ) ) . '" class="btn btn-gradient rounded-pill px-4 mt-2">Explore Other Stations</a>
                </div>
            </div>';
        }
        $grid_html = ob_get_clean();

        // Rewind and Generate List View HTML
        $query->rewind_posts();
        ob_start();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                get_template_part( 'template-parts/content', 'station-list' );
            }
        } else {
            echo '<div class="empty-state text-center py-5 glass rounded-4" style="border: 1px dashed rgba(148, 163, 184, 0.3);">
                <i class="bi bi-broadcast fs-1 text-muted mb-3 d-block"></i>
                <h3 class="h5 fw-bold">No Stations Found</h3>
                <p class="text-muted">We couldn\'t find any stations matching your criteria in this category.</p>
                <a href="' . esc_url( home_url( '/' ) ) . '" class="btn btn-gradient rounded-pill px-4 mt-2">Explore Other Stations</a>
            </div>';
        }
        $list_html = ob_get_clean();

        wp_reset_postdata();

        $response_data = array(
            'grid' => $grid_html,
            'list' => $list_html
        );
        set_transient( $transient_key, $response_data, 5 * MINUTE_IN_SECONDS );
    }

    wp_send_json_success( $response_data );
}
add_action( 'wp_ajax_liveradio_filter_stations', 'liveradio_filter_stations' );
add_action( 'wp_ajax_nopriv_liveradio_filter_stations', 'liveradio_filter_stations' );

// Submit Station
function liveradio_submit_station() {
    check_ajax_referer( 'liveradio_nonce', 'nonce' );

    $name = isset( $_POST['stationName'] ) ? sanitize_text_field( $_POST['stationName'] ) : '';
    $stream_url = isset( $_POST['streamUrl'] ) ? esc_url_raw( $_POST['streamUrl'] ) : '';
    $website_url = isset( $_POST['websiteUrl'] ) ? esc_url_raw( $_POST['websiteUrl'] ) : '';
    $logo_url = isset( $_POST['logoUrl'] ) ? esc_url_raw( $_POST['logoUrl'] ) : '';
    
    $country_input = isset( $_POST['countrySelect'] ) ? sanitize_text_field( $_POST['countrySelect'] ) : '';
    $country = 0;
    if ( is_numeric($country_input) ) {
        $country = intval($country_input);
    } else {
        $term = get_term_by('name', $country_input, 'country');
        if ($term) {
            $country = $term->term_id;
        }
    }

    $genre_input = isset( $_POST['genreSelect'] ) ? $_POST['genreSelect'] : array();
    $genres = is_array($genre_input) ? array_map('intval', $genre_input) : array(intval($genre_input));
    $genres = array_filter($genres);

    $description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';

    if ( empty( $name ) || empty( $stream_url ) || empty( $country ) || empty( $genres ) ) {
        wp_send_json_error( 'Please fill in all required fields.' );
    }

    $post_data = array(
        'post_title'   => $name,
        'post_content' => $description,
        'post_status'  => 'draft',
        'post_type'    => 'radio_station',
    );

    $post_id = wp_insert_post( $post_data );

    if ( ! is_wp_error( $post_id ) ) {
        update_post_meta( $post_id, '_stream_url', $stream_url );
        update_post_meta( $post_id, '_website_url', $website_url );
        
        // Setup terms
        wp_set_object_terms( $post_id, array( $country ), 'country' );
        wp_set_object_terms( $post_id, $genres, 'genre' );

        // Basic handling of logo by attaching it as a custom field if we can't sideload easily
        if ( ! empty( $logo_url ) ) {
            update_post_meta( $post_id, '_logo_external_url', $logo_url );
        }

        wp_send_json_success( 'Your station has been submitted successfully and is pending review.' );
    } else {
        wp_send_json_error( 'There was an error submitting your station. Please try again later.' );
    }
}
add_action( 'wp_ajax_liveradio_submit_station', 'liveradio_submit_station' );
add_action( 'wp_ajax_nopriv_liveradio_submit_station', 'liveradio_submit_station' );

add_action( 'wp_ajax_nopriv_liveradio_get_stream_url', 'liveradio_ajax_get_stream_url' );
add_action( 'wp_ajax_liveradio_get_stream_url', 'liveradio_ajax_get_stream_url' );

function liveradio_ajax_get_stream_url() {
    check_ajax_referer( 'liveradio_nonce', 'nonce' );

    $station_id = isset( $_POST['station_id'] ) ? intval( $_POST['station_id'] ) : 0;

    if ( ! $station_id ) {
        wp_send_json_error( 'Invalid station ID' );
    }

    $stream_url = get_post_meta( $station_id, 'streaming_url', true );
    if ( ! $stream_url ) {
        $stream_url = get_post_meta( $station_id, '_stream_url', true );
    }

    if ( $stream_url ) {
        wp_send_json_success( array( 'stream_url' => esc_url_raw( $stream_url ) ) );
    } else {
        wp_send_json_error( 'No stream URL found' );
    }
}

// Contact Us Submission
function liveradio_contact_submit() {
    check_ajax_referer( 'liveradio_contact_nonce_action', 'nonce' );

    $name = isset( $_POST['contactName'] ) ? sanitize_text_field( $_POST['contactName'] ) : '';
    $email = isset( $_POST['contactEmail'] ) ? sanitize_email( $_POST['contactEmail'] ) : '';
    $subject = isset( $_POST['contactSubject'] ) ? sanitize_text_field( $_POST['contactSubject'] ) : '';
    $message = isset( $_POST['contactMessage'] ) ? sanitize_textarea_field( $_POST['contactMessage'] ) : '';

    if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) ) {
        wp_send_json_error( 'Please fill in all required fields.' );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( 'Please provide a valid email address.' );
    }

    $admin_email = get_option( 'admin_email' );
    $headers = array('Content-Type: text/html; charset=UTF-8', 'From: ' . $name . ' <' . $email . '>', 'Reply-To: ' . $name . ' <' . $email . '>');

    $mail_subject = 'Contact Form: ' . $subject;
    $mail_body = '<p><strong>Name:</strong> ' . $name . '</p><p><strong>Email:</strong> ' . $email . '</p><p><strong>Message:</strong><br>' . nl2br($message) . '</p>';

    $sent = wp_mail( $admin_email, $mail_subject, $mail_body, $headers );

    if ( $sent ) {
        wp_send_json_success( 'Your message has been sent successfully. We will get back to you soon!' );
    } else {
        wp_send_json_error( 'There was an error sending your message. Please try again later.' );
    }
}
add_action( 'wp_ajax_liveradio_contact_submit', 'liveradio_contact_submit' );
add_action( 'wp_ajax_nopriv_liveradio_contact_submit', 'liveradio_contact_submit' );

/**
 * Handle Report Station
 */
add_action( 'wp_ajax_liveradio_report_station', 'liveradio_ajax_report_station' );
add_action( 'wp_ajax_nopriv_liveradio_report_station', 'liveradio_ajax_report_station' );
function liveradio_ajax_report_station() {
    check_ajax_referer( 'liveradio_ajax_nonce', 'nonce' );

    $station_id = isset( $_POST['station_id'] ) ? intval( $_POST['station_id'] ) : 0;
    
    if ( $station_id > 0 ) {
        // Here you could send an email to the admin or save a meta field
        // Example: update_post_meta( $station_id, '_reported_broken', current_time('mysql') );
        wp_send_json_success( array( 'message' => 'Report received' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Invalid station' ) );
    }
}

/**
 * Get Favorites via AJAX
 */
add_action( 'wp_ajax_liveradio_get_favorites', 'liveradio_ajax_get_favorites' );
add_action( 'wp_ajax_nopriv_liveradio_get_favorites', 'liveradio_ajax_get_favorites' );
function liveradio_ajax_get_favorites() {
    check_ajax_referer( 'liveradio_nonce', 'nonce' );

    $station_ids = isset( $_POST['station_ids'] ) ? $_POST['station_ids'] : array();
    
    // Validate inputs
    $station_ids = is_array( $station_ids ) ? array_map( 'intval', $station_ids ) : array();
    $station_ids = array_filter( $station_ids );

    if ( empty( $station_ids ) ) {
        wp_send_json_success( array( 'html' => '' ) );
    }

    $args = array(
        'post_type'      => 'radio_station',
        'post__in'       => $station_ids,
        'posts_per_page' => -1,
        'orderby'        => 'post__in', // Maintain the order of the array
        'post_status'    => 'publish',
    );

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'station-card' );
        }
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success( array( 'html' => $html ) );
}

/**
 * Get a Random Station via AJAX
 */
add_action( 'wp_ajax_liveradio_random_station', 'liveradio_ajax_random_station' );
add_action( 'wp_ajax_nopriv_liveradio_random_station', 'liveradio_ajax_random_station' );
function liveradio_ajax_random_station() {
    check_ajax_referer( 'liveradio_nonce', 'nonce' );

    $args = array(
        'post_type'      => 'radio_station',
        'posts_per_page' => 1,
        'orderby'        => 'rand',
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_stream_url',
                'value'   => '',
                'compare' => '!=',
            ),
        ),
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        $query->the_post();
        $station_id   = get_the_ID();
        $station_name = get_the_title();
        $station_url  = get_permalink();
        $img          = get_the_post_thumbnail_url( $station_id, 'medium' );
        wp_reset_postdata();
        wp_send_json_success( array(
            'station_id'   => $station_id,
            'station_name' => $station_name,
            'station_url'  => $station_url,
            'img'          => $img ?: '',
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'No stations found' ) );
    }
}

/**
 * Increment play count (listener count) for a station via AJAX
 */
add_action( 'wp_ajax_liveradio_increment_play', 'liveradio_ajax_increment_play' );
add_action( 'wp_ajax_nopriv_liveradio_increment_play', 'liveradio_ajax_increment_play' );
function liveradio_ajax_increment_play() {
    check_ajax_referer( 'liveradio_nonce', 'nonce' );

    $station_id = isset( $_POST['station_id'] ) ? intval( $_POST['station_id'] ) : 0;
    if ( ! $station_id || get_post_type( $station_id ) !== 'radio_station' ) {
        wp_send_json_error();
    }

    $current = (int) get_post_meta( $station_id, '_play_count', true );
    update_post_meta( $station_id, '_play_count', $current + 1 );
    wp_send_json_success( array( 'play_count' => $current + 1 ) );
}

/**
 * Get Recently Played stations data via AJAX
 */
add_action( 'wp_ajax_liveradio_get_recently_played', 'liveradio_ajax_get_recently_played' );
add_action( 'wp_ajax_nopriv_liveradio_get_recently_played', 'liveradio_ajax_get_recently_played' );
function liveradio_ajax_get_recently_played() {
    check_ajax_referer( 'liveradio_nonce', 'nonce' );

    $station_ids = isset( $_POST['station_ids'] ) ? $_POST['station_ids'] : array();
    $station_ids = is_array( $station_ids ) ? array_map( 'intval', $station_ids ) : array();
    $station_ids = array_filter( $station_ids );

    if ( empty( $station_ids ) ) {
        wp_send_json_success( array( 'html' => '' ) );
    }

    $args = array(
        'post_type'      => 'radio_station',
        'post__in'       => $station_ids,
        'posts_per_page' => count( $station_ids ),
        'orderby'        => 'post__in',
        'post_status'    => 'publish',
    );

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/content', 'station-card' );
        }
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success( array( 'html' => $html ) );
}
