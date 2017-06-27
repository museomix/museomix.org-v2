<?php

class WPML_Elementor_Data_Settings implements IWPML_Page_Builders_Data_Settings {

	public function add_hooks() {
		add_filter( 'wpml_custom_field_values_for_post_signature', array( $this, 'add_data_custom_field_to_md5' ), 10, 2 );
	}

	/**
	 * @return string
	 */
	public function get_meta_field() {
		return '_elementor_data';
	}

	/**
	 * @return string
	 */
	public function get_node_id_field() {
		return 'id';
	}

	/**
	 * @return array
	 */
	public function get_fields_to_copy() {
		return array( '_elementor_version', '_elementor_edit_mode', '_elementor_css' );
	}

	/**
	 * @param array|string $data
	 *
	 * @return array
	 */
	public function convert_data_to_array( $data ) {
		$converted_data = $data;
		if ( is_array( $data ) ) {
			$converted_data = $data[0];
		}

		return json_decode( $converted_data, true );
	}

	/**
	 * @param array $data
	 *
	 * @return string
	 */
	public function prepare_data_for_saving( array $data ) {
		return wp_slash( wp_json_encode( $data ) );
	}

	/**
	 * @return string
	 */
	public function get_pb_name(){
		return 'Elementor';
	}

	/**
	 * @return array
	 */
	public function get_fields_to_save() {
		return array( '_elementor_data' );
	}

	/**
	 * @param array $custom_fields_values
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function add_data_custom_field_to_md5( array $custom_fields_values, $post_id ) {
		$custom_fields_values[] = get_post_meta( $post_id, $this->get_meta_field(), true );
		return $custom_fields_values;
	}
}