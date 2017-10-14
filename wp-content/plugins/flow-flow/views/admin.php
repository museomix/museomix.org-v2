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

$dbm = $context['db_manager'];
if (!$dbm->canCreateCssFolder()){
	echo '<p class="ff-error" xmlns="http://www.w3.org/1999/html">Error: Plugin cannot create folder <strong>wp-content/resources/flow-flow/css</strong>, please add permissions or create this folder manually.</p>';
}
?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=345260089015373";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<div id="fade-overlay" class="loading">
	<i class="flaticon-settings"></i>
</div>
<!-- @TODO: Provide markup for your options page here. -->
<form id="flow_flow_form" method="post" action="<?php echo $context['form-action']; ?>" enctype="multipart/form-data">
	<script id="flow_flow_script">
		var _ajaxurl = '<?php echo $context['admin_url']; ?>';
		var la_plugin_slug_down = '<?php echo $context['slug_down']; ?>';
        var plugin_url = '<?php echo $context['plugin_url'] . $context['slug'] ; ?>';
        var server_time = '<?php echo time() ; ?>';
		var plugin_ver = '<?php echo $context['version'] ; ?>';
		<?php if (isset($context['js-vars'])) echo $context['js-vars'];?>
	</script>
	<?php
		settings_fields('ff_opts');
		if (isset($context['hidden-inputs'])) echo $context['hidden-inputs'];
	?>
	<div class="wrapper">
		<?php
                echo '<h2>'. $context['admin_page_title'] . ' v. ' . $context['version'] . ' <a href="http://docs.social-streams.com/" target="_blank">Documentation & FAQ</a></h2>';
                echo '<div id="ff-cats">';
                if (FF_USE_WP) { wp_dropdown_categories( ); }
                echo '</div>';
		?>
		<ul class="section-tabs">
			<?php
				/** @var LATab $tab*/
				foreach ( $context['tabs'] as $tab ) {
					echo '<li id="'.$tab->id().'"><i class="'.$tab->flaticon().'"></i> <span>'.$tab->title().'</span></li>';
				}
				if (isset($context['buttons-after-tabs'])) echo $context['buttons-after-tabs'];
			?>
		</ul>
		<div class="section-contents">
			<?php
				/** @var LATab $tab*/
				foreach ( $context['tabs'] as $tab ) {
					$tab->includeOnce($context);
				}
			?>
		</div>
	</div>

	<?php if (!FF_USE_WP):?>
		<div id="ff-footer">
			<div class="width-wrapper">
				<div class="ff-table">
					<div class="ff-cell">
						Flow-Flow Social Hub plugin<br>
						<?php if (defined( 'FF_PLUGIN_VER' )) echo 'Version: ' . FF_PLUGIN_VER;?><br>
						Made by <a href="http://looks-awesome.com/">Looks Awesome</a>
					</div>
					<div class="ff-cell">
						<h1>HOT TOPICS</h1>
						<a href="http://flow-php.looks-awesome.com/docs/Getting_Started/First_Steps_After_Installation">How to add stream on page</a><br>
						<a href="http://flow-php.looks-awesome.com/docs/Getting_Started/First_Steps_After_Installation#refresh">How to refresh my streams</a><br>
						<a href="http://flow-php.looks-awesome.com/docs/Social_Networks_Auth/Authenticate_with_Facebook">How to authorize Facebook</a><br>
						<a href="">Frequently asked questions</a>
					</div>
					<div class="ff-cell">
						<h1>CONTACT US</h1>
						<a href="http://looks-awesome.com/help">Support request</a><br>
						<a href="http://looks-awesome.com/">Looks Awesome site</a><br>
						<a href="https://twitter.com/looks_awesooome">Twitter</a><br>
						<a href="https://www.facebook.com/looksawesooome">Facebook</a>
					</div>
				</div>
			</div>
		</div>
	<?php endif?>
</form>
<div class="cd-popup" role="alert">
    <div class="cd-popup-container">
        <p>Are you sure you want to delete this element?</p>
        <ul class="cd-buttons">
            <li><a href="#0" id="cd-button-no">No</a></li>
            <li><a href="#0" id="cd-button-yes">Yes</a></li>
        </ul>
        <a href="#0" class="cd-popup-close img-replace">Close</a>
    </div> <!-- cd-popup-container -->
</div> <!-- cd-popup -->

<script>
jQuery(document).trigger('html_ready')
</script>
