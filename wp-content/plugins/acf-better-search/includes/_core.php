<?php

  class ACFBetterSearch {

    function __construct() {

      $this->init();

    }

    private function init() {

      add_filter('wp_loaded', [$this, 'selectCore']);

    }

    public function selectCore() {

      $isAjax       = defined('DOING_AJAX') && DOING_AJAX;
      $isAjaxAction = isset($_POST['action']) && in_array($_POST['action'], ['acf_better_search_notice', 'query-attachments']);

      if (!is_admin() || ($isAjax && !$isAjaxAction)) {

        $this->loadSearchCore();

      } else {

        $this->loadAdminCore();

      }

    }

    private function loadSearchCore() {

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
      $class = $class . 'ACFBetterSearch';

      require_once $var . '.php';

      $this->$var = new $class();

    }

  }