<?php
/*
 * Plugin Name: Background Update Tester
 * Description: Visit the Dashboard → Update Tester screen after activating this plugin. (If using multisite, visit Updates → Update Tester in the network admin.)
 * Plugin URI: http://wordpress.org/plugins/background-update-tester/
 * Author: the WordPress team
 * Author URI: http://wordpress.org/
 * Version: 1.1
 * Network: true
 * Text Domain: background-update-tester
 */

class Background_Update_Tester_Plugin {

	function __construct() {
		if ( is_multisite() )
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		else
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_menu() {
		load_plugin_textdomain( 'background-update-tester' );
		$top_level_menu = is_multisite() ? 'update-core.php' : 'index.php';
		add_submenu_page(
			$top_level_menu,
			__( 'Background Update Tester' , 'background-update-tester' ),
			__( 'Update Tester' , 'background-update-tester' ),
			is_multisite() ? 'manage_network_options' : 'manage_options',
			'background-updates-debugger',
			array( $this, 'content_wrapper' )
		);
	}

	function content_wrapper() {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		echo '<div class="wrap">';
		screen_icon();
		echo '<h2>' . __( 'Background Update Tester' , 'background-update-tester' ) . '</h2>';
		$this->content();
		echo '</div>';
	}

	function content() {
		echo '<p>' . __( "Automatic background updates require a number of conditions to be met." , 'background-update-tester' );
		echo ' <span id="testing-now">' . __( 'Testing now...' , 'background-update-tester' ) . '</span>';
		echo '</p>';
		flush();

		$tests = $this->tests();

		$failures = wp_list_filter( $tests, array( 'severity' => 'fail' ) ) || wp_list_filter( $tests, array( 'severity' => 'warning' ) );
		if ( $failures )
		echo '<p>' . __( 'Your hosting company, support forum volunteers, or a friendly developer may be able to use this information to help you:', 'default' ) . '</p>';

		echo '<ul style="margin: 3em 2em">';
		echo "<script>jQuery('#testing-now').hide();</script>";

		foreach ( $tests as $item ) {

			switch ( $item->severity ) {
				case 'warning':
					$status = '<strong style="color:orange;">' . __( 'WARNING:' , 'background-update-tester' ) . '</strong>';
					break;
				case 'pass':
					$status = '<strong style="color:green;">' . __( 'PASS:' , 'background-update-tester' ) . '</strong>';
					break;
				case 'info':
					$status = '<strong style="color:black;">' . __( 'INFO:' , 'background-update-tester' ) . '</strong>';
					break;
				case 'fail':
					$status = '<strong style="color:red;">' . __( 'FAIL:' , 'background-update-tester' ) . '</strong>';
					break;
			}

			printf( '<li>%s %s</li>', $status, $item->desc );
		}
		echo '</ul>';

		echo '<h3>';
		if ( $failures ) {
			if ( apply_filters( 'send_core_update_notification_email', true ) ) {
				printf( __( 'This site <strong>is not</strong> able to apply these updates automatically. But we&#8217;ll email %s when there is a new security release.' , 'default' ), esc_html( get_site_option( 'admin_email' ) ) );
			} else {
				_e( 'This site <strong>is not</strong> able to apply these updates automatically.', 'default' );
			}
		} else {
			_e( 'This site <strong>is</strong> able to apply these updates automatically. Cool!', 'default' );
		}
		echo '</h3>';

		echo '<p>' . sprintf( __( 'If you experience any issues or need support, the volunteers in the <a href="%s">WordPress.org support forums</a> may be able to help.' , 'background-update-tester' ),
			__( 'http://wordpress.org/support/', 'default' ) ). '</p>';
	}

	function tests() {
		$tests = array();
		foreach ( get_class_methods( $this ) as $method ) {
			if ( 'test_' != substr( $method, 0, 5 ) )
				continue;

			$result = call_user_func( array( $this, $method ) );

			// Is it applicable to this install?
			if ( false === $result || null === $result )
				continue;

			$result = (object) $result;
			if ( empty( $result->severity ) )
				$result->severity = 'warning';

			$tests[ $method ] = $result;
		}
		return $tests;
	}

	function test_https_supported() {
		$support = wp_http_supports( array( 'ssl' ) );
		return array(
			'desc' => $support ?
				__( 'Your WordPress install can communicate with WordPress.org securely.', 'background-update-tester' ) :
				__( 'Your WordPress install cannot communicate with WordPress.org securely. Talk to your web host about OpenSSL support for PHP.', 'background-update-tester' ),
			'severity' => $support ? 'pass' : 'fail',
		);
	}

	function test_constant_FILE_MODS() {
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			return array(
				'desc' => sprintf( __( 'The %1$s constant is defined as %2$s.', 'background-update-tester' ), '<code>DISALLOW_FILE_MODS</code>', 'true' ),
				'severity' => 'fail',
			);
		}
	}

