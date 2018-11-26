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
?>
<div class="section-content" data-tab="addons-tab">
    <div class="section" id="extensions">
        <h1 class="desc-following">Available extensions</h1>
        <p class="desc">Enhance Flow-Flow functionality with these great add-ons.</p>

        <div class="extension">
            <div class="extension__item" id="extension-ads">
                <div class="extension__image"></div>
                <div class="extension__content">
                    <a class="extension__cta" target="_blank" href="http://goo.gl/m7uFzr">Get</a>
                    <h1 class="extension__title">Advertising & Branding extension</h1>
                    <p class="extension__text">Personalize your Flow-Flow stream with custom cards. Make sticky and always show custom content: your brand advertisement with links to social profiles, custom advertisements (like AdSense), any announcements, event promotion and whatever you think of.<br>
                        <strong>Supported products:</strong> Flow-Flow PRO v 2.5+, Flow-Flow Lite v 3.0.5+</p>

                 </div>

            </div>
            <div class="extension__item" id="extension-tv">
                <div class="extension__image"></div>
                <div class="extension__content">
                    <a class="extension__cta" target="_blank" href="http://goo.gl/jWCl9T">Get</a>
                    <h1 class="extension__title">Big Screens extension</h1>
                    <p class="extension__text">Cast your social hub directly to a live TV, projector, or HDMI broadcast device with just one click! This extension comes with realtime updating and posts automatic rotation for full-screen mode. You just need to output stream page to desired screen.<br>
                        <strong>Supported products:</strong> Flow-Flow PRO v 2.8+, Flow-Flow Lite v 3.0.5+</p>
                 </div>

            </div>
        </div>
    </div>
    <div class="section" id="other_products">
        <h1 class="desc-following">Social Stream Apps</h1>
        <p class="desc">Other products built on Flow-Flow's core.</p>

        <div class="extension">
            <div class="extension__item" id="plugin-grace">
                <div class="extension__image"></div>
                <div class="extension__content">
                    <a class="extension__cta" target="_blank" href="http://go.social-streams.com/get-grace">Get</a>
                    <h1 class="extension__title">Grace — Instagram Feed Gallery for WordPress</h1>
                    <p class="extension__text">The most advanced plugin for creating graceful Instagram feed media walls of Instagram public posts. This feature-rich plugin lets you aggregate and showcase posts of Instagram accounts, hashtags and locations. And the great thing is that you can mix any of Instagram feeds in the same social media wall or carousel. Add eye-catching Instagram gallery to your site in fast and easy way!</p>
                </div>
            </div>
            <div class="extension__item" id="plugin-php">
                <div class="extension__image"></div>
                <div class="extension__content">
                    <a class="extension__cta" target="_blank" href="https://goo.gl/aTmQp5">Get</a>
                    <h1 class="extension__title">Flow-Flow — Social Streams PHP Script</h1>
                    <p class="extension__text">Standalone version of Flow-Flow app for PHP servers without WordPress CMS installed. Can be used on any PHP server but requires more coding knowledge. Provides same features as WordPress version</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    	/** @noinspection PhpIncludeInspection */
		include($context['root']  . 'views/footer.php');
	?>
</div>
