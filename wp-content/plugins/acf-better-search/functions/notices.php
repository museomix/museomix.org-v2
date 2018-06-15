<?php 

  class Notices_ACFBetterSearch {

    function __construct() {

      $this->initActions();

    }

    /* ---
      Actions
    --- */

      private function initActions() {

        add_action('admin_notices',                    [$this, 'showAdminNotice']);
        add_action('activated_plugin',                 [$this, 'hideAdminNotice']);
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
              <?= __('Thank you for using our plugin ACF: Better Search!', 'acf-better-search'); ?>
            </h2>
            <p>
              <?php echo sprintf(__('Please let us know what you think about our plugin. It is important that we can develop this tool. Thank you for all the ratings, reviews and donates. %sIf you have a technical problem, please contact us first before adding the rating. We will try to help you!', 'acf-better-search'), '<br>'); ?>
            </p>
            <p>
              <a href="https://wordpress.org/support/plugin/acf-better-search/" target="_blank" class="button button-primary">
                <?= __('Add technical topic', 'acf-better-search'); ?>
              </a>
              <a href="https://wordpress.org/support/plugin/acf-better-search/reviews/#new-post" target="_blank" class="button button-primary">
                <?= __('Add review', 'acf-better-search'); ?>
              </a>
              <a href="https://www.paypal.me/mateuszgbiorczyk/" target="_blank" class="button button-primary">
                <?= __('Donate us', 'acf-better-search'); ?>
              </a>
            </p>
          </div>
        <?php

      }

    /* ---
      Turn off notice
    --- */

      public function hideAdminNotice($plugin) {

        if (basename($plugin) != 'acf-better-search.php')
          return;

        delete_transient('acf_better_search_notice');
        set_transient('acf_better_search_notice', true, DAY_IN_SECONDS);

      }

      public function saveNoticeClosing() {

        set_transient('acf_better_search_notice', true, MONTH_IN_SECONDS);

      }

  }