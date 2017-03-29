		<footer class="motopress-wrapper footer">
			<div class="container">
				<div class="row">
					<div class="<?php echo cherry_get_layout_class( 'full_width_content' ); ?>" data-motopress-wrapper-file="wrapper/wrapper-footer.php" data-motopress-wrapper-type="footer" data-motopress-id="<?php echo uniqid() ?>">
						<?php get_template_part('wrapper/wrapper-footer'); ?>
					</div>
				</div>
			</div>
		</footer>
		<!--End #motopress-main-->
	</div>
	<div id="back-top-wrapper" class="visible-desktop">
		<p id="back-top">
			<?php echo apply_filters( 'cherry_back_top_html', '<a href="#top"><span></span></a>' ); ?>
		</p>
	</div>
	<?php if(of_get_option('ga_code')) { ?>
		<script type="text/javascript">
			<?php echo stripslashes(of_get_option('ga_code')); ?>
		</script>
		<!-- Show Google Analytics -->
	<?php } ?>
	<?php wp_footer(); ?> <!-- this is used by many Wordpress features and for plugins to work properly -->
	<script>
		jQuery('.add_to_cart_button').on('click', function () {
        var cart = jQuery('.cart-holder');
        var imgtodrag = jQuery(this).parents('.product').children('.woocommerce-LoopProduct-link').find("img").eq(0);


        if (imgtodrag) {
            var imgclone = imgtodrag.clone()
                .offset({
                top: imgtodrag.offset().top,
                left: imgtodrag.offset().left
            })
                .css({
                'opacity': '0.5',
                    'position': 'absolute',
                    'height': '150px',
                    'width': '150px',
                    'z-index': '100'
            })
                .appendTo(jQuery('body'))
                .animate({
                'top': cart.offset().top + 10,
                    'left': cart.offset().left + 10,
                    'width': 75,
                    'height': 75
            }, 1000, 'easeInOutExpo');
            
           
            imgclone.animate({
                'width': 0,
                    'height': 0
            }, function () {
                jQuery(this).detach()
            });
        }
    });
	</script>
    <script>
            jQuery(window).scroll(function() {
            if (jQuery(this).scrollTop() > 2){  
                jQuery('.top_nav').addClass("stickmenu");
              }
              else{
                jQuery('.top_nav').removeClass("stickmenu");
              }
            });
        </script>
</body>
</html>