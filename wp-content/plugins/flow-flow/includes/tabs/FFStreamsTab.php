<?php namespace flow\tabs;
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

class FFStreamsTab implements LATab{
	public function __construct() {
	}

	public function id() {
		return 'streams-tab';
	}

	public function flaticon() {
		return 'flaticon-ctrl-left';
	}

	public function title() {
		return 'Streams';
	}

	public function includeOnce( $context ) {
		$arr = $context['streams'];

		$export = array();
		foreach ($arr as $stream) {

			$item = array();

			foreach ($stream as $key => $value) {
                if ($key !== 'value') {
					if ($key === 'error') {
						$item['error'] = true;
					} else {
						if ($key === 'css') {
							$value = str_replace('"', "'", $value);
						}
						$item[$key] = $value;
					}
				}
			}

			$export[] = $item;
		}
//		debug
//		$export[0]['css'] = '';
//		$export[0]['heading'] = '';
		?>
		<script>
//			var str = '<?php //echo json_encode($export, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>//';
			/*str = str.replace(/\n/g, "\\\\n").replace(/\r/g, "\\\\r").replace(/\t/g, "\\\\t").replace(/\\\'/g, "'")
			var streams = JSON.parse(str, function(name, value) {
				//if (name === 'css') debugger
				return value
			});
			*/
			var streams = <?php echo json_encode($export, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
		</script>
		<div class="section-content" id="streams-cont" data-tab="streams-tab">
			<div class="section-stream" id="streams-list" data-view-mode="streams-list">

				<div class="section" id="streams-list-section">
					<h1 class="desc-following"><span>List of your streams</span> <span class="admin-button green-button button-add">create stream</span></h1>
					<p class="desc">Here is a list of your streams. Edit them to change styling or to add/remove social feeds. Status means all connected feeds are loaded or not.</p>
					<table>
						<thead>
						<tr>
							<th></th>
							<th>Stream</th>
							<th>Layout</th>
							<th>Feeds</th>
							<?php
							if (FF_USE_WP) echo '<th>Status</th><th>Shortcode</th>';
							else echo '<th>ID</th><th>Status</th>';
							?>
						</tr>
						</thead>
						<tbody>
						<?php

						foreach ($arr as $stream) {
							if (!isset($stream['id'])) continue;
							$id = $stream['id'];

							$status = $stream['status'] == 1 ? 'ok' : 'error';
							$additionalInfo = FF_USE_WP ?
								'<td><span class="cache-status-'. $status .'"></span></td><td><span class="shortcode">[ff id="' . $id . '"]</span><span class="shortcode-copy">Copy</span></td>' :
								'<td>' . $id . '</td><td><span class="cache-status-'. $status .'"></span></td>';

							if (isset($_REQUEST['debug']) && isset($stream['error'])) {
								$additionalInfo .= $stream['error'];
							}
							$info = '';
							if (isset($stream['feeds']) && !empty($stream['feeds'])) {
								$feeds = $stream['feeds'];
								if (is_array($feeds) || is_object($feeds)){
									foreach ( $feeds as $feed ) {
										$info = $info . '<i class="flaticon-' . $feed['type'] . '"></i>';
									}
								}
							}


							echo
								'<tr data-stream-id="' . $id . '">
							      <td class="controls"><div class="loader-wrapper"><div class="throbber-loader"></div></div><i class="flaticon-pen"></i> <i class="flaticon-copy"></i> <i class="flaticon-trash"></i></td>
							      <td class="td-name">' . (!empty($stream['name']) ? stripslashes($stream['name']) : 'Unnamed') . '</td>
							      <td class="td-type">' . (isset($stream['layout']) ? '<span class="highlight">' . $stream['layout'] . '</span>': '-') . '</td>
							      <td class="td-feed">' . (empty($info) ? '-' : $info) . '</td>'
								. $additionalInfo .
								'</tr>';
						}

						if (empty($arr)) {
							echo '<tr class="empty-row"><td class="empty-cell" colspan="6">Please add at least one stream</td></tr>';
						}

						?>
						</tbody>
					</table>
				</div>
                <div class="section rating-promo">
                    <div class="fb-wrapper"><div class="fb-page" data-href="https://www.facebook.com/looksawesooome/" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/looksawesooome/"><a href="https://www.facebook.com/looksawesooome/">Looks Awesome</a></blockquote></div></div></div>
                    <h1 class="desc-following"><span>Help plugin to grow</span></h1>
                    <p class="">A lot of users only think to review Flow-Flow when something goes wrong while many more people use it satisfactory. Don't let this go unnoticed. If you find Flow-Flow useful please leave your honest rating and review on plugins <a href="http://codecanyon.net/downloads" target="_blank">Downloads page</a> to help Flow-Flow grow and endorse its further development!</p>
                </div>
			</div>
		</div>
		<?php
	}
} 