<?php

get_header();

/* Request to retrieve community linked news */
$community_news = get_posts(
		array(
			'post_type' => 'post',
			'meta_query' => array(
				array(
					'key'     => 'linked_communities',
					'value'   => '"'.get_the_ID().'"',
					'compare' => 'LIKE',
				),
			),
			'suppress_filters' => false,
		)
);

/* Request to retrieve community linked museums */
$community_museums = get_posts(
		array(
			'post_type' => 'museomix',
			'meta_query' => array(
				array(
					'key'     => 'community',
					'value'   => '"'.get_the_ID().'"',
					'compare' => 'LIKE',
				),
			),
			'posts_per_page' => -1,
			'suppress_filters' => false,
		)
);
if ($community_museums):
	$community_museums_tmp = array();
	$i = 0;
	foreach($community_museums as $museum):
		$edition = get_field('edition', $museum);
		$community_museums_tmp[$edition->post_title.'_'.$i] = $museum;
		$i++;
	endforeach;
	if ($community_museums_tmp):
		$community_museums = $community_museums_tmp;
		krsort($community_museums);
		unset($community_museums_tmp);
	endif;
endif;

/* Menu */
$items = array();
if ($community_news ||$community_museums):
	$items[]= array(
		'text' => __('Presentation','museomix'),
		'url' => get_the_permalink().'#community-presentation'
	);
endif;
if ($community_news):
	$items[] = array(
		'text' => __('News', 'museomix'),
		'url' => get_the_permalink().'#community-news'
	);
endif;
if ($community_museums):
	$items[] = array(
		'text' => __('Remixed museums', 'museomix'),
		'url' => get_the_permalink().'#community-museums'
	);
endif;
add_subitems_to_menu(__('Community menu','museomix'),0,  $items);
?>

<div id="main-content">
	<?php
	$top_banner = get_field('banner');
	if ((bool)$top_banner): ?>
		<div id="top_banner" style="background-image:url('<?php echo wp_get_attachment_image_src($top_banner, 'full')[0]; ?>');">
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
	<div id="community-top">
		<div class="container ">
			<div class="breadcrumb">
				<?php
				if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb('
					<p id="breadcrumbs">','</p>
					');
				}
				if (!empty($items)) {
					wp_nav_menu(
						array(
								'menu' => __('Community menu','museomix'),
								'menu_class' => 'page_menu'
						)
					);
				}				
				?>
				</div>
				<?php the_field('introduction'); ?>
		</div>
	</div>
	<div class="et_pb_section et_section_regular">
		<div class=" et_pb_row ">
			<div id="community-presentation" class="et_pb_column et_pb_column_1_2  et_pb_column_0 "><?php the_field('presentation');?></div>
			<div id="community-details" class="et_pb_column et_pb_column_1_2  et_pb_column_1 ">
				<?php
				$community_logo = get_field('community_logo');
				if ($community_logo): ?>
					<div id="community-logo">
						<?php echo wp_get_attachment_image($community_logo, 'full'); ?>
					</div>
				<?php endif; ?>
				<?php
				$presentation_image = get_field('presentation_image');
				if ($presentation_image): ?>
					<div id="community-image">
						<?php echo wp_get_attachment_image($presentation_image, 'community_image'); ?>
					</div>
				<?php endif; ?>
				<div id="community-infos">
					<?php
					$community_phone = get_field('phone');
					$community_website = get_field('website');
					$community_social_networks = get_field('social_networks');
					$community_email = get_field('email');
					if ($community_phone || $community_website || $community_social_networks || $community_email ): ?>
						<div class="community-contacts title-picto_bird">
							<h5 class=""><?php _e('Contacts', 'museomix'); ?></h5>
							<?php
							if ($community_phone): ?>
								<p><?php _e('Phone :', 'museomix'); ?>&nbsp;<?php echo $community_phone; ?></p>
							<?php endif;?>
							<?php
							if ($community_website):
								$community_website_display = trim(str_replace(array('http://', 'https://'), '', $community_website), '/');;
								?>
								<p><a target="_new" href="<?php echo $community_website; ?>"><?php echo $community_website_display; ?></a></p>
							<?php endif;?>
							<?php
							if ($community_social_networks || $community_email): ?><p><?php
								foreach($community_social_networks as $network):
									if (strtolower($network['network']) == 'facebook'): ?>
										<a target="_new" class="community_social facebook" href="<?php echo $network['url']; ?>"><?php _e('Facebook','museomix'); ?></a>
									<?php
									endif;
									if (strtolower($network['network']) == 'twitter'): ?>
										<a target="_new" class="community_social twitter" href="<?php echo $network['url']; ?>"><?php _e('Twitter','museomix'); ?></a>
									<?php
									endif;
									?>
								<?php
								endforeach;
								if ($community_email): ?>
									<a class="community_social email" href="mailto:<?php echo $community_email; ?>"><?php _e('E-mail','museomix'); ?></a>
								<?php
								endif;
							?></p><?php
							endif;?>
						</div>
					<?php endif; ?>
					<?php
					$community_sphere_of_influence = get_field('sphere_of_influence');
					if ($community_sphere_of_influence): ?>
						<div class="community-contacts title-influence">
							<h5 class=""><?php _e('Influence area', 'museomix'); ?></h5>
							<p><?php echo $community_sphere_of_influence; ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
  <?php


  if ($community_news || $community_museums):
  ?>
  	<div class="et_pb_section et_section_regular" id="community-news">
				<?php if ($community_news): ?>
		  		<div class=" et_pb_row tricolumn_block">
		  			<h2 class="yellow_sub_title"><?php echo sprintf( __('%1$s news','museomix'), get_the_title()); ?></h2>
		        <div class="columns">
		          <?php display_news($community_news); ?>
		        </div>
		  		</div>
				<?php endif; ?>
				<?php if ($community_museums): ?>
					<div class=" et_pb_row">
							<div class="et_pb_column et_pb_column_4_4  et_pb_column_7">
								<div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off  et_always_center_on_mobile et-animated separator">
									<img src="<?php echo get_stylesheet_directory_uri().'/assets/images/msx_sep.png'; ?>" alt="">
								</div>
								<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_center icecubes_title big_titles">
									<h2 id="community-museums"><?php _e('Remixed museums', 'museomix'); ?></h2>
								</div>
							</div>
						</div>
						<div class=" et_pb_row tricolumn_block">
								<div class="columns">
				          <?php display_news($community_museums); ?>
				        </div>
							</div>
						</div>
					</div>
				<?php endif; ?>
  	</div>
  <?php endif; ?>
</div> <!-- #main-content -->

<?php get_footer(); ?>
