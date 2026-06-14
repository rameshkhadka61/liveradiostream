<?php
/**
 * The template for displaying comments
 */

if ( post_password_required() ) {
    return;
}

// Calculate Average Rating
$rating_data = liveradio_get_station_rating( get_the_ID() );
$avg_rating = $rating_data['average'];
$total_ratings = $rating_data['count'];
?>

<div id="comments" class="comments-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-chat-square-quote me-2" style="color:var(--accent-cyan);"></i>Listener Reviews</h2>
        <?php if ( $total_ratings > 0 ) : ?>
            <div class="d-flex align-items-center gap-2">
                <span class="rating-stars fs-5">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <?php if ( $i <= floor($avg_rating) ) : ?>
                            <i class="bi bi-star-fill"></i>
                        <?php elseif ( $i == ceil($avg_rating) && $avg_rating - floor($avg_rating) >= 0.5 ) : ?>
                            <i class="bi bi-star-half"></i>
                        <?php else : ?>
                            <i class="bi bi-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                </span>
                <strong><?php echo esc_html( number_format( $avg_rating, 1 ) ); ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( have_comments() ) : ?>
        <div class="mb-4">
            <?php
            wp_list_comments( array(
                'style'       => 'div',
                'short_ping'  => true,
                'avatar_size' => 44,
                'callback'    => 'liveradiostream_comment_callback',
            ) );
            ?>
        </div>
    <?php endif; ?>

    <!-- Leave a Review Form -->
    <hr style="border-color: var(--glass-border);">
    <?php
    $commenter = wp_get_current_commenter();

    $fields = array(
        'author' => '<div class="col-md-6 mb-3"><input type="text" class="form-control" name="author" id="author" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="Your Name *" aria-label="Your name" required="required"></div>',
        'email'  => '<div class="col-md-6 mb-3"><input type="email" class="form-control" name="email" id="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" placeholder="Your Email *" aria-label="Your email" required="required"></div>',
    );

    $rating_html = '<div class="col-md-12 mb-3">
        <label class="form-label text-muted small fw-bold text-uppercase">Your Rating *</label>
        <div class="interactive-rating">
            <input type="radio" name="rating" id="rating-5" value="5" required>
            <label for="rating-5" title="5 stars"><i class="bi bi-star-fill"></i></label>
            <input type="radio" name="rating" id="rating-4" value="4">
            <label for="rating-4" title="4 stars"><i class="bi bi-star-fill"></i></label>
            <input type="radio" name="rating" id="rating-3" value="3">
            <label for="rating-3" title="3 stars"><i class="bi bi-star-fill"></i></label>
            <input type="radio" name="rating" id="rating-2" value="2">
            <label for="rating-2" title="2 stars"><i class="bi bi-star-fill"></i></label>
            <input type="radio" name="rating" id="rating-1" value="1">
            <label for="rating-1" title="1 star"><i class="bi bi-star-fill"></i></label>
        </div>
    </div>';

    comment_form( array(
        'class_form'           => 'row g-3',
        'title_reply_before'   => '<h3 id="reply-title" class="h6 fw-bold mb-3 col-12">',
        'title_reply'          => 'Leave a Review',
        'title_reply_after'    => '</h3>',
        'comment_notes_before' => '',
        'fields'               => $fields,
        'comment_field'        => $rating_html . '<div class="col-12"><textarea class="form-control" name="comment" id="comment" rows="3" placeholder="Share your listening experience..." aria-label="Review comment" required></textarea></div>',
        'submit_button'        => '<div class="col-12 mt-3"><button type="submit" class="btn btn-gradient rounded-pill px-4">Submit Review</button></div>',
        'submit_field'         => '%1$s %2$s',
    ) );
    ?>
</div>
