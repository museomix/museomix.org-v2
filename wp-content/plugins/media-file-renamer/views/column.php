<?php
/**
 * @param Meow_MFRH_UI $ui
 * @param Meow_MFRH_Core $core
 * @param Meow_MFRH_Admin $admin
 * @param int $id
 */

$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : ""; // What for?
$paged = isset( $_GET['paged'] ) ? ( '&paged=' . $_GET['paged'] ) : "";

$file = array (); // Various stats of the attachment
$needsRename = $core->check_attachment( get_post( $id, ARRAY_A ), $file );
?>
<input type="hidden" data-name="id" value="<?php echo $id; ?>">

<?php // Quick Renamer ?>
<?php $filename = mfrh_basename( get_attached_file( $id ) ); ?>
<?php $disabled = !( $admin->is_registered() && apply_filters( 'mfrh_manual', false ) ); ?>
<input type="text" data-name="filename" value="<?php esc_attr_e( $filename ); ?>" autocomplete="off" data-origin="<?php esc_attr_e( $filename ); ?>"<?php if ($disabled) echo ' readonly'; ?>>
<?php if ( isset( $file['desired_filename'] ) ): // i ?>
<input type="text" data-name="recommended-filename" value="<?php esc_attr_e( $file['desired_filename'] ); ?>" readonly>
<?php endif; // i ?>
<a href="#" class="button button-primary rename hidden">
	<span class="label dashicons dashicons-edit"></span>
</a>

<?php // Locked media ?>
<?php if ( $locked = get_post_meta( $id, '_manual_file_renaming', true ) ): // i ?>
<div class="icons-wrap">
	<span title="<?php _e( 'Manually renamed.', 'media-file-renamer' ); ?>" class="dashicons dashicons-yes"></span>
	<a title="<?php _e( 'Locked to manual only. Click to unlock it.', 'media-file-renamer' ); ?>" href="?<?php echo $page . '&mfrh_unlock=' . $id . $paged; ?>" class="unlock dashicons dashicons-lock"></a>
</div>

<?php // Media that needs renaming ?>
<?php elseif ( $needsRename ): // i ?>
<?php static $previous = array(); // This doesn't work ?>
<?php if ( $file['post_title'] == "" ): // ii ?>
<a class="button-primary" href="post.php?post=<?php echo $id . '&action=edit'; ?>"><?php _e( 'Edit Media', 'media-file-renamer' ); ?></a><br />
<small><?php _e( 'This title cannot be used for a filename.', 'media-file-renamer' ); ?></small>
<?php elseif ( $file['desired_filename_exists'] ): // ii ?>
<a class="button-primary" href="post.php?post=<?php echo $id . '&action=edit'; ?>"><?php _e( 'Edit Media', 'media-file-renamer' ); ?></a><br />
<div style="line-height: 12px; font-size: 10px; margin-top: 5px;">
	<?php _e( 'The ideal filename already exists. If you would like to use a count and rename it, enable the <b>Numbered Files</b> option in the plugin settings.', 'media-file-renamer' ); ?>
</div>
<?php else: // ii ?>
<?php
$mfrh_scancheck = ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) ) ? '&mfrh_scancheck' : '';
$mfrh_to_rename = ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) ? '&to_rename=1' : '';
$modify_url = "post.php?post=" . $id . "&action=edit";

// This doesn't work
$isNew = true;
if ( in_array( $file['desired_filename'], $previous ) )
	$isNew = false;
else
	array_push( $previous, $file['desired_filename'] );
?>
<a class="button button-primary auto-rename" href="?<?php echo $page . $mfrh_scancheck . $mfrh_to_rename . '&mfrh_rename=' . $id; ?>">
	<span class="label"><?php _e( 'Auto', 'media-file-renamer' ); ?></span>
</a>
<div class="icons-wrap">
	<a title="<?php _e( 'Click to lock it to manual only.', 'media-file-renamer' ); ?>" href="?<?php echo $page . '&mfrh_lock=' . $id; ?>" class="lock dashicons dashicons-unlock"></a>
</div>
<?php if ( $file['case_issue'] ): // iii ?>
<div style="line-height: 12px; font-size: 10px; margin-top: 5px;">
	<?php printf( __( 'Rename in lowercase, to %s. You can also <a href="%s">edit this media</a>.', 'media-file-renamer' ), $file['desired_filename'], $modify_url ); ?>
</div>
<?php else: // iii ?>
<div style="line-height: 12px; font-size: 10px; margin-top: 5px;">
	<?php printf( __( 'Rename to %s. You can also <a href="%s">EDIT THIS MEDIA</a>.', 'media-file-renamer' ), $file['desired_filename'], $modify_url ); ?>
</div>
<?php endif; // iii ?>
<?php if ( !$isNew ): // iii ?>
<div style="line-height: 12px; font-size: 10px; margin-top: 5px;">
	<i><?php _e( 'The first media you rename will actually get this filename; the next will be either not renamed or will have a counter appended to it.', 'media-file-renamer' ); ?></i>
</div>
<?php endif; // iii ?>
<?php endif; // ii ?>

<?php // Non-locked media ?>
<?php else: // i ?>
<div class="icons-wrap">
	<?php $original_filename = get_post_meta( $id, '_original_filename', true ); ?>
	<span title="<?php _e( 'Automatically renamed.', 'media-file-renamer' ); ?>" class="dashicons dashicons-yes"></span>
	<?php if ( get_option( 'mfrh_undo', false ) && !empty( $original_filename ) ): // ii ?>
	<a title="<?php echo __( 'Rename to original filename: ', 'media-file-renamer' ) . $original_filename; ?>" href="?<?php echo $page . "&mfrh_undo=" . $id . $paged; ?>" class="undo dashicons dashicons-undo"></a>
	<?php endif; // ii ?>
	<a title="<?php _e( 'Click to lock it to manual only.', 'media-file-renamer' ); ?>" href="?<?php echo $page . "&mfrh_lock=" . $id . $paged; ?>" class="lock dashicons dashicons-unlock"></a>
</div>
<?php endif; // i ?>
