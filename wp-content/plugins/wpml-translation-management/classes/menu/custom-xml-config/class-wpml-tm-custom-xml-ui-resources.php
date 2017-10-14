<?php

/**
 * @author OnTheGo Systems
 */
class WPML_TM_Custom_XML_UI_Resources {
	private $is_vk_beautify_installed;
	private $wpml_wp_api;
	private $is_code_mirror_installed;

	private $wpml_tm_path;

	private $wpml_tm_url;

	function __construct( WPML_WP_API $wpml_wp_api ) {
		$this->wpml_wp_api              = $wpml_wp_api;
		$wpml_tm_path                   = $this->wpml_wp_api->constant( 'WPML_TM_PATH' );
		$this->wpml_tm_url              = $this->wpml_wp_api->constant( 'WPML_TM_URL' );
		$this->is_code_mirror_installed = file_exists( $wpml_tm_path . '/libraries/CodeMirror/lib/codemirror.js' );
		$this->is_vk_beautify_installed = file_exists( $wpml_tm_path . '/libraries/vkBeautify/vkbeautify.js' );
	}

	function admin_enqueue_scripts() {
		if ( $this->wpml_wp_api->is_tm_page( 'custom-xml-config' ) ) {
			$this->add_vk_beautify();
			$this->add_code_mirror();

			$wpml_tm_custom_xml_config_dependencies = array( 'jquery' );
			if ( $this->is_code_mirror_installed ) {
				$wpml_tm_custom_xml_config_dependencies[] = 'wpml-tm-custom-xml-editor';
			}

			wp_register_script( 'wpml-tm-custom-xml-config', $this->wpml_tm_url . '/res/js/custom-xml-config/wpml-tm-custom-xml.js', $wpml_tm_custom_xml_config_dependencies, WPML_TM_VERSION );
			wp_register_style( 'wpml-tm-custom-xml-config', $this->wpml_tm_url . '/res/css/custom-xml.css', array(), WPML_TM_VERSION );

			wp_enqueue_style( 'wpml-tm-custom-xml-config' );
			wp_enqueue_script( 'wpml-tm-custom-xml-config' );
		}
	}

	private function add_code_mirror() {
		if ( $this->is_code_mirror_installed ) {
			$this->add_code_mirror_scripts();
			$this->add_code_mirror_styles();
		}
	}

	private function add_code_mirror_scripts() {
		$code_mirror_url = $this->get_code_mirror_url();
		wp_register_script( 'codemirror', $code_mirror_url . '/lib/codemirror.js', array(), WPML_TM_VERSION );

		$wpml_tm_custom_xml_editor_dependencies = array( 'codemirror' );
		if ( $this->is_vk_beautify_installed ) {
			$wpml_tm_custom_xml_editor_dependencies[] = 'vkbeautify';
		}

		wp_register_script( 'wpml-tm-custom-xml-editor', $this->wpml_tm_url . '/res/js/custom-xml-config/wpml-tm-custom-xml-editor.js', $wpml_tm_custom_xml_editor_dependencies, WPML_TM_VERSION );

		$modes = array(
			'xml' => 'xml/xml.js',
		);

		$addons = array(
			'foldcode'      => 'fold/foldcode.js',
			'foldgutter'    => 'fold/foldgutter.js',
			'brace-fold'    => 'fold/brace-fold.js',
			'xml-fold'      => 'fold/xml-fold.js',
			'matchtag'      => 'edit/matchtags.js',
			'matchbrackets' => 'edit/matchbrackets.js',
			'closebrackets' => 'edit/closebrackets.js',
			'closetag'      => 'edit/closetag.js',
			'show-hint'     => 'hint/show-hint.js',
			'xml-hint'      => 'hint/xml-hint.js',
			'display-panel' => 'display/panel.js',
		);

		foreach ( $modes as $mode => $path ) {
			wp_enqueue_script( 'codemirror-mode-' . $mode, $code_mirror_url . '/mode/' . $path, array( 'wpml-tm-custom-xml-config' ), WPML_TM_VERSION );
		}

		foreach ( $addons as $addon => $path ) {
			wp_enqueue_script( 'codemirror-addon-' . $addon, $code_mirror_url . '/addon/' . $path, array( 'wpml-tm-custom-xml-config' ), WPML_TM_VERSION );
		}
	}

	private function add_code_mirror_styles() {
		$code_mirror_url = $this->get_code_mirror_url();

		wp_register_style( 'codemirror', $code_mirror_url . '/lib/codemirror.css', array( 'wpml-tm-custom-xml-config' ), WPML_TM_VERSION );
		wp_enqueue_style( 'codemirror-theme', $code_mirror_url . '/theme/dracula.css', array( 'codemirror' ), WPML_TM_VERSION );

		$addons = array(
			'foldgutter' => 'fold/foldgutter.css',
			'xml-hint'   => 'hint/show-hint.css',
		);
		foreach ( $addons as $addon => $path ) {
			wp_enqueue_style( 'codemirror-addon-' . $addon, $code_mirror_url . '/addon/' . $path, array( 'codemirror' ), WPML_TM_VERSION );
		}
	}

	private function add_vk_beautify() {
		if ( $this->is_vk_beautify_installed ) {
			wp_enqueue_script( 'vkbeautify', $this->wpml_tm_url . '/libraries/vkBeautify/vkbeautify.js', array(), WPML_TM_VERSION );
		}
	}

	/**
	 * @return string
	 */
	private function get_code_mirror_url() {
		return $this->wpml_tm_url . '/libraries/CodeMirror';
	}
}
