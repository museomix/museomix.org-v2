<?php
class WPML_ACF_Field_Annotations {

	public function __construct() {
		add_action('acf/create_field', array($this, 'acf_create_field'), 10, 1);
	}

	public function acf_create_field($field) {
		static $run_times = array();
		if (!isset($run_times[ $field['key'] ]) || $run_times[ $field['key'] ] == 0) {
			$has_element_with_display_translated = false;
			$current_language = apply_filters('wpml_current_language', null);
			$default_language = apply_filters('wpml_default_language', null);

			if ($current_language != $default_language) {
				// $relational_field_types = array('post_object', 'page_link', 'relationship', 'taxonomy');

				$field_object = $this->resolve_field($field);

				if ($field_object) {
					$has_element_with_display_translated = $field_object->has_element_with_display_translated($has_element_with_display_translated, $field);
				}
			}

			if ($has_element_with_display_translated == true) {
				echo "<div class='wpml_acf_annotation ". $field_object->field_type() ."'>";
				_e("<strong>Warning</strong>: This field allows to select post type or taxonomy which you set in WPML translation options to 'Translatable - use translation if available or fallback to default language '. Whatever you set in this field for a secondary language post (this post) will be ignored and values from original post will be used (if you set to copy or duplicate value for this field).");
				echo "</div>";
			}


		}
		$run_times[ $field['key'] ]++;
	}

	private function resolve_field($field) {

		$field_object = false;

		// stub data, not used in this context
		$processed_data = new stdClass();
		$processed_data->meta_value = null;
		$processed_data->target_lang = null;
		$processed_data->meta_data = null;
		$processed_data->related_acf_field_value = null;
		$ids_object = new stdClass();

		if (isset($field['class']) && $field['class'] == 'post_object') {
			$field_object = new WPML_ACF_Post_Object_Field($processed_data, $ids_object);
		} else if (isset($field['class']) && $field['class'] == 'page_link') {
			$field_object = new WPML_ACF_Page_Link_Field($processed_data, $ids_object);
		} else if (isset($field['class']) && $field['class'] == 'relationship') {
			$field_object = new WPML_ACF_Relationship_Field($processed_data, $ids_object);
		} else if (isset($field['class']) && $field['class'] == 'taxonomy') {
			$field_object = new WPML_ACF_Taxonomy_Field($processed_data, $ids_object);
		} else if (isset($field['class']) && $field['class'] ==  'gallery') {
			$field_object = new WPML_ACF_Post_Object_Field($processed_data, $ids_object);
		}


		return $field_object;
	}
}