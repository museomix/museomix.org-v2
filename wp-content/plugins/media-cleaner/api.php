<?php

class Meow_WPMC_API {

	function __construct( $core, $admin, $engine ) {
		$this->core = $core;
		$this->engine = $engine;
		$this->admin = $admin;
		add_action( 'wp_ajax_wpmc_prepare_do', array( $this, 'wp_ajax_wpmc_prepare_do' ) );
		add_action( 'wp_ajax_wpmc_scan', array( $this, 'wp_ajax_wpmc_scan' ) );
		add_action( 'wp_ajax_wpmc_scan_do', array( $this, 'wp_ajax_wpmc_scan_do' ) );
		add_action( 'wp_ajax_wpmc_get_all_issues', array( $this, 'wp_ajax_wpmc_get_all_issues' ) );
		add_action( 'wp_ajax_wpmc_get_all_deleted', array( $this, 'wp_ajax_wpmc_get_all_deleted' ) );
		add_action( 'wp_ajax_wpmc_delete_do', array( $this, 'wp_ajax_wpmc_delete_do' ) );
		add_action( 'wp_ajax_wpmc_ignore_do', array( $this, 'wp_ajax_wpmc_ignore_do' ) );
		add_action( 'wp_ajax_wpmc_recover_do', array( $this, 'wp_ajax_wpmc_recover_do' ) );
		add_action( 'wp_ajax_wpmc_validate_option', array( $this, 'wp_ajax_wpmc_validate_option' ) );
	}

	/*******************************************************************************
	 * ASYNCHRONOUS AJAX FUNCTIONS
	 ******************************************************************************/

	function wp_ajax_wpmc_prepare_do() {
		$limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
		$limitsize = get_option( 'wpmc_posts_buffer', 5 );
		$finished = $this->engine->parse( $limit, $limitsize, $message ); // $message is set by run()
		echo json_encode(
			array(
				'success' => true,
				'finished' => $finished,
				'limit' => $limit + $limitsize,
				'message' => $message )
		);
		die();
	}

	function wp_ajax_wpmc_scan() {
		global $wpdb;

		$method = get_option( 'wpmc_method', 'media' );
		if ( !$this->admin->is_registered() )
			$method = 'media';

		if ( $method == 'files' ) {
			$output = null;
			$path = isset( $_POST['path'] ) ? $_POST['path'] : null;
			$files = $this->engine->get_files( $path );

			if ( $files === null ) {
				$output = array(
					'results' => array(), 
					'success' => false, 
					'message' => __( "Unavailable.", 'media-cleaner' )
				);
			}
			else {
				$output = array( 
					'results' => $files, 
					'success' => true, 
					'message' => __( "Files retrieved.", 'media-cleaner' ) 
				);
			}
			echo json_encode( $output );
			die();
		}

		if ( $method == 'media' ) {
			$limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
			$limitsize = get_option( 'wpmc_medias_buffer', 100 );
			$results = $this->engine->get_media_entries( $limit, $limitsize );
			$finished = count( $results ) < $limitsize;
			echo json_encode(
				array(
					'results' => $results,
					'success' => true,
					'finished' => $finished,
					'limit' => $limit + $limitsize,
					'message' => __( "Medias retrieved.", 'media-cleaner' ) )
			);
			die();
		}

		// No task.
		echo json_encode( array( 'success' => false, 'message' => __( "No task.", 'media-cleaner' ) ) );
		die();
	}

	function wp_ajax_wpmc_scan_do() {
		// For debug, to pretend there is a timeout
		//$this->core->deepsleep(10);
		//header("HTTP/1.0 408 Request Timeout");
		//exit;

		ob_start();
		$type = $_POST['type'];
		$data = $_POST['data'];
		$this->core->timeout_check_start( count( $data ) );
		$success = 0;
		if ( $type == 'file' ) {
			do_action( 'wpmc_check_file_init' ); // Build_CroppedFile_Cache() in pro core.php
		}
		foreach ( $data as $piece ) {
			$this->core->timeout_check();
			if ( $type == 'file' ) {
				$this->core->log( "Check File: {$piece}" );

				$result = ( $this->engine->check_file( $piece ) ? 1 : 0 );

				if ( $result )
					$success += $result;
			}
			else if ( $type == 'media' ) {
				$this->core->log( "Checking Media #{$piece}" );
				$result = ( $this->engine->check_media( $piece ) ? 1 : 0 );
				if ( $result )
					$success += $result;
			}
			$this->core->log();
			$this->core->timeout_check_additem();
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'type' => $type, 'data' => $data, 'success' => $success ),
				'message' => __( "Items checked.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_get_all_issues() {
		global $wpdb;
		$isTrash = ( isset( $_POST['isTrash'] ) && $_POST['isTrash'] == 1 ) ? true : false;
		$table_name = $wpdb->prefix . "mclean_scan";
		$q = "SELECT id FROM $table_name WHERE ignored = 0 AND deleted = " . ( $isTrash ? 1 : 0 );
		if ( $search = ( isset( $_POST['s'] ) && $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '' )
			$q = $wpdb->prepare( $q . ' AND path LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		$ids = $wpdb->get_col( $q );

		echo json_encode(
			array(
				'results' => array( 'ids' => $ids ),
				'success' => true,
				'message' => __( "List generated.", 'media-cleaner' )
			)
		);
		die;
	}

	function wp_ajax_wpmc_get_all_deleted() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$ids = $wpdb->get_col( "SELECT id FROM $table_name WHERE ignored = 0 AND deleted = 1" );
		echo json_encode(
			array(
				'results' => array( 'ids' => $ids ),
				'success' => true,
				'message' => __( "List generated.", 'media-cleaner' )
			)
		);
		die;
	}

	function wp_ajax_wpmc_delete_do() {
		ob_start();
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			$success += ( $this->core->delete( $piece ) ? 1 : 0 );
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'data' => $data, 'success' => $success ),
				'message' => __( "Status unknown.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_ignore_do() {
		ob_start();
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			$success += ( $this->core->ignore( $piece ) ? 1 : 0 );
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'data' => $data, 'success' => $success ),
				'message' => __( "Status unknown.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_recover_do() {
		ob_start();
		$data = $_POST['data'];
		$success = 0;
		foreach ( $data as $piece ) {
			$success +=  ( $this->core->recover( $piece ) ? 1 : 0 );
		}
		ob_end_clean();
		echo json_encode(
			array(
				'success' => true,
				'result' => array( 'data' => $data, 'success' => $success ),
				'message' => __( "Status unknown.", 'media-cleaner' )
			)
		);
		die();
	}

	function wp_ajax_wpmc_validate_option() {
		$name = $_POST['name']; // Option Name
		$value = $_POST['value']; // Option Value
		$value = wp_unslash( $value ); // Unescape backslashes
		$validated = $this->admin->validate_option( $name, $value );
		if ( $validated instanceof WP_Error ) { // Invalid value
			$error = array (
				'code' => $validated->get_error_code() ?: 'invalid_option',
				'message' => $validated->get_error_message() ?: __( "Invalid Option Value", 'media-cleaner' )
			);
			wp_send_json_error( $error );
		}
		wp_send_json_success();
	}
}