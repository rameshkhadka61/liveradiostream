<?php
/**
 * Template Name: Submit Station
 */

get_header(); ?>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white" style="padding: 60px 0;">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3">Submit Your <span class="text-gradient">Radio Station</span></h1>
            <p class="lead mb-0 text-light opacity-75">Join our global directory and reach thousands of listeners.</p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container pb-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="custom-card p-4 p-md-5" style="border-top: 3px solid #06b6d4;">
                    
                    <div id="submit-message" class="alert d-none" role="alert"></div>

                    <form id="submitStationForm" class="needs-validation" novalidate>
                        <?php wp_nonce_field( 'liveradio_submit_station', 'submit_station_nonce' ); ?>
                        <input type="hidden" name="action" value="liveradio_submit_station">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="stationName" class="form-label text-muted small fw-bold text-uppercase">Radio Station Name *</label>
                                <input type="text" name="stationName" class="form-control form-control-lg bg-transparent text-white" id="stationName" placeholder="e.g., Cool FM" required>
                                <div class="invalid-feedback">Please provide the station name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="streamUrl" class="form-label text-muted small fw-bold text-uppercase">Stream URL (Audio) *</label>
                                <input type="url" name="streamUrl" class="form-control form-control-lg bg-transparent text-white" id="streamUrl" placeholder="https://..." required>
                                <div class="invalid-feedback">Please provide a valid stream URL.</div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="websiteUrl" class="form-label text-muted small fw-bold text-uppercase">Website URL</label>
                                <input type="url" name="websiteUrl" class="form-control form-control-lg bg-transparent text-white" id="websiteUrl" placeholder="https://...">
                            </div>
                            <div class="col-md-6">
                                <label for="logoUrl" class="form-label text-muted small fw-bold text-uppercase">Logo Image URL</label>
                                <input type="url" name="logoUrl" class="form-control form-control-lg bg-transparent text-white" id="logoUrl" placeholder="https://...">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="countrySelect" class="form-label text-muted small fw-bold text-uppercase">Country *</label>
                                <input list="countryList" name="countrySelect" id="countrySelect" class="form-control form-control-lg bg-transparent text-white" placeholder="Type to search country..." required autocomplete="off">
                                <datalist id="countryList">
                                    <?php
                                    $countries = get_terms( array( 'taxonomy' => 'country', 'hide_empty' => false ) );
                                    foreach ( $countries as $country ) {
                                        echo '<option value="' . esc_attr( $country->name ) . '"></option>';
                                    }
                                    ?>
                                </datalist>
                                <div class="invalid-feedback">Please select a valid country.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="genreSelect" class="form-label text-muted small fw-bold text-uppercase">Primary Genre(s) *</label>
                                <select name="genreSelect[]" class="form-select form-select-lg bg-transparent text-white" id="genreSelect" multiple style="min-height: 120px;" required>
                                    <?php
                                    $genres = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => false ) );
                                    foreach ( $genres as $genre ) {
                                        echo '<option value="' . esc_attr( $genre->term_id ) . '" class="text-dark">' . esc_html( $genre->name ) . '</option>';
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Please select at least one genre.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label text-muted small fw-bold text-uppercase">Description (Optional)</label>
                            <textarea name="description" class="form-control bg-transparent text-white" id="description" rows="4" placeholder="Tell us about your radio station..."></textarea>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required>
                            <label class="form-check-label text-muted small" for="termsCheck">I confirm that I have the right to submit this station and stream URL.</label>
                            <div class="invalid-feedback">You must agree before submitting.</div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" id="submitBtn" class="btn btn-gradient btn-lg rounded-pill px-5 w-100 py-3 fw-bold shadow-glow">Submit Station</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php get_footer(); ?>

