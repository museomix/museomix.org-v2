<?php

  class SettingsACFBetterSearch {

    function __construct() {

      $this->setVars();
      $this->initActions();

    }

    private function setVars() {

      $this->fields = [
        'text'     => __('Text', 'acfbs'),
        'textarea' => __('Text Area', 'acfbs'),
        'number'   => __('Number', 'acfbs'),
        'email'    => __('Email', 'acfbs'),
        'url'      => __('Url', 'acfbs'),
        'wysiwyg'  => __('Wysiwyg Editor', 'acfbs'),
        'select'   => __('Select', 'acfbs'),
        'checkbox' => __('Checkbox', 'acfbs'),
        'radio'    => __('Radio Button', 'acfbs')
      ];

      $this->features = [
        'whole_phrases' => __('Search for whole phrases instead of each single word of phrase', 'acfbs'),
        'lite_mode'     => sprintf(__('Use lite mode %s(faster search, without checking field types)%s', 'acfbs'), '<em>', '</em>')
      ];

      $pluginBasename  = plugin_basename(__FILE__);
      $pluginDirectory = substr($pluginBasename, 0, strpos($pluginBasename, '/'));

      $this->path = plugins_url() . '/' . $pluginDirectory . '/assets';

    }

    /* ---
      Admin menu
    --- */

      private function initActions() {

        add_action('admin_menu', [$this, 'addSettingsPage']);

      }

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

    /* ---
      Settings page
    --- */

      public function showSettingsPage() {

        $this->getSelectedFieldsTypes();
        $this->getSelectedFeatures();

        require_once 'settings-page.php';

      }

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

    /* ---
      Save option
    --- */

      private function saveOption($key, $value) {

        if (get_option($key) !== false)
          update_option($key, $value);
        else
          add_option($key, $value);

      }

    /* ---
      Options on settings page
    --- */

      private function showFieldsList() {

        foreach ($this->fields as $key => $label) {

          $id         = 'acfbs_fields_' . $key;
          $isChecked  = in_array($key, $this->selected) ? 'checked' : '';
          $isDisabled = $this->lite_mode ? 'disabled' : '';
          
          ?>
            <tr>
              <td>
                <label for="<?php echo $id; ?>"><?php echo $label; ?></label>
              </td>
              <td>
                <input type="checkbox" id="<?php echo $id; ?>" name="acfbs_fields_types[]" value="<?php echo $key; ?>" <?php echo $isChecked . ' ' . $isDisabled ?>>
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
                <label for="<?php echo $id; ?>"><?php echo $label; ?></label>
              </td>
              <td>
                <input type="checkbox" id="<?php echo $id; ?>" name="acfbs_features[]" value="<?php echo $key; ?>" <?php echo $isChecked ?>>
              </td>
            </tr>
          <?php

        }

      }

  }