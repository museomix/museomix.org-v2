<?php


get_header();
$locations = array();
$edition_year =  get_query_var( 'edition_year', 1 );

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );

?>

<div id="main-content">

<?php if ( ! $is_page_builder_used ) : ?>

	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">

<?php endif; ?>
<?php
$post_thumbnail_id = get_post_thumbnail_id();
if ((bool)$post_thumbnail_id): ?>
	<div id="top_banner" style="background-image:url('<?php echo wp_get_attachment_image_src($post_thumbnail_id, 'full')[0]; ?>');">
		<article id="post-<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> page type-page status-publish has-post-thumbnail hentry">
			<div class="entry-content">
				<div class="et_pb_section  et_section_regular">
					<div class=" et_pb_row">
						<div class="et_pb_column et_pb_column_4_4  et_pb_column_0">
							<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_right  et_pb_text_0">
								<h1 id="post_title"><?php the_title(); ?></h1>
							</div> <!-- .et_pb_text -->
						</div> <!-- .et_pb_column -->
					</div> <!-- .et_pb_row -->
				</div> <!-- .et_pb_section -->
			</div> <!-- .entry-content -->
		</article>
	</div>
<?php endif; ?>

			<?php while ( have_posts() ) : the_post();
				$locations[] = $post;
			endwhile;  ?>
			<div class=" et_pb_row tricolumn_block">
				<div class="columns">
					<h2 class="yellow_sub_title"><?php echo sprintf( __('%1$s locations','museomix'), $edition_year); ?></h2>
					<?php display_news($locations); ?>
				</div>
			</div>
<?php if ( ! $is_page_builder_used ) : ?>

			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->

<?php endif; ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>
