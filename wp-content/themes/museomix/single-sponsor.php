<?php

get_header();

$show_default_title = get_post_meta( get_the_ID(), '_et_pb_show_title', true );

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );

?>

<div id="main-content">
	<?php
		if ( et_builder_is_product_tour_enabled() ):
			// load fullwidth page in Product Tour mode
			while ( have_posts() ): the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
					<?php
						the_content();
					?>
					</div> <!-- .entry-content -->

				</article> <!-- .et_pb_post -->

		<?php endwhile;
		else:
	?>
	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php if (et_get_option('divi_integration_single_top') <> '' && et_get_option('divi_integrate_singletop_enable') == 'on') echo(et_get_option('divi_integration_single_top')); ?>
				<div class="breadcrumb">
					<?php if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb('
					<p id="breadcrumbs">','</p>
					');
					} ?>
				</div>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'et_pb_post' ); ?>>
					<?php if ( ( 'off' !== $show_default_title && $is_page_builder_used ) || ! $is_page_builder_used ) { ?>
						<div class="et_post_meta_wrapper">
							<h1 class="entry-title"><?php the_title(); ?></h1>
							<div class="entry-content">
								<?php if (get_field('logo')): ?>
									<div class="txt-center">
										<?php echo wp_get_attachment_image(get_field('logo'), 'sponsor_thumbnail', false); ?>
									</div>
								<?php endif; ?>
								<?php if (!empty(get_field('description_de_partenaire'))): ?>
									<p><?php the_field('description_de_partenaire'); ?></p>
								<?php endif; ?>
								<?php if (get_field('lien')) : ?>
									<p><?php _e('Site','museomix'); ?> : <a href="<?php the_field('lien'); ?>"><?php the_field('lien'); ?></a></p>
								<?php endif; ?>
							
							</div>

						
					</div> <!-- .et_post_meta_wrapper -->
				<?php  } ?>

					<div class="entry-content">
					<?php
						do_action( 'et_before_content' );

						the_content();

					?>
					</div> <!-- .entry-content -->
					
				</article> <!-- .et_pb_post -->

			<?php endwhile; ?>
			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
	<?php endif; ?>
</div> <!-- #main-content -->

<?php get_footer(); ?>
