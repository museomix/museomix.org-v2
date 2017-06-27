<?php

class WPML_Beaver_Builder_Tab extends WPML_Beaver_Builder_Module_With_Items {

	protected function get_title( $field ) {
		switch( $field ) {
			case 'label':
				return esc_html__( 'Tab Item Label', 'wpml-page-builders' );

			case 'content':
				return esc_html__( 'Tab Item Content', 'wpml-page-builders' );

			default:
				return '';
		}
	}

}
