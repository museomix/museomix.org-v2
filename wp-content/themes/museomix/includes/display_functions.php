<?php
function display_news($data, $link_text = null) {
	global $post;
	if (!$data):
		return;
	endif;

	if (!$link_text):
			$link_text = __('Read more', 'museomix');
	endif;

	$i=1;
	$j=1;
	foreach($data as $item):
		setup_postdata($item);
		$post_thumbnail = null;
		if ($item->post_type == 'museomix'):
			$post_thumbnail = get_field('vignette_lieu', $item->ID);
		endif;
		if ($item->post_type == 'prototype'):
			$post_thumbnail = (int)get_attachment_id_from_url(get_field('visuel_prototype', $item->ID));
		endif;
		if (!$post_thumbnail):
			$post_thumbnail = get_the_post_thumbnail($item);
		endif;
		$link = get_the_permalink($item);
		if ($item->post_type == 'museomix'):
			$edition = get_field('edition', $item);
			$link = icl_get_home_url().__('editions','edition slug', 'museomix').'/'.$edition->post_title.'/'.$item->post_name;
		endif;

		?>
			<div class="et_pb_column et_pb_column_1_3  et_pb_column_<?php echo $i; ?> et_pb_line_<?php echo floor($i/3); ?> <?php echo 'post_type-'.$item->post_type; ?>">
			<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left <?php echo (!in_array($item->post_type, array('museomix', 'prototype')) ? ' link_text ' : ''); ?> ">
					<div class="news_image">
						<a href="<?php echo $link; ?>">
							<?php if ($item->post_type == 'museomix'): ?>
								<div class="edition_logo">
									<div class="edition_word"><?php _e('Edition','museomix'); ?></div>
									<div class="edition_year"><?php echo $edition->post_title; ?></div>
								</div>
							<?php endif; ?>
						<?php if ($post_thumbnail): ?>
							<?php echo wp_get_attachment_image($post_thumbnail, 'news_thumbnail', false); ?>
						<?php else: ?>
							<img src="<?php echo get_stylesheet_directory_uri().'/assets/images/picto_news.png'; ?>" />
						<?php endif; ?>
					</a>
				</div>
				<a href="<?php echo $link; ?>">
					<?php if (in_array($item->post_type, array('museomix', 'prototype'))): ?>
						<h3 style="text-align: center;"><?php echo get_the_title($item); ?></h3>
					<?php else:
						$museums = get_field('museum', $item);
						$museums_list = '';
						if ($museums):
							foreach($museums as $museum):
								$museum_object = get_post($museum);
								$museums_list[] = $museum_object->post_title;
							endforeach;
						?>
						<h3 style="text-align: center;"><?php echo implode(',', $museums_list); ?></h3>
					<?php
					endif;

				endif; ?>
				</a>
				<?php if (!in_array($item->post_type, array('museomix', 'prototype'))): ?>
					<p style="text-align: left;"><?php echo substr(get_the_excerpt($item->ID), 0, 100); ?>&hellip;</p>
				<?php endif; ?>
				<?php if (!in_array($item->post_type, array('museomix', 'prototype'))): ?>
					<p style="text-align: left;">
						<a href="<?php echo get_the_permalink($item); ?>" class="big-button bigteal"><?php echo $link_text; ?></a>
					</p>
				<?php endif; ?>
		</div> <!-- .et_pb_text -->
	</div><?php
	if ($i == 3):
		$i = 1;
	else:
		$i++;
	endif;
	endforeach;
}
