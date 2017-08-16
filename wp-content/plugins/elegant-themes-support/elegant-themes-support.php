<?php
/*
 * Plugin Name: Elegant Themes Support
 * Plugin URI: http://elegantthemes.com
 * Description: Creates a temporary account to use for support queries
 * Version: 1.3
 * Author: Elegant Themes
 * Author URI: http://elegantthemes.com
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ET_Support_Account {
	/**
	 * Self instance of the object
	 *
	 * @var object
	 */
	private static $_this;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	var $version = '1.3';

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	var $options;

	/**
	 * Support account name
	 *
	 * @var string
	 */
	var $account_name = 'elegant_themes_support_account';

	/**
	 * Support page name
	 *
	 * @var string
	 */
	var $settings_page_name = 'et_support_settings_page';

	/**
	 * Plugin options name in the database
	 *
	 * @var string
	 */
	var $options_name = 'et_support_options';

	/**
	 * Cron name
	 *
	 * @var string
	 */
	var $cron_name = 'et_cron_delete_support_account';

	/**
	 * Expiration time
	 *
	 * @var string
	 */
	var $expiration_time = '+4 days';

	function __construct() {
		// Don't allow more than one instance of the class
		if ( isset( self::$_this ) ) {
			wp_die( sprintf( esc_html__( '%s is a singleton class and you cannot create a second instance.', 'et_support' ),
				get_class( $this ) )
			);
		}

		self::$_this = $this;

		$this->get_options();

		$this->maybe_set_site_id();

		add_action( 'plugins_loaded', array( $this, 'localization' ) );

		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_settings_link' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

		register_deactivation_hook( __FILE__, array( $this, 'delete_account' ) );

		add_action( $this->cron_name, array( $this, 'cron_maybe_delete_account' ) );
	}

	/**
	 * Return an instance of the object
	 *
	 * @return object
	 */
	static function get_this() {
		return self::$_this;
	}

	/**
	 * Add settings link to the plugin on WP-Admin / Plugins page
	 *
	 * @param array $links Default plugin links
	 * @return array Plugin links
	 */
	function add_settings_link( $links ){
		$settings = sprintf( '<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'tools.php?page=et_support_account_page' ) ),
			esc_html__( 'Settings', 'et_support' )
		);
		array_push( $links, $settings );

		return $links;
	}

	/**
	 * Add plugin localization
	 * Domain: et_support
	 *
	 * @return void
	 */
	function localization() {
		load_plugin_textdomain( 'et_support', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Clear "Delete Account" cron hook
	 *
	 * @return void
	 */
	function clear_delete_cron() {
		wp_clear_scheduled_hook( $this->cron_name );
	}

	/**
	 * Delete the support account if it's expired or the expiration date is not set
	 *
	 * @return void
	 */
	function cron_maybe_delete_account() {
		if ( ! username_exists( $this->account_name ) ) {
			return false;
		}

		if ( isset( $this->options['date_created'] ) ) {
			$this->maybe_delete_expired_account();
		} else {
			// if the expiration date isn't set, delete the account anyway
			$this->delete_account();
		}
	}

	/**
	 * Schedule account removal check
	 *
	 * @return void
	 */
	function init_cron_delete_account() {
		$this->clear_delete_cron();

		wp_schedule_event( time(), 'twicedaily', $this->cron_name );
	}

	/**
	 * Get plugin options
	 *
	 * @return void
	 */
	function get_options() {
		$this->options = get_option( $this->options_name );
	}

	function maybe_set_site_id() {
		$site_id = get_option( 'et_support_site_id' );

		if ( ! empty( $site_id ) ) {
			return;
		}

		$site_id = '';

		$send_to_api = array(
			'action' => 'get_site_id',
		);

		$settings = array(
			'timeout' => 30,
			'body'    => $send_to_api,
		);

		$request = wp_remote_post( 'https://www.elegantthemes.com/api/token.php', $settings );

		if ( is_wp_error( $request ) ) {
			$request = wp_remote_post( 'https://cdn.elegantthemes.com/api/token.php', $settings );
		}

		if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) == 200 ){
			$response = unserialize( wp_remote_retrieve_body( $request ) );

			if ( ! empty( $response['site_id'] ) ) {
				$site_id = sanitize_text_field( $response['site_id'] );
			}
		}

		update_option( 'et_support_site_id', $site_id );
	}

	/**
	 * Add the plugin settings page ( WP-Admin / Tools / Elegant Themes Support Account )
	 *
	 * @return void
	 */
	function register_settings_page() {
		add_submenu_page( 'tools.php', esc_html__( 'Elegant Themes Support Account', 'et_support' ), esc_html__( 'Elegant Themes Support Account', 'et_support' ), 'manage_options', 'et_support_account_page', array( $this, 'account_page_settings' ) );
	}

	/**
	 * Display the plugin settings page
	 *
	 * @return void
	 */
	function account_page_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( -1 );
		}

		$message = '';

		$account_exists = username_exists( $this->account_name );

		$token_action = $account_exists ? 'regenerate' : 'generate';

		if ( isset( $_POST['token_delete'] ) || isset( $_POST['token_action'] ) ) {
			if ( isset( $_POST['token_delete'] ) ) {
				$message = $this->delete_account();
			} else if ( isset( $_POST['token_action'] ) ) {
				switch ( $_POST['token_action'] ) {
					case 'generate' :
						if ( ! $account_exists ) {
							$result = $this->generate_new_account();

							$message = $result;
						} else {
							$message = new WP_Error( 'account_exists', esc_html__( 'Support account has been created already.', 'et_support' ) );
						}

						break;

					case 'regenerate' :
						if ( $account_exists ) {
							if ( ! is_wp_error( $message = $this->delete_account() ) ) {
								$result = $this->generate_new_account( $regenerate_account = true );

								$message = $result;
							}
						} else {
							$message = new WP_Error( 'regenerate_account_exists', esc_html__( 'Token can\'t be regenerated. Support account doesn\'t exist.', 'et_support' ) );
						}

						break;
				}
			}

			// update variables
			$account_exists = username_exists( $this->account_name );
			$token_action = $account_exists ? 'regenerate' : 'generate';
		} else {
			// delete the account if it's expired
			$this->maybe_delete_expired_account();
		}

		printf(
			'<div class="wrap">
				<h2>%1$s</h2>

				%7$s

				<p>%3$s</p>

				%6$s

				<form method="post">
					%2$s
					%5$s
					<input name="token_action" type="hidden" value="%4$s" />
				</form>
			</div>',
			esc_html__( 'Elegant Themes Support Account', 'et_support' ),
			get_submit_button( ( 'regenerate' === $token_action ? esc_html__( 'Regenerate Token', 'et_support' ) : esc_html__( 'Generate New Token', 'et_support' ) ), 'primary', 'submit', false ),
			__( 'This plugin is used to securely create an admin account that our support team can use to log in to your WordPress Dashboard. The purpose of this plugin is to be able to create an account without sharing the password. Instead, a secret token is created that can be shared with our team that will allow them access for a limited time. After we have finished assisting you, you can disable the plugin to automatically remove the new account. Tokens created using this plugin will expire after 4 days, after which a new token will need to be generated to grant access once more. <br><br> Please click the button below to generate a new token, and then send it to the moderator who has requested access using a Private Message on our support forums.', 'et_support' ),
			esc_attr( $token_action ),
			( $account_exists ? get_submit_button( esc_html__( 'Delete Token', 'et_support' ), 'delete', 'token_delete', false ) : '' ),
			$this->get_token_info(),
			$this->get_status_message( $message )
		);
	}

	/**
	 * Generate random token
	 *
	 * @param  integer $password_length Token Length
	 * @return string  $token           Token
	 */
	function generate_token( $length = 17 ) {
		$symbols = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^*()-=+";
		$token = substr( str_shuffle( $symbols ), 0, $length );

		return $token;
	}

	/**
	 * Generate password from token
	 *
	 * @param  string $token    Token
	 * @return string $password Password
	 */
	function generate_password( $token ) {
		global $wp_version;

		$salt = '';

		$site_id = get_option( 'et_support_site_id' );

		if ( empty( $site_id ) ) {
			return false;
		}

		$send_to_api = array(
			'action'  => 'get_salt',
			'site_id' => $site_id,
		);

		$options = array(
			'timeout'    => 30,
			'body'		 => $send_to_api,
			'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
		);

		$request = wp_remote_post( 'https://www.elegantthemes.com/api/token.php', $options );

		if ( is_wp_error( $request ) ) {
			$request = wp_remote_post( 'https://cdn.elegantthemes.com/api/token.php', $options );
		}

		if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) == 200 ){
			$response = unserialize( wp_remote_retrieve_body( $request ) );

			if ( ! empty( $response['salt'] ) ) {
				$salt = sanitize_text_field( $response['salt'] );
			}
		}

		if ( empty( $salt ) ) {
			return false;
		}

		$password = hash( 'sha256', $token . $salt );

		return $password;
	}

	/**
	 * Get token expiration date
	 *
	 * @return string Formatted date string
	 */
	function get_expiration_date() {
		if ( ! isset( $this->options['date_created'] ) ) {
			return new WP_Error( 'date_created_missing', esc_html__( 'Token expiration date is not set. You should regenerate the token.', 'et_support' ) );
		}

		$format = sprintf(
			'%1$s, %2$s',
			get_option( 'date_format' ),
			get_option( 'time_format' )
		);

		$expiration_date_unix = strtotime( $this->expiration_time, $this->options['date_created'] );

		// use gmt offset to display local time
		$gmt_offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

		$expiration_date = date_i18n( $format, $expiration_date_unix + $gmt_offset );

		return $expiration_date;
	}

	/**
	 * Get a token information
	 *
	 * @return string  Token information
	 */
	function get_token_info() {
		$output = '';

		if ( isset( $this->options['token'] ) ) {
			$output = sprintf(
				'<p style="font-size: 15px;">%1$s</p>',
				sprintf(
					__( 'Current Token: <br/><code style="padding: 10px; display: inline-block; font-size: 18px; font-style: italic; margin-top: 10px;">%1$s</code>', 'et_support' ),
					esc_html( $this->options['token'] . '|' . get_option( 'et_support_site_id' ) )
				)
			);

			$output .= sprintf(
				'<p><small>%1$s</small></p>',
				( ! is_wp_error( $expiration_date = $this->get_expiration_date() )
					? esc_html__( 'This token will expire on ', 'et_support' ) . esc_html( $expiration_date )
					: $expiration_date->get_error_message()
				)
			);
		}

		return $output;
	}

	/**
	 * Generate new account
	 *
	 * @return string | WP_Error  Success or error message
	 */
	function generate_new_account( $regenerate_account = false ) {
		$token = $this->generate_token();

		$password = $this->generate_password( $token );

		if ( ! $password ) {
			$message = esc_html__( 'Token generation failed. Please try again or contact Elegant Themes support for assistance.', 'et_support' );

			return $message;
		}

		$user_id = wp_insert_user( array(
			'user_login' => $this->account_name,
			'user_pass'  => $password,
			'role'       => 'administrator',
		) );

		if ( ! is_wp_error( $user_id ) ) {
			$message = $regenerate_account ? esc_html__( 'Token has been regenerated succesfully.', 'et_support' ) : esc_html__( 'Token has been created succesfully.', 'et_support' );

			$account_settings = array(
				'date_created' => time(),
				'token'        => $token,
			);

			update_option( $this->options_name, $account_settings );

			// update options variable
			$this->get_options();

			$this->init_cron_delete_account();
		} else {
			$message = new WP_Error( 'create_user_error', $user_id->get_error_message() );
		}

		return $message;
	}

	/**
	 * Delete the account if it's expired
	 *
	 * @return void
	 */
	function maybe_delete_expired_account() {
		if ( empty( $this->options['date_created'] ) ) {
			return;
		}

		$expiration_date_unix = strtotime( $this->expiration_time, $this->options['date_created'] );

		if ( time() >= $expiration_date_unix ) {
			$this->delete_account();
		}
	}

	/**
	 * Delete support account and the plugin options ( token, expiration date )
	 *
	 * @return true | WP_Error  True on success, WP_Error on failure
	 */
	function delete_account() {
		if ( defined( 'DOING_CRON' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/user.php' );
		}

		if ( ! username_exists( $this->account_name ) ) {
			return new WP_Error( 'get_user_data', esc_html__( 'Support account doesn\'t exist.', 'et_support' ) );
		}

		$support_account_data = get_user_by( 'login', $this->account_name );

		if ( $support_account_data ) {
			$support_account_id = $support_account_data->ID;

			if ( ! wp_delete_user( $support_account_id ) ) {
				return new WP_Error( 'delete_user', esc_html__( 'Support account hasn\'t been removed. Try to regenerate token again.' ) );
			}

			delete_option( $this->options_name );

			$this->remove_site_id();

			$this->clear_delete_cron();
		} else {
			return new WP_Error( 'get_user_data', esc_html__( 'Cannot get the support account data. Try to regenerate token again.' ) );
		}

		// update options variable
		$this->get_options();

		return esc_html__( 'Token has been deleted successfully. ', 'et_support' );
	}

	function remove_site_id() {
		$site_id = get_option( 'et_support_site_id' );

		if ( empty( $site_id ) ) {
			return;
		}

		$send_to_api = array(
			'action'  => 'remove_site_id',
			'site_id' => $site_id,
		);

		$settings = array(
			'timeout' => 30,
			'body'    => $send_to_api,
		);

		$request = wp_remote_post( 'https://www.elegantthemes.com/api/token.php', $settings );

		if ( is_wp_error( $request ) ) {
			$request = wp_remote_post( 'https://cdn.elegantthemes.com/api/token.php', $settings );
		}
	}

	/**
	 * Get success/error status
	 *
	 * @param  string | WP_Error  $message Success / error message
	 * @return string             $output  Success / error box html code
	 */
	function get_status_message( $message ) {
		$output = '';

		$is_error_message = is_wp_error( $message );

		if ( ! $is_error_message ) {
			if ( '' !== $message ) {
				$output = sprintf( '<p>%1$s</p>', esc_html( $message ) );
			}
		} else {
			$output = sprintf( '<p>%1$s</p>', esc_html( $message->get_error_message() ) );
		}

		if ( '' !== $output ) {
			$output = sprintf(
				'<div id="setting-error-settings_updated" class="%1$s settings-error">
					%2$s
				</div>',
				( $is_error_message ? 'error' : 'updated' ),
				$output
			);
		}

		return $output;
	}
}

new ET_Support_Account();