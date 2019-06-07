<div class='wrap'>

	<?php
		echo $admin->display_title( "Media Cleaner" );
		$posts_per_page = get_option( 'wpmc_results_per_page', 20 );
		$view = isset ( $_GET[ 'view' ] ) ? sanitize_text_field( $_GET[ 'view' ] ) : "issues";
		$paged = isset ( $_GET[ 'paged' ] ) ? sanitize_text_field( $_GET[ 'paged' ] ) : 1;
		$reset = isset ( $_GET[ 'reset' ] ) ? $_GET[ 'reset' ] : 0;
		if ( $reset ) {
			wpmc_reset();
			$core->reset_issues();
		}
		$s = isset ( $_GET[ 's' ] ) ? sanitize_text_field( $_GET[ 's' ] ) : null;
		$table_name = $wpdb->prefix . "mclean_scan";
		$issues_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ignored = 0 AND deleted = 0" );
		$total_size = $wpdb->get_var( "SELECT SUM(size) FROM $table_name WHERE ignored = 0 AND deleted = 0" );
		$trash_total_size = $wpdb->get_var( "SELECT SUM(size) FROM $table_name WHERE ignored = 0 AND deleted = 1" );
		$ignored_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ignored = 1" );
		$deleted_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE deleted = 1" );

		if ( $view == 'deleted' ) {
			$items_count = $deleted_count;
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT id, type, postId, path, size, ignored, deleted, issue
				FROM $table_name WHERE ignored = 0 AND deleted = 1 AND path LIKE %s
				ORDER BY path, time
				DESC LIMIT %d, %d", '%' . $s . '%', ( $paged - 1 ) * $posts_per_page, $posts_per_page ), OBJECT );
		}
		else if ( $view == 'ignored' ) {
			$items_count = $ignored_count;
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT id, type, postId, path, size, ignored, deleted, issue
				FROM $table_name
				WHERE ignored = 1 AND deleted = 0 AND path LIKE %s
				ORDER BY path, time
				DESC LIMIT %d, %d", '%' . $s . '%', ( $paged - 1 ) * $posts_per_page, $posts_per_page ), OBJECT );
		}
		else {
			$items_count = $issues_count;
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT id, type, postId, path, size, ignored, deleted, issue
				FROM $table_name
				WHERE ignored = 0 AND deleted = 0  AND path LIKE %s
				ORDER BY path, time
				DESC LIMIT %d, %d", '%' . $s . '%', ( $paged - 1 ) * $posts_per_page, $posts_per_page ), OBJECT );
		}
	?>

	<style>
		#wpmc-pages {
			float: right;
			position: relative;
			top: 12px;
		}

		#wpmc-pages a {
			text-decoration: none;
			border: 1px solid black;
			padding: 2px 5px;
			border-radius: 4px;
			background: #E9E9E9;
			color: lightslategrey;
			border-color: #BEBEBE;
		}

		#wpmc-pages .current {
			font-weight: bold;
		}
	</style>

	<div id="wpmc_actions" style='margin-top: 0px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>

		<!-- SCAN -->
		<?php if ( $view != 'deleted' ): ?>
		<a id='wpmc_scan' class='button-primary' style='float: left;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-search"></span><?php _e("Start Scan", 'media-cleaner'); ?></a>
		<?php endif; ?>

		<!-- PAUSE -->
		<?php if ( $view != 'deleted' ): ?>
		<a id='wpmc_pause' onclick='wpmc_pause()' class='button' style='float: left; margin-left: 5px; display: none;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-pause"></span><?php _e("Pause", 'media-cleaner'); ?></a>
		<?php endif; ?>

		<!-- DELETE SELECTED -->
		<a id='wpmc_delete' class='button exclusive' style='float: left; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-no"></span><?php _e("Delete", 'media-cleaner'); ?></a>
		<?php if ( $view == 'deleted' ): ?>
		<a id='wpmc_recover' onclick='wpmc_recover()' class='button-secondary' style='float: left; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-repeat"></span><?php _e( "Recover", 'media-cleaner' ); ?></a>
		<?php endif; ?>

		<!-- IGNORE SELECTED -->
		<a id='wpmc_ignore' class='button exclusive' style='float: left; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-yes"></span><?php
			if ( $view == 'ignored' )
				_e( "Mark as Issue", 'media-cleaner' );
			else
				_e( "Ignore", 'media-cleaner' );
		?>
		</a>

		<!-- RESET -->
		<?php if ( $view != 'deleted' ): ?>
		<a id='wpmc_reset' href='?page=media-cleaner&reset=1' class='button-primary' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-sos"></span><?php _e("Reset", 'media-cleaner'); ?></a>
		<?php endif; ?>

		<!-- DELETE ALL -->
		<?php if ( $view == 'deleted' ): // i ?>
		<a id='wpmc_recover_all' class='button-primary exclusive' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-repeat"></span><?php _e("Recover all", 'media-cleaner'); ?></a>
		<a id='wpmc_empty_trash' class='button button-red exclusive' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-trash"></span><?php _e("Empty trash", 'media-cleaner'); ?></a>
		<?php else: // i ?>
		<?php if ( $s ): // ii ?>
		<a id='wpmc_delete_all' class='button button-red exclusive' data-filter="<?php echo esc_attr( $s ); ?>" style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-trash"></span><?php _e("Delete all search results", 'media-cleaner'); ?></a>
		<?php else: // ii ?>
		<a id='wpmc_delete_all' class='button button-red exclusive' style='float: right; margin-left: 5px;'><span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-trash"></span><?php _e("Delete all", 'media-cleaner'); ?></a>
		<?php endif; // ii ?>
		<?php endif; // i ?>

		<form id="posts-filter" action="upload.php" method="get" style='float: right;'>
			<p class="search-box" style='margin-left: 5px; float: left;'>
				<input type="search" name="s" class="exclusive" style="width: 120px;" value="<?php echo $s ? esc_attr( $s ) : ""; ?>">
				<input type="hidden" name="page" value="media-cleaner">
				<input type="hidden" name="view" value="<?php echo $view; ?>">
				<input type="hidden" name="paged" value="1">
				<input type="submit" class="button exclusive" value="<?php _e( 'Search', 'media-cleaner' ) ?>"><span style='border-right: #A2A2A2 solid 1px; margin-left: 5px; margin-right: 3px;'>&nbsp;</span>
			</p>
		</form>

		<!-- PROGRESS -->
		<span style='margin-left: 12px; font-size: 15px; top: 5px; position: relative; color: #747474;' id='wpmc_progression'></span>

	</div>

	<p>
		<?php
			$method = "";
			$table_scan = $wpdb->prefix . "mclean_scan";
			$table_refs = $wpdb->prefix . "mclean_refs";
			if ( strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table_scan'" ) ) != strtolower( $table_scan ) ||
				strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table_refs'" ) ) != strtolower( $table_refs ) ) {
					_e( "<div class='notice notice-error'><p><b>The database is not ready for Media Cleaner. The scan will not work.</b> Click on the <b>Reset</b> button, it re-creates the tables required by Media Cleaner. If this message still appear, contact the support.</p></div>", 'media-cleaner' );
			}
			else {
				$method = get_option( 'wpmc_method', 'media' );
				if ( !$admin->is_registered() )
					$method = 'media';

				$hide_warning = get_option( 'wpmc_hide_warning', false );

				if ( !$hide_warning ) {
					_e( "<div class='notice notice-warning'><p><b style='color: red;'>Important.</b> <b>Backup your DB and your /uploads directory before using Media Cleaner. </b> The deleted files will be temporarily moved to the <b>uploads/wpmc-trash</b> directory. After testing your website, you can check the <a href='?page=media-cleaner&s&view=deleted'>trash</a> to either empty it or recover the media and files. The Media Cleaner does its best to be safe to use. However, WordPress being a very dynamic and pluggable system, it is impossible to predict all the situations in which your files are used. <b style='color: red;'>Again, please backup!</b> If you don't know how, give a try to this: <a href='https://meow.click/blogvault' target='_blank'>BlogVault</a>. <br /><br /><b style='color: red;'>Be thoughtful.</b> Don't blame Media Cleaner if it deleted too many or not enough of your files. It makes cleaning possible and this task is only possible this way; don't post a bad review because it broke your install. <b>If you have a proper backup, there is no risk</b>. Sorry for the lengthy message, but better be safe than sorry. You can disable this big warning in the options if you have a Pro license. Make sure you read this warning twice. Media Cleaner is awesome and always getting better so I hope you will enjoy it. Thank you :)</p></div>", 'media-cleaner' );
				}

				if ( !MEDIA_TRASH ) {
					_e( "<div class='notice notice-warning'><p>The trash for the Media Library is disabled. Any media removed by the plugin will be <b>permanently deleted</b>. To enable it, modify your wp-config.php file and add this line (preferably at the top): <b>define( 'MEDIA_TRASH', true );</b></p></div>", 'media-cleaner' );
				}
			}

			if ( !$admin->is_registered() ) {
				echo "<div class='notice notice-info'><p>";
				_e( "<b>This version is not Pro.</b> This plugin is a lot of work so please consider <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a> in order to receive support and to contribute in the evolution of it. Also, <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a> version will also give you the option <b>to scan the physical files in your /uploads folder</b> and extra checks for the common Page Builders.", 'media-cleaner' );
				echo "</p></div>";

				if ( class_exists( 'ACF' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>ACF has been detected</b>. The free version might detect the files used by ACF correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

				if ( function_exists( '_et_core_find_latest' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>Divi has been detected</b>. The free version might detect the files used by Divi correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

				if ( class_exists( 'Vc_Manager' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>Visual Composer has been detected</b>. The free version might detect the files used by Visual Composer correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

				if ( function_exists( 'fusion_builder_map' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>Fusion Builder has been detected</b>. The free version might detect the files used by Fusion Builder correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

				if ( class_exists( 'FLBuilderModel' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>Beaver Builder has been detected</b>. The free version might detect the files used by Beaver Builder correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

				if ( function_exists( 'elementor_load_plugin_textdomain' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>Elementor has been detected</b>. The free version might detect the files used by Elementor correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

				if ( class_exists( 'SiteOrigin_Panels' ) ) {
					echo "<div class='notice notice-warning'><p>";
					_e( "<b>SiteOrigin Page Builder has been detected</b>. The free version might detect the files used by SiteOrigin Page Builder correctly, but its full support is only available in <a target='_blank' href='//meowapps.com/media-cleaner'>Media Cleaner Pro</a>.", 'media-cleaner' );
					echo "</p></div>";
				}

			}

			$anychecks = get_option( 'wpmc_posts', false ) || get_option( 'wpmc_postmeta', false ) || get_option( 'wpmc_widgets', false );
			$check_library = get_option(' wpmc_media_library', true );

			if ( $method == 'media' ) {
				if ( !$anychecks )
					_e( "<div class='error'><p>Media Cleaner will analyze your Media Library. However, There is <b>NOTHING MARKED TO BE CHECKED</b> in the Settings. Media Cleaner will therefore run a special scan: <b>only the broken medias will be detected as issues.</b></p></div>", 'media-cleaner' );
				else
					_e( "<div class='notice notice-success'><p>Media Cleaner will analyze your Media Library.</p></div>", 'media-cleaner' );
			}
			else if ( $method == 'files' ) {
				if ( !$anychecks && !$check_library )
					_e( "<div class='error'><p>Media Cleaner will analyze your Filesystem. However, There is <b>NOTHING MARKED TO BE CHECKED</b> in the Settings. If you scan now, all the files will be detected as <b>NOT USED</b>.</p></div>", 'media-cleaner' );
				else
					_e( "<div class='notice notice-success'><p>Media Cleaner will analyze your Filesystem.</p></div>", 'media-cleaner' );
			}

			echo sprintf( __( 'There are <b>%s issue(s)</b> with your files, accounting for <b>%s MB</b>. Your trash contains <b>%s MB.</b>', 'media-cleaner' ), number_format( $issues_count, 0 ), number_format( $total_size / 1000000, 2 ), number_format( $trash_total_size / 1000000, 2 ) );
		?>
	</p>

	<div id='wpmc-pages'>
	<?php
	echo paginate_links(array(
		'base' => '?page=media-cleaner&s=' . urlencode($s) . '&view=' . $view . '%_%',
		'current' => $paged,
		'format' => '&paged=%#%',
		'total' => ceil( $items_count / $posts_per_page ),
		'prev_next' => false
	));
	?>
	</div>

	<ul class="subsubsub">
		<li class="all"><a <?php if ( $view == 'issues' ) echo "class='current'"; ?> href='?page=media-cleaner&s=<?php echo $s; ?>&view=issues'><?php _e( "Issues", 'media-cleaner' ); ?></a><span class="count">(<?php echo $issues_count; ?>)</span></li> |
		<li class="all"><a <?php if ( $view == 'ignored' ) echo "class='current'"; ?> href='?page=media-cleaner&s=<?php echo $s; ?>&view=ignored'><?php _e( "Ignored", 'media-cleaner' ); ?></a><span class="count">(<?php echo $ignored_count; ?>)</span></li> |
		<li class="all"><a <?php if ( $view == 'deleted' ) echo "class='current'"; ?> href='?page=media-cleaner&s=<?php echo $s; ?>&view=deleted'><?php _e( "Trash", 'media-cleaner' ); ?></a><span class="count">(<?php echo $deleted_count; ?>)</span></li>
	</ul>

	<table id='wpmc-table' class='wp-list-table widefat fixed media'>

		<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input id="wpmc-cb-select-all" type="checkbox"></th>
				<?php if ( !get_option( 'wpmc_hide_thumbnails', false ) ): ?>
				<th style='width: 64px;'><?php _e( 'Thumb', 'media-cleaner' ) ?></th>
				<?php endif; ?>
				<th style='width: 50px;'><?php _e( 'Type', 'media-cleaner' ) ?></th>
				<th style='width: 80px;'><?php _e( 'Origin', 'media-cleaner' ) ?></th>

				<?php if ( !empty( $wplr ) ):  ?>
					<th style='width: 70px;'><?php _e( 'LR ID', 'media-cleaner' ) ?></th>
				<?php endif; ?>

				<th><?php _e( 'Path', 'media-cleaner' ) ?></th>
				<th style='width: 220px;'><?php _e( 'Issue', 'media-cleaner' ) ?></th>
				<th style='width: 80px; text-align: right;'><?php _e( 'Size', 'media-cleaner' ) ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
				foreach ( $items as $issue ) {
					$regex = "^(.*)(\\s\\(\\+.*)$";
					$issue->path = preg_replace( '/' .$regex . '/i', '$1', $issue->path );
			?>
			<tr>
				<td><input type="checkbox" name="id" value="<?php echo $issue->id ?>"></td>
				<?php if ( !get_option( 'wpmc_hide_thumbnails', false ) ): ?>
				<td>
					<?php
						if ( $issue->deleted == 0 ) {
							if ( $issue	->type == 0 ) {
								// FILE
								$upload_dir = wp_upload_dir();
								$url = htmlspecialchars( $upload_dir['baseurl'] . '/' . $issue->path, ENT_QUOTES );
								echo "<a target='_blank' href='" . $url .
									"'><img style='max-width: 48px; max-height: 48px;' src='" . $url . "' /></a>";
							}
							else {
								// MEDIA
								$file = get_attached_file( $issue->postId );
								if ( file_exists( $file ) ) {
									$attachmentsrc = wp_get_attachment_image_src( $issue->postId, 'thumbnail' );
									if ( empty( $attachmentsrc ) )
										echo '<span class="dashicons dashicons-no-alt"></span>';
									else {
										$attachmentsrc_clean = htmlspecialchars( $attachmentsrc[0], ENT_QUOTES );
										echo "<a target='_blank' href='" . $attachmentsrc_clean .
											"'><img style='max-width: 48px; max-height: 48px;' src='" .
											$attachmentsrc_clean . "' />";
									}
								}
								else {
									echo '<span class="dashicons dashicons-no-alt"></span>';
								}
							}
						}
						if ( $issue->deleted == 1 ) {
							$upload_dir = wp_upload_dir();
							$url = htmlspecialchars( $upload_dir['baseurl'] . '/wpmc-trash/' . $issue->path, ENT_QUOTES );
							echo "<a target='_blank' href='" . $url .
								"'><img style='max-width: 48px; max-height: 48px;' src='" . $url . "' /></a>";
						}
					?>
				</td>
				<?php endif; ?>
				<td><?php echo $issue->type == 0 ? 'FILE' : 'MEDIA'; ?></td>
				<td><?php echo $issue->type == 0 ? 'Filesystem' : ("<a href='post.php?post=" .
					$issue->postId . "&action=edit'>ID " . $issue->postId . "</a>"); ?></td>
				<?php if ( !empty( $wplr ) ) { $info = $wplr->get_sync_info( $issue->postId ); ?>
					<td style='width: 70px;'><?php echo ( !empty( $info ) && $info->lr_id ? $info->lr_id : "" ); ?></td>
				<?php } ?>
				<td><?php echo stripslashes( $issue->path ); ?></td>
				<td><?php $core->echo_issue( $issue->issue ); ?></td>
				<td style='text-align: right;'><?php echo number_format( $issue->size / 1000, 2 ); ?> KB</td>
			</tr>
			<?php } ?>
		</tbody>

		<tfoot>
			<tr><th></th>
			<?php if ( !get_option( 'hide_thumbnails', false ) ): ?>
			<th></th>
			<?php endif; ?>
			<th><?php _e( 'Type', 'media-cleaner' ) ?></th><th><?php _e( 'Origin', 'media-cleaner' ) ?></th>
			<?php if ( !empty( $wplr ) ):  ?>
				<th style='width: 70px;'><?php _e( 'LR ID', 'media-cleaner' ) ?></th>
			<?php endif; ?>
			<th><?php _e( 'Path', 'media-cleaner' ) ?></th><th><?php _e( 'Issue', 'media-cleaner' ) ?></th><th style='width: 80px; text-align: right;'><?php _e( 'Size', 'media-cleaner' ) ?></th></tr>
		</tfoot>

	</table>
</div>

<div id="wpmc-dialog" class="hidden" style="max-width:800px"></div>

<div id="wpmc-error-dialog" class="hidden" style="max-width:800px">
	<h3><!-- The content will be inserted by JS --></h3>
	<p>Please check your logs.<br>Do you want to <a href="#" class="retry">try again</a>, or <a href="#" class="skip">skip this entry</a>?</p>
</div>
