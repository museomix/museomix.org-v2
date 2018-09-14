<?php

/**
 * Class WPML_ACF
 */
class WPML_ACF {

	private $WPML_ACF_Requirements;
	private $WPML_ACF_Editor_Hooks;

	/**
	 * @return WPML_ACF_Worker
	 */
	public function init_worker() {
		global $wpdb;
		add_action( 'wpml_loaded', array( $this, 'init_acf_xliff' ) );
		add_action( 'wpml_loaded', array( $this, 'init_acf_pro' ) );
		add_action( 'wpml_loaded', array( $this, 'init_acf_field_annotations' ) );

		$this->WPML_ACF_Requirements = new WPML_ACF_Requirements();

		$this->WPML_ACF_Editor_Hooks = new WPML_ACF_Editor_Hooks();
		$this->WPML_ACF_Editor_Hooks->init_hooks();

		return $this->init_duplicated_post( $wpdb );
	}

	private function init_duplicated_post( $wpdb ) {
		$duplicated_post = new WPML_ACF_Duplicated_Post( $wpdb );

		return new WPML_ACF_Worker( $duplicated_post );
	}

	public function init_acf_xliff() {
		if ( defined( 'WPML_ACF_XLIFF_SUPPORT' ) && WPML_ACF_XLIFF_SUPPORT ) {
			if ( is_admin() ) {
				if ( class_exists( 'acf' ) ) {
					global $wpdb, $sitepress;
					$WPML_ACF_Xliff = new WPML_ACF_Xliff( $wpdb, $sitepress );
					$WPML_ACF_Xliff->init_hooks();
				}
			}
		}
	}

	public function init_acf_pro() {
		if ( class_exists( 'acf' ) ) {
			$WPML_ACF_Pro = new WPML_ACF_Pro();
		}
	}

	public function init_acf_field_annotations() {
		if ( class_exists( 'acf' ) ) {
			$WPML_ACF_Field_Annotations = new WPML_ACF_Field_Annotations();
		}
	}
}
