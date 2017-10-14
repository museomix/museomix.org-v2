<?php namespace flow\tabs;

use flow\settings\FFGeneralSettings;
use flow\social\FFFeedUtils;

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
class FFSourcesTab implements LATab {
	public function __construct() {
	}

	public function id() {
		return 'sources-tab';
	}

	public function flaticon() {
		return 'flaticon-error';
	}

	public function title() {
		return 'Feeds';
	}

	public function includeOnce( $context ) {
		?>
		<script>
//			var feedStr = '<?php //echo json_encode($context['sources'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>//';
//			feedStr = feedStr.replace(/\n/g, "\\\\n").replace(/\r/g, "\\\\r").replace(/\t/g, "\\\\t").replace(/\\\'/g, "'")
//			var feeds = JSON.parse(feedStr);
			var feeds = <?php echo json_encode($context['sources'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			if (_.isArray(feeds)) feeds = {};
		</script>
		<div class="section-content" id="sources-cont" data-tab="sources-tab">
			<div class="section-sources" id="sources-list" data-view-mode="sources-list">
				<div class="section" id="feeds-list-section">
					<h1 class="desc-following"><span>List of feeds</span> <span class="admin-button green-button button-add">Create feed</span></h1>
					<p class="desc">Each feed can be connected to multiple streams. Cache for feed is being built immediately on creation. You can disable any feed and it will be disabled in all streams where it's connected. Feeds with errors are automatically disabled. <a class="ff-pseudo-link" href="#">Show only error feeds</a>.</p>
					<div id="feeds-view">
						<table class="feeds-list">
							<thead>
							<tr>
								<th></th>
								<th>Feed</th>
								<th>Status</th>
								<th>Settings</th>
								<th>Last update</th>
								<th>Enabled?</th>
								<th></th>
							</tr>
							</thead>
							<tbody id="feeds-list">
							<?php

							foreach ($context['sources'] as $feed) {
								$id = $feed['id'];
								$settings = '';

								$settingArr = array();
								if ($feed['type'] === 'rss') {
									if (isset($feed['channel-name']) && !empty($feed['channel-name'])) {
										$settingArr['content'] = $feed['channel-name'];
									} else {
										$settingArr['content'] = $feed['content'];
									}
								}
								else if (isset($feed['content'])) {
									$settingArr['content'] = $feed['content'];
								} else {
									if (isset($feed['category-name']) && !empty($feed['category-name'])) {
										$settingArr['content'] = $feed['category-name'];
									} else {
										$settingArr['content'] = $feed['wordpress-type'];
									}
								}
								if (isset($feed['timeline-type'])) $settingArr['timeline-type'] = $feed['timeline-type'];
								if (isset($feed['mod']) && $feed['mod'] !== 'nope') $settingArr['mod'] = $feed['mod'];

								foreach ($settingArr  as $key => $value ) {
									if (!empty($value)) {
										/*if (isset($_GET['debug'])){
											ini_set('xdebug.var_display_max_depth', '5');
											$value = is_array($value) ? var_dump2str($value) : $value;
											$settings .= '<span>' . $key . ':<span class="highlight">' . $value . '</span></span>';
										}
										else {*/

											$v = str_replace('-', ' ', stripslashes($value));
											$v = str_replace('_timeline', '', $v);
											$v = str_replace('http://', '', $v);
											$v = str_replace('https://', '', $v);
											$k = str_replace('timeline-', '', $key);
											$k = str_replace('-', ' ', ucfirst($k));
											if ($key === 'mod') $v = 'moderated';

											if ( strlen($v) > 20) {
												$v = substr( $v , 0, 20 ) . '...';
											}

											$settings .= '<span><span class="highlight">' . $v . '</span></span>';
//										}
									}
								}

								// it will be done via JS
								// $status = (isset($feed['status']) && $feed['status'] == 1) ? 'cache-status-ok' : 'cache-status-error';
								//$last_update = $feed['last_update'] == 0 ? '' : FFFeedUtils::classicStyleDate($feed['last_update']);
								$enabled = isset($feed['enabled']) ? ($feed['enabled'] === 'yep'? true : false) : true;

								$fc = $feed['cache_lifetime'];
								if ($fc == 5) {
									$int = 'Every 5 min';
								} else if ($fc == 30) {
									$int = 'Every 30 min';
								} else if ($fc == 60) {
									$int = 'Every hour';
								} else if ($fc == 360) {
									$int = 'Every 6 hours';
								} else if ($fc == 1440) {
									$int = 'Once a day';
								} else if ($fc == 10080) {
									$int = 'Once a week';
								}

								$feed['last_update'] = $feed['last_update'] === 'N/A' ? $feed['last_update'] : $feed['last_update'] . ' (' . $int . ')';

								echo
									'<tr data-uid="' . $id . '" data-network="'. $feed['type'] .'" class="' . ( $enabled ? 'feed-enabled' : '' ) . '">
										<td class="controls"><i class="flaticon-pen"></i> <i class="flaticon-copy"></i> <i class="flaticon-trash"></i></td>
										<td class="td-feed"><i class="flaticon-'. $feed['type'] .'"></i></td>
										<td class="td-status"><span class="cache-status-' . ($feed['status'] == 1 ? 'ok' : 'error') . '"></span></td>
										<td class="td-info">' . $settings . '</td>
										<td class="td-last-update">' . $feed['last_update'] . '</td>
										<td class="td-enabled"><label for="feed-enabled-' . $id .'"><input ' . ( $enabled ? 'checked' : '' ) . ' id="feed-enabled-' . $id .'" class="switcher" type="checkbox" name="feed-enabled-' . $id .'" value="yep"><div><div></div></div></label></td>
										<td class="td-more"><i class="flaticon-more-1"></i><ul class="feed-dropdown-menu"><li data-action="filter">Filter feed</li><li data-action="cache">Rebuild cache</li></ul></td>
									</tr>';
							}

							if (empty($context['sources'])) {
								echo '<tr class="empty-row"><td class="empty-cell" colspan="7">Please add at least one feed</td></tr>';
							}

							?>
							</tbody>
						</table>
						<div class="holder"></div>
						<div class="popup">
							<div class="section">
								<i class="popupclose flaticon-close-4"></i>
								<div class="networks-choice add-feed-step">
									<h1>Create feed to use in your streams.</h1>
									<p class="desc">Choose source and then set up what content you want to load from it.</p>
									<ul class="networks-list">
										<li class="network-twitter"
											data-network="twitter"
											data-network-name="Twitter">
											<i class="flaticon-twitter"></i>
										</li>
										<li class="network-facebook"
											data-network="facebook"
											data-network-name="Facebook">
											<i class="flaticon-facebook"></i>
										</li>
										<li class="network-instagram"
											data-network="instagram"
											data-network-name="Instagram">
											<i class="flaticon-instagram"></i>
										</li>
										<li class="network-youtube"
											data-network="youtube"
											data-network-name="YouTube">
											<i class="flaticon-youtube"></i>
										</li>
										<li class="network-pinterest"
											data-network="pinterest"
											data-network-name="Pinterest">
											<i class="flaticon-pinterest"></i>
										</li>
										<li class="network-linkedin"
											data-network="linkedin"
											data-network-name="LinkedIn">
											<i class="flaticon-linkedin"></i>
										</li>

										<li class="network-flickr"
											data-network="flickr"
											data-network-name="Flickr">
											<i class="flaticon-flickr"></i>
										</li>
										<li class="network-tumblr"
											data-network="tumblr"
											data-network-name="Tumblr"
											style="margin-right:0">
											<i class="flaticon-tumblr"></i>
										</li>
										<br>

										<li class="network-google"
											data-network="google"
											data-network-name="Google +">
											<i class="flaticon-google"></i>
										</li>
										<li class="network-vimeo"
											data-network="vimeo"
											data-network-name="Vimeo">
											<i class="flaticon-vimeo"></i>
										</li>
										<li class="network-wordpress"
											data-network="wordpress"
											data-network-name="WordPress">
											<i class="flaticon-wordpress"></i>
										</li>
										<li class="network-foursquare"
											data-network="foursquare"
											data-network-name="Foursquare">
											<i class="flaticon-foursquare"></i>
										</li>
										<li class="network-soundcloud"
											data-network="soundcloud"
											data-network-name="SoundCloud">
											<i class="flaticon-soundcloud"></i>
										</li>
										<li class="network-dribbble"
											data-network="dribbble"
											data-network-name="Dribbble">
											<i class="flaticon-dribbble"></i>
										</li>
										<li class="network-rss"
											data-network="rss"
											data-network-name="RSS"
											style="margin-right:0">
											<i class="flaticon-rss"></i>
										</li>
									</ul>
								</div>
								<div class="networks-content  add-feed-step">
									<div id="feed-views"></div>
									<div id="filter-views"></div>
									<p class="feed-popup-controls add">
										<span id="feed-sbmt-1"
											  class="admin-button green-button submit-button">Add feed</span><span
											class="space"></span><span class="admin-button grey-button button-go-back">Back to first step</span>
									</p>
									<p class="feed-popup-controls edit">
										<span id="feed-sbmt-2"
											  class="admin-button green-button submit-button">Save changes</span>
									</p>
									<p class="feed-popup-controls enable">
										<span id="feed-sbmt-3"
											  class="admin-button blue-button submit-button">Save & Enable</span>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include($context['root']  . 'views/footer.php'); ?>
		</div>
	<?php
	}
}