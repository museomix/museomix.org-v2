<?php

  class Settings_ACFBetterSearch {

    function __construct() {

      $this->initActions();

    }

    /* ---
      Actions
    --- */

      private function initActions() {

        $this->loadSettings();

        add_action('admin_menu', [$this, 'addSettingsPage']);

      }

      private function loadSettings() {

        $this->fields = [
          'text'     => __('Text', 'acf-better-search'),
          'textarea' => __('Text Area', 'acf-better-search'),
          'number'   => __('Number', 'acf-better-search'),
          'email'    => __('Email', 'acf-better-search'),
          'url'      => __('Url', 'acf-better-search'),
          'file'     => sprintf(__('File %s(it doesn\'t work in lite mode)%s', 'acf-better-search'), '<em>', '</em>'),
          'wysiwyg'  => __('Wysiwyg Editor', 'acf-better-search'),
          'select'   => __('Select', 'acf-better-search'),
          'checkbox' => __('Checkbox', 'acf-better-search'),
          'radio'    => __('Radio Button', 'acf-better-search')
        ];

        $this->features = [
          'whole_phrases' => __('Search for whole phrases instead of each single word of phrase', 'acf-better-search'),
          'lite_mode'     => sprintf(__('Use lite mode %s(faster search, without checking field types)%s', 'acf-better-search'), '<em>', '</em>')
        ];

      }

    /* ---
      Admin page
    --- */

      public function addSettingsPage() {

        add_submenu_page(
          'options-general.php',
          'ACF: Better Search',
          'ACF: Better Search',
          'manage_options',
          'acfbs_admin_page',
          [$this, 'showSettingsPage']
        );

      }

      public function showSettingsPage() {

        $this->getSelectedFieldsTypes();
        $this->getSelectedFeatures();

        require_once ACFBS_PATH . 'includes/settings.php';

      }

    /* ---
      Options
    --- */

      private function getSelectedFieldsTypes() {

        if (isset($_POST['acfbs_save']) && !get_option('acfbs_lite_mode', false)) {

          $this->selected = isset($_POST['acfbs_fields_types']) ? $_POST['acfbs_fields_types'] : [];
          $this->saveOption('acfbs_fields_types', $this->selected);

        } else {

          $this->selected = get_option('acfbs_fields_types', ['text', 'textarea', 'wysiwyg']);

        }

      }

      private function getSelectedFeatures() {

        foreach ($this->features as $key => $label) {

          if (isset($_POST['acfbs_save'])) {

            $this->$key = (isset($_POST['acfbs_features']) && in_array($key, $_POST['acfbs_features']));
            $this->saveOption('acfbs_' . $key, $this->$key);

          } elseif (get_option('acfbs_' . $key) !== false) {

            $this->$key = get_option('acfbs_' . $key);

          } else {

            $this->$key = false;

          }

        }

      }

      private function saveOption($key, $value) {

        if (get_option($key) !== false)
          update_option($key, $value);
        else
          add_option($key, $value);

      }

    /* ---
      Admin page content
    --- */

      private function showFieldsList() {

        foreach ($this->fields as $key => $label) {

          $id         = 'acfbs_fields_' . $key;
          $isChecked  = in_array($key, $this->selected) ? 'checked' : '';
          $isDisabled = $this->lite_mode ? 'disabled' : '';
          
          ?>
            <tr>
              <td>
                <label for="<?= $id; ?>"><?= $label; ?></label>
              </td>
              <td>
                <input type="checkbox" id="<?= $id; ?>" name="acfbs_fields_types[]" value="<?= $key; ?>" <?= $isChecked . ' ' . $isDisabled ?>>
              </td>
            </tr>
          <?php

        }

      }

      private function showFeaturesList() {

        foreach ($this->features as $key => $label) {

          $id        = 'acfbs_' . $key;
          $isChecked = $this->$key ? 'checked="checked"' : '';
          
          ?>
            <tr>
              <td>
                <label for="<?= $id; ?>"><?= $label; ?></label>
              </td>
              <td>
                <input type="checkbox" id="<?= $id; ?>" name="acfbs_features[]" value="<?= $key; ?>" <?= $isChecked ?>>
              </td>
            </tr>
          <?php

        }

      }

  }