	function test_constant_AUTOMATIC_UPDATER_DISABLED() {
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'desc' => sprintf( __( 'The %1$s constant is defined as %2$s.', 'background-update-tester' ), '<code>AUTOMATIC_UPDATER_DISABLED</code>', 'true' ),
				'severity' => 'fail',
			);
		}
	}

	function test_constant_WP_AUTO_UPDATE_CORE() {
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE ) {
			return array(
				'desc' => sprintf( __( 'The %1$s constant is defined as %2$s.', 'background-update-tester' ), '<code>WP_AUTO_UPDATE_CORE</code>', 'false' ),
				'severity' => 'fail',
			);
		}
	}

	function test_filters_automatic_updater_disabled() {
		if ( apply_filters( 'automatic_updater_disabled', false ) ) {
			return array(
				'desc' => sprintf( __( 'The <code>%2%s</code> filter returns <code>%2$s</code>.', 'background-update-tester' ), 'automatic_updater_disabled', 'true' ),
				'severity' => 'fail',
			);
		}
	}

	function test_if_failed_update() {
		$failed = get_site_option( 'auto_core_update_failed' );

		if ( ! $failed )
			return false;

		if ( ! empty( $failed['critical'] ) ) {
			$desc = __( "A previous automatic background update ended with a critical failure, so updates are now disabled." , 'background-update-tester' );
			$desc .= ' ' . __( "You would have received an email because of this." , 'background-update-tester' );
			$desc .= ' ' . __( "When you&#8217;ve been able to update using the &#8220;Update Now&#8221; button on Dashboard &rarr; Updates, we&#8217;ll clear this error for future update attempts." , 'background-update-tester' );
			$desc .= ' ' . sprintf( __( "The error code was %s." , 'background-update-tester' ), '<code>' . $failed['error_code'] . '</code>' );
			return array(
				'desc' => $desc,
				'severity' => 'warning',
			);
		}

		$desc = __( "A previous automatic background update could not occur." , 'background-update-tester' );
		if ( empty( $failed['retry'] ) )
			$desc .= ' ' . __( "You would have received an email because of this." , 'background-update-tester' );

		$desc .= ' ' . __( "We&#8217;ll try again with the next release." , 'background-update-tester' );
		$desc .= ' ' . sprintf( __( "The error code was %s." , 'background-update-tester' ), '<code>' . $failed['error_code'] . '</code>' );
		return array(
			'desc' => $desc,
			'severity' => 'warning'
		);
	}

	function _test_is_vcs_checkout( $context ) {
		$context_dirs = array( ABSPATH );
		$vcs_dirs = array( '.svn', '.git', '.hg', '.bzr' );
		$check_dirs = array();

		foreach ( $context_dirs as $context_dir ) {
			// Walk up from $context_dir to the root.
			do {
				$check_dirs[] = $context_dir;

				// Once we've hit '/' or 'C:\', we need to stop. dirname will keep returning the input here.
				if ( $context_dir == dirname( $context_dir ) )
					break;

			// Continue one level at a time.
			} while ( $context_dir = dirname( $context_dir ) );
		}

		$check_dirs = array_unique( $check_dirs );

		// Search all directories we've found for evidence of version control.
		foreach ( $vcs_dirs as $vcs_dir ) {
			foreach ( $check_dirs as $check_dir ) {
				if ( $checkout = @is_dir( rtrim( $check_dir, '\\/' ) . "/$vcs_dir" ) ) {
					break 2;
				}
			}
		}

		if ( $checkout && ! apply_filters( 'automatic_updates_is_vcs_checkout', true, $context ) ) {
			return array(
				'desc' => sprintf( __( 'The folder %s was detected as being under version control (%s), but the %s filter is allowing updates.' , 'background-update-tester' ),
					'<code>' . $check_dir . '</code>', "<code>$vcs_dir</code>", '<code>automatic_updates_is_vcs_checkout</code>' ),
				'severity' => 'info',
			);
		}

		if ( $checkout ) {
			return array(
				'desc' => sprintf( __( 'The folder %s was detected as being under version control (%s).' , 'background-update-tester' ),
					'<code>' . $check_dir . '</code>', "<code>$vcs_dir</code>" ),
				'severity' => 'fail',
			);
		}

		return array(
			'desc' => __( 'No version control systems were detected.' , 'background-update-tester' ),
			'severity' => 'pass'
		);
	}

	function test_vcs_ABSPATH() {
		$result = $this->_test_is_vcs_checkout( ABSPATH );
		$result['desc'] = sprintf( __( '%s' , 'background-update-tester' ), $result['desc'] );
		return $result;
	}

	function test_check_wp_filesystem_method() {
		$skin = new Automatic_Upgrader_Skin;
		$success = $skin->request_filesystem_credentials( false, ABSPATH );

		if ( ! $success ) {
			$desc = __( 'Your installation of WordPress prompts for FTP credentials to perform updates.' , 'background-update-tester' );
			$desc .= ' ' . __( '(Your site is performing updates over FTP due to file ownership. Talk to your hosting company.)' , 'background-update-tester' );

			return array(
				'desc' => $desc,
				'severity' => 'fail',
			);
		}

		return array(
			'desc' => __( 'Your installation of WordPress doesn&#8217;t require FTP credentials to perform updates.' , 'background-update-tester' ),
			'severity' => 'pass',
		);
	}

	function test_all_files_writable() {
		global $wp_filesystem;
		include ABSPATH . WPINC . '/version.php'; // $wp_version; // x.y.z

		$skin = new Automatic_Upgrader_Skin;
		$success = $skin->request_filesystem_credentials( false, ABSPATH );

		if ( ! $success )
			return false;

		WP_Filesystem();
		
		if ( 'direct' != $wp_filesystem->method )
			return false;
	
		$checksums = get_core_checksums( $wp_version, 'en_US' );
		$dev = ( false !== strpos( $wp_version, '-' ) );
		// Get the last stable version's files and test against that
		if ( ! $checksums && $dev )
			$checksums = get_core_checksums( (float) $wp_version - 0.1, 'en_US' );

		// There aren't always checksums for development releases, so just skip the test if we still can't find any
		if ( ! $checksums && $dev )
			return false;

		if ( ! $checksums ) {
			$desc = sprintf( __( 'Couldn#8217;t retrieve a list of the checksums for WordPress %s.' , 'background-update-tester' ), $wp_version );
			$desc .= ' ' . __( 'This could mean that connections are failing to WordPress.org.' , 'background-update-tester' );
			return array(
				'desc' => $desc,
				'severity' => 'warning',
			);
		}

		$unwritable_files = array();
		foreach ( array_keys( $checksums ) as $file ) {
			if ( 'wp-content' == substr( $file, 0, 10 ) )
				continue;
			if ( ! file_exists( ABSPATH . '/' . $file ) )
				continue;
			if (  ! is_writable( ABSPATH . '/' . $file ) )
				$unwritable_files[] = $file;
		}

		if ( $unwritable_files ) {
			if ( count( $unwritable_files ) > 20 ) {
				$unwritable_files = array_slice( $unwritable_files, 0, 20 );
				$unwritable_files[] = '...';
			}
			return array(
				'desc' => __( 'Some files are not writable by WordPress:' , 'background-update-tester' ) . ' <ul><li>' . implode( '</li><li>', $unwritable_files ) . '</li></ul>',
				'severity' => 'fail',
			);
		} else {
			return array(
				'desc' => __( 'All of your WordPress files are writable.' , 'background-update-tester' ),
				'severity' => 'pass',
			);
		}
	}

	function test_accepts_dev_updates() {
		include ABSPATH . WPINC . '/version.php'; // $wp_version; // x.y.z
		// Only for dev versions
		if ( false === strpos( $wp_version, '-' ) )
			return false;

		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && ( 'minor' === WP_AUTO_UPDATE_CORE || false === WP_AUTO_UPDATE_CORE ) ) {
			return array(
				'desc' => sprintf( __( 'WordPress development updates are blocked by the %s constant.' , 'background-update-tester' ),
					'<code>WP_AUTO_UPDATE_CORE</code>' ),
				'severity' => 'fail',
			);
		}

		if ( ! apply_filters( 'allow_dev_auto_core_updates', $wp_version ) ) {
			return array(
				'desc' => sprintf( __( 'WordPress development updates are blocked by the %s filter.' , 'background-update-tester' ),
					'<code>allow_dev_auto_core_updates</code>' ),
				'severity' => 'fail',
			);
		}
	}

	function test_accepts_minor_updates() {
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE ) {
			return array(
				'desc' => sprintf( __( 'WordPress security and maintenance releases are blocked by %s.' , 'background-update-tester' ),
					"<code>define( 'WP_AUTO_UPDATE_CORE', false );</code>" ),
				'severity' => 'fail',
			);
		}

		if ( ! apply_filters( 'allow_minor_auto_core_updates', true ) ) {
			return array(
				'desc' => sprintf( __( 'WordPress security and maintenance releases are blocked by the %s filter.' , 'background-update-tester' ),
					'<code>allow_minor_auto_core_updates</code>' ),
				'severity' => 'fail',
			);
		}
	}
}
new Background_Update_Tester_Plugin;
