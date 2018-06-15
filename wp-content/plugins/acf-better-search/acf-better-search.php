<?php 

  /*
    Plugin Name: ACF: Better Search
    Description: Adds to default WordPress search engine the ability to search by content from selected fields of Advanced Custom Fields PRO plugin.
    Version: 3.0.1
    Author: Mateusz Gbiorczyk
    Author URI: https://gbiorczyk.pl/
    Text Domain: acf-better-search
  */

  define('ACFBS_PATH', plugin_dir_path(__FILE__));
  define('ACFBS_HTTP', plugin_dir_url(__FILE__));

  include 'functions/_core.php';

  new ACFBetterSearch();