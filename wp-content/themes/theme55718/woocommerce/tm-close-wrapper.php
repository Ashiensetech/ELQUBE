<?php
/**
 * Open wrapper temaplte for shop pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
			</div>
			
			<div class="sidebar <?php echo cherry_get_layout_class( 'sidebar' ); ?>" id="sidebar" data-motopress-type="static-sidebar"  data-motopress-sidebar-file="sidebar.php">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</div>