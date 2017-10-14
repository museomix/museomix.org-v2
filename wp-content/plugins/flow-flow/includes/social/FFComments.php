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
class FFComments extends FFBaseFeed{
    private $authors;
	private $profileImage;
	/** @var array */
	private $args;
	/** @var string */
	private $postTitle;

	public function __construct() {
		parent::__construct( 'comments' );
	}

	public function deferredInit($options, $feed) {
		$post_id = $feed->{'post-id'};
		$show_post_title = @FFSettingsUtils::YepNope2ClassicStyle($feed->{'include-post-title'});
		$number = $this->getCount();
		$this->args = array(
			'post_id'       => $post_id,
			'number'        => $number,
			'status'        => 'approve',
			'post_status'   => 'publish'
		);
	    $this->profileImage = $this->context['plugin_url'] . '/' . $this->context['slug'] . '/assets/avatar_default.png';
		$this->postTitle = ($show_post_title && !empty($post_id)) ? get_the_title($post_id) : '';
    }

    public function onePagePosts(){
        $comments = get_comments(apply_filters( 'widget_comments_args',  $this->args));
        $result = array();
        foreach ($comments as $comment){
	        $post = $this->parse($comment);
	        if ($this->isSuitablePost($post)) $result[$post->id] = $post;
        }
        return $result;
    }

    public function useCache(){
        return false;
    }

    private function parse($comment){
        $tc = new \stdClass();
	    $tc->feed_id = $this->id();
        $tc->id = (string)$comment->comment_ID;
	    $tc->header = $this->postTitle;
        $tc->type = $this->getType();
        $tc->nickname = $this->getAuthor($comment->user_id, 'nicename');
        $tc->screenname = trim($this->getAuthor($comment->user_id, 'user_full_name'));
        if (empty($tc->screenname)) $tc->screenname = (string)$comment->comment_author;
        $tc->system_timestamp = strtotime($comment->comment_date);
        $tc->text = $comment->comment_content;
	    $userpic = get_avatar($comment->user_id, 80, '');
	    $tc->userpic =  (strpos($userpic,'avatar-default') !== false) ? $this->profileImage : FFFeedUtils::getUrlFromImg($userpic);
	    $tc->userlink = $this->getCommentAuthorProfileLink($comment);
	    $tc->permalink = get_comment_link($comment->comment_ID);
        return $tc;
    }

    private function getAuthor($author_id, $key){
        if (!isset($this->authors[$author_id])){
            $this->authors[$author_id] = array(
                'nicename' => (string)get_the_author_meta('nicename', $author_id),
                'user_full_name' => (string)get_the_author_meta('display_name', $author_id),
            );
        }
        return $this->authors[$author_id][$key];
    }

	private function getCommentAuthorProfileLink($comment){
		$userlink = '';
		if (array_key_exists('userpro', $GLOBALS)){
			global $userpro;
			$userlink = $userpro->permalink($comment->user_id);
		}

		if (empty($userlink)){
			$id = $comment->user_id;
			if ($id == 0) {
				/* Unregistered commenter */
				$url = get_comment_author_url( $id );
				$author = get_comment_author( $id );
				$userlink = ( empty( $url ) || 'http://' == $url ) ? $author : $url;
			}
			else{
				/* Registered Commenter */
				/** @var \WP_User*/
				$user = get_userdata($id);
				$authorID = $user->ID;
				$authorURL = $user->get('user_url');
				$authorLevel = $user->get('user_level');

				/* Check if they have edit posts capabilities & is author or higher */
				if ($authorLevel > 1 && user_can($authorID,'edit_posts') == true && count_user_posts($authorID) > 0) {
					$userlink =  home_url() . '/?author=' . $authorID;
				} else {
					$userlink = ( empty( $authorURL ) || 'http://' == $authorURL ) ? '' : $authorURL;
				}
			}
		}
		return $userlink;
	}
}