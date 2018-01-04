<?php 

  class NoticesACFBetterSearch {

    function __construct() {

      $this->initNotice();

    }

    /* ---
      Hooks
    --- */

      private function initNotice() {

        add_action('admin_notices',                    [$this, 'showAdminNotice']);
        add_action('wp_ajax_acf_better_search_notice', [$this, 'saveNoticeClosing']);

      }

    /* ---
      Notice box
    --- */

      public function showAdminNotice() {

        if ((get_transient('acf_better_search_notice') !== false) || (get_current_screen()->id != 'dashboard'))
          return false;

        ?>
          <div class="notice notice-success is-dismissible" data-notice="acf-better-search">
            <h2>
              <?php _e('Do you like ACF Better Search plugin? Could you rate him?', 'acfbs'); ?>
            </h2>
            <p>
              <?php _e('Please let us know what you think about our plugin. It is important that we can develop this tool. Thank you for all the ratings, reviews and donates.', 'acfbs'); ?>
            </p>
            <p>
              <a href="https://wordpress.org/support/plugin/acf-better-search/reviews/#new-post" target="_blank" class="button button-primary">
                <?php _e('Add review', 'acfbs'); ?>
              </a>
            </p>
          </div>
        <?php

      }

    /* ---
      Turn off notice
    --- */

      public function saveNoticeClosing() {

        set_transient('acf_better_search_notice', true, MONTH_IN_SECONDS);

      }

  }