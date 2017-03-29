<?php /* Wrapper Name: Header */ ?>
<link rel="stylesheet" type="text/css" media="all" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">

<div class="box">
    <div class="row">
    	<div class="span3" data-motopress-type="static" data-motopress-static-file="static/static-logo.php">
    		<?php get_template_part("static/static-logo"); ?>
    	</div>
        <div class="span7" data-motopress-type="static" data-motopress-static-file="static/static-logo.php">
    		 <?php get_template_part("static/static-nav"); ?>
    	</div>
        <div class="span2" data-motopress-type="static" data-motopress-static-file="static/static-logo.php" style="    padding-top: 8px;">
    		 <?php get_template_part("static/static-search"); ?>
                 
    	</div>
        <!-- <div class="span4" style="text-align: center;" data-motopress-type="static" data-motopress-static-file="static/static-logo.php" style="    padding-top: 8px;">    		
		
		    	 	<?php //dynamic_sidebar( 'cart-holder' ); ?>
                  <?php //get_template_part("static/static-shop-account"); ?>

    	
        </div> -->
    </div>
