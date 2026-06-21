<?php
/**
 * Bulk Upload Radio Stations via Excel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Add Submenu Page
function liveradio_add_bulk_upload_menu() {
    add_submenu_page(
        'edit.php?post_type=radio_station',
        __( 'Bulk Upload Stations', 'liveradio' ),
        __( 'Bulk Upload', 'liveradio' ),
        'manage_options',
        'liveradio-bulk-upload',
        'liveradio_bulk_upload_page_html'
    );
}
add_action( 'admin_menu', 'liveradio_add_bulk_upload_menu' );

// 2. Render Admin Page
function liveradio_bulk_upload_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p>Upload an Excel file (.xlsx, .xls) to bulk import Radio Stations.</p>
        
        <div class="notice notice-info">
            <p><strong>Expected Columns:</strong> Name, Description, Streaming URL, Website URL, Bitrate, Frequency, Owner, Genre, Country, Language, Featured Image.</p>
            <p><em>Note: "Featured Image" should be the absolute local path to the image on your computer (e.g. C:\images\logo.png). Genres, Countries, and Languages can be comma-separated if multiple.</em></p>
        </div>

        <form id="liveradio-bulk-upload-form" method="post" enctype="multipart/form-data">
            <input type="file" id="excel_file" name="excel_file" accept=".xlsx, .xls, .csv" required>
            <br><br>
            <button type="submit" class="button button-primary" id="process_excel_btn">Process Excel File</button>
            <span class="spinner" id="upload_spinner" style="float:none; margin-top:0;"></span>
        </form>

        <div id="upload_status" style="margin-top:20px;"></div>
    </div>

    <!-- Include SheetJS from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    jQuery(document).ready(function($) {
        $('#liveradio-bulk-upload-form').on('submit', function(e) {
            e.preventDefault();
            var fileInput = document.getElementById('excel_file');
            if (fileInput.files.length === 0) {
                alert('Please select a file.');
                return;
            }

            var file = fileInput.files[0];
            var reader = new FileReader();

            $('#process_excel_btn').prop('disabled', true);
            $('#upload_spinner').addClass('is-active');
            $('#upload_status').html('<p>Reading Excel file...</p>');

            reader.onload = function(e) {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, {type: 'array'});
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];
                
                // Convert to JSON
                var jsonData = XLSX.utils.sheet_to_json(worksheet, {defval: ""});
                
                if (jsonData.length === 0) {
                    $('#upload_status').html('<p style="color:red;">No data found in the Excel file.</p>');
                    resetForm();
                    return;
                }

                $('#upload_status').html('<p>Found ' + jsonData.length + ' rows. Processing...</p><ul id="upload_log"></ul>');
                
                processRows(jsonData, 0);
            };
            
            reader.readAsArrayBuffer(file);
        });

        function processRows(rows, index) {
            if (index >= rows.length) {
                $('#upload_status').append('<p style="color:green; font-weight:bold;">All rows processed successfully!</p>');
                resetForm();
                return;
            }

            var row = rows[index];
            var rowNum = index + 1;
            
            // Send AJAX request for this row
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'liveradio_bulk_upload_station',
                    nonce: '<?php echo wp_create_nonce("liveradio_bulk_upload_nonce"); ?>',
                    station_data: JSON.stringify(row)
                },
                success: function(response) {
                    if (response.success) {
                        $('#upload_log').append('<li style="color:green;">Row ' + rowNum + ': ' + response.data.message + '</li>');
                    } else {
                        $('#upload_log').append('<li style="color:red;">Row ' + rowNum + ' Error: ' + response.data + '</li>');
                    }
                },
                error: function() {
                    $('#upload_log').append('<li style="color:red;">Row ' + rowNum + ' Error: Server request failed.</li>');
                },
                complete: function() {
                    // Process next row
                    processRows(rows, index + 1);
                }
            });
        }

        function resetForm() {
            $('#process_excel_btn').prop('disabled', false);
            $('#upload_spinner').removeClass('is-active');
            $('#excel_file').val('');
        }
    });
    </script>
    <?php
}

// 3. AJAX Handler
function liveradio_bulk_upload_station() {
    check_ajax_referer( 'liveradio_bulk_upload_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied.' );
    }

    $station_data = isset( $_POST['station_data'] ) ? json_decode( stripslashes( $_POST['station_data'] ), true ) : array();

    if ( empty( $station_data ) ) {
        wp_send_json_error( 'No data provided.' );
    }

    // Helper to find column case-insensitively
    $get_col = function($keys, $data) {
        foreach ($data as $k => $v) {
            foreach ($keys as $key) {
                if ( strtolower(trim($k)) === strtolower($key) ) {
                    return $v;
                }
            }
        }
        return '';
    };

    $name = $get_col( ['Name', 'Title', 'Station Name'], $station_data );
    if ( empty( $name ) ) {
        wp_send_json_error( 'Station Name is missing.' );
    }

    $description = $get_col( ['Description', 'Content', 'About'], $station_data );
    $streaming_url = $get_col( ['Streaming URL', 'Stream URL', 'Stream'], $station_data );
    $website_url = $get_col( ['Website URL', 'Website'], $station_data );
    $bitrate = $get_col( ['Bitrate'], $station_data );
    $frequency = $get_col( ['Frequency'], $station_data );
    $owner = $get_col( ['Owner'], $station_data );
    
    $genre_str = $get_col( ['Genre', 'Genres'], $station_data );
    $country_str = $get_col( ['Country', 'Countries'], $station_data );
    $language_str = $get_col( ['Language', 'Languages'], $station_data );
    
    $featured_image_path = $get_col( ['Featured Image', 'Image', 'Logo'], $station_data );

    // Create Post
    $post_id = wp_insert_post( array(
        'post_title'   => wp_strip_all_tags( $name ),
        'post_content' => wp_kses_post( $description ),
        'post_status'  => 'publish',
        'post_type'    => 'radio_station',
    ) );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( 'Failed to create post: ' . $post_id->get_error_message() );
    }

    // Update Meta
    if ( ! empty( $streaming_url ) ) {
        update_post_meta( $post_id, 'streaming_url', esc_url_raw( $streaming_url ) );
        update_post_meta( $post_id, '_stream_url', esc_url_raw( $streaming_url ) );
    }
    if ( ! empty( $website_url ) ) update_post_meta( $post_id, '_website_url', esc_url_raw( $website_url ) );
    if ( ! empty( $bitrate ) ) update_post_meta( $post_id, '_bitrate', sanitize_text_field( $bitrate ) );
    if ( ! empty( $frequency ) ) update_post_meta( $post_id, '_frequency', sanitize_text_field( $frequency ) );
    if ( ! empty( $owner ) ) update_post_meta( $post_id, '_owner', sanitize_text_field( $owner ) );

    // Handle Taxonomies
    $set_taxonomies = function( $term_str, $taxonomy ) use ( $post_id ) {
        if ( empty( $term_str ) ) return;
        
        $terms = array_map( 'trim', explode( ',', $term_str ) );
        $term_ids = array();
        
        foreach ( $terms as $term_name ) {
            if ( empty( $term_name ) ) continue;
            
            $term = term_exists( $term_name, $taxonomy );
            
            if ( ! $term ) {
                $term = wp_insert_term( $term_name, $taxonomy );
            }
            
            if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
                $term_ids[] = (int) $term['term_id'];
            }
        }
        
        if ( ! empty( $term_ids ) ) {
            wp_set_object_terms( $post_id, $term_ids, $taxonomy );
        }
    };

    $set_taxonomies( $genre_str, 'genre' );
    $set_taxonomies( $country_str, 'country' );
    $set_taxonomies( $language_str, 'language' );

    // Handle Local Featured Image Upload
    $image_msg = '';
    if ( ! empty( $featured_image_path ) ) {
        // Remove quotes if present
        $featured_image_path = trim($featured_image_path, '"\' ');
        
        if ( file_exists( $featured_image_path ) ) {
            $file_content = file_get_contents( $featured_image_path );
            if ( $file_content !== false ) {
                $filename = basename( $featured_image_path );
                $upload_file = wp_upload_bits( $filename, null, $file_content );
                
                if ( ! $upload_file['error'] ) {
                    $wp_filetype = wp_check_filetype( $filename, null );
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    
                    $attach_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_file['file'] );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    set_post_thumbnail( $post_id, $attach_id );
                    $image_msg = ' & Image attached.';
                } else {
                    $image_msg = ' & Image upload failed: ' . $upload_file['error'];
                }
            } else {
                $image_msg = ' & Could not read image file.';
            }
        } else {
            $image_msg = ' & Image file not found at path: ' . htmlspecialchars($featured_image_path);
        }
    }

    wp_send_json_success( array( 'message' => 'Imported ' . esc_html( $name ) . $image_msg, 'post_id' => $post_id ) );
}
add_action( 'wp_ajax_liveradio_bulk_upload_station', 'liveradio_bulk_upload_station' );
