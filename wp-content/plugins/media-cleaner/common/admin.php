<?php

if ( !class_exists( 'MeowApps_Admin' ) ) {

	class MeowApps_Admin {

		public static $loaded = false;
		public static $admin_version = "1.2";

		public $prefix; 		// prefix used for actions, filters (mfrh)
		public $mainfile; 	// plugin main file (media-file-renamer.php)
		public $domain; 		// domain used for translation (media-file-renamer)

		public function __construct( $prefix, $mainfile, $domain ) {

			// Core Admin (used by all Meow Apps plugins)
			if ( !MeowApps_Admin::$loaded ) {
				if ( is_admin() ) {
					add_action( 'admin_menu', array( $this, 'admin_menu_start' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
					add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
				}
				MeowApps_Admin::$loaded = true;
			}

			// Variables for this plugin
			$this->prefix = $prefix;
			$this->mainfile = $mainfile;
			$this->domain = $domain;

			// Check if the free version is installed but there is license
			// TODO: In the future, this should be removed ideally
			if ( is_admin() ) {
				$license = get_option( $this->prefix . '_license', "" );
				if ( ( !empty( $license ) ) && !file_exists( plugin_dir_path( $this->mainfile ) . 'common/meowapps/admin.php' ) ) {
					add_action( 'admin_notices', array( $this, 'admin_notices_licensed_free' ) );
				}
			}
		}

		function admin_notices_licensed_free() {
			if ( isset( $_POST[$this->prefix . '_reset_sub'] ) ) {
				delete_option( $this->prefix . '_pro_serial' );
				delete_option( $this->prefix . '_license' );
				return;
			}
			echo '<div class="error">';
			echo '<p>It looks like you are using the free version of the plugin (<b>' . $this->mainfile . '</b>) but a license for the Pro version was also found. The Pro version might have been replaced by the Free version during an update (might be caused by a temporarily issue). If it is the case, <b>please download it again</b> from the <a target="_blank" href="https://store.meowapps.com">Meow Store</a>. If you wish to continue using the free version and clear this message, click on this button.';
			echo '<p>
				<form method="post" action="">
					<input type="hidden" name="' . $this->prefix . '_reset_sub" value="true">
					<input type="submit" name="submit" id="submit" class="button" value="Remove the license">
				</form>
			</p>
			';
			echo '</div>';
		}

		function display_ads() {
			return !get_option( 'meowapps_hide_ads', false );
		}

		function display_title( $title = "Meow Apps",
			$author = "By <a style='text-decoration: none;' href='http://meowapps.com' target='_blank'>Jordy Meow</a>" ) {
			if ( !empty( $this->prefix ) )
				$title = apply_filters( $this->prefix . '_plugin_title', $title );
			if ( $this->display_ads() ) {
				echo '<a class="meow-header-ad" target="_blank" href="http://www.shareasale.com/r.cfm?b=906810&u=767054&m=41388&urllink=&afftrack="">
				<img src="' . $this->common_url( 'img/wpengine.png' ) . '" height="60" border="0" /></a>';
			}
			?>
			<h1 style="line-height: 16px;">
				<img width="36" style="margin-right: 10px; float: left; position: relative; top: -5px;"
					src="<?php echo $this->meowapps_logo_url(); ?>"><?php echo $title; ?><br />
				<span style="font-size: 12px"><?php echo $author; ?></span>
			</h1>
			<div style="clear: both;"></div>
			<?php
		}

		function admin_enqueue_scripts() {
			wp_register_style( 'meowapps-core-css', $this->common_url( 'admin.css' ) );
			wp_enqueue_style( 'meowapps-core-css' );
		}

		function admin_menu_start() {
			if ( get_option( 'meowapps_hide_meowapps', false ) ) {
				register_setting( 'general', 'meowapps_hide_meowapps' );
				add_settings_field( 'meowapps_hide_ads', 'Meow Apps Menu', array( $this, 'meowapps_hide_dashboard_callback' ), 'general' );
				return;
			}

			// Creates standard menu if it does NOT exist
			global $submenu;
			if ( !isset( $submenu[ 'meowapps-main-menu' ] ) ) {
				add_menu_page( 'Meow Apps', 'Meow Apps', 'manage_options', 'meowapps-main-menu',
					array( $this, 'admin_meow_apps' ), 'dashicons-camera', 82 );
				add_submenu_page( 'meowapps-main-menu', __( 'Dashboard', 'meowapps' ),
					__( 'Dashboard', 'meowapps' ), 'manage_options',
					'meowapps-main-menu', array( $this, 'admin_meow_apps' ) );
			}

			add_settings_section( 'meowapps_common_settings', null, null, 'meowapps_common_settings-menu' );
			add_settings_field( 'meowapps_hide_meowapps', "Main Menu",
				array( $this, 'meowapps_hide_dashboard_callback' ),
				'meowapps_common_settings-menu', 'meowapps_common_settings' );
			add_settings_field( 'meowapps_hide_ads', "Ads",
				array( $this, 'meowapps_hide_ads_callback' ),
				'meowapps_common_settings-menu', 'meowapps_common_settings' );
			register_setting( 'meowapps_common_settings', 'meowapps_hide_meowapps' );
			register_setting( 'meowapps_common_settings', 'meowapps_hide_ads' );
		}

		function meowapps_hide_ads_callback() {
			$value = get_option( 'meowapps_hide_ads', null );
			$html = '<input type="checkbox" id="meowapps_hide_ads" name="meowapps_hide_ads" value="1" ' .
				checked( 1, get_option( 'meowapps_hide_ads' ), false ) . '/>';
	    $html .= __( '<label>Hide</label><br /><small>Doesn\'t display the ads.</small>', 'wp-retina-2x' );
	    echo $html;
		}

		function meowapps_hide_dashboard_callback() {
			$value = get_option( 'meowapps_hide_meowapps', null );
			$html = '<input type="checkbox" id="meowapps_hide_meowapps" name="meowapps_hide_meowapps" value="1" ' .
				checked( 1, get_option( 'meowapps_hide_meowapps' ), false ) . '/>';
	    $html .= __( '<label>Hide <b>Meow Apps</b> Menu</label><br /><small>Hide Meow Apps menu and all its components, for a nicer an faster WordPress admin UI. An option will be added in Settings > General to display it again.</small>', 'wp-retina-2x' );
	    echo $html;
		}

		function display_serialkey_box( $url = "https://meowapps.com/" ) {
			$html = '<div class="meow-box">';
      $html .= '<h3 class="' . ( $this->is_registered( $this->prefix ) ? 'meow-bk-blue' : 'meow-bk-red' ) . '">Pro Version ' .
        ( $this->is_registered( $this->prefix ) ? '(enabled)' : '(disabled)' ) . '</h3>';
      $html .= '<div class="inside">';
			echo $html;
			$html = apply_filters( $this->prefix . '_meowapps_license_input', ( 'More information about the Pro version here:
				<a target="_blank" href="' . $url . '">' . $url . '</a>.' ), $url );
      $html .= '</div>';
      $html .= '</div>';
			echo $html;
		}

		function is_registered() {
			return apply_filters( $this->prefix . '_meowapps_is_registered', false, $this->prefix  );
		}

		function check_install( $plugin ) {
			$pro = false;
			$pluginpath = get_home_path() . 'wp-content/plugins/' . $plugin . '-pro';
			if ( !file_exists( $pluginpath ) ) {
				$pluginpath = get_home_path() . 'wp-content/plugins/' . $plugin;
				if ( !file_exists( $pluginpath ) ) {
					$url = wp_nonce_url( "update.php?action=install-plugin&plugin=$plugin", "install-plugin_$plugin" );
					return "<a href='$url'><small><span class='' style='float: right;'>install</span></small></a>";
				}
			}
			else {
				$pro = true;
				$plugin = $plugin . "-pro";
			}

			$plugin_file = $plugin . '/' . $plugin . '.php';
			if ( is_plugin_active( $plugin_file ) ) {
				if ( $pro )
					return "<small><span style='float: right;'><span class='dashicons dashicons-heart' style='color: rgba(255, 63, 0, 1); font-size: 30px !important; margin-right: 10px;'></span></span></small>";
				else
					return "<small><span style='float: right;'><span class='dashicons dashicons-yes' style='color: #00b4ff; font-size: 30px !important; margin-right: 10px;'></span></span></small>";
			}
			else {
				$url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ),
					'activate-plugin_' . $plugin_file );
				return '<small><span style="color: black; float: right;">off
				(<a style="color: rgba(30,140,190,1); text-decoration: none;" href="' .
					$url . '">enable</a>)</span></small>';
			}
		}

		function common_url( $file ) {
			die( "Meow Apps: The function common_url( \$file ) needs to be overriden." );
			// Normally, this should be used:
			// return plugin_dir_url( __FILE__ ) . ( '\/common\/' . $file );
		}

		function meowapps_logo_url() {
			return $this->common_url( 'img/meowapps.png' );
		}

		function plugins_loaded() {
			if ( isset( $_GET[ 'tool' ] ) && $_GET[ 'tool' ] == 'error_log' ) {
 				$sec = "5";
 				header("Refresh: $sec;");
			}
		}

		function admin_meow_apps() {

			echo '<div class="wrap meow-dashboard">';
			if ( isset( $_GET['tool'] ) && $_GET['tool'] == 'phpinfo' ) {
				echo "<a href=\"javascript:history.go(-1)\">< Go back</a><br /><br />";
				echo '<div id="phpinfo">';
				ob_start();
				phpinfo();
				$pinfo = ob_get_contents();
				ob_end_clean();
				$pinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1', $pinfo );
				echo $pinfo;
				echo "</div>";
			}
			else if ( isset( $_GET['tool'] ) && $_GET['tool'] == 'error_log' ) {
				$errorpath = ini_get( 'error_log' );
				echo "<a href=\"javascript:history.go(-1)\">< Go back</a><br /><br />";
				echo '<div id="error_log">';
				if ( file_exists( $errorpath ) ) {
					echo "Now (auto-reload every 5 seconds):<br />[" . date( "d-M-Y H:i:s", time() ) . " UTC]<br /<br /><br />Errors (order by latest):";
					$errors = file_get_contents( $errorpath );
					$errors = explode( "\n", $errors );
					$errors = array_reverse( $errors );
					$errors = implode( "<br />", $errors );
					echo $errors;
				}
				else {
					echo "The PHP Error Logs cannot be found. Please ask your hosting service for it.";
				}
				echo "</div>";

			}
			else {

				?>
				<?php $this->display_title(); ?>
				<p>
				<?php _e( 'Meow Apps is run by Jordy Meow, a photographer and software developer living in Japan (and taking <a target="_blank" href="http://offbeatjapan.org">a lot of photos</a>). Meow Apps is a suite of plugins focusing on photography, imaging, optimization and it teams up with the best players in the community (other themes and plugins developers). For more information, please check <a href="http://meowapps.com" target="_blank">Meow Apps</a>.', 'meowapps' )
				?>
				</p>
				<div class="meow-row">
					<div class="meow-box meow-col meow-span_1_of_2 ">
						<h3 class=""><span class="dashicons dashicons-camera"></span> UI Plugins </h3>
						<ul class="">
							<li><b>WP/LR Sync</b> <?php echo $this->check_install( 'wplr-sync' ) ?><br />
								Bring synchronization from Lightroom to WordPress.</li>
							<li><b>Meow Lightbox</b> <?php echo $this->check_install( 'meow-lightbox' ) ?><br />
								Lightbox with EXIF information nicely displayed.</li>
							<li><b>Meow Gallery</b> <?php echo $this->check_install( 'meow-gallery' ) ?><br />
								Simple gallery to make your photos look better (Masonry and others).</li>
							<li><b>Audio Story for Images</b> <?php echo $this->check_install( 'audio-story-images' ) ?><br />
								Add audio to your images.</li>
						</ul>
					</div>
					<div class="meow-box meow-col meow-span_1_of_2">
						<h3 class=""><span class="dashicons dashicons-admin-tools"></span> System Plugins</h3>
						<ul class="">
							<li><b>Media File Renamer</b> <?php echo $this->check_install( 'media-file-renamer' ) ?><br />
								Nicer filenames and better SEO, automatically.</li>
							<li><b>Media Cleaner</b> <?php echo $this->check_install( 'media-cleaner' ) ?><br />
								Detect the files which are not in use.</li>
							<li><b>WP Retina 2x</b> <?php echo $this->check_install( 'wp-retina-2x' ) ?><br />
								The famous plugin that adds Retina support.</li>
							<li><b>WP Category Permalink</b> <?php echo $this->check_install( 'wp-category-permalink' ) ?><br />
								Allows you to select a main category (or taxonomy) for nicer permalinks.</li>
						</ul>
					</div>
				</div>

				<div class="meow-row">
					<div class="meow-box meow-col meow-span_2_of_3">
						<h3><span class="dashicons dashicons-admin-tools"></span> Common</h3>
						<div class="inside">
							<form method="post" action="options.php">
								<?php settings_fields( 'meowapps_common_settings' ); ?>
								<?php do_settings_sections( 'meowapps_common_settings-menu' ); ?>
								<?php submit_button(); ?>
							</form>
						</div>
					</div>

					<div class="meow-box meow-col meow-span_1_of_3">
						<h3><span class="dashicons dashicons-admin-tools"></span> Debug</h3>
						<div class="inside">
							<ul>
								<li><a href="?page=meowapps-main-menu&amp;tool=error_log">Display Error Log</a></li>
								<li><a href="?page=meowapps-main-menu&amp;tool=phpinfo">Display PHP Info</a></li>
							</ul>
						</div>
					</div>
				</div>

				<?php

			}

			echo "<br /><small style='color: lightgray;'>Meow Admin " . MeowApps_Admin::$admin_version . "</small></div>";
		}

		// HELPERS

		static function size_shortname( $name ) {
			$name = preg_split( '[_-]', $name );
			$short = strtoupper( substr( $name[0], 0, 1 ) );
			if ( count( $name ) > 1 )
				$short .= strtoupper( substr( $name[1], 0, 1 ) );
			return $short;
		}

	}

}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/meowapps/admin.php' ) ) {
	require( 'meowapps/admin.php' );
}

?>
