<?php 

  class Assets_ACFBetterSearch {

    function __construct() {

      $this->initActions();

    }

    /* ---
      Actions
    --- */

      private function initActions() {

        add_filter('admin_enqueue_scripts', [$this, 'loadStyles']);
        add_filter('admin_enqueue_scripts', [$this, 'loadScripts']);

      }

    /* ---
      Load styles & scripts
    --- */

      public function loadStyles() {

        wp_register_style('acf-better-search', ACFBS_HTTP . 'assets/css/styles.css');
        wp_enqueue_style('acf-better-search');

      }

      public function loadScripts() {

        wp_register_script('acf-better-search', ACFBS_HTTP . 'assets/js/scripts.js', 'jquery', '', true);
        wp_enqueue_script('acf-better-search');

      }

  }