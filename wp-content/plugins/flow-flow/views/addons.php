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
                    <p class="extension__text">Personalize your Flow-Flow stream with custom cards. Make sticky and always show custom content: your brand advertisement with links to social profiles, custom advertisements (like AdSense), any announcements, event promotion and whatever you think of.</p>

                 </div>

            </div>
            <div class="extension__item extension__secret" id="extension-tv">
                <div class="extension__image"></div>
                <div class="extension__content">
                    <a class="extension__cta" target="_blank" href="http://goo.gl/jWCl9T">Get</a>
                    <h1 class="extension__title">Big Screens extension</h1>
                    <p class="extension__text">Cast your social hub directly to a live TV, projector, or HDMI broadcast device with just one click! This extension comes with realtime updating and posts automatic rotation for full-screen mode. You just need to output stream page to desired screen.</p>
                 </div>

            </div>
            <div class="extension__item extension__secret" id="">
                <div class="extension__image"></div>
                <div class="extension__content">
                    <h1 class="extension__title">Secret extension</h1>
                    <p class="extension__text">One of Flow-Flow extensions incoming this year!</p>
                 </div>

            </div>
        </div>
    </div>
    <?php include($context['root']  . 'views/footer.php'); ?>
</div>
