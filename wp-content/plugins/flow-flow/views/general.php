<?php if ( ! defined( 'WPINC' ) ) die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */
$options = $context['options'];
?>
<div class="section-content" data-tab="general-tab">
	<div class="section" id="general-settings">
		<h1  class="desc-following">General Settings</h1>
		<p class="desc">Adjust plugin's global settings here.</p>
		<dl class="section-settings">
			<dt class="ff_mod_roles ff_hide4site">Who can moderate
			<p class="desc">User roles that are allowed to moderate feeds.</p>
			</dt>
			<dd class="ff_mod_roles ff_hide4site">
				<?php
				if (FF_USE_WP){
					$wp_roles = new WP_Roles();
					$roles = $wp_roles->get_names();
					
					foreach ($roles as $role_value => $role_name) {
						$checked =  isset($options['mod-role-' . $role_value ]) && $options['mod-role-' . $role_value ] == 'yep' ? 'checked' : '';
						$value = $checked ? 'yep' : 'none';
						echo '<div class="checkbox-row"><input type="checkbox" ' . $checked . ' value="yep" name="flow_flow_options[mod-role-' . $role_value . ']" id="mod-role-' . $role_value . '"><label for="mod-role-' . $role_value . '">' . $role_name . '</label></div>';
					}
				}
				?>
			</dd>
			<dt class="multiline">Date format<p class="desc">Used in post timestamps.</p></dt>
			<dd>
                <input id="general-settings-ago-format" class="clearcache" type="radio" name="flow_flow_options[general-settings-date-format]" <?php if ( (isset($options['general-settings-date-format']) && $options['general-settings-date-format'] == 'agoStyleDate') || !isset($options['general-settings-date-format'])) echo "checked"; ?> value="agoStyleDate"/>
				<label for="general-settings-ago-format">Short</label>
                <input id="general-settings-classic-format" class="clearcache" type="radio" name="flow_flow_options[general-settings-date-format]" <?php if (isset($options['general-settings-date-format']) && $options['general-settings-date-format'] == 'classicStyleDate') echo "checked"; ?> value="classicStyleDate"/>
				<label for="general-settings-classic-format">Classic</label>
				<?php if (FF_USE_WP) { ?>
                <input id="general-settings-wp-format" class="clearcache" type="radio" name="flow_flow_options[general-settings-date-format]" <?php if (isset($options['general-settings-date-format']) && $options['general-settings-date-format'] == 'wpStyleDate') echo "checked"; ?> value="wpStyleDate"/>
				<label for="general-settings-wp-format">WordPress</label>
				<?php }?>
			</dd>
			<dt class="multiline">Open links in new tab<p class="desc">Any link in post will be opened in new tab.</p></dt>
			<dd>
				<label for="general-settings-open-links-in-new-window">
					<input id="general-settings-open-links-in-new-window" class="switcher clearcache" type="checkbox"
					       name="flow_flow_options[general-settings-open-links-in-new-window]"
                        <?php if (!isset($options['general-settings-open-links-in-new-window']) || (isset($options['general-settings-open-links-in-new-window']) && $options['general-settings-open-links-in-new-window'] == 'yep')) echo "checked"; ?>
					       value="yep"/><div><div></div></div>
				</label>
			</dd>
			<dt class="multiline">Disable proxy pictures<p class="desc">Proxying improves performance.</p></dt>
			<dd>
				<label for="general-settings-disable-proxy-server">
					<input id="general-settings-disable-proxy-server" class="clearcache switcher" type="checkbox"
					       name="flow_flow_options[general-settings-disable-proxy-server]"
						<?php if (isset($options['general-settings-disable-proxy-server']) && $options['general-settings-disable-proxy-server'] == 'yep') echo "checked"; ?>
					       value="yep"/><div><div></div></div>
			</dd>
			<dt class="multiline">Disable curl "follow location"
			<p class="desc">Can help if your server uses deprecated security setting 'safe_mode' and streams don't load.</p></dt>
			<dd>
				<label for="general-settings-disable-follow-location">
					<input id="general-settings-disable-follow-location" class="clearcache switcher" type="checkbox"
					       name="flow_flow_options[general-settings-disable-follow-location]"
						<?php if (isset($options['general-settings-disable-follow-location']) && $options['general-settings-disable-follow-location'] == 'yep') echo "checked"; ?>
					       value="yep"/><div><div></div></div>
			</dd>
			<dt class="multiline">Use IPv4 protocol
			<p class="desc">Sometimes servers use older version of Internet protocol. Use setting when you see "Network is unreachable" error.</p></dt>
			<dd>
				<label for="general-settings-ipv4">
					<input id="general-settings-ipv4" class="clearcache switcher" type="checkbox"
					       name="flow_flow_options[general-settings-ipv4]"
						<?php if (isset($options['general-settings-ipv4']) && $options['general-settings-ipv4'] == 'yep') echo "checked"; ?>
					       value="yep"/><div><div></div></div>
			</dd>

			<dt class="multiline">Force HTTPS for all resources
			<p class="desc">Load images and videos via HTTPS. Use this setting if you notice browser security warnings. Be advised, not every API provides resources via HTTPS.</p></dt>
			<dd>
				<label for="general-settings-https">
					<input id="general-settings-https" class="clearcache switcher" type="checkbox"
					       name="flow_flow_options[general-settings-https]"
						<?php if (isset($options['general-settings-https']) && $options['general-settings-https'] == 'yep') echo "checked"; ?>
					       value="yep"/><div><div></div></div>
			</dd>

			<dt class="multiline">Аmount of stored posts for each feed
			<p class="desc"></p></dt>
			<dd>
				<label for="general-settings-feed-post-count">
					<input id="general-settings-feed-post-count" class="clearcache short" type="text"
						   name="flow_flow_options[general-settings-feed-post-count]"
						   value="<?php if (isset($options['general-settings-feed-post-count'])) {
							   echo (int)$options['general-settings-feed-post-count'];
						   } else {
							   if (defined('FF_FEED_POSTS_COUNT')) {
								   echo FF_FEED_POSTS_COUNT;
							   } else {
								   echo 100;
							   }
						   }
						   ?>"/><div><div></div></div>
			</dd>

            <dt class="multiline">Send notifications about broken feeds
            <p class="desc">You will get notifications once per day to your blog admin email.</p>
            </dt>
            <dd>
                <label for="general-notifications">
                    <input id="general-notifications" class="clearcache switcher" type="checkbox" name="flow_flow_options[general-notifications]"
                        <?php if (isset($options['general-notifications']) && $options['general-notifications'] == 'yep') echo "checked"; ?> value="yep"/>
                    <div><div></div></div>
            </dd>

			<dt class="multiline">Remove all data when uninstall plugin
			<p class="desc">Check this if you want to erase all database records that plugin created.</p>
			</dt>
			<dd>
				<label for="general-uninstall">
					<input id="general-uninstall" class="clearcache switcher" type="checkbox" name="flow_flow_options[general-uninstall]"
						<?php if (isset($options['general-uninstall']) && $options['general-uninstall'] == 'yep') echo "checked"; ?> value="yep"/>
					<div><div></div></div>
			</dd>
		</dl>
		<span id="general-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span>
	</div>
	<?php
		/** @noinspection PhpIncludeInspection */
		include($context['root']  . 'views/footer.php');
	?>

</div>
