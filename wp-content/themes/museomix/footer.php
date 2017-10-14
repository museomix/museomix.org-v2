<?php if ( 'on' == et_get_option( 'divi_back_to_top', 'false' ) ) : ?>

	<span class="et_pb_scroll_top et-pb-icon"></span>

<?php endif;

if ( ! is_page_template( 'page-template-blank.php' ) ) : ?>

			<footer id="main-footer">
			<div class="et_pb_section  et_pb_section_0 et_section_regular">
				<div class=" et_pb_row  et_pb_gutters1 et_pb_row_fullwidth" id="footer-full-width">
					<div class=" et_pb_row">
						<div class="et_pb_column et_pb_column_1_3  et_pb_column_0">
							<ul><?php dynamic_sidebar( 'footer-left' ); ?></ul>
						</div><div class="et_pb_column et_pb_column_1_3  et_pb_column_1">
							<ul><?php dynamic_sidebar( 'footer-center' ); ?></ul>
						</div><div class="et_pb_column et_pb_column_1_3  et_pb_column_2">
							<ul><?php dynamic_sidebar( 'footer-right' ); ?></ul>				
						</div>
					</div>
				</div>
			</div>
				<?php get_sidebar( 'footer' ); ?>


		<?php
			if ( has_nav_menu( 'footer-menu' ) ) : ?>

				<div id="et-footer-nav">
					<div class="container">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer-menu',
								'depth'          => '1',
								'menu_class'     => 'bottom-nav',
								'container'      => '',
								'fallback_cb'    => '',
							) );
						?>
					</div>
				</div> <!-- #et-footer-nav -->

			<?php endif; ?>

				<div id="footer-bottom">
					<div class="container clearfix">
				<?php
					if ( false !== et_get_option( 'show_footer_social_icons', true ) ) {
						get_template_part( 'includes/social_icons', 'footer' );
					}

					echo et_get_footer_credits();
				?>
				
				<?php get_sidebar('footerwidget'); ?>
				
					</div>	<!-- .container -->
				</div>
			</footer> <!-- #main-footer -->
		</div> <!-- #et-main-area -->

<?php endif; // ! is_page_template( 'page-template-blank.php' ) ?>

	</div> <!-- #page-container -->

	<?php wp_footer(); ?>
</body>
</html>