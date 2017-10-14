<div class="wrap acfbsOptionPage">
  <h1 class="wp-heading-inline">
    <?php _e('ACF: Better Search settings', 'acfbs'); ?>
  </h1>
  <div class="acfbsOptionPage__columns">
    <div class="acfbsOptionPage__column">
      <form method="post">
        <table class="widefat">
          <thead>
            <tr>
              <th colspan="2">
                <?php _e('List of supported fields types:', 'acfbs'); ?>
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
                <?php _e('Additional features:', 'acfbs'); ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php $this->showFeaturesList(); ?>
          </tbody>
        </table>
        <input type="submit" class="button button-primary" name="acfbs_save" value="<?php _e('Save Changes', 'acfbs'); ?>">
      </form>
    </div>
    <div class="acfbsOptionPage__column">
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?php _e('How does this work?', 'acfbs'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <?php _e('Plugin changes all SQL queries by extending the standard search to selected fields of Advanced Custom Fields PRO.', 'acfbs'); ?>
              </p>
              <p>
                <?php _e('Advanced search works for WP_Query class.', 'acfbs'); ?>
              </p>
              <p>
                <?php _e('On search page this works automatically.', 'acfbs'); ?>
              </p>
              <p>
                <?php _e('For custom WP_Query loop also if you add <a href="https://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter" target="_blank">Search Parameter</a>.', 'acfbs'); ?>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?php _e('Do you have an idea for a new feature?', 'acfbs'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <?php _e('Please let us know about it in the review. We will try to add it!', 'acfbs'); ?>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="widefat">
        <thead>
          <tr>
            <th>
              <?php _e('Do you like our plugin? Could you rate him?', 'acfbs'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <?php _e('Please let us know what you think about our plugin. It is important that we can develop this tool. Thank you for all the ratings, reviews and donates.', 'acfbs'); ?>
              </p>
              <p class="acfbsOptionPage__button">
                <a href="https://wordpress.org/support/plugin/acf-better-search/reviews/#new-post" target="_blank" class="button button-primary">
                  <?php _e('Add review', 'acfbs'); ?>
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
              <?php _e('Would you like to appreciate our work?', 'acfbs'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <p>
                <a href="https://www.paypal.me/mateuszgbiorczyk/" target="_blank">
                  <img src="<?php echo $this->path . '/img/donate-button.png'; ?>" alt="<?php _e('Donate', 'acfbs'); ?>">
                </a>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>