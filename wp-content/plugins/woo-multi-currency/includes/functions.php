<?php
if ( ! function_exists( 'wmc_check_vpro' ) ) {
	/**
	 * Check pro version
	 * @return bool
	 */
	function wmc_check_vpro() {
		if ( is_plugin_active( 'woo-multi-currency-pro/woo-multi-currency-pro.php' ) ) {
			return true;
		}

		return false;
	}
}