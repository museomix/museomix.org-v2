<?php 

  class AssetsACFBetterSearch {

    function __construct() {

      $this->setVars();
      $this->initFilters();

    }

    function setVars() {

      $pluginBasename  = plugin_basename(__FILE__);
      $pluginDirectory = substr($pluginBasename, 0, strpos($pluginBasename, '/'));

      $this->path = plugins_url() . '/' . $pluginDirectory . '/assets';

    }

    private function initFilters() {

      add_filter('admin_enqueue_scripts', array($this, 'loadStyles')); 
      add_filter('admin_enqueue_scripts', array($this, 'loadScripts')); 

    }

    public function loadStyles() {

      wp_register_style('acf-better-search', $this->path . '/css/styles.css');
      wp_enqueue_style('acf-better-search');

    }

    public function loadScripts() {

      wp_register_script('acf-better-search', $this->path . '/js/scripts.js', 'jquery', '', true);
      wp_enqueue_script('acf-better-search');

    }

  }