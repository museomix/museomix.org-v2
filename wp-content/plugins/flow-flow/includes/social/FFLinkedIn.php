<?php namespace flow\social;
if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */

class FFLinkedIn extends FFHttpRequestFeed {
	private $image;
	private $media;
	private $company;
	private $profileUrl;
	private $profileImageUrl;

	public function __construct( $type = null ) {
		if (is_null($type)) $type = 'linkedin';
		parent::__construct( $type );
	}

	/**
	 * Search company.
	 * http://stackoverflow.com.80bola.com/questions/17860616/search-company-api-linkedin
	 *
	 *
	 * @param FFGeneralSettings $options
	 * @param $feed
	 *
	 * @return mixed|void
	 */
	public function deferredInit( $options, $feed ) {
		$original = $options->original();
		$token = $original['linkedin_access_token'];
		$start = 0;
		$num = $this->getCount();
		$this->company = $feed->content;
		$event_type = '';
		if (isset($feed->{'event-type'}) && $feed->{'event-type'} != 'any'){
			$event_type = '&event-type=' . $feed->{'event-type'};
		}
		$this->url = "https://api.linkedin.com/v1/companies/{$this->company}/updates?oauth2_access_token={$token}&count={$num}&format=json";
		$this->profileUrl = "https://api.linkedin.com/v1/companies/{$this->company}:(id,name,logo-url,square-logo-url)?oauth2_access_token={$token}&format=json";

		$data = $this->getFeedData($this->profileUrl);
		if ( sizeof( $data['errors'] ) > 0 ) {
			$this->errors[] = array(
				'type'    => $this->getType(),
				'message' => $this->filterErrorMessage($data['errors']),
				'url' => $this->getUrl()
			);
		}
		if (isset($data['response'])){
			$profile = json_decode($data['response']);
			if (isset($profile->squareLogoUrl) && !empty($profile->squareLogoUrl)){
				$this->profileImageUrl = $profile->squareLogoUrl;
			}
			else if (isset($profile->logoUrl)) {
				$this->profileImageUrl = $profile->logoUrl;
			}
			else {
				$this->profileImageUrl = '';
			}
		}
	}

	protected function items( $request ) {
		$pxml = json_decode($request);
		if (isset($pxml->values)) {
			return $pxml->values;
		}
		return array();
	}

	protected function getId( $item ) {
		if (isset($item->updateContent->companyStatusUpdate)){
			return $item->updateContent->companyStatusUpdate->share->id;
		}
		elseif (isset($item->updateContent->companyJobUpdate)){
			return $item->updateContent->companyJobUpdate->job->id;
		}
	}

	protected function getHeader( $item ) {
		if (isset($item->updateContent->companyStatusUpdate)){
			return '';//$item->updateContent->companyStatusUpdate->share->comment;
		}
		elseif (isset($item->updateContent->companyJobUpdate)){
			return $item->updateContent->companyJobUpdate->job->position->title;
		}
	}

	protected function getScreenName( $item ) {
		return $item->updateContent->company->name;
	}

	protected function getProfileImage( $item ) {
		return $this->profileImageUrl;
	}

	protected function getSystemDate( $item ) {
		return (int) floor($item->timestamp/1000);
	}

	protected function getContent( $item ) {
		if (isset($item->updateContent->companyStatusUpdate)){
			$content = FFFeedUtils::wrapLinks($item->updateContent->companyStatusUpdate->share->comment);
			if (!empty($item->updateContent->companyStatusUpdate->share->content->description)){
				$content .= '<br><br><b>' . $item->updateContent->companyStatusUpdate->share->content->title . '</b>';
				$content .= '<br><br>' . $item->updateContent->companyStatusUpdate->share->content->description;
			}
			return $content;
		}
		elseif (isset($item->updateContent->companyJobUpdate)){
			$location = $item->updateContent->companyJobUpdate->job->locationDescription;
			return $location . '<br>' . $item->updateContent->companyJobUpdate->job->description;
		}
	}

	protected function getUserlink( $item ) {
		return 'https://www.linkedin.com/company/' . $this->company;
	}

	protected function getPermalink( $item ) {
		if (isset($item->updateContent->companyJobUpdate)){
			return $item->updateContent->companyJobUpdate->job->siteJobRequest->url;
		}
		//https://www.linkedin.com/hp/update/6254947980814618625
		if (false !== ($ar = explode('-', $item->updateKey))){
			return 'https://www.linkedin.com/hp/update/' . $ar[2];
		}
		return $this->getUserlink($item);
	}

	protected function showImage( $item ) {
		if (isset($item->updateContent->companyStatusUpdate->share->content)){
			$content = $item->updateContent->companyStatusUpdate->share->content;
			if (isset($content->submittedImageUrl)){
				$url = $content->submittedImageUrl;
				$this->image = $this->createImage($url);
				$this->media = $this->createMedia($url);
				return true;
			}
			else if (isset($content->submittedUrl)){
				$url = $content->submittedUrl;
				$this->image = $this->createImage($url);
				$this->media = $this->createMedia($url);
				return true;
			}
		}
		return false;
	}

	protected function getImage( $item ) {
		return $this->image;
	}

	protected function getMedia( $item ) {
		return $this->media;
	}

	protected function getAdditionalInfo( $item ) {
		$additional = parent::getAdditionalInfo( $item );
		$additional['likes']      = (string)@$item->numLikes;
		$additional['comments']   = (string)@$item->updateComments->{'_total'};
		return $additional;
	}
}