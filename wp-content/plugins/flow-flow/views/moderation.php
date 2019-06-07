<?php if ( ! defined( 'WPINC' ) ) die;
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
$arr = $context['streams'];
?>
<div class="section-content" data-tab="moderation-tab">
	<div class="section" id="moderation-settings">
		<h1 class="desc-following"><span>Moderation mode for streams</span></h1>
		<p class="desc">Set if stream posts need approval or not. Approve
			posts right on site pages. Users will see only approved posts.</p>
		<table>
			<thead>
			<tr>
				<th>Stream</th>
				<th>Moderation active?</th>
			</tr>
			</thead>
			<tbody>
			<?php

			foreach ($arr as $stream) {
				if (!isset($stream['id'])) continue;
				$id = $stream['id'];




				echo
					'<tr data-stream-id="' . $id . '">
							      <td class="td-name">' . (!empty($stream['name']) ? $stream['name'] : 'Unnamed') . '</td>
							      <td class="td-moder"></td>' .
					'</tr>';
			}

			if (empty($arr)) {
				echo '<tr><td class="empty-cell" colspan="6">Please add at least one stream</td></tr>';
			}

			?>
			</tbody>
		</table>
		<span id="moderation-sbmt" class='admin-button green-button submit-button'>Save Changes</span>

	</div>
</div>