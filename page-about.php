<?php
/**
 * Template Name: About Us
 */

get_header(); ?>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white" style="padding: 80px 0; background: linear-gradient(135deg, rgba(6,182,212,0.85), rgba(59,130,246,0.85)), url('https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=1200&auto=format&fit=crop') center/cover;">
        <div class="hero-overlay"></div>
        <div class="container hero-content position-relative">
            <h1 class="display-4 fw-bold mb-3">About <span class="text-gradient">Live Radio Stream</span></h1>
            <p class="lead mb-0 text-light opacity-85 mx-auto" style="max-width: 700px;">Connecting music lovers, news seekers, and sports fans with thousands of live AM/FM broadcast stations across the globe.</p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container pb-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Main Glassmorphic Card -->
                <div class="custom-card p-4 p-md-5 mb-5 shadow-glow" style="border-top: 3px solid #06b6d4;">
                    
                    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                        <div class="entry-content text-muted mb-5" style="line-height: 1.85; font-size: 1.1rem;">
                            <?php the_content(); ?>
                        </div>
                    <?php endwhile; endif; ?>

                    <!-- Mission & Feature Pillars -->
                    <div class="row g-4 my-2">
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 h-100 transition-hover" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle" style="width: 54px; height: 54px; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: #fff; font-size: 1.5rem;">
                                    📻
                                </div>
                                <h3 class="h4 fw-bold text-white mb-2">Our Mission</h3>
                                <p class="text-muted mb-0" style="line-height: 1.7;">We believe radio should be universal and borderless. Our goal is to curate the world's highest-fidelity audio streams into one fast, organized, and accessible directory available anytime, anywhere.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 rounded-4 h-100 transition-hover" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle" style="width: 54px; height: 54px; background: linear-gradient(135deg, #8b5cf6, #ec4899); color: #fff; font-size: 1.5rem;">
                                    ⚡
                                </div>
                                <h3 class="h4 fw-bold text-white mb-2">Why Us?</h3>
                                <p class="text-muted mb-0" style="line-height: 1.7;">Say goodbye to intrusive popup ads and sluggish players. We prioritize lightning-fast page loading, instant search filtering, local favorite saving, and uninterrupted background audio.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Live Stats Banner -->
                    <div class="row g-4 my-5 py-4 px-3 rounded-4 text-center align-items-center" style="background: linear-gradient(135deg, rgba(6,182,212,0.1), rgba(59,130,246,0.1)); border: 1px solid rgba(6,182,212,0.25);">
                        <div class="col-sm-4">
                            <div class="display-5 fw-bold text-gradient mb-1">200+</div>
                            <div class="text-white small fw-bold text-uppercase tracking-wider">Countries Covered</div>
                        </div>
                        <div class="col-sm-4 border-start border-end border-secondary border-opacity-25">
                            <div class="display-5 fw-bold text-gradient mb-1">10k+</div>
                            <div class="text-white small fw-bold text-uppercase tracking-wider">Radio Stations</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="display-5 fw-bold text-gradient mb-1">100%</div>
                            <div class="text-white small fw-bold text-uppercase tracking-wider">Free & Unlimited</div>
                        </div>
                    </div>

                    <!-- Founder / E-E-A-T Spotlight Box -->
                    <div class="mt-5 pt-3 border-top border-secondary border-opacity-25">
                        <div class="d-flex flex-column flex-md-row align-items-center gap-4 p-4 rounded-4" style="background: rgba(0, 0, 0, 0.2);">
                            <div class="flex-shrink-0 text-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-glow mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: #fff; font-size: 2.2rem; font-weight: bold;">
                                    RK
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-info text-dark fw-bold px-2 py-1 small">FOUNDER & CURATOR</span>
                                    <h4 class="h5 fw-bold text-white mb-0">Ramesh Khadka</h4>
                                </div>
                                <p class="text-muted small mb-0" style="line-height: 1.7;">Dedicated to building transparent, high-performance web applications and promoting global culture through audio broadcasting. Every radio stream is audited and monitored regularly for optimal streaming uptime.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Call To Action Footer -->
                    <div class="text-center mt-5 pt-4">
                        <h4 class="fw-bold text-white mb-3">Want Your Station Featured Worldwide?</h4>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 550px;">We accept community FM stations, internet radios, and syndicated broadcasts completely free of charge.</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="<?php echo esc_url( home_url( '/submit/' ) ); ?>" class="btn btn-gradient btn-lg rounded-pill px-5 py-3 fw-bold shadow-glow">Submit Your Station</a>
                            <a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">Contact Us</a>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </main>

<?php get_footer(); ?>
