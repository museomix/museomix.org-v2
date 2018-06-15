<div class="wrap acfbsOptionPage">
  <h1 class="wp-heading-inline">
    <?= __('ACF: Better Search settings', 'acf-better-search'); ?>
  </h1>
  <div class="acfbsOptionPage__columns">
    <div class="acfbsOptionPage__column">
      <form method="post">
        <table class="widefat">
          <thead>
            <tr>
              <th colspan="2">
                <?= __('List of supported fields types:', 'acf-better-search'); ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php $this->showFieldsList(); ?>
          </tbody>
        </table>
        <table class="widefat">
          <thead>
            <tr>
              <th colspan="2">
                <?= __('Additional features:', 'acf-better-search'); ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php $this->showFeaturesList(); ?>
          </tbody>
        </table>
        <input type="submit" class="button button-primary" name="acfbs_save" value="<?= __('Save Changes', 'acf-better-search'); ?>">
      </form>
    </div>
    <div class="acfbsOptionPage__column">
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?= __('How does this work?', 'acf-better-search'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <?= __('Plugin changes all SQL queries by extending the standard search to selected fields of Advanced Custom Fields PRO.', 'acf-better-search'); ?>
              </p>
              <p>
                <?= __('On search page and in admin panel everything works automatically. No need to add any additional code.', 'acf-better-search'); ?>
              </p>
              <p>
                <?= sprintf(__('For custom WP_Query loop and get_posts() function you must add %sSearch Parameter%s.', 'acf-better-search'), '<a href="https://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter" target="_blank">', '</a>'); ?>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?= __('Do you have an idea for a new feature?', 'acf-better-search'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <?= __('Please let us know about it in the review. We will try to add it!', 'acf-better-search'); ?>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?= __('Do you like our plugin? Could you rate him?', 'acf-better-search'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <?= __('Please let us know what you think about our plugin. It is important that we can develop this tool. Thank you for all the ratings, reviews and donates.', 'acf-better-search'); ?>
              </p>
              <p class="acfbsOptionPage__button">
                <a href="https://wordpress.org/support/plugin/acf-better-search/reviews/#new-post" target="_blank" class="button button-primary">
                  <?= __('Add review', 'acf-better-search'); ?>
                </a>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?= __('Would you like to appreciate our work?', 'acf-better-search'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <a href="https://www.paypal.me/mateuszgbiorczyk/" target="_blank">
                  <img src="<?= ACFBS_HTTP . 'assets/img/donate-button.png'; ?>" alt="<?= __('Donate us', 'acf-better-search'); ?>">
                </a>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>