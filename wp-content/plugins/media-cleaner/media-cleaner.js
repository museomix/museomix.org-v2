/*
Plugin Name: Media Cleaner
Description: Clean your Media Library and Uploads Folder.
Author: Jordy Meow
*/

function wpmc_pop_array(items, count) {
	var newItems = [];
	while ( newItems.length < count && items.length > 0 ) {
		newItems.push( items.pop() );
	}
	return newItems;
}

/**
 *
 * RECOVER
 *
 */

function wpmc_recover() {
	var items = [];
	jQuery('#wpmc-table input:checked').each(function (index) {
		if (jQuery(this)[0].value != 'on') {
			items.push(jQuery(this)[0].value);
		}
	});
	wpmc_recover_do(items, items.length);
}

function wpmc_recover_all() {
	var items = [];
	var data = { action: 'wpmc_get_all_deleted' };
	jQuery.post(ajaxurl, data, function (response) {
		reply = jQuery.parseJSON(response);
		if ( !reply.success ) {
			alert( reply.message );
			return;
		}
		wpmc_recover_do(reply.results.ids, reply.results.ids.length);
	});
}

function wpmc_recover_do(items, totalcount) {
	wpmc_update_progress(totalcount - items.length, totalcount);
	if (items.length > 0) {
		newItems = wpmc_pop_array(items, 5);
		data = { action: 'wpmc_recover_do', data: newItems };
	}
	else {
		jQuery('#wpmc_pause').hide();
		jQuery('#wpmc_progression').html("Done. Please <a href='?page=media-cleaner'>refresh</a> this page.");
		return;
	}
	jQuery.post(ajaxurl, data, function (response) {
		reply = jQuery.parseJSON(response);
		if ( !reply.success ) {
			alert( reply.message );
			return;
		}
		wpmc_recover_do(items, totalcount);
	});
}

/**
 *
 * DELETE
 *
 */

function wpmc_ignore() {
	var items = [];
	jQuery('#wpmc-table input:checked').each(function (index) {
		if (jQuery(this)[0].value != 'on') {
			items.push(jQuery(this)[0].value);
		}
	});
	wpmc_ignore_do(items, items.length);
}

function wpmc_delete() {
	var items = [];
	jQuery('#wpmc-table input:checked').each(function (index) {
		if (jQuery(this)[0].value != 'on') {
			items.push(jQuery(this)[0].value);
		}
	});
	wpmc_delete_do(items, items.length);
}

function wpmc_delete_all(isTrash) {
	var items = [];
	var data = { action: 'wpmc_get_all_issues', isTrash: isTrash ? 1 : 0 };
	jQuery.post(ajaxurl, data, function (response) {
		reply = jQuery.parseJSON(response);
		if ( !reply.success ) {
			alert( reply.message );
			return;
		}
		wpmc_delete_do(reply.results.ids, reply.results.ids.length);
	});
}

function wpmc_update_progress(current, totalcount, isDeleting) {
	if (isDeleting === undefined)
  	isDeleting = false;
	var action = isDeleting ? "Deleting" : "Analyzing";
	jQuery('#wpmc_progression').html('<span class="dashicons dashicons-controls-play"></span> ' + action + ' ' + current + "/" + totalcount + " (" + Math.round(current / totalcount * 100) + "%)");
}

function wpmc_delete_do(items, totalcount) {
	wpmc_update_progress(totalcount - items.length, totalcount, true);
	if (items.length > 0) {
		newItems = wpmc_pop_array(items, 5);
		data = { action: 'wpmc_delete_do', data: newItems };
	}
	else {
		jQuery('#wpmc_progression').html("Done. Please <a href='?page=media-cleaner'>refresh</a> this page.");
		return;
	}
	jQuery.post(ajaxurl, data, function (response) {
		reply = jQuery.parseJSON(response);
		if ( !reply.success ) {
			alert( reply.message );
			return;
		}
		wpmc_delete_do(items, totalcount);
	});
}

