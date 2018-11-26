<div class="wrap">
	<?php $admin->display_title( "Media File Renamer" ); ?>

	<?php if ( $locked ): ?>
		<div class="updated">
			<p><?php _e( 'All the media files are now locked.', 'media-file-renamer' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( !$admin->is_registered() ): ?>
		<div class="updated">
			<p>
				<?php _e( '<b>The Pro version</b> of the plugin allows you to <b>rename based on the title of the post</b> (product or whatever else) you media is attached to, <b>rename manually</b>, use <b>numbered files</b> (by adding a counter if the filenames are similar), <b>sync the title with your ALT text</b>, a force rename (to repair a broken install), and, more importantly, <b>supports the developer</b> :) Thank you!<br /><br /><a class="button-primary" href="https://store.meowapps.com/" target="_blank">Get the Pro</a>', 'media-file-renamer' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<h2>Rename in Bulk</h2>

	<p>
		<?php _e( 'You might have noticed that some of your media are locked by the file renamer, others are unlocked. Automatically, the plugin locks the media you renamed manually. By default, they are unlocked. Here, you have the choice of rename all the media in your DB or only the ones which are unlocked (to keep the files you renamed manually). <span style="color: red; font-weight: bold;">Please backup your uploads folder + DB before using this.</span> If you don\'t know how, give a try to this: <a href="https://meow.click/blogvault" target="_blank">BlogVault</a>.', 'media-file-renamer' ); ?>
	</p>

	<div style='margin-top: 12px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>

		<a onclick='mfrh_rename_media(false)' id='mfrh_rename_all_images' class='button-primary'
			style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
			<?php echo sprintf( __( "Rename ALL [%d]", 'media-file-renamer' ), $all_media - $manual_media ); ?>
		</a>
		<a onclick='mfrh_rename_media(true)' id='mfrh_unlock_rename_all_images' class='button-primary'
			style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
			<?php echo sprintf( __( "Unlock ALL & Rename [%d]", 'media-file-renamer' ), $all_media ); ?>
		</a>
		<span style='margin-right: 5px; margin-left: 5px;'>|</span>
		<a href="?page=rename_media_files&mfrh_lockall" id='mfrh_lock_all_images' class='button-primary'
			style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
			<?php echo sprintf( __( "Lock ALL [%d]", 'media-file-renamer' ), $all_media ); ?>
		</a>
		<a href="?page=rename_media_files&mfrh_unlockall" id='mfrh_unblock_all_images' class='button-primary'
			style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
			<?php echo sprintf( __( "Unlock ALL [%d]", 'media-file-renamer' ), $all_media ); ?>
		</a>
		<a onclick='mfrh_undo_media()' id='mfrh_undo_all_images' class='button button-red'
			style='margin-right: 0px; float: right;'><span class="dashicons dashicons-undo" style="position: relative; top: 3px; left: -2px;"></span>
			<?php echo sprintf( __( "Undo ALL [%d]", 'media-file-renamer' ), $all_media ); ?>
		</a>
		<span id='mfrh_progression'></span>

		<?php if ( get_option( 'mfrh_flagging' ) ): ?>
			<?php if ($flagged > 0): ?>
				<a onclick='mfrh_rename_media(false)' id='mfrh_rename_dued_images' class='button-primary'>
					<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
				</a>
			<?php else: ?>
				<a id='mfrh_rename_dued_images' class='button-primary'>
					<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
				</a>
			<?php endif; ?>
		<?php endif; ?>

	</div>

	<h2>Rename 1 by 1</h2>
	<p><?php _e( 'If you want to rename the media this way, I recommend you to do it from the Media Library directly. If you think this "Scan All" is really handy, please tell me that you are using it on the forums. I am currently planning to remove it and moving the "Rename in Bulk" with the settings of File Renamer (to clean the WordPress UI).', 'media-file-renamer' ); ?></p>
	<table class='wp-list-table widefat fixed media' style='margin-top: 15px;'>
		<thead>
			<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
		</thead>
		<tfoot>
			<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
		</tfoot>
		<tbody>
			<?php
				if ( $checkFiles != null ) {
					foreach ( $checkFiles as $file ) {
						echo "<tr><td><a href='post.php?post=" . $file['post_id'] . "&action=edit'>" . ( $file['post_title'] == "" ? "(no title)" : $file['post_title'] ) . "</a></td>"
							. "<td>" . $file['current_filename'] . "</td>"
							. "<td>" . $file['desired_filename'] . "</td>";
						echo "<td>";
						$ui->generate_explanation( $file );
						echo "</td></tr>";
					}
				}
				else if ( isset( $_GET['mfrh_scancheck'] ) && ( $checkFiles == null || count( $checkFiles ) < 1 ) ) {
					?><tr><td colspan='4'><div style='width: 100%; margin-top: 15px; margin-bottom: 15px; text-align: center;'>
						<div style='margin-top: 15px;'><?php _e( 'There are no issues. Cool!<br />Let\'s go visit <a target="_blank" href=\'https://offbeatjapan.org\'>The Offbeat Guide of Japan</a> :)', 'media-file-renamer' ); ?></div>
					</div></td><?php
				}
				else if ( $checkFiles == null ) {
					?><tr><td colspan='4'><div style='width: 100%; text-align: center;'>
						<a class='button-primary' href="?page=rename_media_files&mfrh_scancheck" style='margin-top: 15px; margin-bottom: 15px;'><span class="dashicons dashicons-admin-generic" style="position: relative; top: 3px; left: -2px;"></span>
							<?php _e( "Scan All & Show Issues", 'media-file-renamer' ); ?>
						</a>
					</div></td><?php
				}
			?>
		</tbody>
	</table>

	<h2>Before / After</h2>
	<p><?php _e( 'This is useful if you wish to create redirections from your old filenames to your new ones. The CSV file generated by Media File Renamer is compatible with the import function of the <a href="https://wordpress.org/plugins/redirection/" target="_blank">Redirection</a> plugin. The redirections with slugs are already automatically and natively handled by WordPress.', 'media-file-renamer' ); ?></p>

	<div style='margin-top: 12px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>

		<a href="?page=rename_media_files&mfrh_beforeafter_filenames" class='button-primary' style='margin-right: 0px;'>
			<span class="dashicons dashicons-media-spreadsheet" style="position: relative; top: 3px; left: -2px;"></span>
			<?php esc_html_e( "Display Filenames", 'media-file-renamer' ); ?>
		</a>

		<a onclick="mfrh_export_table('#mfrh-before-after')" class='button-primary' style='margin-right: 0px; float: right;'>
			<span class="dashicons dashicons-arrow-down-alt" style="position: relative; top: 3px; left: -2px;"></span>
			<?php esc_html_e( "Export as CSV", 'media-file-renamer' ); ?>
		</a>

	</div>

	<table id='mfrh-before-after' class='wp-list-table widefat fixed media' style='margin-top: 15px;'>
		<thead>
			<tr><th><?php _e( 'Before', 'media-file-renamer' ); ?></th><th><?php _e( 'After', 'media-file-renamer' ); ?></th></tr>
		</thead>
		<tfoot>
			<tr><th><?php _e( 'Before', 'media-file-renamer' ); ?></th><th><?php _e( 'After', 'media-file-renamer' ); ?></th></tr>
		</tfoot>
		<tbody>
			<?php
				if ( isset( $_GET['mfrh_beforeafter_filenames'] ) || isset( $_GET['mfrh_beforeafter_slugs'] ) ) {
					$results = $wpdb->get_results( "
						SELECT m.post_id as ID, m.meta_value as original_filename, m2.meta_value as current_filename
						FROM {$wpdb->postmeta} m
						JOIN {$wpdb->postmeta} m2 on m2.post_id = m.post_id AND m2.meta_key = '_wp_attached_file'
						WHERE m.meta_key = '_original_filename'" );
					foreach ( $results as $row ) {
						$fullsize_path = wp_get_attachment_url( $row->ID );
						$parts = mfrh_pathinfo( $fullsize_path );
						$shorten_url = trailingslashit( $parts['dirname'] ) . $row->original_filename;
						if ( isset( $_GET['mfrh_beforeafter_filenames'] ) )
							echo "<tr><td>{$shorten_url}</td><td>$fullsize_path</td></tr>";
						else
							echo "<tr><td>{$row->original_slug}</td><td>{$row->current_slug}</td></tr>";
					}
				}
			?>
		</tbody>
	</table>
</div>
