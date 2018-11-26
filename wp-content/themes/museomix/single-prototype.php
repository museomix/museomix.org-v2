<?php
global $post;
get_header();


/* We link prototype to a location (museum + edition) */
$location = get_field('museomix');
$museum_id = get_field('museum', $location->ID);
if ($museum_id):
		$museum = get_post($museum_id[0]);
endif;

$edition = get_field('edition', $location->ID);

/* Content retrieval */
$items = array();

$prototype_picture = get_field('visuel_prototype');
$prototype_description = get_field('resume_du_projet');

$prototype_scenario  = get_field('scenario');
$prototype_intentions = get_field('intentions');
$prototype_hardware = get_field('materiel');
$prototype_experience = get_field('experience');
$prototype_gallery = get_field('galerie');
$prototype_faq = get_field('faq');
$prototype_external_link = get_field('lien_externe');
//
$prototype_team_picture = get_field('photo_equipe');
if (strpos($prototype_team_picture, 'http')!== false) {
	$prototype_team_picture = get_image_id_from_url($prototype_team_picture);
}
$prototype_team_description = get_field('descriptif_equipe');

/* Menu generation */
if ($prototype_scenario):
	$items[]= array(
		'text' => __('Scenario','museomix'),
		'url' => get_the_permalink().'#scenario'
	);
endif;
if ($prototype_intentions):
	$items[] = array(
		'text' => __('Intentions', 'museomix'),
		'url' => get_the_permalink().'#intentions'
	);
endif;
if ($prototype_hardware):
	$items[] = array(
		'text' => __('Hardware', 'museomix'),
		'url' => get_the_permalink().'#hardware'
	);
endif;
if ($prototype_experience):
	$items[] = array(
		'text' => __('Experience', 'museomix'),
		'url' => get_the_permalink().'#experience'
	);
endif;
if ($prototype_faq):
	$items[] = array(
		'text' => __('FAQ', 'museomix'),
		'url' => get_the_permalink().'#faq'
	);
endif;
if ($prototype_team_picture ||$prototype_team_description):
	$items[] = array(
		'text' => __('Team', 'museomix'),
		'url' => get_the_permalink().'#team'
	);
endif;
if ($prototype_gallery):
	$items[] = array(
		'text' => __('Gallery', 'museomix'),
		'url' => get_the_permalink().'#gallery'
	);
endif;
add_subitems_to_menu(__('PrototypeMenu-EN','museomix'),0,  $items);

/* Prototypes */
$museum_prototypes = get_posts(
		array(
			'post_type' => 'prototype',
			'meta_query' => array(
				array(
					'key'     => 'museomix',
					'value'   =>	get_the_ID(),
					'compare' => 'LIKE',
				),
			),
			'suppress_filters' => false,
		)
);
?>
<?php
$top_banner = get_field('banner');
if (!$top_banner):
	$top_banner = get_field('visuel_page');
endif;
if ((bool)$top_banner && $museum): ?>
	<div id="top_banner" style="background-image:url('<?php echo wp_get_attachment_image_src($top_banner, 'full')[0]; ?>');">
		<article id="post-<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> page type-page status-publish has-post-thumbnail hentry">
			<div class="entry-content">
				<div class="et_pb_section  et_section_regular">
					<div class=" et_pb_row">
						<div class="et_pb_column et_pb_column_4_4  et_pb_column_0">
							<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_right page_title_container">
								<h1 id="post_title"><?php the_title(); ?></h1>
							</div> <!-- .et_pb_text -->
						</div> <!-- .et_pb_column -->
					</div> <!-- .et_pb_row -->
				</div> <!-- .et_pb_section -->
			</div> <!-- .entry-content -->
		</article>
	</div>
<?php endif; ?>
<div id="museomix-top">
	<div class="container ">
		<div class="breadcrumb">
			<?php
			if ( function_exists('yoast_breadcrumb') ) {
				yoast_breadcrumb('
				<p id="breadcrumbs">','</p>
				');
			}
			wp_nav_menu(
				array(
						'menu' => __('PrototypeMenu-EN','museomix'),
						'menu_class' => 'page_menu'
				)
			);
			?>
		</div>
	</div>
</div>
<div id="main-content">

	<div class="et_pb_section et_section_regular">
		<?php if ($prototype_picture || $prototype_description): ?>
			<div class=" et_pb_row" id="scenario">
				<div class="proto_text">
					<?php if ($prototype_picture):
						if (!(int)$prototype_picture):
							$prototype_picture = get_attachment_id_from_url($prototype_picture);
						endif; ?>
						<?php echo wp_get_attachment_image((int)$prototype_picture, 'medium', false, array(
							'class' => 'alignleft'
						)); ?>
					<?php endif; ?>
					<?php if ($prototype_description): ?>
						<?php echo $prototype_description; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_scenario): ?>
			<div class=" et_pb_row" id="scenario">
				<h2 class="yellow_sub_title"><?php _e('Scenario','museomix') ?></h2>
				<div class="proto_text">
					<?php echo $prototype_scenario; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_intentions): ?>
			<div class=" et_pb_row" id="intentions">
				<h2 class="yellow_sub_title"><?php _e('Intentions & Processes','museomix') ?></h2>
				<div class="proto_text">
					<?php echo $prototype_intentions; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_hardware): ?>
			<div class=" et_pb_row" id="hardware">
				<h2 class="yellow_sub_title"><?php _e('Hardware','museomix') ?></h2>
				<div class="proto_text">
					<?php echo $prototype_hardware; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_experience): ?>
			<div class=" et_pb_row" id="experience">
				<h2 class="yellow_sub_title"><?php _e('Experience','museomix') ?></h2>
				<div class="proto_text">
					<?php echo $prototype_experience; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_faq): ?>
			<div class=" et_pb_row" id="faq">
				<h2 class="yellow_sub_title"><?php _e('FAQ','museomix') ?></h2>
				<div class="proto_text">
					<?php echo $prototype_faq; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_team_picture ||$prototype_team_description): ?>
			<div class=" et_pb_row" id="team">
				<h2 class="yellow_sub_title"><?php _e('Team','museomix') ?></h2>
				<div class="proto_text">
					<?php if ($prototype_team_picture): ?>
						<a href="<?php echo wp_get_attachment_image_src((int)$prototype_team_picture, 'full')[0]; ?>">
							<?php echo wp_get_attachment_image((int)$prototype_team_picture, 'medium', false, array(
								'class' => 'alignleft'
							)); ?>
						</a>
					<?php endif; ?>
					<?php if ($prototype_team_description): ?>
						<?php echo $prototype_team_description; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_external_link) : ?>
			<div class=" et_pb_row" id="external_link">
				<h2 class="yellow_sub_title"><?php _e('External link','museomix') ?></h2>
				<div class="proto_text">
					<a href="<?php echo $prototype_external_link ?>" target="_blank"><?php echo $prototype_external_link; ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($prototype_gallery && is_array($prototype_gallery)): ?>
			<div class=" et_pb_row" id="gallery">
				<h2 class="yellow_sub_title"><?php _e('Gallery','museomix') ?></h2>
				<div class="proto_text">
					<?php echo do_shortcode('[gallery ids="'.implode(',', wp_list_pluck($prototype_gallery, 'ID')).'" link="file"]'); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div> <!-- #main-content -->

<?php get_footer(); ?>
