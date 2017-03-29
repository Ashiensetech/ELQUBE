<?php
/**
 * Open wrapper temaplte for shop pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="motopress-wrapper content-holder clearfix woocommerce">
	<div class="container">
		<div class="row">
			<div class="span12" data-motopress-type="static" data-motopress-static-file="static/static-title.php">
				<?php get_template_part("static/static-title"); ?>
			</div>
		</div>
		<div class="row">
			<div class="span12" id="content">