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
$backups = $context['backups'];
?>
<div class="section-content" data-tab="backup-tab">
	<div class="section" id="backup-settings">
		<h1 class="desc-following">Snapshots management</h1>
		<p class="desc">Save and restore plugin data from specific point of time</p>
		<table id="backups">
			<thead><tr><th>Snapshot Date</th><th>Version</th><th>Actions</th></tr></thead>
			<tbody>

			<?php
			if (isset($backups)) {
				$count = count($backups);
				foreach ($backups as $backup) {
					$description = trim($backup->creation_time . ' ' . $backup->description);
					$version = $backup->version;
					if ($backup->outdated){
						$action = '<span class="admin-button grey-button delete_backup">Delete snapshot</span>';
					}
					else $action = '<span class="admin-button grey-button delete_backup">Delete snapshot</span><span class="space"></span><span class="admin-button grey-button restore_backup">Restore from this point</span>';
					echo '<tr backup-id="' . $backup->id . '"><td>' . $description . '</td><td>' . $version . '</td><td>' . $action . '</td></tr>';
				}
				if ($count == 0) {
					echo '<tr><td colspan="3">Please make at least one snapshot</td></tr>';
				}
			} else {
				echo '<tr><td colspan="3">Please deactivate/activate plugin to initialize snapshot database. Required only once.</td></tr>';
			}
			?>
			</tbody>
		</table>

		<span class='admin-button green-button create_backup'>Create new database snapshot</span>
	</div>
	<?php
		/** @noinspection PhpIncludeInspection */
		include($context['root']  . 'views/footer.php');
	?>

</div>
