<?php

/**
 * Process mini cart
 * Class WMC_Frontend_Mini_Cart
 */
class WMC_Frontend_Mini_Cart {
	function __construct() {
		add_action( 'woocommerce_before_mini_cart', array( $this, 'woocommerce_before_mini_cart' ) );
	}

	/**
	 * Recalculator for mini cart
	 */
	public function woocommerce_before_mini_cart() {

		WC()->cart->calculate_totals();
	}
}

new WMC_Frontend_Mini_Cart();