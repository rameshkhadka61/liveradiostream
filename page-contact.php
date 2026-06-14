<?php
/**
 * Template Name: Contact Us
 */

get_header(); ?>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white" style="padding: 60px 0;">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3"><?php the_title(); ?></h1>
            <p class="lead mb-0 text-light opacity-75">We'd love to hear from you. Send us a message!</p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container mb-5 py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="custom-card p-4 p-md-5" style="border-top: 3px solid #06b6d4;">
                    
                    <div class="mb-4 text-muted text-center" style="font-size: 1.05rem;">
                        <?php
                        // If the user added any text in the page editor, show it here above the form
                        if ( have_posts() ) {
                            while ( have_posts() ) {
                                the_post();
                                the_content();
                            }
                        }
                        ?>
                    </div>

                    <div id="contact-message" class="alert d-none" role="alert"></div>

                    <form id="contactForm" class="needs-validation" novalidate>
                        <?php wp_nonce_field( 'liveradio_contact_nonce_action', 'contact_nonce' ); ?>
                        <input type="hidden" name="action" value="liveradio_contact_submit">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="contactName" class="form-label text-muted small fw-bold text-uppercase">Your Name *</label>
                                <input type="text" name="contactName" class="form-control form-control-lg bg-transparent text-white" id="contactName" placeholder="John Doe" required>
                                <div class="invalid-feedback">Please provide your name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label text-muted small fw-bold text-uppercase">Your Email *</label>
                                <input type="email" name="contactEmail" class="form-control form-control-lg bg-transparent text-white" id="contactEmail" placeholder="john@example.com" required>
                                <div class="invalid-feedback">Please provide a valid email address.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="contactSubject" class="form-label text-muted small fw-bold text-uppercase">Subject *</label>
                            <input type="text" name="contactSubject" class="form-control form-control-lg bg-transparent text-white" id="contactSubject" placeholder="How can we help?" required>
                            <div class="invalid-feedback">Please provide a subject.</div>
                        </div>

                        <div class="mb-4">
                            <label for="contactMessage" class="form-label text-muted small fw-bold text-uppercase">Message *</label>
                            <textarea name="contactMessage" class="form-control bg-transparent text-white" id="contactMessage" rows="6" placeholder="Write your message here..." required></textarea>
                            <div class="invalid-feedback">Please write a message.</div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" id="contactSubmitBtn" class="btn btn-gradient btn-lg rounded-pill px-5 w-100 py-3 fw-bold shadow-glow">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php get_footer(); ?>
