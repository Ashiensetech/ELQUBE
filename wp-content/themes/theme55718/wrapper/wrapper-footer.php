<?php /* Wrapper Name: Footer */ ?>
<?php //if (is_front_page()) { ?>
    <div class="newsletter_box">
        <div class="row">
            <div class="span12" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-1">
                <?php /*dynamic_sidebar("footer-sidebar-3");*/ ?>
            </div>
        </div>
    </div>
    <div class="footer-widgets">
        <div class="container">
            <div class="row">
            	<div class="span2" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-1">
            		<?php dynamic_sidebar("footer-sidebar-1"); ?>
            	</div>
                <div class="span3" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-1">
            		<?php /*get_template_part("static/static-footer-nav"); */?>
                        <?php dynamic_sidebar("footer-sidebar-2"); ?>
            	</div>
                
            	<div class="span4" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-2">
                    <h4>Help</h4>
            		<p class="phone">CALL US:<strong> +8801973440660</strong></p>
                    <address>House # 53 (2nd Floor), Road # 14, Sector # 13, Uttara Model Town, Dhaka-1230 Bangladesh.</address>
            	</div>
                <div class="span3" data-motopress-type="dynamic-sidebar" data-motopress-sidebar-id="footer-sidebar-1">
                    <?php dynamic_sidebar("footer-sidebar-3"); ?>           
                </div>
            </div>
        </div>
    </div>

    <div class="footer_logo_holder">
        <p><a href=""><img src="/wp-content/uploads/2017/03/logo-fnl.png"></a></p>
        <p><img src="/wp-content/uploads/2017/03/vendor-1.png"></p>
		
    </div>
<?php //} ?>
<div class="copyright">
    <div class="container">
        <div class="row">
        	<div class="span9" data-motopress-type="static" data-motopress-static-file="static/static-footer-nav.php">
                <?php //get_template_part("static/static-footer-text"); ?>
		<p>Copyright <a href="/"><strong>ELQUBE</strong></a></p>
        	</div>
        	<div class="span3" data-motopress-type="static" data-motopress-static-file="static/static-footer-text.php">
        		<?php //get_template_part("static/static-social-networks"); ?>
			<p>Developed by <a href="http://workspaceit.com/" class="developer_link">Workspace Infotech Ltd.</a></p>
        	</div>
        </div>
    </div>
</div>

<script type="text/javascript">

jQuery('.product-thumbnails_item img').on("click",function(){
 var a = jQuery(this);
 jQuery('.product-large-image img').attr("src",a.attr("src"));
jQuery('.product-large-image img').attr("srcset","");
});
jQuery(".showcheckout").on("click",function(){
        jQuery("form.woocommerce-checkout").toggle("");
        console.log("fuck you");
    });

</script>

        
