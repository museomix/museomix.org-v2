<?php

  if (!defined('WP_UNINSTALL_PLUGIN'))
    die;

  /* ---
    Remove options
  --- */

    delete_option('acfbs_fields_types');
    delete_option('acfbs_whole_phrases');
    delete_option('acfbs_lite_mode');

  /* ---
    Remove transient
  --- */

    delete_transient('acf_better_search_notice');