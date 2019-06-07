/**
 * The script for Media File Renamer in Media Library
 */

/**
 * Quick Renamer
 */
(function($) {
	var col;

	/**
	 * Initializes the entire part of the UI
	 */
	$(document).ready(function($) {
		init('.mfrh_column');
	});

	/**
	 * Initializes the specified columns
	 * @param {string|DOMElement|jQuery} cols - Columns to initialize
	 */
	function init(cols) {
		cols = $(cols);
		cols.each(function() {
			var col = $(this);
			var id = col.find('input[data-name="id"]').val();

			// Quick Renamer
			(function() {
				var wrap = col;
				var button = wrap.find('.button.rename');
				var input = wrap.find('input[data-name="filename"]');

				button.on('click', function(ev) {
					ev.preventDefault();
					if (isBusy(col)) return;
					goBusy(col);
					button.addClass('updating-message');
					rename(col, input.val());
				});
				input.on('keydown', function(ev) {
					if (ev.which != 13) return; // 13 = Enter
					ev.preventDefault();
					button.trigger('click');
				});
				input.on('change keyup', function(ev) {
					if (input.val() == input.attr('data-origin')) input.removeClass('changed');
					else input.addClass('changed');
				});
			})();

			// Quick Auto Renamer
			(function() {
				var wrap = col;
				var button = wrap.find('.button.auto-rename');
				var input = wrap.find('input[data-name="filename"]');

				button.on('mouseover', function(ev) {
					if (isBusy(col)) return;
					input.addClass('hidden');
				});
				button.on('mouseout', function(ev) {
					if (isBusy(col)) return;
					input.removeClass('hidden');
				});
				button.on('click', function(ev) {
					ev.preventDefault();
					if (isBusy(col)) return;
					goBusy(col);
					button.addClass('updating-message');
					rename(col, null);
				});
			})();

			// Quick Undo
			(function() {
				var wrap = col;
				var button = wrap.find('.undo');

				button.on('click', function(ev) {
					ev.preventDefault();
					if (isBusy(col)) return;
					goBusy(col);

					$.ajax(ajaxurl, {
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'mfrh_undo_media',
							subaction: 'undoMediaId',
							id: id
						}

					}).done(function(result) {
						/**
						 * Expected result format: {
						 *   success: True or False
						 *   data: {
						 *     filename: New Filename
						 *     ids: Affected Posts IDs
						 *   }
						 * }
						 */
						if (result.success === false) { // Rejected
							alert(result.data);
							return;
						}
						// Update all the affected posts' columns
						for (var i = 0; i < result.data.ids.length; i++) {
							var id = result.data.ids[i];
							update('#post-' + id + ' .mfrh_column', result.data.filename);
						}
					});
				});
			})();

			// Quick Lock
			(function() {
				var wrap = col;
				var button = wrap.find('.lock');

				button.on('click', function(ev) {
					ev.preventDefault();
					if (isBusy(col)) return;
					goBusy(col);

					$.ajax(ajaxurl, {
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'mfrh_lock_media',
							id: id
						}

					}).done(function(result) {
						if (result.success === false) { // Rejected
							alert(result.data);
							return;
						}
						update(col, null);
					});
				});
			})();

			// Quick Unlock
			(function() {
				var wrap = col;
				var button = wrap.find('.unlock');

				button.on('click', function(ev) {
					ev.preventDefault();
					if (isBusy(col)) return;
					goBusy(col);

					$.ajax(ajaxurl, {
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'mfrh_lock_media',
							subaction: 'unlock',
							id: id
						}

					}).done(function(result) {
						if (result.success === false) { // Rejected
							alert(result.data);
							return;
						}
						update(col, null);
					});
				});
			})();
		});

		cols.removeClass('busy');
	}

	/**
	 * Returns whether a column is busy
	 * @param {string|DOMElement|jQuery} col - A column to check
	 * @return {boolean}
	 */
	function isBusy(col) {
		return $(col).hasClass('busy');
	}

	/**
	 * Makes a column busy
	 * @param {string|DOMElement|jQuery} col - A column to make busy
	 */
	function goBusy(col) {
		col = $(col);
		col.addClass('busy');
		col.find('input[type="text"]').prop('disabled', true);
	}

	/**
	 * Sends a rename request
	 * @param {string|DOMElement|jQuery} col - A column to update
	 * @param {string|null} newName - The new name of the media. null means auto
	 * @return {jQuery.Deffered} - The actual ajax request
	 */
	function rename(col, newName) {
		col = $(col);
		var id = col.find('input[data-name="id"]').val();

		var data = {
			action: 'mfrh_rename_media',
			subaction: 'renameMediaId',
			id: id
		}
		if (typeof newName == 'string') data.newName = newName;

		return $.ajax(ajaxurl, {
			type: 'POST',
			dataType: 'json',
			data: data

		}).done(function(result) {
			/**
			 * Expected result format: {
			 *   success: True or False
			 *   data: {
			 *     filename: New Filename
			 *     ids: Affected Posts IDs
			 *   }
			 * }
			 */
			if (result.success === false) { // Rejected
				alert(result.data);
				return;
			}
			// Update all the affected posts' columns
			for (var i = 0; i < result.data.ids.length; i++) {
				var id = result.data.ids[i];
				update('#post-' + id + ' .mfrh_column', result.data.filename);
			}
		});
	}

	/**
	 * Updates the view of the row which affected by asynchronous rename
	 * @param {string|DOMElement|jQuery} col - A column to update
	 * @param {string|null} newName - The new name of the media
	 * @return {jQuery.Deffered} - The actual ajax request
	 */
	function update(col, newName) {
		col = $(col);
		var id = col.find('input[data-name="id"]').val();

		// Update the filename information in 'File' column
		if (typeof newName == 'string') {
			var filename = col.closest('tr').find('.column-title .filename');
			var children = filename.children().detach();
			filename.text(newName);
			filename.prepend(children);
		}

		// Re-render the column
		return $.ajax(ajaxurl, {
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mfrh_render_column',
				id: id
			}

		}).done(function(result) {
			if (result.success === false) { // Rejected
				// TODO Reload the page
				return;
			}
			// Overwrite the column content with the rendering result
			col.html(result.data);
			// Re-initialize the content
			init(col);
		});
	}

})(jQuery);
