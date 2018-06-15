<?php
/*
 * Plugin Name: TinyMCE Custom Styles
 * Plugin URI: https://timreeves.de/
 * Description: Add custom editor stylesheets to TinyMCE and Theme, and configure the TinyMCE styles dropdown to match.
 * Version: 1.0.9
 * Author: Tim Reeves (original author David Stöckl)
 * Author URI: https://timreeves.de/
 * License: GPLv3
 * Text Domain: tinymce-custom-styles
 * Domain Path: /languages
 *
 * Note: This Plugins is GPLv3 licensed. This Plugin is released without any warranty.
 *
*/

// 1. Localization

define('TCS_TEXTDOMAIN', 'tinymce-custom-styles');

add_action('init', 'tcs_localization');

function tcs_localization() {
	load_plugin_textdomain( TCS_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

// 2. Version check

global $wp_version;

$exit_msg = sprintf(__('This Plugin requires WordPress version 3.0 or higher. %sPlease update!%s', TCS_TEXTDOMAIN), '<a href="http://codex.wordpress.org/Upgrading_Wordpress">', '</a>');

if (!version_compare($wp_version,"2.9",">")) { exit ($exit_msg); }

// 3. Install / Uninstall

function tcs_uninstall() {
	delete_option('tcs_addstyledrop');
}

function tcs_activate() {

	register_uninstall_hook(__FILE__, "tcs_uninstall");
}
register_activation_hook(__FILE__, "tcs_activate");

// 4. Utility functions

// 4a. Returns the current Styles url
function tcs_get_style_url($style_name) {

	$keyword = get_option('tcs_locstyle');

	$http_path = "";

	if ($keyword == 'themes_directory') {
		$http_path = get_bloginfo('template_url') . '/' . $style_name;
	} elseif ($keyword == 'themes_child_directory') {
		$http_path = get_stylesheet_directory_uri() . '/' . $style_name;
	} elseif ($keyword == 'custom_directory') {
		$http_path= content_url() . '/' . get_option('tcs_cuslink') . $style_name;
	}

	return $http_path;
}

// 4b. Returns the absolute server path of the styles directory
function tcs_get_style_server_path($style_name) {

	$keyword = get_option("tcs_locstyle");

	$server_side_path = "";

	if ($keyword == 'themes_directory') {
		$server_side_path = get_template_directory() . '/'. $style_name;
	} elseif ($keyword == 'themes_child_directory') {
		$server_side_path = get_stylesheet_directory() . '/' . $style_name;
	} elseif ($keyword == 'custom_directory') {
		$server_side_path = WP_CONTENT_DIR . '/' . get_option('tcs_cuslink') . $style_name;
	}

	return $server_side_path;
}

// 4c. Admin Notices

$GLOBALS['tcsAdminNotices'] = array();

function tcs_addAdminNotice($notice, $class='info') {
	if ($class !== 'info' AND $class !== 'warning' AND $class !== 'error') {
		$class = 'error';
		$notice = 'Internal Plugin-Error - wrong call to function tcs_addAdminNotice';
	}
	$GLOBALS['tcsAdminNotices'][] = array($class, $notice);
	// echo "Added $class notice\n";
}

function tcs_adminNotice0() {
	$class = $GLOBALS['tcsAdminNotices'][0][0];
	$notice = $GLOBALS['tcsAdminNotices'][0][1];
	echo "\t\t\t<div class=\"notice notice-{$class} is-dismissible\"><p><strong>{$notice}</strong></p></div>\n";
}

function tcs_adminNotice1() {
	$class = $GLOBALS['tcsAdminNotices'][1][0];
	$notice = $GLOBALS['tcsAdminNotices'][1][1];
	echo "\t\t\t<div class=\"notice notice-{$class} is-dismissible\"><p><strong>{$notice}</strong></p></div>\n";
}

function tcs_adminNotice2() {
	$class = $GLOBALS['tcsAdminNotices'][2][0];
	$notice = $GLOBALS['tcsAdminNotices'][2][1];
	echo "\t\t\t<div class=\"notice notice-{$class} is-dismissible\"><p><strong>{$notice}</strong></p></div>\n";
}

function tcs_outputAdminNotices() {
	if (count($GLOBALS['tcsAdminNotices']) === 0 AND isset($_POST['tcs_backend_update']) AND $_POST['tcs_backend_update'] != "") {
		$class = 'success';
		$notice = __('Settings saved successfully.', TCS_TEXTDOMAIN);
		$GLOBALS['tcsAdminNotices'][] = array($class, $notice);
	}
	// for ($i=0; $i<count($GLOBALS['tcsAdminNotices']); $i++) {
		// add_action('admin_notices', "tcs_adminNotice{$i}");
	// }
	foreach ($GLOBALS['tcsAdminNotices'] as &$arrClassNotice) {
		echo "\t\t\t<div class=\"notice notice-{$arrClassNotice[0]} is-dismissible\"><p><strong>{$arrClassNotice[1]}</strong></p></div>\n";
	}
}

// 4d. createAndSetEditorStyles

function tcs_createCssStubFiles($keyword, $custom_path="") {

	if (!in_array($keyword, array("themes_directory", "themes_child_directory", "custom_directory"))) {
		tcs_addAdminNotice(__("No (valid) location for CSS files selected.", TCS_TEXTDOMAIN), 'error');
		return FALSE;
	}

	// Create both style files if they do not exist

	$blnCreateError = FALSE;
	$strMessage = __("A stub file %s has been created automatically.", TCS_TEXTDOMAIN);

	if (!file_exists(tcs_get_style_server_path("editor-style.css"))) {

$content = '/* These styles are used only in the backend editor */

/* Here you can override any CSS which causes layout or visibility problems in the editor, */
/* and duplicate any Theme-CSS to make the editor display look more like the real frontend */
';

		$fp = @fopen(tcs_get_style_server_path("editor-style.css"), "wb");
		if ($fp !== FALSE) {
			fwrite($fp, $content);
			fclose($fp);
    		tcs_addAdminNotice(sprintf($strMessage, tcs_get_style_server_path('editor-style.css')));
		}
		else
			$blnCreateError = TRUE;
	}

	if (!file_exists(tcs_get_style_server_path("editor-style-shared.css"))) {

$content = '/* These styles are used in the backend editor AND in the Theme (frontend) */

/* CSS is provided by the theme itself, and modified by any custom css you add to the theme. */
/* But that CSS is not active when using the backend-editor, so here is a good place to put  */
/* any custom css which should be active in the frontend website and also applied to editor. */
/* The goal is to make the visual editor display as like the final frontend view as possible */
/* so do not forget to configure the TinyMCE styles dropdown with elements to match these.   */

/* Suggested general non-tag-specific visual styles (for all websites) */

.list       { margin: 0.35rem 0; }
.stdtop     { margin-top: 0.65rem !important; }
.moretop    { margin-top: 0.8rem  !important; }
.lotstop    { margin-top: 1rem    !important; }
.hugetop    { margin-top: 1.3rem  !important; }
.stdbottom  { margin-bottom: 0.65rem !important; }
.morebottom { margin-bottom: 0.8rem  !important; }
.lotsbottom { margin-bottom: 1rem    !important; }
.hugebottom { margin-bottom: 1.3rem  !important; }

.topless    { margin-top: 0 !important; }
.bottomless { margin-bottom: 0 !important; }

.beforelist { margin-bottom: 0.3rem; }

.lastitempx { padding-bottom: 12px !important; }
.lastitemem { margin-bottom: 0; padding-bottom: 1.3rem; }

strong, .strong, .fett, .bold, .smallBold, .smallerBold { font-weight: bold; }

/* Revert to normal text within strong */
.notstrong { font-weight: normal; }

.smaller, .smallerBold { font-size: 0.95rem; line-height: 1.25; }

.small, .smallBold { font-size: 0.89rem; line-height: 1.2; }

/* Append your site-specific styles here */
';

		$fp = @fopen(tcs_get_style_server_path("editor-style-shared.css"), "wb");
		if ($fp !== FALSE) {
			fwrite($fp, $content);
			fclose($fp);
    		tcs_addAdminNotice(sprintf($strMessage, tcs_get_style_server_path('editor-style-shared.css')));
		}
		else
			$blnCreateError = TRUE;
	}

	if ($blnCreateError === TRUE) {
		$strMessage = __("Could not create one or both CSS stub files. The folder %s must exist on your server and be writable for WordPress.", TCS_TEXTDOMAIN);
		tcs_addAdminNotice(sprintf($strMessage, tcs_get_style_server_path('')), 'error');
		return FALSE;
	}

	return TRUE;

}	// tcs_createCssStubFiles()


// 5. Add settings link on plugin page
function tcs_settings_link($links) {

  $settings_link = sprintf(__('%s Settings %s', TCS_TEXTDOMAIN), '<a href="options-general.php?page=tinymce-custom-styles/tinymce-custom-styles.php">', '</a>');
  array_unshift($links, $settings_link);

  return $links;
}


$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'tcs_settings_link');


function tcs_tinymce_css($wp) {
        $wp .= ',' . tcs_get_style_url("editor-style.css");
		$wp .= ',' . tcs_get_style_url("editor-style-shared.css");
        return $wp;
}
// Add user defined editor styles
add_filter('mce_css', 'tcs_tinymce_css', 100);

function tcs_add_stylesheet() {
    // Respects SSL, style.css is relative to the current file
    wp_register_style( 'bb-tcs-editor-style-shared', tcs_get_style_url('editor-style-shared.css') );
    wp_enqueue_style( 'bb-tcs-editor-style-shared' );
}
// And add stylesheet to theme
add_action( 'wp_enqueue_scripts', 'tcs_add_stylesheet' );


// Callback function to insert 'styleselect' into the $buttons array
function my_mce_buttons_2( $buttons ) {
	array_unshift( $buttons, 'styleselect' );
	return $buttons;
}
// Register our callback to the appropriate filter
add_filter('mce_buttons_2', 'my_mce_buttons_2');


// Callback function to filter the MCE settings
function tcs_mce_before_init_insert_formats( $settings ) {

	// Define the style_formats array
	$style_formats = get_option('tcs_addstyledrop');

	// Shift our styles into a submenu if selected
	if (get_option('tcs_submenu') == "1") {
		$mainmenu = array( array( 'title' => 'Custom Styles', 'items' => $style_formats ) );
		$style_formats = $mainmenu;
	}

	// Add the array, JSON ENCODED, into 'style_formats', preserving anything already there
	if (isset($settings['style_formats'])) {

		$json_decode_orig_settings = json_decode($settings['style_formats'], true);

		// Check to make sure incoming 'style_formats' is an array
		if (is_array($json_decode_orig_settings)) {
			$newArray = array_merge($json_decode_orig_settings, $style_formats);
			$settings['style_formats'] = json_encode($newArray);
		}
		else {
			$settings['style_formats'] = json_encode($style_formats);
		}

	} else {
		$settings['style_formats'] = json_encode($style_formats);
	}

	// See: https://www.tinymce.com/docs/configure/content-formatting/#style_formats
	$settings['style_formats_merge'] = (get_option('tcs_nomerge') == '1') ? false : true;

	return $settings;
}

// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'tcs_mce_before_init_insert_formats' );


/********** Backend Page *********************/


function tcs_options() {
	add_options_page('TinyMCE Custom Styles', 'TinyMCE Custom Styles', 'manage_options', __FILE__, 'tcs_backend_page');
}
// add the backend menu entry
add_action('admin_menu', 'tcs_options');


function tcs_backend_page() {

	echo "\t\t<div class=\"wrap\">\n";

	echo "\t\t\t<h2>" . __('Settings: TinyMCE Custom Styles', TCS_TEXTDOMAIN) . "</h2>\n";

	if (isset($_POST['tcs_backend_update']) && $_POST['tcs_backend_update'] != "") {

		// Process form submission

        // Debug
        // $arr_postErrors[] = printf("ploc=%s oloc=%s pcus=%s ocus=%s", $_POST["tcs_locstyle"], get_option('tcs_locstyle'), $_POST["tcs_cuslink"], get_option('tcs_cuslink'));

		// 1. Save any changes to location specification and create stub css files if missing

		// 1a. Note new option values if themes folder location, custom theme directory or submenu option changed
		if ($_POST["tcs_locstyle"] != get_option('tcs_locstyle')) update_option("tcs_locstyle", $_POST['tcs_locstyle']);
		if ($_POST["tcs_cuslink"]  != get_option('tcs_cuslink'))  update_option("tcs_cuslink",  $_POST['tcs_cuslink']);
		$valUseSubMenu = isset($_POST["tcs_submenu"]) ? "1" : "0";
		if ($valUseSubMenu != get_option('tcs_submenu'))  update_option("tcs_submenu",  $valUseSubMenu);
		$valNoMerge = isset($_POST["tcs_nomerge"]) ? "1" : "0";
		if ($valNoMerge != get_option('tcs_nomerge'))  update_option("tcs_nomerge",  $valNoMerge);

		// 1b. Check if the files are both present at this (new) location, try to create them if not
		if (!file_exists(tcs_get_style_server_path('editor-style.css')) OR
			!file_exists(tcs_get_style_server_path("editor-style-shared.css")))
		{
		    tcs_createCssStubFiles($_POST['tcs_locstyle'], $_POST['tcs_cuslink']);
		}

		// 1c. Get the option value for adding the custom styles as a submenu

		// 2. Update the stored options from the table
   		$all = intval($_POST['addstyledrop_number']);

		$all_options = array();
		$allowed = false;

        // Wordpress sometimes tries to escape the HTML when saving something to wp-options
        for ($i=1; $i<=$all; $i++) {

        	$allowed = TRUE;

			$field0 = $_POST["addstyledrop_0_" . $i];	// Title

        	// Type (radios)
        	$field1 = 'unset';
			if (isset($_POST["addstyledrop_1_" . $i])) $field1 = $_POST["addstyledrop_1_" . $i];

			$field3 = $_POST["addstyledrop_3_" . $i];	// Type value

			$field4 = $_POST["addstyledrop_4_" . $i];	// CSS Class(es)

			// Exact checkbox
			$field7 = 0;
			if (isset($_POST["addstyledrop_7_" . $i])) $field7 = 1;

			// Wrapper checkbox
			$field8 = 0;
			if (isset($_POST["addstyledrop_8_" . $i])) $field8 = 1;

			// If the title is empty then the row will be deleted
			if ($field0 != "") {

				if (!in_array($field1, array("inline", "block", "selector"))) {
					$allowed = FALSE;
					$strMessage = __("Settings row %d not saved: No Type Option was checked.", TCS_TEXTDOMAIN);
					tcs_addAdminNotice(sprintf($strMessage, $i), 'error');
				}

				if ($field3 == "") {
					$allowed = FALSE;
					$strMessage = __("Settings row %d not saved: No Type Value was entered.", TCS_TEXTDOMAIN);
					tcs_addAdminNotice(sprintf($strMessage, $i), 'error');
				}

	        	if ($allowed) {
	        		$checked_row = array();
					$checked_row["title"] = $field0;
					$checked_row[$field1] = $field3;
					$checked_row["classes"] = $field4;

					// save the custom styles
					$styles_to_check = intval($_POST["tpcount_5_{$i}"]);	// Begins 1
					$ready_styles = array();

					for ($a=1; $a<=$styles_to_check; $a++) {
						$k = "addstyledrop_5_{$i}_{$a}_key";
						$v = "addstyledrop_5_{$i}_{$a}_val";
						if ($_POST[$k] != "" && $_POST[$v] != "") {
							$ready_styles[$_POST[$k]] = $_POST[$v];
						}
					}
					$checked_row["styles"] = $ready_styles;

					// save the custom attributes
					$styles_to_check = intval($_POST["tpcount_6_{$i}"]);	// Begins 1
					$ready_attribs = array();

					for ($a=1; $a<=$styles_to_check; $a++) {
						$k = "addstyledrop_6_{$i}_{$a}_key";
						$v = "addstyledrop_6_{$i}_{$a}_val";
						if ($_POST[$k] != "" && $_POST[$v] != "") {
							$ready_attribs[$_POST[$k]] = $_POST[$v];
						}
					}
					$checked_row["attributes"] = $ready_attribs;

					$checked_row["exact"] = $field7 === 1;
					$checked_row["wrapper"] = $field8 === 1;

	        		$all_options[] = $checked_row;
	        	}

			}	// Title not empty

        }	// for all rows

		update_option('tcs_addstyledrop', $all_options);

		tcs_outputAdminNotices();
		// add_action('admin_notices', 'tcs_outputAdminNotices');

	}	// Submit via POST

    $breakIndentAfterRadio = '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

?>			<form method="post" action="">
				<h3><?php _e('Enhance the visual style of your TinyMCE', TCS_TEXTDOMAIN); ?></h3>
				<p><?php _e('This plugin adds two stylesheets to upgrade the appearance of your TinyMCE visual editor: editor-style.css (TinyMCE only) and editor-style-shared.css (for TinyMCE AND your theme).', TCS_TEXTDOMAIN); ?></p>
				<p><?php _e('Please choose a location for your stylesheet files:', TCS_TEXTDOMAIN); ?></p>
				<p><input type="radio" name="tcs_locstyle" value="themes_directory" <?php if (get_option('tcs_locstyle') == "themes_directory") {?>checked="checked" <?php } ?> /> <?php printf(__('Directory of current Theme (do %snot%s choose in case of automatically updated theme)', TCS_TEXTDOMAIN), '<strong>', '</strong>'); ?></p>
				<p><input type="radio" name="tcs_locstyle" value="themes_child_directory" <?php if (get_option('tcs_locstyle') == "themes_child_directory") {?>checked="checked" <?php } ?> /> <?php printf(__('Directory of current Child Theme (do %snot%s choose in case of automatically updated child theme)', TCS_TEXTDOMAIN), '<strong>', '</strong>'); ?></p>
				<p><input type="radio" name="tcs_locstyle" value="custom_directory" <?php if (get_option('tcs_locstyle') == "custom_directory") {?>checked="checked" <?php } ?> /> <?php printf(__('Use a custom directory (recommended) at %s/', TCS_TEXTDOMAIN), WP_CONTENT_DIR); ?><input size="30" type="text" name="tcs_cuslink" value="<?php echo get_option('tcs_cuslink'); ?>" />editor-style[-shared].css
				<?php printf(__('%sYour custom directory must pre-exist and your input above must begin without a slash, and include one at the end of each directory.%sAny characters after the last slash will be prepended to the file names.', TCS_TEXTDOMAIN), $breakIndentAfterRadio, $breakIndentAfterRadio); ?></p>
<?php
				// Output error or usage messages for the css files
				echo "\t\t\t\t<p>";
				$strError = '<span style="color:#f00; font-weight:bold;">' . __('Error: ', TCS_TEXTDOMAIN) . '</span>';
				if (get_option('tcs_locstyle') == "") {
					 echo $strError . __('Please choose a location for the editor css files (select a radio button above).', TCS_TEXTDOMAIN);
				}
				else {
					$blnBothFound = TRUE;
					$strMessage = $strError . __('The file "%s" was not found and could not be created in the specified directory. The directory must exist and be writable for WordPress.', TCS_TEXTDOMAIN);
					if (!file_exists(tcs_get_style_server_path('editor-style.css'))) {
						printf($strMessage, 'editor-style.css');
						$blnBothFound = FALSE;
					}
					if (!file_exists(tcs_get_style_server_path("editor-style-shared.css"))) {
						if (!$blnBothFound) echo '<br/>';
						printf($strMessage, 'editor-style-shared.css');
						$blnBothFound = FALSE;
			        }
			        if ($blnBothFound) {
						if (get_option("tcs_locstyle") == 'themes_directory') {
							printf(__('Edit your %s editor-style.css %s for the editor-only styles here.', TCS_TEXTDOMAIN), '<a href="' . get_admin_url() . '/theme-editor.php?file=editor-style.css" target="_blank">', '</a>');
						    echo '<br/>';
							printf(__('Edit your %s editor-style-shared.css %s for the common editor AND theme styles here.', TCS_TEXTDOMAIN), '<a href="' . get_admin_url() . '/theme-editor.php?file=editor-style-shared.css" target="_blank">', '</a>');
						} elseif (get_option("tcs_locstyle") == 'themes_child_directory') {
							printf(__('Edit your editor-only styles located at %s on your server.', TCS_TEXTDOMAIN), "<strong>" . tcs_get_style_server_path("editor-style.css") . "</strong>");
						    echo '<br/>';
							printf(__('Edit your theme/editor shared styles located at %s on your server.', TCS_TEXTDOMAIN), "<strong>" . tcs_get_style_server_path("editor-style-shared.css") . "</strong>");
						} elseif (get_option("tcs_locstyle") == 'custom_directory') {
							printf(__('Your editor-only styles are located at %s', TCS_TEXTDOMAIN), "<strong>" . tcs_get_style_server_path("editor-style.css") . "</strong>");
						    echo '<br/>';
							printf(__('Your theme/editor shared styles are located at %s', TCS_TEXTDOMAIN), "<strong>" . tcs_get_style_server_path("editor-style-shared.css") . "</strong>");
						}
			        }
		        }
				echo "</p>\n";
				?>

				<p><input type="checkbox" name="tcs_submenu" value="1" <?php if (get_option("tcs_submenu") == "1") {?>checked="checked" <?php } ?> /> <?php _e('Select to add the styles in a submenu "Custom Styles".', TCS_TEXTDOMAIN); ?></p>

				<p><input type="checkbox" name="tcs_nomerge" value="1" <?php if (get_option("tcs_nomerge") == "1") {?>checked="checked" <?php } ?> /> <?php _e('Select to remove the standard styles from the menu (overridden by WP Edit > Add Predefined Styles).', TCS_TEXTDOMAIN); ?></p>

				<p style="text-align:center;"><input type="submit" name="Save" value="<?php _e('Save all settings', TCS_TEXTDOMAIN); ?>" class="button-primary" /></p>

				<h3 style="margin-top:1.6em;"><?php _e('Manage your custom formats and styles for TinyMCE', TCS_TEXTDOMAIN); ?></h3>

				<p style="margin-bottom:1em"><?php printf(__('%s This part %s of the official TinyMCE documentation will help you understanding this table.', TCS_TEXTDOMAIN), '<a href="https://www.tinymce.com/docs/configure/content-formatting/#formats" target="_blank">', '</a>'); ?></p>

				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e('Column', TCS_TEXTDOMAIN); ?></th>
							<th><?php _e('Description', TCS_TEXTDOMAIN); ?></th>
						</tr>
					</thead>
				<tbody>
				<tr>
				<td><strong><?php _e('Title', TCS_TEXTDOMAIN); ?></strong> [<?php _e('required', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('The label (name) for this dropdown item.', TCS_TEXTDOMAIN); ?></td>
				</tr>
				<tr>
				<td><strong><?php printf(__('Type%s (radios) [%s]', TCS_TEXTDOMAIN), '</strong> ', __('required', TCS_TEXTDOMAIN)); ?></td>
				<td><ul style="margin:0;">
					<li><?php printf(__('%sInline%s: Enter one %s HTML inline element %s (e.g. span) to create, with the classes/styles of the row applied, which will wrap the current editor selection, not replacing any tags.', TCS_TEXTDOMAIN), '<strong> &bull; ', '</strong>', '<a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Inline_elements" target="_blank">', '</a>'); ?></li>
					<li><?php printf(__('%sBlock%s: Enter one %s HTML block-level element %s (e.g. blockquote) to create with the classes/styles of the row applied. It will REPLACE the existing block element at cursor.', TCS_TEXTDOMAIN), '<strong> &bull; ', '</strong>', '<a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Block-level_elements" target="_blank">', '</a>'); ?></li>
					<li style="margin:0;"><?php printf(__('%sSelector%s: Enter %s a valid CSS 3 selector %s to select existing HTML tags to which the classes/styles of the row will be applied. Can select complex things like odd rows in a table.', TCS_TEXTDOMAIN), '<strong> &bull; ', '</strong>', '<a href="http://www.w3schools.com/cssref/css_selectors.asp" target="_blank">', '</a>'); ?></li>
				</ul></td>
				</tr>
				<tr>
				<td><strong><?php _e('Type Value', TCS_TEXTDOMAIN); ?></strong> [<?php _e('required', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('The HTML-Element to create or CSS 3 selector pattern to apply.', TCS_TEXTDOMAIN); ?></td>
				</tr>
				<tr>
				<td><strong><?php _e('CSS Class(es)', TCS_TEXTDOMAIN); ?></strong> [<?php _e('optional', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('A space-separated list of classes to apply to the element.', TCS_TEXTDOMAIN); ?></td>
				</tr>
				<tr>
				<td><strong><?php _e('CSS Styles', TCS_TEXTDOMAIN); ?></strong> [<?php _e('optional', TCS_TEXTDOMAIN); ?>]</td>
				<td><ul style="margin:0;">
					<li><?php _e('You can enter CSS here which will be applied directly to the element in its style attribute.', TCS_TEXTDOMAIN); ?></li>
					<li><strong><?php _e('Note:', TCS_TEXTDOMAIN); ?></strong> <?php printf(__('Multi-word attributes, like %sfont-size%s, are written in Javascript-friendly camel case: %sfontSize%s.', TCS_TEXTDOMAIN), '<em>', '</em>', '<em>', '</em>'); ?></li>
					<li style="margin:0;"><strong><?php _e('Note:', TCS_TEXTDOMAIN); ?></strong> <?php _e('It is more recommendable to use classes of your editor-style.css / editor-style-shared.css in most cases.', TCS_TEXTDOMAIN); ?> </li>
				</ul></td>
				</tr>
				<tr>
				<td><strong><?php _e('Attributes', TCS_TEXTDOMAIN); ?></strong> [<?php _e('optional', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('You can define HTML-Attributes here which will be applied to the element(s).', TCS_TEXTDOMAIN); ?></td>
				</tr>
				<tr>
				<td><strong><?php _e('Exact',TCS_TEXTDOMAIN); ?></strong> [<?php _e('optional', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('Checking this option disables the "merge similar styles" feature, needed for some CSS inheritance issues.', TCS_TEXTDOMAIN); ?></td>
				</tr>
				<tr>
				<td><strong><?php _e('Wrapper', TCS_TEXTDOMAIN); ?></strong> [<?php _e('optional', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('If you check this, selecting the style creates a new block-level element around any selected block-level elements.', TCS_TEXTDOMAIN); ?></td>
				</tr>
				<tr>
				<td><strong><?php _e('Remove', TCS_TEXTDOMAIN); ?></strong> [<?php _e('action', TCS_TEXTDOMAIN); ?>]</td>
				<td><?php _e('Clicking the "X" removes the row. Multiple rows can be deleted by saving with empty titles.', TCS_TEXTDOMAIN); ?></td>
				</tr>
				</tbody>
				</table>
				<div style="margin-top:1.5em" id="tcs_settings_table">
					<table class="widefat">
						<thead>
							<tr valign="top">
								<th scope="row"><?php _e('Title *', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Type *', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Type Value *', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('CSS Class(es)', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('CSS Styles', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Attributes', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Exact', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Wrapper', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Remove', TCS_TEXTDOMAIN); ?></th>
							</tr>
						</thead>
						<tbody id="tcs_addstyledrop">
						<?php

						$op_ct = 0;	// Incremented at TOP of loop, so rows numbered from 1

						$items = get_option('tcs_addstyledrop', array());

						foreach ($items as $item) {

							$op_ct++;

							$type = "";
							$typeval = "";

							if (array_key_exists('inline', $item)) {
								$type = "inline";
								$typeval = $item["inline"];
							} elseif (array_key_exists('block', $item)) {
								$type = "block";
								$typeval = $item["block"];
							} elseif (array_key_exists('selector', $item)) {
								$type = "selector";
								$typeval = $item["selector"];
							}

							$strTypeIdRoot = "addstyledrop_1_{$op_ct}";
						?>
						<tr id="addstyledrop_row_<?php echo "$op_ct"; if ($op_ct % 2 == 0) echo '\" style="background-color:#f3f3f3;';?>" valign="top">
							<td><input type="text" name="addstyledrop_0_<?php echo $op_ct; ?>" id="addstyledrop_0_<?php echo $op_ct; ?>" value="<?php echo $item['title']; ?>" /></td>
							<td>
								<label style="white-space:nowrap;"><input type="radio" value="inline" name="<?php echo "{$strTypeIdRoot}"; if ($type == "inline") echo '" checked="checked'; ?>" /> Inline</label><br>
								<label style="white-space:nowrap;"><input type="radio" value="block" name="<?php echo "{$strTypeIdRoot}"; if ($type == "block") echo '" checked="checked'; ?>" /> Block</label><br>
								<label style="white-space:nowrap;"><input type="radio" value="selector" name="<?php echo "{$strTypeIdRoot}"; if ($type == "selector") echo '" checked="checked'; ?>" /> Selector</label>
							</td>
							<td><input type="text" size="14" name="addstyledrop_3_<?php echo $op_ct; ?>" id="addstyledrop_3_<?php echo $op_ct; ?>" value="<?php echo $typeval; ?>" /></td>
							<td style="border-right: 1px solid #e1e1e1;"><input type="text" size="14" name="addstyledrop_4_<?php echo $op_ct; ?>" id="addstyledrop_4_<?php echo $op_ct; ?>" value="<?php echo $item['classes']; ?>" /></td>
							<td style="border-right: 1px solid #e1e1e1;">
								<table id="addstyledrop_5_<?php echo $op_ct; ?>">
									<tr>
										<th style="padding-top:0;">Style</th>
										<th style="padding-top:0;"><?php _e('Value', TCS_TEXTDOMAIN); ?></th>
										<th style="padding-top:0;"><?php _e('Delete', TCS_TEXTDOMAIN); ?></th>
									</tr>
								<?php
								$tp_items = $item["styles"];
								$tp_ct = 0;	// Incremented at TOP of loop, so rows numbered from 1
								foreach ($tp_items as $key => $tp_item) {
									$tp_ct++;
								?>
									<tr id="tprow_5_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>">
										<td>
											<input type="text" size="14" id="addstyledrop_5_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_key" name="addstyledrop_5_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_key" value="<?php echo $key; ?>" />
										</td>
										<td>
											<input type="text" size="14" id="addstyledrop_5_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_val" name="addstyledrop_5_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_val" value="<?php echo $tp_item; ?>" />
										</td>
										<td><a style="cursor:pointer;" onclick="delete_tp_row(5, <?php echo $op_ct; ?>, <?php echo $tp_ct; ?>)">X</a></td>
									</tr>
									<?php
								}
								?>
								</table>
								<div>
									<input value="<?php echo $tp_ct; ?>" type="hidden" id="tpcount_5_<?php echo $op_ct; ?>" name="tpcount_5_<?php echo $op_ct; ?>" />
									<button type="button" class="button-secondary" onclick="add_tp_row(<?php echo $op_ct; ?>,5)"><?php _e('Add new style', TCS_TEXTDOMAIN); ?></button>
								</div>
							</td>
							<td style="border-right: 1px solid #e1e1e1;">
								<table id="addstyledrop_6_<?php echo $op_ct; ?>">
									<tr>
										<th style="padding-top:0;"><?php _e('Attribute', TCS_TEXTDOMAIN); ?></th>
										<th style="padding-top:0;"><?php _e('Value', TCS_TEXTDOMAIN); ?></th>
										<th style="padding-top:0;"><?php _e('Delete', TCS_TEXTDOMAIN); ?></th>
									</tr>
								<?php
								$tp_items = $item["attributes"];
								$tp_ct = 0;	// Incremented at TOP of loop, so rows numbered from 1
								foreach ($tp_items as $key => $tp_item) {
									$tp_ct++;
								?>
									<tr id="tprow_6_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>">
										<td>
											<input type="text" size="14" id="addstyledrop_6_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_key" name="addstyledrop_6_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_key" value="<?php echo $key; ?>" />
										</td>
										<td>
											<input type="text" size="14" id="addstyledrop_6_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_val" name="addstyledrop_6_<?php echo $op_ct; ?>_<?php echo $tp_ct; ?>_val" value="<?php echo $tp_item; ?>" />
										</td>
										<td><a style="cursor:pointer;" onclick="delete_tp_row(6, <?php echo $op_ct; ?>, <?php echo $tp_ct; ?>)">X</a></td>
									</tr>
									<?php
								}
								?>
								</table>
								<div>
									<input value="<?php echo $tp_ct; ?>" type="hidden" id="tpcount_6_<?php echo $op_ct; ?>" name="tpcount_6_<?php echo $op_ct; ?>" />
									<button type="button" class="button-secondary" onclick="add_tp_row(<?php echo $op_ct; ?>,6)"><?php _e('Add new attribute', TCS_TEXTDOMAIN); ?></button>
								</div>
							</td>
							<td><input type="checkbox" name="addstyledrop_7_<?php echo $op_ct; ?>" id="addstyledrop_7_<?php echo $op_ct; ?>" value="1" <?php if(intval($item['exact']) == 1) {?>checked="checked"<?php } ?> /></td>
							<td><input type="checkbox" name="addstyledrop_8_<?php echo $op_ct; ?>" id="addstyledrop_8_<?php echo $op_ct; ?>" value="1" <?php if(intval($item['wrapper']) == 1) {?>checked="checked"<?php } ?> /></td>
							<td><strong><a style="cursor:pointer;" onclick="rowremove(<?php echo $op_ct; ?>)">X</a></strong></td>
						</tr>
						<?php
						}	// End foreach ($items as $item)
						?>
						</tbody>
						<tfoot>
							<tr valign="top">
								<th scope="row"><?php _e('Title *', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Type *', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Type Value *', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('CSS Class(es)', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('CSS Styles', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Attributes', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Exact', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Wrapper', TCS_TEXTDOMAIN); ?></th>
								<th scope="row"><?php _e('Remove', TCS_TEXTDOMAIN); ?></th>
							</tr>
						</tfoot>
					</table>
					<p>
							<input value="<?php echo $op_ct; ?>" type="hidden" id="addstyledrop_number" name="addstyledrop_number" />
							<button type="button" class="button-secondary" onclick="add()"><?php _e('Add new style', TCS_TEXTDOMAIN); ?></button>
					</p>
					<script type="text/javascript">
						function add_tp_row(main_row,tp_id) {
							var rowcount = parseInt(document.getElementById('tpcount_' + tp_id + '_' + main_row).value);
							rowcount++;
							var table = document.getElementById('addstyledrop_' + tp_id + '_' + main_row);
							var rowHTML ='<tr id="tprow_' + tp_id + '_' + main_row + '_' + rowcount + '"><td>';
							rowHTML += '<input type="text" size="14" id="addstyledrop_' + tp_id + '_' + main_row + '_' + rowcount + '_key" name="addstyledrop_' + tp_id + '_' + main_row + '_' + rowcount + '_key" value="" />';
							rowHTML += '</td><td>';
							rowHTML += '<input type="text" size="14" id="addstyledrop_' + tp_id + '_' + main_row + '_' + rowcount + '_val" name="addstyledrop_' + tp_id + '_' + main_row + '_' + rowcount + '_val" value="" />';
							rowHTML += '</td><td><a style="cursor:pointer;" onclick="delete_tp_row(' + tp_id + ',' + main_row + ',' + rowcount + ')">X</a></td></tr>';
							table.insertAdjacentHTML( "beforeend", rowHTML );
							document.getElementById('tpcount_' + tp_id + '_' + main_row).value = rowcount;
						}

						function delete_tp_row(tp_id, main_row, tp_row) {
							document.getElementById('tprow_' + tp_id + '_' + main_row + '_' + tp_row).style.display = 'none';
							document.getElementById('addstyledrop_' + tp_id + '_' + main_row + '_' + tp_row + '_key').value = '';
						}

						function rowremove(row) {
							document.getElementById('addstyledrop_row_' + row).style.display = 'none';
							document.getElementById('addstyledrop_0_' + row).value = '';
						}

						function add() {
							rowcount = parseInt(document.getElementById('addstyledrop_number').value) + 1;
							var rowHTML = '<tr id="addstyledrop_row_' + rowcount + '" valign="top"';
							if (rowcount % 2 == 0) rowHTML += ' style="background-color:#f3f3f3;"';
							rowHTML += '><td><input type="text" name="addstyledrop_0_' + rowcount + '" id="addstyledrop_0_' + rowcount + '" value="" /></td>';
							rowHTML += '<td>';
    						rowHTML += '<label style="white-space:nowrap;"><input type="radio" name="addstyledrop_1_' + rowcount + '" value="inline" checked="checked" /> Inline</label><br>';
							rowHTML += '<label style="white-space:nowrap;"><input type="radio" name="addstyledrop_1_' + rowcount + '" value="block" /> Block</label><br>';
							rowHTML += '<label style="white-space:nowrap;"><input type="radio" name="addstyledrop_1_' + rowcount + '" value="selector" /> Selector</label>';
							rowHTML += '</td>';
							rowHTML += '<td><input type="text" size="14" name="addstyledrop_3_' + rowcount + '" id="addstyledrop_3_' + rowcount + '" value="" /></td>';
							rowHTML += '<td style="border-right: 1px solid #e1e1e1;"><input type="text" size="14" name="addstyledrop_4_' + rowcount + '" id="addstyledrop_4_' + rowcount + '" value="" /></td>';
							rowHTML += '<td style="border-right: 1px solid #e1e1e1;">';
							rowHTML += '	<table id="addstyledrop_5_' + rowcount + '">';
							rowHTML += '		<tr>';
							rowHTML += '			<th style="padding-top:0;">Style</th>';
							rowHTML += '			<th style="padding-top:0;">Value</th>';
							rowHTML += '			<th style="padding-top:0;">Delete</th>';
							rowHTML += '		</tr>';
							rowHTML += '	</table>';
							rowHTML += '	<div>';
							rowHTML += '<input value="0" type="hidden" id="tpcount_5_' + rowcount + '" name="tpcount_5_' + rowcount + '" />';
							rowHTML += '<button type="button" class="button-secondary" onclick="add_tp_row(' + rowcount + ',5)"><?php _e('Add new style', TCS_TEXTDOMAIN); ?></button>';
							rowHTML += '	</div>';
							rowHTML += '</td>';
							rowHTML += '<td style="border-right: 1px solid #e1e1e1;">';
							rowHTML += '	<table id="addstyledrop_6_' + rowcount + '">';
							rowHTML += '		<tr>';
							rowHTML += '			<th style="padding-top:0;">Attribute</th>';
							rowHTML += '			<th style="padding-top:0;">Value</th>';
							rowHTML += '			<th style="padding-top:0;">Delete</th>';
							rowHTML += '		</tr>';
							rowHTML += '	</table>';
							rowHTML += '	<div>';
							rowHTML += '<input value="0" type="hidden" id="tpcount_6_' + rowcount + '" name="tpcount_6_' + rowcount + '" />';
							rowHTML += '<button type="button" class="button-secondary" onclick="add_tp_row(' + rowcount + ',6)"><?php _e('Add new attribute', TCS_TEXTDOMAIN); ?></button>';
							rowHTML += '	</div>';
							rowHTML += '</td>';
							rowHTML += '<td><input type="checkbox" name="addstyledrop_7_' + rowcount + '" id="addstyledrop_7_' + rowcount + '" value="1" /></td>';
							rowHTML += '<td><input type="checkbox" name="addstyledrop_8_' + rowcount + '" id="addstyledrop_8_' + rowcount + '" value="1" /></td>';
							rowHTML += '<td><a style="cursor:pointer;" onclick="rowremove(' + rowcount + ')">X</a></td>';
							rowHTML += '</tr>';
							document.getElementById("tcs_addstyledrop").insertAdjacentHTML( "beforeend", rowHTML );
							document.getElementById('addstyledrop_number').value = rowcount;
						}
					</script>
				</div><!-- tcs_options div -->
				<hr/>
				<p style="text-align:center;margin:1em 0;"><input type="hidden" name="tcs_backend_update" value="doit" />
				<input type="submit" name="Save" value="<?php _e('Save all settings', TCS_TEXTDOMAIN); ?>" class="button-primary" /></p>
			</form>
		</div>
<?php

}	// tcs_backend_page()

?>
