<?php

/**
 * Process mini cart
 * Class WMC_Frontend_Mini_Cart
 */
class WMC_Frontend_Shipping {
	function __construct() {
		if ( ! is_admin() ) {
			global $wpdb;

			$raw_methods_sql = "SELECT method_id, method_order, instance_id, is_enabled FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE is_enabled = 1 order by instance_id ASC;";
			$raw_methods     = $wpdb->get_results( $raw_methods_sql );

			if ( count( $raw_methods ) ) {
				foreach ( $raw_methods as $method ) {
					if ( $method == 'free_shipping' ) {
						add_filter( 'option_woocommerce_' . trim( $method->method_id ) . '_' . intval( $method->instance_id ) . '_settings', array( $this, 'free_cost' ) );
					} else {
						add_filter( 'option_woocommerce_' . trim( $method->method_id ) . '_' . intval( $method->instance_id ) . '_settings', array( $this, 'flat_pick_cost' ) );
					}
				}
			}
		}
	}

	/**
	 * Tax on free ship
	 * @param $data
	 *
	 * @return mixed
	 */
	public function free_cost( $data ) {
		print_r($data);
		if ( get_option( 'woocommerce_currency' ) != '' ) {
			$main_currency    = get_option( 'woocommerce_currency' );
			$current_currency = $main_currency;
		} else {
			return $data;
		}

		/*Check currency*/
		$selected_currencies = get_option( 'wmc_selected_currencies', array() );
		if ( isset( $_GET['wmc_current_currency'] ) && array_key_exists( $_GET['wmc_current_currency'], $selected_currencies ) ) {
			$current_currency = $_GET['wmc_current_currency'];
		} elseif ( isset( $_COOKIE['wmc_current_currency'] ) && array_key_exists( $_COOKIE['wmc_current_currency'], $selected_currencies ) ) {
			$current_currency = $_COOKIE['wmc_current_currency'];
		} else {
			$current_currency = $main_currency;
		}
		if ( isset( $data['min_amount'] ) ) {
			$data['min_amount'] = $data['min_amount'] * $selected_currencies[$current_currency]['rate'];
		}

		return $data;
	}

	/**
	 * Recalculator for mini cart
	 */
	public function flat_pick_cost( $data ) {
		if ( get_option( 'woocommerce_currency' ) != '' ) {
			$main_currency    = get_option( 'woocommerce_currency' );
			$current_currency = $main_currency;
		} else {
			return $data;
		}

		/*Check currency*/
		$selected_currencies = get_option( 'wmc_selected_currencies', array() );
		if ( isset( $_GET['wmc_current_currency'] ) && array_key_exists( $_GET['wmc_current_currency'], $selected_currencies ) ) {
			$current_currency = $_GET['wmc_current_currency'];
		} elseif ( isset( $_COOKIE['wmc_current_currency'] ) && array_key_exists( $_COOKIE['wmc_current_currency'], $selected_currencies ) ) {
			$current_currency = $_COOKIE['wmc_current_currency'];
		} else {
			$current_currency = $main_currency;
		}
		if ( isset( $data['cost'] ) ) {
			$data['cost'] = $data['cost'] * $selected_currencies[$current_currency]['rate'];
		}

		return $data;
	}
}

new WMC_Frontend_Shipping();