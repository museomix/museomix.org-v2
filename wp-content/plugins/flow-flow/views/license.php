<?php if ( ! defined( 'WPINC' ) ) die;
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
$options = $context['options'];

$company_name = isset($options['company_name']) ? $options['company_name'] : '';
$company_email = isset($options['company_email']) ? $options['company_email'] : '';
$purchase_code = isset($options['purchase_code']) ? $options['purchase_code'] : '';
$news_subscription = isset($options['news_subscription']) ? $options['news_subscription'] : 'nope';
$activated = $context['activated'];
//$news_subscription = 'nope';
//$activated = false;
$disabled = $activated ? ' class="disabled"' : '';

?>
<div class="section-content" data-tab="license-tab">
    <div class="section <?php if ($activated) { echo 'plugin-activated'; } ?>" id="envato_license">
	    <?php
        if (!$activated) {
            echo '<h1>Activate Flow-Flow</h1>';
            echo '<h3>Plugin activation with Envato purchase code is required to get access to easy updating via WP dashboard. We need your email address for additional confirmation. You can subscribe to important notifications if you mark checkbox in the form below. These notifications will include announcements about major updates and Flow-Flow extension releases.</h3>';
        }
        ?>

        <dl class="section-settings">
            <dt class="vert-aligned">YOUR OR COMPANY NAME</dt>
            <dd>
                <input <?php echo $disabled ?> class="clearcache" type="text" id="ff_company_name" name="flow_flow_options[company_name]" placeholder="<?php echo $disabled ? 'No name specified' : 'Enter name here'?>" value="<?php echo $company_name?>" >
            </dd>
            <dt class="vert-aligned">YOUR EMAIL<?php if (!$activated) { echo ' *'; } ?></dt>
            <dd>
                <input <?php echo $disabled ?> id="company_email" class="clearcache" required type="email" name="flow_flow_options[company_email]" placeholder="Enter valid email here" value="<?php echo $company_email?>">
            </dd>
            <dt class="<?php echo $activated ?  'vert-aligned' : 'multiline' ?>">PURCHASE CODE<?php if (!$activated) { echo ' *<p class="desc"><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">Where\'s my purchase code?</a></p>'; } ?></dt>
            <dd>
                <input <?php echo $disabled ?> id="purchase_code" class="clearcache" required type="text" name="flow_flow_options[purchase_code]" placeholder="Paste code here" value="<?php echo $purchase_code?>">
            </dd>
            <dt class="vert-aligned"></dt>

            <dd><?php
                if ($activated) {
                    if ($news_subscription == 'yep') {
                        echo '<span>You subscribed for important announcements.</span>';
                    } else {
                        echo '<span>You haven\'t subscribed for important announcements yet.</span>';
                    }
                }
                ?><div class="checkbox-row"><input id="news_subscription" type="checkbox" name="flow_flow_options[news_subscription]" value="yep" <?php if ($news_subscription == 'yep') echo "checked"; ?>>
                    <label for="news_subscription">Receive important announcements and updates on email. <strong>We won't spam you</strong>.</label></div></dd>


        <dt class="vert-aligned"></dt>
        <dd><span id="user-settings-sbmt" class="admin-button green-button submit-button">
                <?php
                if ($activated) {
                    echo 'Deactivate';
                } else {
                    echo 'Submit';
                }
                ?></span>
<?php
if ($activated) {
    if ($news_subscription == 'yep') {

    } else {
        echo '<span id="user-settings-sbmt-2" class="admin-button green-button submit-button">Subscribe</span>';
    }
} ?></dd>
        </dl>
    </div>
    <?php include($context['root']  . 'views/footer.php'); ?>

</div>