function wpmc_ignore_do(items, totalcount) {
	wpmc_update_progress(totalcount - items.length, totalcount);
	if (items.length > 0) {
		newItems = wpmc_pop_array(items, 5);
		data = { action: 'wpmc_ignore_do', data: newItems };
	}
	else {
		jQuery('#wpmc_progression').html("Done. Please <a href='?page=media-cleaner'>refresh</a> this page.");
		return;
	}
	jQuery.post(ajaxurl, data, function (response) {
		reply = jQuery.parseJSON(response);
		if ( !reply.success ) {
			alert( reply.message );
			return;
		}
		wpmc_ignore_do(items, totalcount);
	});
}

/**
 *
 * SCAN
 *
 */

var wpmc = {
	dirs: [],
	files: [],
	medias: [],
	total: 0,
	issues: 0,
	isPause: false,
	isPendingPause: false
};

// WPMC GET INITIAL INFO

function wpmc_scan_type_finished() {

}

function wpmc_scan_type_next(type, path) {

}

function wpmc_prepare(limit = 0) {
	var data = { action: 'wpmc_prepare_do', limit: limit };
	setTimeout(
		function() {
			jQuery('#wpmc_progression').html('<span class="dashicons dashicons-portfolio"></span> Preparing posts (' + limit + ' posts)...');
				jQuery.post(ajaxurl, data, function (response) {
					reply = jQuery.parseJSON(response);
					if ( !reply.success ) {
						alert( reply.message );
						return;
					}
					if (!reply.finished)
						return wpmc_prepare(reply.limit);
					else
						return wpmc_scan_start();
				});
		}, wpmc_cfg.delay
	);
}

function wpmc_scan_type(type, path = null, limit = 0) {
	var data = { action: 'wpmc_scan', medias: type === 'medias', files: type === 'files', path: path, limit: limit };
	if (path) {
		elpath = path.replace(/^.*[\\\/]/, '');
		jQuery('#wpmc_progression').html('<span class="dashicons dashicons-portfolio"></span> Preparing files (' + elpath + ')...');
	}
	else if (type === 'medias')
		jQuery('#wpmc_progression').html('<span class="dashicons dashicons-admin-media"></span> Preparing medias (' + limit + ' medias)...');
	else
		jQuery('#wpmc_progression').html('<span class="dashicons dashicons-portfolio"></span> Preparing files...');

	setTimeout(
		function() {
			jQuery.post(ajaxurl, data, function (response) {
				reply = jQuery.parseJSON(response);
				if ( !reply.success ) {
					alert( reply.message );
					return;
				}

				// Store results
				for (var i = 0, len = reply.results.length; i < len; i++) {
				  var r = reply.results[i];
					if (type === 'files') {
						if ( r.type === 'dir' )
							wpmc.dirs.push( r.path );
						else if ( r.type === 'file' ) {
							wpmc.files.push( r.path );
							wpmc.total++;
						}
					}
					else if (type === 'medias') {
						wpmc.medias.push( r );
						wpmc.total++;
					}
				}

				// Next query
				if (type === 'medias') {
					if (wpmc_cfg.scanFiles || !reply.finished)
						return wpmc_scan_type('medias', null, reply.limit);
					else
						return wpmc_scan_do();
				}
				else if (type === 'files') {
					var dir = wpmc.dirs.pop();
					if (dir)
						return wpmc_scan_type('files', dir);
					else
						return wpmc_scan_do();
				}
			});
		}, wpmc_cfg.delay
	);
}

function wpmc_pause() {
	if (wpmc.isPause) {
		jQuery('#wpmc_pause').html('<span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-pause"></span>Pause');
		wpmc.isPause = false;
		wpmc_scan_do();
	}
	else if (wpmc.isPendingPause) {
		wpmc.isPendingPause = false;
	}
	else {
		jQuery('#wpmc_pause').html('<span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-pause"></span>Pausing...');
		wpmc.isPendingPause = true;
	}
}

function wpmc_scan() {
	if (!wpmc.isPause)
		wpmc_prepare();
	else
		wpmc_scan_start();
}

