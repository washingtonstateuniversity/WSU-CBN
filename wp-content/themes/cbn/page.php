<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that other
 * 'pages' on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
               	 <!-- temp remove
					<header class="entry-header">
						<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
						<div class="entry-thumbnail">
							<?php the_post_thumbnail(); ?>
						</div>
						<?php endif; ?>

						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>.entry-header -->
					
					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentythirteen' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) ); ?>
					</div><!-- .entry-content -->

					<footer class="entry-meta">
						<?php edit_post_link( __( 'Edit', 'twentythirteen' ), '<span class="edit-link">', '</span>' ); ?>
					</footer><!-- .entry-meta -->
				</article><!-- #post -->

				<?php comments_template(); ?>
			<?php endwhile; ?>

		</div><!-- #content -->
	</div><!-- #primary -->
    
    <?php if ( is_front_page() ) { ?>
        <aside id="right-col">
            <a href="https://www.applyonlinenow.com/USCCapp/Ctl/entry?sc=VABJ6K&mboxSession=1357690082163-219380"><img src="/content/themes/cbn/img/sponsors/AlaskaAirlines.png" alt="Alaska Airlines Cougar Visa" title="Alaska Airlines Cougar Visa"></a>
            <a href="http://www.ryanswansonlaw.com/"><img src="/content/themes/cbn/img/sponsors/RyanSwanson.png" alt="Ryan, Swanson &amp; Cleveland, PLLC" title="Ryan, Swanson &amp; Cleveland, PLLC"></a>
            <a href="http://www.griffinmaclean.com/"><img src="/content/themes/cbn/img/sponsors/GriffinMacLean.jpg" alt="Griffin MacLean Insurance Brokers" title="Griffin MacLean Insurance Brokers"></a>
            <a href="http://omba.wsu.edu/landingform_jun2011_b/?Access_Code=WSU-MBA-ALUMBANFEB2013&utm_source=ALUMBANFEB2013&utm_medium=internetadvertising"><img src="/content/themes/cbn/img/sponsors/OnlineMBA.png" alt="WSU Online MBA Program" title="WSU Online MBA Program"></a>
            <a href="https://www.libertymutual.com/wsu-alumni"><img src="/content/themes/cbn/img/sponsors/LibertyMutual.png" alt="liberty mutual" title="liberty mutual"></a>
            
        </aside><!--#right-col-->
    <?php }  // This is a homepage  ?>
    
    
<?php get_sidebar(); ?>

<?php if ( !is_front_page() ) { ?>
<div style="clear:both;"></div>
<div id="details">
    <hr>    <p><strong>PLEASE NOTE:</strong></p>
    <p>
     Cougar-owned or -managed businesses are defined as businesses clearly owned by alumni or friends of WSU, or a business where alumni are the principal executive(s) in a leadership role. Businesses in question will be reviewed and considered by the WSUAA Executive Committee. The WSUAA reserves the right to determine if a business is deemed appropriate for the WSU alumni audience.</p>
</div>
<?php }  // This is a homepage  ?>
<?php get_footer(); ?>