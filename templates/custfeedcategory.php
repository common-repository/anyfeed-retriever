<?php
/**
 * User: anushkar
 * Date: 2/4/19
 * Time: 11:31 AM
 */
get_header();
?>

    <section id="primary" class="content-area">
        <main id="main" class="site-main">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>><header class="entry-header"> <h2>Feed Archive Template</h2></header>
            <div class="entry-content">
            <?php
                if(isset($_GET['custfeedcategory']) && !empty($_GET['custfeedcategory'])) {
                    echo do_shortcode('[anyfeed catslug="' . sanitize_key( $_GET['custfeedcategory'] ). '"]');
                }
                else{
                    echo esc_html_e('Invalid feed category','anyfeed');
                }
            ?>
            </div>
            </article>

        </main><!-- .site-main -->
    </section><!-- .content-area -->

<?php
get_footer();
