<?php
	if ( ! is_active_sidebar( 'et_pb_widget_area_1' ) )
		return;
?>


	
<?php	if ( is_active_sidebar( 'et_pb_widget_area_1' ) ) : ?>
	<div id="footer-info" class="clearfix">

		<?php dynamic_sidebar( 'et_pb_widget_area_1' ); ?>

	</div> <!-- #footer-bottom-widgets -->
<?php endif; ?>
