<?php

  class ACFBetterSearch {

    function __construct() {

      $this->initActions();

    }

    /* ---
      Actions
    --- */

      private function initActions() {

        $this->loadSearchCore();
        $this->loadAdminCore();

      }

    /* ---
      Load cores
    --- */

      private function loadSearchCore() {

        if ((defined('DOING_AJAX') && DOING_AJAX) && (isset($_POST['action']) && in_array($_POST['action'], ['query-attachments'])))
          return;

        $this->loadClass('Search');

      }

      private function loadAdminCore() {

        $this->loadClass('Assets');
        $this->loadClass('Notices');

        if (!is_network_admin())
          $this->loadClass('Settings');

      }

      private function loadClass($class) {

        $var   = strtolower($class);
        $class = $class . '_ACFBetterSearch';

        require_once $var . '.php';

        $this->$var = new $class();

      }

  }