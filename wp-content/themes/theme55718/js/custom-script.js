(function($) {
    $(function(){
    	//Dropdown cart in header
		$('.cart-holder > h3').click(function(){
			if($(this).hasClass('cart-opened')) {
				$(this).removeClass('cart-opened').next().slideUp(300);
			} else {
				$(this).addClass('cart-opened').next().slideDown(300);
			}
		});

        // Make cart content disappear. If an event gets to the body
        $("html, a.cherry-wc-account_title").click(function(){
            $(".cart-holder h3").removeClass("cart-opened").next().slideUp(300);
        });

        // Prevent events from getting pass h3
        $(".cart-holder h3, .widget_shopping_cart_content").click(function(e){
            var cherryWcAccountTitle = $('.cherry-wc-account_title');
            if (cherryWcAccountTitle.hasClass('cherry-dropdown-opened')) {
                cherryWcAccountTitle.removeClass('cherry-dropdown-opened');
                cherryWcAccountTitle.parent().find('.cherry-wc-account_content').slideUp(300).removeClass('opened');
                e.stopPropagation();
            } else {
                e.stopPropagation();
            }
        });

		//Popup rating content
		$('.star-rating').each(function(){
			rate_cont = $(this).attr('title');
			$(this).append('<b class="rate_content">' + rate_cont + '</b>');
		});

		//Disable cart selection
		(function ($) {
			$.fn.disableSelection = function () {
				return this
					.attr('unselectable', 'on')
					.css('user-select', 'none')
					.on('selectstart', false);
			};
			$('.cart-holder h3').disableSelection();
		})(jQuery);

		//Fix contact form not valid messages errors
		jQuery(window).load(function() {
			jQuery('.wpcf7-not-valid-tip').live('mouseover', function(){
				jQuery(this).fadeOut();
			});

			jQuery('.wpcf7-form input[type="reset"]').live('click', function(){
				jQuery('.wpcf7-not-valid-tip, .wpcf7-response-output').fadeOut();
			});
		});

		// compare trigger
		$(document).on('click', '.cherry-compare', function(event) {
			event.preventDefault();
			button = $(this);
			$('body').trigger( 'yith_woocompare_open_popup', { response: compare_data.table_url, button: button } )
		});

    });
    
    $('.products .product').each(function(){
        _this = $(this);
        _this.find('.price').after(_this.find('h3'));
        _this.find('.price > ins').after(_this.find('.price > del'));
        
        var thisButtonsBlock = $('<div class="buttonsBlock"></div>');
        _this.append(thisButtonsBlock);
        var buttons = _this.find('.product-link-wrap > .btn, div.yith-wcwl-add-to-wishlist, .compare, li> .btn, .add_to_cart_button');
        thisButtonsBlock.append(buttons);
      })
      
      /*-----animation-----*/
        $(".parallax_wrapper .span4:first-child .banner-wrap").addClass("fadeInLeft wow");
        $(".parallax_wrapper .span4:first-child+.span4 .banner-wrap").addClass("fadeInUp wow");
        $(".parallax_wrapper .span4:first-child+.span4+.span4 .banner-wrap").addClass("fadeInRight wow");
        $(".ul-item-0").addClass("fadeInUp wow");
        $(".ul-item-1").addClass("fadeInUp wow");
        $(".ul-item-2").addClass("fadeInUp wow");
        
      
})(jQuery);
jQuery('.products .product .buttonsBlock .btn, .button, .yith-wcwl-add-to-wishlist a, .compare').wrapInner('<b/>');