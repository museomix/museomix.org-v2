<?php if ( ! defined( 'WPINC' ) )  die;
/**
 * FlowFlow.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 *
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
$options = $context['options'];
$auth = $context['auth_options'];
$auth['facebook_access_token'] = isset($auth['facebook_access_token']) ? $auth['facebook_access_token'] : '';
//$facebook_long_life_token = $context['facebook_long_life_token'];
?>
<div class="section-content" data-tab="auth-tab">
    <div class="section" id="auth-settings">
        <h1 class="desc-following">Twitter auth settings</h1>
        <p class="desc"><a target="_blank" href="http://docs.social-streams.com/article/48-authenticate-with-twitter">Setup guide</a></p>
        <dl class="section-settings">
            <dt class="vert-aligned">Consumer Key (API Key)</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[consumer_key]" placeholder="Copy and paste from Twitter" value="<?php echo isset($options['consumer_key']) ? $options['consumer_key'] : ''?>"/>
            </dd>
            <dt class="vert-aligned">Consumer Secret (API Secret)</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[consumer_secret]" placeholder="Copy and paste from Twitter" value="<?php echo isset($options['consumer_secret']) ? $options['consumer_secret'] : ''?>"/>
            </dd>
            <dt class="vert-aligned">Access Token</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[oauth_access_token]" placeholder="Copy and paste from Twitter" value="<?php echo isset($options['oauth_access_token']) ? $options['oauth_access_token'] : ''?>"/>
            </dd>
            <dt class="vert-aligned">Access Token Secret</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[oauth_access_token_secret]" placeholder="Copy and paste from Twitter" value="<?php echo isset($options['oauth_access_token_secret']) ? $options['oauth_access_token_secret'] : ''?>"/>						</dd>

        </dl>
        <p class="button-wrapper"><span id="tw-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>

        <h1 class="desc-following" style="min-height: 38px"><span style="vertical-align: middle;line-height: 38px;">Facebook auth settings</span> <span id="facebook-auth" class='admin-button auth-button blue-button'>Authorize</span></h1>
        <p class="desc"><a target="_blank" href="http://docs.social-streams.com/article/46-authenticate-with-facebook">Setup guide</a></p>
        <dl class="section-settings">
            <dt class="ff-toggler">Use own app</dt>
            <dd class="ff-toggler">
                <label><input class="clearcache switcher" <?php echo flow\settings\FFSettingsUtils::YepNope2ClassicStyleSafe($auth, 'facebook_use_own_app', true) ? 'checked' : ''?> type="checkbox" id="facebook_use_own_app" name="flow_flow_fb_auth_options[facebook_use_own_app]" value="yep"/><div><div></div></div></label>
            </dd>
            <dt class="vert-aligned">Access Token</dt>
            <dd>
                <input class="clearcache" type="text" id="facebook_access_token" name="flow_flow_fb_auth_options[facebook_access_token]" placeholder="Copy and paste from Facebook" value="<?php echo $auth['facebook_access_token']?>"/>
                <?php
                $extended = $context['extended_facebook_access_token'];
                if(!empty($auth['facebook_access_token']) && !empty($extended) ) {
                    if ($auth['facebook_access_token'] != $extended)
                        echo '<p class="desc" style="margin: 10px 0 5px">Generated long-life token, it should be different from that you entered above then FB auth is OK</p><textarea disabled rows=3>' . $extended . '</textarea>';
                } else {
                    if (empty($extended)) {
                        echo '<p class="desc fb-token-notice" style="margin: 10px 0 5px; color: red !important">! Extended token is not generated, Facebook feeds might not work</p>';
                    }
                }
                ?>
            </dd>
            <dt class="vert-aligned own-app-input">APP ID</dt>
            <dd class="own-app-input">
                <input class="clearcache" type="text" name="flow_flow_fb_auth_options[facebook_app_id]" placeholder="Copy and paste from Facebook" value="<?php echo isset($auth['facebook_app_id']) ? $auth['facebook_app_id'] : ''?>"/>
            </dd>
            <dt class="vert-aligned own-app-input">APP Secret</dt>
            <dd class="own-app-input">
                <input class="clearcache" type="text" name="flow_flow_fb_auth_options[facebook_app_secret]" placeholder="Copy and paste from Facebook" value="<?php echo isset($auth['facebook_app_secret']) ? $auth['facebook_app_secret'] : ''?>"/>
            </dd>
        </dl>
        <p class="button-wrapper"><span id="fb-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>


        <h1 class="desc-following ">Instagram auth settings <span id="inst-auth" class='admin-button auth-button blue-button'>Authorize</span></h1>
        <p class="desc"><a target="_blank" href="http://docs.social-streams.com/article/47-authenticate-with-instagram">Setup guide</a></p>
        <dl class="section-settings">
            <dt class="vert-aligned">Access Token</dt>
            <dd>
                <input class="clearcache" type="text" id="instagram_access_token" name="flow_flow_options[instagram_access_token]" placeholder="Copy and paste from Instagram" value="<?php echo isset($options['instagram_access_token']) ? $options['instagram_access_token'] : ''?>"/>
            </dd>
        </dl>
        <p class="button-wrapper"><span id="inst-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>

        <h1 class="desc-following">Google+ and YouTube auth settings</h1>
        <p class="desc"><a target="_blank" href="http://docs.social-streams.com/article/49-authenticate-with-google-and-youtube">Setup guide</a></p>
        <dl class="section-settings">
            <dt class="vert-aligned">API key</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[google_api_key]" placeholder="Copy and paste from Google+" value="<?php echo isset($options['google_api_key']) ? $options['google_api_key'] : ''?>"/>
            </dd>
        </dl>
        <p class="button-wrapper"><span id="gp-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>


        <h1 class="desc-following">Foursquare auth settings  <span id="foursquare-auth" class='admin-button auth-button blue-button'>Authorize</span></h1>
        <p class="desc"><a target="_blank" href="http://docs.social-streams.com/article/54-authenticate-with-foursquare">Setup guide</a></p>
        <dl class="section-settings">
            <dt class="vert-aligned">Access Token</dt>
            <dd>
                <input class="clearcache" type="text" id="foursquare_access_token" name="flow_flow_options[foursquare_access_token]" placeholder="Copy and paste from Foursquare" value="<?php echo isset($options['foursquare_access_token']) ? $options['foursquare_access_token'] : '';?>"/>
            </dd>
            <dt class="vert-aligned">Client ID</dt>
            <dd>
                <input class="clearcache" id="foursquare_client_id" type="text" name="flow_flow_options[foursquare_client_id]" placeholder="Copy and paste from Foursquare" value="<?php echo isset($options['foursquare_client_id']) ? $options['foursquare_client_id'] : ''?>"/>
            </dd>
            <dt class="vert-aligned">Client Secret</dt>
            <dd>
                <input class="clearcache" id="foursquare_client_secret" type="text" name="flow_flow_options[foursquare_client_secret]" placeholder="Copy and paste from Foursquare" value="<?php echo isset($options['foursquare_client_secret']) ? $options['foursquare_client_secret'] : ''?>"/>
            </dd>
        </dl>
        <p class="button-wrapper"><span id="fq-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>

        <h1 class="desc-following">LinkedIn auth settings</h1>
        <p class="desc"><a target="_blank" href="http://docs.social-streams.com/article/53-authenticate-with-linkedin">Setup guide</a></p>

        <dl class="section-settings">
            <dt class="vert-aligned">Client ID</dt>
            <dd>
                <input class="clearcache" type="text" id="linkedin_api_key" name="flow_flow_options[linkedin_api_key]" placeholder="Copy and paste from LinkedIn" value="<?php echo isset($options['linkedin_api_key']) ? $options['linkedin_api_key'] : ''?>"/>
            </dd>
            <dt class="vert-aligned">Client Secret</dt>
            <dd>
                <input class="clearcache" type="text" id="linkedin_secret_key" name="flow_flow_options[linkedin_secret_key]" placeholder="Copy and paste from LinkedIn" value="<?php echo isset($options['linkedin_secret_key']) ? $options['linkedin_secret_key'] : ''?>"/>
            </dd>
            <dt class="vert-aligned">Access token</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[linkedin_access_token]" placeholder="Copy and paste from LinkedIn" value="<?php echo isset($options['linkedin_access_token']) ? $options['linkedin_access_token'] : '' ?>"/>
            </dd>
        </dl>
        <p class="button-wrapper"><span id="linkedin-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>

        <h1 class="desc-following">SoundCloud auth settings</h1>
        <p class="desc"><a target="_blank" href="http://soundcloud.com/you/apps/new">Create SoundCloud app</a> and paste its ID below.</p>


        <dl class="section-settings">
            <dt class="vert-aligned">Your app Client ID</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[soundcloud_api_key]" placeholder="Copy and paste from SoundCloud" value="<?php echo isset($options['soundcloud_api_key']) ? $options['soundcloud_api_key'] : ''?>"/>
            </dd>
        </dl>

        <p class="button-wrapper"><span id="sc-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>

        <h1 class="desc-following">Dribbble auth settings</h1>
        <p class="desc"><a target="_blank" href="http://developer.dribbble.com">Create Dribbble app</a> and paste its access token below.</p>
        <dl class="section-settings">
            <dt class="vert-aligned">Client Access Token</dt>
            <dd>
                <input class="clearcache" type="text" name="flow_flow_options[dribbble_access_token]" placeholder="Copy and paste from Dribbble" value="<?php echo isset($options['dribbble_access_token']) ? $options['dribbble_access_token'] : ''?>"/>
            </dd>
        </dl>
        <p class="button-wrapper"><span id="dribbble-auth-settings-sbmt" class='admin-button green-button submit-button'>Save Changes</span></p>
    </div>
    <?php include($context['root']  . 'views/footer.php'); ?>
</div>