function wpmc_scan_start() {
	wpmc = { dirs: [], files: [], medias: [], total: 0, issues: 0, isPause: false, isPendingPause: false };
	jQuery('#wpmc_pause').hide();
	jQuery('#wpmc_pause').html('<span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-pause"></span>Pause');
	if (wpmc_cfg.scanMedia)
		wpmc_scan_type('medias', null);
	else if (wpmc_cfg.scanFiles)
		wpmc_scan_type('files', null);
}

function wpmc_update_to_pause() {
	if (wpmc.isPendingPause) {
		var current = wpmc.total - (wpmc.files.length + wpmc.medias.length);
		var totalcount = wpmc.total;
		jQuery('#wpmc_progression').html('<span class="dashicons dashicons-controls-pause"></span> Paused at ' + current + "/" + totalcount + " (" + Math.round(current / totalcount * 100) + "%)");
		jQuery('#wpmc_pause').html('<span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-play"></span>Continue');
		wpmc.isPendingPause = false;
		wpmc.isPause = true;
		return;
	}
}

function wpmc_update_to_error(error) {
	var current = wpmc.total - (wpmc.files.length + wpmc.medias.length);
	var totalcount = wpmc.total;
	jQuery('#wpmc_progression').html('<span class="dashicons dashicons-controls-pause"></span> Error at ' + current + "/" + totalcount + " (" + Math.round(current / totalcount * 100) + "%): " + error);
	jQuery('#wpmc_pause').html('<span style="top: 3px; position: relative; left: -5px;" class="dashicons dashicons-controls-play"></span>Retry');
	wpmc.isPendingPause = false;
	wpmc.isPause = true;
}

function wpmc_scan_do() {
	if (wpmc.isPendingPause)
		return wpmc_update_to_pause();
	else
		jQuery('#wpmc_pause').show();
	var newFiles = null;
	var newMedias = null;
	wpmc_update_progress(wpmc.total - (wpmc.files.length + wpmc.medias.length), wpmc.total);
	var data = {};
	var expectedSuccess = 0;
	if (wpmc.files.length > 0) {
		newFiles = wpmc_pop_array(wpmc.files, wpmc_cfg.analysisBuffer);
		expectedSuccess = newFiles.length;
		data = { action: 'wpmc_scan_do', type: 'file', data: newFiles };
	}
	else if (wpmc.medias.length > 0) {
		newMedias = wpmc_pop_array(wpmc.medias, wpmc_cfg.analysisBuffer);
		expectedSuccess = newMedias.length;
		data = { action: 'wpmc_scan_do', type: 'media', data: newMedias };
	}
	else {
		jQuery('#wpmc_progression').html(wpmc.issues + " issue(s) found. <a href='?page=media-cleaner'></span>Refresh</a>.");
		return;
	}

	setTimeout(
		function () {
			jQuery.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function (response) {
					try {
						reply = jQuery.parseJSON(response);
					}
					catch (e) {
						reply = null;
					}
					if (!reply) {
						reply = { success: false, message: "The reply from the server is broken. The reply will be displayed in your Javascript console. You should also check your PHP Error Logs." };
						console.debug( "Media File Cleaner got this reply from the server: " + response);
					}
					if (!reply.success) {
						wpmc_update_to_error(reply.message);
						console.debug("Media Cleaner got an error from server.", reply.message);
					}
					if (reply.result) {
						wpmc.issues += expectedSuccess - reply.result.success;
					}
					wpmc_scan_do();
				},
				error: function(request, status, err) {
					if (newFiles) {
						while (newFiles.length > 0)
							wpmc.files.push(newFiles.pop());
					}
					if (newMedias) {
						while (newMedias.length > 0)
							wpmc.medias.push(newMedias.pop());
					}
					wpmc_update_to_error(err);
					console.debug("Media Cleaner got an error from server.", status, err);
				}
			});
		}, wpmc_cfg.delay
	);
}

/**
 *
 * INIT
 *
 */

jQuery('#wpmc-cb-select-all').on('change', function (cb) {
	jQuery('#wpmc-table input').prop('checked', cb.target.checked);
});
