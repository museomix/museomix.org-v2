<?php
global $post;
get_header();
$museum = false;
$museum_id = get_field('museum');
if ($museum_id):
		$museum = get_post($museum_id[0]);
endif;

$edition = get_field('edition');

/* Menu */
$items = array();
$playgrounds = get_field('playground');
$partners = get_field('sponsor');
$resume = get_field('resume');
$gallery = get_field('galerie');
$local_coordinators = get_field('coordinator_local');
$co_organizers = get_field('co-organisateurs');
if ($co_organizers || $local_coordinators):
	$team = array_merge($co_organizers,$local_coordinators);
else:
	$team = false;
endif;

if ($playgrounds):
	$items[]= array(
		'text' => __('Presentation','museomix'),
		'url' => get_the_permalink().'#presentation'
	);
endif;
if ($playgrounds):
	$items[] = array(
		'text' => __('Playgrounds', 'museomix'),
		'url' => get_the_permalink().'#playgrounds'
	);
endif;
if ($team):
	$items[] = array(
		'text' => __('Team', 'museomix'),
		'url' => get_the_permalink().'#team'
	);
endif;
if ($resume || $gallery):
	$items[] = array(
		'text' => __('Feedback', 'museomix'),
		'url' => get_the_permalink().'#feedback'
	);
endif;
if ($partners):
	$items[] = array(
		'text' => __('Partners', 'museomix'),
		'url' => get_the_permalink().'#partners'
	);
endif;


/* Prototypes */
$museum_prototypes = get_posts(
		array(
			'post_type' => 'prototype',
			'meta_query' => array(
				array(
					'key'     => 'museomix',
					'value'   => get_the_ID(),
					'compare' => 'LIKE',
				),
			),
			'posts_per_page' => -1,
			'suppress_filters' => false,
		)
);
if ($museum_prototypes):
	$items[] = array(
		'text' => __('Prototypes', 'museomix'),
		'url' => get_the_permalink().'#museum-prototypes'
	);
endif;
add_subitems_to_menu(__('Location menu','museomix'),0,  $items);

?>

