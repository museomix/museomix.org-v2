<script type="text/javascript" >

	var current;
	var ids = [];

	function mfrh_process_next() {
		var data = { action: 'mfrh_rename_media', subaction: 'renameMediaId', id: ids[current - 1] };
		jQuery('#mfrh_progression').text(current + "/" + ids.length);
		jQuery.post(ajaxurl, data, function (response) {
			if (++current <= ids.length) {
				mfrh_process_next();
			}
			else {
				jQuery('#mfrh_progression').html("<?php echo __( "Done. Please <a href='?page=rename_media_files'>refresh</a> this page.", 'media-file-renamer' ); ?>");
			}
		});
	}

	function mfrh_rename_media(all) {
		current = 1;
		ids = [];
		var data = { action: 'mfrh_rename_media', subaction: 'getMediaIds', all: all ? '1' : '0' };
		jQuery('#mfrh_progression').text("<?php echo __( "Please wait...", 'media-file-renamer' ); ?>");
		jQuery.post(ajaxurl, data, function (response) {
			reply = jQuery.parseJSON(response);
			ids = reply.ids;
			jQuery('#mfrh_progression').html(current + "/" + ids.length);
			mfrh_process_next();
		});
	}

	function mfrh_process_next_undo() {
		var data = { action: 'mfrh_undo_media', subaction: 'undoMediaId', id: ids[current - 1] };
		jQuery('#mfrh_progression').text(current + "/" + ids.length);
		jQuery.post(ajaxurl, data, function (response) {
			if (++current <= ids.length) {
				mfrh_process_next_undo();
			}
			else {
				jQuery('#mfrh_progression').html("<?php echo __( "Done. Please <a href='?page=rename_media_files'>refresh</a> this page.", 'media-file-renamer' ); ?>");
			}
		});
	}

	function mfrh_undo_media(all) {
		current = 1;
		ids = [];
		var data = { action: 'mfrh_undo_media', subaction: 'getMediaIds', all: all ? '1' : '0' };
		jQuery('#mfrh_progression').text("<?php echo __( "Please wait...", 'media-file-renamer' ); ?>");
		jQuery.post(ajaxurl, data, function (response) {
			reply = jQuery.parseJSON(response);
			ids = reply.ids;
			jQuery('#mfrh_progression').html(current + "/" + ids.length);
			mfrh_process_next_undo();
		});
	}

	function mfrh_export_table(table) {
		var table = jQuery(table);
		var data = [];
		// Header
		table.find('thead tr').each(function(i, tr) {
			var row = [];
			jQuery(tr).find('th').each(function(i, td) {
				var text = jQuery(td).text();
				row.push(text);
			});
			data.push(row);
		});
		// Body
		table.find('tbody tr').each(function(i, tr) {
			var row = [];
			jQuery(tr).find('td').each(function(i, td) {
				var text = jQuery(td).text();
				row.push(text);
			});
			data.push(row);
		});
		var csvContent = "data:text/csv;charset=utf-8,";
		data.forEach(function(infoArray, index){
			dataString = infoArray.join(",");
			csvContent += index < data.length ? dataString+ "\n" : dataString;
		});
		var encodedUri = encodeURI(csvContent);
		var link = document.createElement("a");
		link.setAttribute("href", encodedUri);
		link.setAttribute("download", "media-file-renamer.csv");
		document.body.appendChild(link);
		link.click();
	}

</script>
