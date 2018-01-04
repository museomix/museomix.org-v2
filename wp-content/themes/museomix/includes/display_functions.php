<?php
function display_news($data, $link_text = null, $display = true) {
	global $post;
	if (!$data):
		return;
	endif;

	if (!$link_text):
			$link_text = __('Read more', 'museomix');
	endif;

	$i=1;
	$j=1;
	$out = '';
	foreach($data as $item):
		setup_postdata($item);
		$post_thumbnail = null;
		if ($item->post_type == 'museomix'):
			$post_thumbnail = get_field('vignette_lieu', $item->ID);
		endif;
		if ($item->post_type == 'prototype'):
			if (is_integer(get_field('visuel_prototype', $item->ID)) && get_field('visuel_prototype', $item->ID)) {
				$post_thumbnail = get_field('visuel_prototype', $item->ID);
			} else {
				$post_thumbnail = (int)get_attachment_id_from_url(get_field('visuel_prototype', $item->ID));
			}
		endif;
		if ($item->post_type == 'sponsor'):
			$post_thumbnail = get_field('logo', $item->ID);
		endif;
		if (!$post_thumbnail):
			$post_thumbnail = get_the_post_thumbnail($item);
		endif;
		$link = get_the_permalink($item);
		if ($item->post_type == 'museomix'):
			$edition = get_field('edition', $item);
			$link = icl_get_home_url().__('editions','edition slug', 'museomix').'/'.$edition->post_title.'/'.$item->post_name;
		endif;
		
		$out .= '<div '.($item->post_type =='sponsor' ? ' style="min-height:300px" ' : '').' data-post_thumbnail='.$post_thumbnail.' class="et_pb_column et_pb_column_1_3  et_pb_column_'.$i.' et_pb_line_'.floor($i/3).' post_type-'.$item->post_type.'">
			<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_left '.(!in_array($item->post_type, array('museomix', 'prototype')) ? ' link_text ' : '').'">
					<div class="news_image">
						<a href="'.$link.'">';
		if ($item->post_type == 'museomix'):
			$out .='<div class="edition_logo">
									<div class="edition_word">'.__('Edition','museomix').'</div>
									<div class="edition_year">'.$edition->post_title.'</div>
								</div>';
								
		endif;
		if ($post_thumbnail):
			switch($item->post_type) {
				case 'sponsor':
					$out.=wp_get_attachment_image($post_thumbnail, 'sponsor_thumbnail', false, [
						'alt' => $item->post_title
					]);
					break;
				default:
					$out.=wp_get_attachment_image($post_thumbnail, 'news_thumbnail', false);
					break;
			}			
		else:
			$out .='<img src="'.get_stylesheet_directory_uri().'/assets/images/picto_news.png" />';
		endif;
		$out .='	</a>
				</div>
				<a href="'.$link.'">';
		if (in_array($item->post_type, array('museomix', 'prototype'))):
			$out .='<h3 style="text-align: center;">'.get_the_title($item).'</h3>';
		else:
			$museums = get_field('museum', $item);
			$museums_list = '';
			if ($museums):
				foreach($museums as $museum):
				$museum_object = get_post($museum);
				$museums_list[] = $museum_object->post_title;
				endforeach;
				$out .='<h3 style="text-align: center;">'.implode(',', $museums_list).'</h3>';
			endif;
		endif;
		$out .='</a>';
		if (!in_array($item->post_type, array('museomix', 'prototype','sponsor'))):
			$out .='<p style="text-align: left;">'.substr(get_the_excerpt($item->ID), 0, 100).'&hellip;</p>';
		endif;
		if (!in_array($item->post_type, array('museomix', 'prototype','sponsor'))):
			$out .= '<p style="text-align: left;">
						<a href="'.get_the_permalink($item).'" class="big-button bigteal">'.$link_text.'</a>
					</p>';
		endif;
		$out.='</div> <!-- .et_pb_text --></div>';
	if ($i == 3):
		$i = 1;
	else:
		$i++;
	endif;
	endforeach;
	if ($display):
		echo $out;
	else:
		return $out;
	endif;
}