<div id="main-content">
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
									<?php
									$museum_type = get_field('museum_type', $museum->ID);
									if ($museum_type):
									?>
										<div id="museum_type"><?php echo $museum_type; ?></div>
									<?php
									endif;
									?>
									<h1 id="post_title"><?php echo $museum->post_title; ?></h1>
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
				if ($items):
					wp_nav_menu(
						array(
								'menu' => __('Location menu','museomix'),
								'menu_class' => 'page_menu'
						)
					);
				endif;
				?>
				</div>
				<?php the_field('introduction'); ?>
		</div>
	</div>
	<div class="et_pb_section et_section_regular" id="presentation">
		<div class=" et_pb_row location-block">
			<div id="location-image" class="et_pb_column et_pb_column_1_3  et_pb_column_0 ">
				<?php
				$museum_image = get_field('image_musee', $museum->ID);
				if ($museum_image): ?>
					<?php echo wp_get_attachment_image($museum_image, 'museum_picture'); ?>
				<?php endif; ?>
			</div>
			<div id="museum-details" class="et_pb_column et_pb_column_2_3  et_pb_column_1 ">
				<?php
				
				$museum_logo = get_field('museum_logo', $museum->ID);
				if ($museum_logo): ?>
					<div id="museum-logo" class="et_pb_text_align_center">
						<?php echo wp_get_attachment_image($museum_logo, 'medium'); ?>
					</div>
				<?php endif; ?>
				<h2><?php echo $museum->post_title; ?></h2>
				<?php
				$museum_excerpt = get_field('court_descriptif_musee', $museum->ID);
				if ($museum_excerpt): ?>
					<p>
						<?php echo $museum_excerpt; ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<div class=" et_pb_row">
				<div id="museum-infos">
					<?php
					$museum_address = get_field('adresse', $museum->ID);
					$museum_hours = get_field('horaires', $museum->ID);
					$museum_phone = get_field('phone', $museum->ID);
					$museum_website = get_field('lien', $museum->ID);
					$museum_facebook = get_field('facebook', $museum->ID);
					$museum_twitter = get_field('twitter', $museum->ID);
					$museum_email = get_field('email', $museum->ID);
					if ($museum_address || $museum_hours || $museum_phone || $museum_website || $museum_facebook || $museum_twitter || $museum_email): ?>
						<div class="community-contacts title-marker">
							<h5 class=""><?php echo $museum->post_title; ?></h5>
							<?php
							if ($museum_address): ?>
								<p><?php echo $museum_address; ?>
								<?php
								$museum_city = get_field('ville', $museum->ID);
								if ($museum_city): ?>
									<br /><?php echo $museum_city; ?>
								<?php
								endif;
								?>
								</p>
							<?php endif;?>
						</div>
						<?php if ($museum_hours): ?>
							<div class="community-contacts title-clock">
								<h5 class=""><?php _e('Opening hours', 'museomix'); ?></h5>
								<p><?php echo $museum_hours; ?></p>
							</div>
						<?php endif; ?>
						<?php if ($museum_phone || $museum_website || $museum_facebook || $museum_twitter || $museum_email) :?>
							<div class="community-contacts title-picto_bird">
								<h5 class=""><?php _e('Contacts', 'museomix'); ?></h5>
								<?php
								if ($museum_phone): ?>
									<p><?php _e('Phone :', 'museomix'); ?>&nbsp;<?php echo $museum_phone; ?></p>
								<?php endif;?>
								<?php
								if ($museum_website):
									$museum_website_display = trim(str_replace(array('http://', 'https://'), '', $museum_website), '/');;
									?>
									<p><a target="_new" href="<?php echo $museum_website; ?>"><?php echo $museum_website_display; ?></a></p>
								<?php endif;?>
								<?php
								$museum_facebook = get_field('facebook', $museum->ID);
								$museum_twitter = get_field('twitter', $museum->ID);
								$museum_email = get_field('email', $museum->ID);
								if ($museum_facebook || $museum_twitter || $museum_email): ?><p><?php
									if ($museum_facebook): ?>
										<a target="_new" class="community_social facebook" href="<?php echo $museum_facebook; ?>"><?php _e('Facebook','museomix'); ?></a>
									<?php
									endif;
									if ($museum_twitter): ?>
										<a target="_new" class="community_social facebook" href="<?php echo $museum_twitter; ?>"><?php _e('Twitter','museomix'); ?></a>
									<?php
									endif;
									if ($museum_email): ?>
										<a class="community_social email" href="mailto:<?php echo $museum_email; ?>"><?php _e('E-mail','museomix'); ?></a>
									<?php
									endif;
								?></p><?php
								endif;?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>

		</div>
  <?php
  $community_news = $community_museums = false;

  if ($playgrounds || $community_museums || $museum_prototypes ||  $partners):
  ?>
  	<div class="et_pb_section et_section_regular sep_section" id="playgrounds">
			<?php if ($playgrounds): ?>
				<div class=" et_pb_row">
					<div class="et_pb_column et_pb_column_4_4  et_pb_column_7">
						<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left icecubes_title big_titles">
							<h2><?php _e('Playgrounds', 'museomix'); ?></h2>
							<?php
							$i=1;
							foreach($playgrounds as $playground): ?>
								<div class="playground_block">
									<div class="playground_title">
										<span class="playground_number"><?php echo $i; ?>/</span>&nbsp;<?php echo $playground['title']; ?>
									</div>
										<p><?php echo $playground['description']; ?></p>
										<?php
										$problems = false;
										if (isset($playground['problems'])):
											$problems = $playground['problems'];
										endif;
										if ($problems): ?>
										<div class="problems">
												<strong><?php _e('Problems','museomix'); ?></strong>
												<?php echo $problems; ?>
										</div>
										<?php
										endif;
										?>
								</div>
							<?php
							$i++;
							endforeach;
							 ?>
						</div>
					</div>
				</div>
				<div class=" et_pb_row">
					<div class="et_pb_column et_pb_column_4_4  et_pb_column_7">
						<div class="et_pb_module et-waypoint et_pb_image et_pb_animation_off  et_always_center_on_mobile et-animated separator">
							<img src="<?php echo get_stylesheet_directory_uri().'/assets/images/msx_sep.png'; ?>" alt="">
						</div>
						<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_center icecubes_title big_titles">
							<h2 id="location-edition"><?php echo sprintf( __('%1$s edition','museomix'), $edition->post_title); ?></h2>
							<?php if (!empty(strip_tags(get_the_content()))): ?>
								<div class="et_pb_text_align_left">
									<?php the_content(); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
				endif;
				if ($team):
				?>
				<div class=" et_pb_row tricolumn_block" id="team">
						<h2 class="yellow_sub_title"><?php _e('The team', 'museomix'); ?></h2>
						<div class="columns">
								<?php
								foreach($team as $member):
									$i=1;
									?>
									<div class="et_pb_column et_pb_column_1_3 hexa_block et_pb_column_<?php echo $i; ?> et_pb_line_<?php echo floor($i/3); ?>">
										<div class="hexagon">
											<?php
											 if ($member['photo']):?>
											 		<?php echo wp_get_attachment_image($member['photo']['ID'], 'hexa_image'); ?>
											<?php else: ?>
													<img src="<?php echo get_stylesheet_directory_uri().'/assets/images/picto_people.png'; ?>" />
											<?php endif; ?>
										</div>
										<div class="hexa_text">
											<div class="hexa_title">
												<?php echo $member['prenom'].' '.$member['nom_de_famille']; ?>
											</div>
											<?php
											$descriptif = $member['descriptif'];
											if ($descriptif): ?>
											<div class="hexa_desc">
												<?php echo $descriptif; ?>
											</div>
											<?php
											endif;
											?>
											<?php
											$email = $member['email'];
											if ($email): ?>
											<div class="hexa_desc">
												<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
											</div>
											<?php
											endif;
											?>
											<?php
											$twitter = $member['compte_twitter'];
											if ($twitter):
												$twitter_url = $twitter;
												if (strpos('twitter.com', $twitter)=== false):
													$twitter_url = 'https://twitter.com/'.$twitter;
												endif;
												?>
											<div class="hexa_desc">
												<a href="<?php echo $twitter_url; ?>">@<?php echo $twitter; ?></a>
											</div>
											<?php
											endif;
											?>
										</div>
									</div>
								<?php
								if ($i == 3):
									$i = 1;
								else:
									$i++;
								endif;
							endforeach;
							?>
						</div>
				</div>
				<?php
				endif; ?>
				<?php
				if ($resume || $gallery): ?>
					<div class="et_pb_row tricolumn_block">
						<h2 class="yellow_sub_title"><?php _e('Feedback on event','museomix'); ?></h2>
						<?php if ($resume): ?>
							<?php echo $resume; ?>
						<?php endif; ?>
						<?php
						if ($gallery): ?>
							<div class="columns">
								<?php
								$i=1;
								foreach($gallery as $image): ?>
									<div class="et_pb_column et_pb_column_1_3 gallery et_pb_column_<?php echo $i; ?> et_pb_line_<?php echo floor($i/3); ?>">
										<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left  ">
												<div class="news_image">
													<?php echo wp_get_attachment_image($image['ID'], 'news_thumbnail', false, array('class' => 'bottom_blue')); ?>
												</div>
												<?php if ($image['caption']): ?>
													<p class="caption"><?php echo $image['caption']; ?></p>
												<?php endif; ?>
										</div>
									</div>
								<?php
								if ($i == 3):
									$i = 1;
								else:
									$i++;
								endif;
								endforeach;
								?>
			        </div>
					<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php
				if ($museum_prototypes): ?>
					<div class=" et_pb_row tricolumn_block">
						<h2 class="yellow_sub_title"><?php _e('Prototypes','museomix'); ?></h2>
						<div class="columns">
		          <?php display_news($museum_prototypes); ?>
		        </div>
					</div>
				<?php endif; ?>
				<?php
				if ($partners): ?>
					<div class=" et_pb_row tricolumn_block">
						<h2 class="yellow_sub_title" name="#partners"><?php _e('Partners','museomix'); ?></h2>
						<div class="columns">
		          <?php display_news($partners); ?>
		        </div>
					</div>
				<?php endif; ?>
				</div>
			</div>
  	</div>
  <?php endif; ?>
</div> <!-- #main-content -->

<?php get_footer(); ?>
