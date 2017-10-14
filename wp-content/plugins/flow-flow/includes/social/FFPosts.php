<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;

use flow\settings\FFSettingsUtils;

/**
 * Flow-Flow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
class FFPosts extends FFBaseFeed {
    private $args;
    private $shortcodes;
    private $authors;
	private $profileImage;
	private $use_excerpt;

	public function __construct() {
		parent::__construct( 'posts' );
	}

	public function deferredInit($options, $feed){
		if ( isset( $feed->{'shortcodes'} ) ) {
			$this->shortcodes = $feed->{'shortcodes'};
		}
		$this->args = array(
			'numberposts'   => $this->getCount(),
			'post_status'   => 'publish',
            'has_password' => false
		);
		if ( isset( $feed->{'category-name'} ) ) {
			$this->args['category_name'] = $feed->{'category-name'};
		}
		if (isset($feed->{'slug'})) {
			$this->args['post_type'] = $feed->{'slug'};
		}
		$this->use_excerpt  = @FFSettingsUtils::YepNope2ClassicStyle($feed->{'use-excerpt'}, false);
		$this->profileImage = $this->context['plugin_url'] . '/' . $this->context['slug'] . '/assets/avatar_default.png';
	}

	public function onePagePosts(){
		$posts = wp_get_recent_posts($this->args);
		$result = array();
		foreach($posts as $item){
			$post = $this->parse($item);
			if ($this->isSuitablePost($post)) $result[$post->id] = $post;
		}

		return $result;
	}

	public function useCache() {
		return false;
	}

    private function parse($post){
	    $tc = new \stdClass();
		$tc->feed_id    = $this->id();
        $tc->id = (string)$post['ID'];
        $tc->type = $this->getType();
        $tc->header = $post['post_title'];
        $tc->nickname = $this->getAuthor($post['post_author'], 'nicename');
        $tc->screenname = trim($this->getAuthor($post['post_author'], 'user_full_name'));
        if (empty($tc->screenname)) $tc->screenname = get_bloginfo('name');
        $tc->system_timestamp = strtotime($post['post_date_gmt']);
	    $tc->text = $this->getText($post);
	    $userpic = get_avatar($post['post_author'], 80, '');
	    $tc->userpic =  (strpos($userpic,'avatar-default') !== false) ? $this->profileImage : FFFeedUtils::getUrlFromImg($userpic);
	    if (empty($tc->userpic)) $tc->userpic = $this->profileImage;
	    $tc->userlink = get_author_posts_url($post['post_author']);
	    $tc->permalink = get_permalink($post["ID"]);

	    if ( has_post_thumbnail($post["ID"]) ) {
			$thumb_id = get_post_thumbnail_id($post["ID"]);
			$thumb = wp_get_attachment_image_src($thumb_id, 'medium', true);
			$full = wp_get_attachment_image_src($thumb_id, 'thumbnail-size', true);
            $tc->img = $this->createImage($thumb[0], $thumb[1], $thumb[2]);
		    $tc->media = $this->createMedia($full[0], $full[1], $full[2]);
        }
	    $counter = wp_count_comments($post["ID"]);
	    @$tc->additional = array('comments' => (string)$counter->approved);
        return $tc;
    }

	private function getText( $post ) {
		$text = ($this->use_excerpt === true) ? $post['post_excerpt'] :  $post['post_content'];
		$text = ($this->shortcodes == 'strip') ? strip_shortcodes($this->removeVcShortcodes($text)) : do_shortcode($text);
		return $text;
	}

	private function removeVcShortcodes( $text ) {
		$patterns = "/\[[\/]?vc_[^\]]*\]/";
		$replacements = "";
		return preg_replace($patterns, $replacements, $text);
	}

	private function getAuthor( $author_id, $key ) {
		if ( ! isset( $this->authors[ $author_id ] ) ) {
			$this->authors[ $author_id ] = array(
				'nicename'       => (string) get_the_author_meta( 'nicename', $author_id ),
				'url'            => (string) get_the_author_meta( 'url', $author_id ),
				'user_full_name' => (string) get_the_author_meta( 'display_name', $author_id ),
			);
		}
		return $this->authors[ $author_id ][ $key ];
	}
}