<?php
/**
 * Register Meta Boxes for Radio Station
 */

function liveradio_add_meta_boxes() {
    add_meta_box(
        'liveradio_station_details',
        __( 'Station Details', 'liveradio' ),
        'liveradio_station_details_callback',
        'radio_station',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'liveradio_add_meta_boxes' );

function liveradio_station_details_callback( $post ) {
    wp_nonce_field( 'liveradio_save_meta_box_data', 'liveradio_meta_box_nonce' );

    $stream_url = get_post_meta( $post->ID, 'streaming_url', true );
    if ( ! $stream_url ) {
        $stream_url = get_post_meta( $post->ID, '_stream_url', true );
    }
    $website_url = get_post_meta( $post->ID, '_website_url', true );
    $bitrate = get_post_meta( $post->ID, '_bitrate', true );
    $frequency = get_post_meta( $post->ID, '_frequency', true );
    $owner = get_post_meta( $post->ID, '_owner', true );
    $listeners = get_post_meta( $post->ID, '_listeners', true );

    echo '<table class="form-table">';
    echo '<tr><th><label for="streaming_url">Streaming URL</label></th><td><input type="url" id="streaming_url" name="streaming_url" value="' . esc_attr( $stream_url ) . '" size="50" /></td></tr>';
    echo '<tr><th><label for="website_url">Website URL</label></th><td><input type="url" id="website_url" name="website_url" value="' . esc_attr( $website_url ) . '" size="50" /></td></tr>';
    echo '<tr><th><label for="bitrate">Bitrate</label></th><td><input type="text" id="bitrate" name="bitrate" value="' . esc_attr( $bitrate ) . '" size="25" /></td></tr>';
    echo '<tr><th><label for="frequency">Frequency</label></th><td><input type="text" id="frequency" name="frequency" value="' . esc_attr( $frequency ) . '" size="25" /></td></tr>';
    echo '<tr><th><label for="owner">Owner</label></th><td><input type="text" id="owner" name="owner" value="' . esc_attr( $owner ) . '" size="50" /></td></tr>';
    echo '<tr><th><label for="listeners">Listeners count (mock)</label></th><td><input type="number" id="listeners" name="listeners" value="' . esc_attr( $listeners ) . '" size="25" /></td></tr>';
    echo '</table>';
}

function liveradio_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['liveradio_meta_box_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['liveradio_meta_box_nonce'], 'liveradio_save_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['streaming_url'] ) ) update_post_meta( $post_id, 'streaming_url', esc_url_raw( $_POST['streaming_url'] ) );
    // Fallback/legacy
    if ( isset( $_POST['streaming_url'] ) ) update_post_meta( $post_id, '_stream_url', esc_url_raw( $_POST['streaming_url'] ) );
    
    if ( isset( $_POST['website_url'] ) ) update_post_meta( $post_id, '_website_url', esc_url_raw( $_POST['website_url'] ) );
    if ( isset( $_POST['bitrate'] ) ) update_post_meta( $post_id, '_bitrate', sanitize_text_field( $_POST['bitrate'] ) );
    if ( isset( $_POST['frequency'] ) ) update_post_meta( $post_id, '_frequency', sanitize_text_field( $_POST['frequency'] ) );
    if ( isset( $_POST['owner'] ) ) update_post_meta( $post_id, '_owner', sanitize_text_field( $_POST['owner'] ) );
    if ( isset( $_POST['listeners'] ) ) update_post_meta( $post_id, '_listeners', intval( $_POST['listeners'] ) );
}
add_action( 'save_post', 'liveradio_save_meta_box_data' );
