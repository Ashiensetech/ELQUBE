<?php

/*
Plugin Name: Woo Multi Currency
Plugin URI: http://villatheme.com
Description: Creat a price switcher or approximate price with unlimit currency. Working base on WooCommerce plugin.
Version: 1.4.4
Author: Cuong Nguyen and Andy Ha (tuhn@villatheme.com)
Author URI: http://villatheme.com
Copyright 2016 VillaTheme.com. All rights reserved.
*/
define( 'WOO_MULTI_CURRENCY_VERSION', '1.4.4' );
define( 'WOO_MULTI_CURRENCY_CSS', WP_PLUGIN_URL . '/woo-multi-currency/css/' );
define( 'WOO_MULTI_CURRENCY_JS', WP_PLUGIN_URL . '/woo-multi-currency/js/' );

require_once plugin_dir_path( __FILE__ ) . 'admin/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'front-end/mini-cart.php';
require_once plugin_dir_path( __FILE__ ) . 'front-end/filter-price.php';
require_once plugin_dir_path( __FILE__ ) . 'front-end/shipping.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ads.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

if ( ! function_exists( 'get_woocommerce_currencies' ) ) {
	require_once WP_PLUGIN_DIR . '/woocommerce/includes/wc-core-functions.php';
}

class Woo_Multi_Currency {
	public $main_currency = "GBP";
	public $current_currency = "GBP";
	public $current_position = "";
	public $selected_currencies = array();
	public $currencies_list = array();
	public $currencies_symbol = array();


	public function __construct() {
		if ( get_option( 'woocommerce_currency' ) != '' ) {
			$this->main_currency    = get_option( 'woocommerce_currency' );
			$this->current_currency = $this->main_currency;
		}
		$this->currencies_list   = $this->wmc_get_currencies_list();
		$this->currencies_symbol = $this->wmc_get_currencies_symbol();
		add_action( 'init', array( $this, 'wmc_load_js_css' ) );
		if ( is_admin() && isset( $_POST['wmc_currency'] ) ) {
			$this->wmc_save_selected_currencies();
		}
		if ( get_option( 'wmc_allow_multi' ) == "" ) {
			update_option( 'wmc_allow_multi', 'yes' );
		}
		$this->selected_currencies = get_option( 'wmc_selected_currencies' );
		if ( empty( $this->selected_currencies ) || ( ! empty( $this->selected_currencies ) && ! array_key_exists( $this->main_currency, $this->selected_currencies ) ) ) {
			$this->selected_currencies[$this->main_currency]['rate'] = 1;
			if ( get_option( 'woocommerce_currency_pos' ) != '' ) {
				$this->selected_currencies[$this->main_currency]['pos'] = get_option( 'woocommerce_currency_pos' );
			} else {
				$this->selected_currencies[$this->main_currency]['pos'] = 'left';
			}
			$this->selected_currencies[$this->main_currency]['symbol']     = $this->currencies_symbol[$this->main_currency];
			$this->selected_currencies[$this->main_currency]['is_main']    = 1;
			$this->selected_currencies[$this->main_currency]['num_of_dec'] = 2;
			update_option( 'wmc_selected_currencies', $this->selected_currencies );
		}
		if ( ! empty( $this->selected_currencies ) && array_key_exists( $this->main_currency, $this->selected_currencies ) ) {
			if ( $this->selected_currencies[$this->main_currency]['is_main'] != 1 ) {
				foreach ( $this->selected_currencies as $code => $details ) {
					$this->selected_currencies[$code]['is_main'] = 0;
				}
				$this->selected_currencies[$this->main_currency]['is_main'] = 1;
				$this->selected_currencies[$this->main_currency]['rate']    = 1;
				update_option( 'wmc_selected_currencies', $this->selected_currencies );
			}
		}
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_shortcode( 'woo_multi_currency', array( $this, 'wmc_widget_shortcode' ) );
		add_shortcode( 'woo_multi_currency_converter', array( $this, 'wmc_widget_converter_shortcode' ) );
		add_filter( 'woocommerce_get_order_currency', array( $this, 'wmc_get_order_currency' ), 10, 2 );

		if ( is_admin() ) {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'wmc_add_tab' ), 30 );
			add_action( 'woocommerce_settings_tabs_wmc', array( $this, 'wmc_add_setting_fields' ) );
			add_action( 'woocommerce_update_options_wmc', array( $this, 'wmc_update_settings_fields' ) );
			add_filter(
				'plugin_action_links_woo-multi-currency/woo-multi-currency.php', array(
					$this,
					'wmc_add_settings_link'
				)
			);

		} else {
			@session_start();
			if ( isset( $_GET['wmc_current_currency'] ) && array_key_exists( $_GET['wmc_current_currency'], $this->selected_currencies ) ) {
				$this->current_currency = $_GET['wmc_current_currency'];
				setcookie( 'wmc_current_currency', $this->current_currency, time() + 60 * 60 * 24, '/' );
			} elseif ( isset( $_COOKIE['wmc_current_currency'] ) && array_key_exists( $_COOKIE['wmc_current_currency'], $this->selected_currencies ) ) {
				$this->current_currency = $_COOKIE['wmc_current_currency'];
			} else {
				$this->current_currency = $this->main_currency;
			}

			if ( get_option( 'wmc_allow_multi' ) == 'no' ) {
				if ( isset( $_GET['wc-ajax'] ) AND $_GET['wc-ajax'] == 'update_order_review' ) {
					$this->current_currency = $this->main_currency;
					setcookie( 'wmc_current_currency', $this->current_currency, - ( time() + 60 * 60 * 24 ), '/' );
				}
			}

			if ( get_option( 'wmc_allow_multi' ) == 'yes' ) {
				add_filter( 'woocommerce_get_regular_price', array( $this, 'wmc_woocommerce_get_price' ), 10, 2 );
				add_filter( 'woocommerce_get_sale_price', array( $this, 'wmc_woocommerce_get_price' ), 10, 2 );
				add_filter( 'woocommerce_get_price', array( $this, 'wmc_woocommerce_get_price' ), 10, 2 );
				add_filter( 'woocommerce_currency', array( $this, 'wmc_get_current_currency' ) );
//				add_filter( 'woocommerce_package_rates', array( $this, 'wmc_package_rates' ) );

				add_filter(
					'woocommerce_get_variation_regular_price', array(
					$this,
					'wmc_woocommerce_get_price'
				), 9999
				);
				add_filter( 'woocommerce_get_variation_sale_price', array( $this, 'wmc_woocommerce_get_price' ), 9999 );
				add_filter( 'woocommerce_variation_prices', array( $this, 'get_woocommerce_variation_prices' ) );
				add_filter(
					'woocommerce_variation_prices_price', array(
					$this,
					'get_woocommerce_variation_prices'
				), 9999, 1
				);
				add_filter(
					'woocommerce_variation_prices_regular_price', array(
					$this,
					'get_woocommerce_variation_prices'
				), 9999, 1
				);
				add_filter(
					'woocommerce_variation_prices_sale_price', array(
					$this,
					'get_woocommerce_variation_prices'
				), 9999, 1
				);
				add_filter(
					'woocommerce_get_variation_prices_hash', array(
					$this,
					'wmc_get_woocommerce_get_variation_prices_hash'
				), 9999, 3
				);
//				add_filter( 'woocommerce_shipping_free_shipping_is_available', array( $this, 'woocommerce_shipping_free_shipping_is_available' ), 999, 2 );
			} else {
				add_filter( 'raw_woocommerce_price', array( $this, 'wmc_woocommerce_get_price' ) );
			}

			//add 2 filters to compatible with extra product options plugin.
			add_filter( 'wc_epo_price', array( $this, 'wmc_woocommerce_get_price' ) );
			add_filter( 'woocommerce_tm_epo_price_on_cart', array( $this, 'wmc_woocommerce_get_price' ) );

		}
		add_filter( 'woocommerce_price_format', array( $this, 'wmc_get_price_format' ) );
		add_filter( 'woocommerce_currency_symbol', array( $this, 'wmc_get_currency_symbol' ) );
		add_action( 'init', array( $this, 'wmc_load_text_domain' ) );

		add_filter( 'woocommerce_price_filter_widget_min_amount', array( $this, 'wmc_woocommerce_get_max_min_price' ) );
		add_filter( 'woocommerce_price_filter_widget_max_amount', array( $this, 'wmc_woocommerce_get_max_min_price' ) );
		add_filter( 'wc_price_args', array( $this, 'wc_price_args' ), 10, 1 );

		/*Add Currency Option Title*/
		add_filter( 'woo_multi_currency_tab_options', array( $this, 'currency_option_title' ) );
		if ( get_option( 'wmc_allow_multi', 'no' ) == 'yes' && get_option( 'wmc_recalculate_coupon', 'no' ) == 'yes' ) {
			add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'wmc_coupon_get_discount_amount' ), 999, 5 );
		}
		/*Add order information*/
		add_filter( 'woocommerce_thankyou_order_id', array( $this, 'woocommerce_thankyou_order_id' ), 9 );
	}
	/**
	 * Recalculate free shipping value
	 *
	 * @param $is_available
	 * @param $package
	 *
	 * @return mixed|void
	 */
	public function woocommerce_shipping_free_shipping_is_available( $is_available, $package ) {
		$chosen_shipping_methods = WC()->shipping()->shipping_methods;
		foreach ( $chosen_shipping_methods as $instance_id => $method ) {
			if ( is_int( $instance_id ) && is_object( $method ) && isset( $method->id ) && $method->id == 'free_shipping' ) {
				$wcsfs              = $method;
				$has_coupon         = false;
				$has_met_min_amount = false;
				if ( in_array( $wcsfs->requires, array( 'coupon', 'either', 'both' ) ) ) {
					if ( $coupons = WC()->cart->get_coupons() ) {
						foreach ( $coupons as $code => $coupon ) {
							if ( $coupon->is_valid() && $coupon->enable_free_shipping() ) {
								$has_coupon = true;
								break;
							}
						}
					}
				}

				if ( in_array( $wcsfs->requires, array( 'min_amount', 'either', 'both' ) ) && isset( WC()->cart->cart_contents_total ) ) {
					$total = WC()->cart->get_displayed_subtotal();

					if ( 'incl' === WC()->cart->tax_display_cart ) {
						$total = $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() );
					} else {
						$total = $total - WC()->cart->get_cart_discount_total();
					}

					if ( $total >= ( $wcsfs->min_amount * $this->selected_currencies[$this->current_currency]['rate'] ) ) {
						$has_met_min_amount = true;
					}
				}

				switch ( $wcsfs->requires ) {
					case 'min_amount' :
						$is_available = $has_met_min_amount;
						break;
					case 'coupon' :
						$is_available = $has_coupon;
						break;
					case 'both' :
						$is_available = $has_met_min_amount && $has_coupon;
						break;
					case 'either' :
						$is_available = $has_met_min_amount || $has_coupon;
						break;
					default :
						$is_available = true;
						break;
				}
				if ( $is_available == true ) {
					return apply_filters( 'wmc_woocommerce_shipping_free_shipping_is_available', $is_available, $package );
				}
			}
		}

		return apply_filters( 'wmc_woocommerce_shipping_free_shipping_is_available', $is_available, $package );
	}

	/**
	 * Insert information about order after checkout
	 *
	 * @param $order_id
	 *
	 * @return mixed
	 */
	public function woocommerce_thankyou_order_id( $order_id ) {
		$wmc_order_info = get_option( 'wmc_selected_currencies' );
		update_post_meta( $order_id, 'wmc_order_info', $wmc_order_info );

		return $order_id;
	}

	/**
	 *Change coupon fixed value as exachange rate of order currency with main currency
	 *
	 * @param $discount
	 * @param $discounting_amount
	 * @param $cart_item
	 * @param $single
	 * @param $wc_coupon
	 *
	 * @return mixed
	 */
	public function wmc_coupon_get_discount_amount( $discount, $discounting_amount, $cart_item, $single, $wc_coupon ) {
		if ( $wc_coupon->is_type( array( 'fixed_cart', 'fixed_product' ) ) ) {
			$discount *= $this->selected_currencies[$this->current_currency]['rate'];
		}

		return $discount;

	}

	public function wc_price_args( $price_arg ) {
		$price_arg['decimals'] = $this->get_price_decimals();

		return $price_arg;
	}

	/**
	 * Coverted min price
	 *
	 * @param $raw_price
	 *
	 * @return mixed
	 */
	public function wmc_woocommerce_get_max_min_price( $raw_price ) {

		$demicial = $this->get_price_decimals();

		$raw_price = $raw_price * $this->selected_currencies[$this->current_currency]['rate'];

		return $raw_price;
	}

	/**
	 * Change currency when view orders for each order  to the ordered currency
	 *
	 * @param $order_currency
	 * @param $WC_Order
	 *
	 * @return mixed
	 */
	public function wmc_get_order_currency( $order_currency, $WC_Order ) {
		$this->current_currency                             = $order_currency;
		$this->selected_currencies[$order_currency]['rate'] = 1;
		if ( ! array_key_exists( 'pos', $this->selected_currencies[$order_currency] ) ) {
			$this->selected_currencies[$order_currency]['pos']        = $this->selected_currencies[$this->main_currency]['pos'];
			$this->selected_currencies[$order_currency]['num_of_dec'] = $this->selected_currencies[$this->main_currency]['num_of_dec'];
		}

		return apply_filters( 'wmc_get_order_currency', $order_currency, $WC_Order );
	}

	/**
	 * Return nothing to overwrite hash of price to get
	 */
	public function wmc_get_woocommerce_get_variation_prices_hash() {
		//Do nothing to remove prices hash to alway get live price.
	}

	/**
	 * Convert price of vaiation product to current currency
	 *
	 * @param $price_arr
	 *
	 * @return array
	 */
	public function get_woocommerce_variation_prices( $price_arr ) {
		if ( is_array( $price_arr ) && ! empty( $price_arr ) ) {
			foreach ( $price_arr as $price_type => $values ) {
				foreach ( $values as $key => $value ) {
					$price_arr[$price_type][$key] = $value * $this->selected_currencies[$this->current_currency]['rate'];
				}
			}
		}

		return $price_arr;
	}

	/**
	 * Change currency position as setting
	 *
	 * @param $post
	 *
	 * @return mixed
	 */
	public function view_order( $post ) {
		if ( is_object( $post ) AND $post->post_type == 'shop_order' ) {
			$currency = get_post_meta( $post->ID, '_order_currency', true );
			if ( ! empty( $currency ) ) {
				$this->current_currency = $currency;
				add_filter( 'woocommerce_price_format', array( $this, 'wmc_get_price_format' ) );
			}
		}

		return $post;
	}
	/**
	 * @param $rates  standar taxs or shipping rates of  main currency
	 *
	 * @return mixed    taxs or shipping rates after convert to current currency
	 */
	public function wmc_package_rates( $rates ) {
		$demicial = $this->get_price_decimals();
		foreach ( $rates as $rate ) {
			$value      = $rate->cost * $this->selected_currencies[$this->current_currency]['rate'];
			$rate->cost = number_format( floatval( $value ), $demicial, $this->get_price_decimal_separator(), '' );
		}

		return $rates;
	}

	/**
	 * @return string    Curren currency
	 */
	public function wmc_get_current_currency() {
		return $this->current_currency;
	}

	/**
	 * @param $raw_price    standar price
	 *
	 * @return mixed    price after convert to current currency
	 */
	public function wmc_woocommerce_get_price( $raw_price ) {

		$demicial = $this->get_price_decimals();
		if ( $raw_price !== '' ) {
			$raw_price = $raw_price * $this->selected_currencies[$this->current_currency]['rate'];
		}

		//$raw_price = number_format( $raw_price, $demicial, $this->get_price_decimal_separator(), '' );

		return $raw_price;
	}

	/**
	 * @return mixed    current currency symbol
	 */
	public function wmc_get_currency_symbol() {
		if ( get_post_type() == 'product' && is_admin() ) {
			$this->current_currency = get_option( 'woocommerce_currency' );
		}
		if ( empty( $this->current_currency ) ) {
			$this->current_currency = $this->main_currency;
		}

		return $this->currencies_symbol[$this->current_currency];
	}

	/**
	 * @return string    price format of current currency
	 */
	public function wmc_get_price_format() {
		if ( array_key_exists( $this->current_currency, $this->selected_currencies ) ) {
			$current_pos = $this->selected_currencies[$this->current_currency]['pos'];
		} else {
			$current_pos = get_option( 'woocommerce_currency_pos' );
		}

		switch ( $current_pos ) {
			case 'left' :
				$format = '%1$s%2$s';
				break;
			case 'right' :
				$format = '%2$s%1$s';
				break;
			case 'left_space' :
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space' :
				$format = '%2$s&nbsp;%1$s';
				break;
		}

		return $format;
	}


	/**
	 * Load text domain function
	 */
	public function wmc_load_text_domain() {
		$plugin_dir = basename( dirname( __FILE__ ) );
		load_plugin_textdomain( 'woo-multi-currency', false, $plugin_dir . '\languages' );
	}

	/**
	 * Add Setting link under plugin name
	 *
	 * @param $links
	 *
	 * @return mixed
	 */

	public function wmc_add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=wc-settings&tab=wmc" title="' . esc_attr__( 'Woo Multi Currency', 'woo-multi-currency' ) . '>">' . esc_attr__( 'Settings', 'woo-multi-currency' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Create short code
	 *
	 * @param $args
	 */
	public function wmc_widget_shortcode( $args ) {
		if ( empty( $args ) ) {
			$args = array();
		}

		$args = wp_parse_args(
			$args, apply_filters(
				'wmc_widget_arg', array(
					'selected_currencies' => $this->selected_currencies,
					'currencies_list'     => $this->currencies_list,
					'current_currency'    => $this->current_currency,
				)
			)
		);

		$this->wmc_get_template( 'woo-multi-currency_widget.php', $args );

	}

	/**
	 * Create short code
	 *
	 * @param $args
	 */
	public function wmc_widget_converter_shortcode( $args ) {
		if ( empty( $args ) ) {
			$args = array();
		}

		$args = wp_parse_args(
			$args, apply_filters(
				'wmc_widget_arg', array(
					'selected_currencies' => $this->selected_currencies,
					'currencies_list'     => $this->currencies_list,
					'current_currency'    => $this->current_currency,
				)
			)
		);

		$this->wmc_get_template( 'woo-multi-currency_converter_widget.php', $args );

	}

	/**
	 * Init widget
	 */
	public function widgets_init() {
		include plugin_dir_path( __FILE__ ) . 'widgets/wmc_widget.php';
		register_widget( 'WMC_Widget' );
		include plugin_dir_path( __FILE__ ) . 'widgets/wmc_widget_converter.php';
		register_widget( 'WMC_Widget_Converter' );
	}

	/**
	 * Save currency in backend
	 */
	public function wmc_save_selected_currencies() {
		foreach ( $_POST['wmc_currency'] as $key => $code ) {
			if ( ! empty( $code ) ) {
				$result[$code] = array(
					'is_main'    => intval( $_POST['wmc_hidden_is_main'][$key] ),
					'pos'        => strval( $_POST['wmc_pos'][$key] ),
					'rate'       => floatval( $_POST['wmc_rate'][$key] ),
					'symbol'     => strval( $this->currencies_symbol[$code] ),
					'num_of_dec' => intval( $_POST['num_of_dec'][$key] ),
				);
				if ( $_POST['wmc_hidden_is_main'][$key] == 1 ) {
					$this->main_currency = $code;
					$main_currency_pos   = strval( $_POST['wmc_pos'][$key] );
				}

			}
		}
		update_option( 'wmc_selected_currencies', apply_filters( 'wmc_selected_currencies', $result ) );
		update_option( 'woocommerce_currency', $this->main_currency );
		update_option( 'woocommerce_currency_pos', $main_currency_pos );

	}

	/**
	 * Init JS and CSS
	 */
	public function wmc_load_js_css() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'select2', WOO_MULTI_CURRENCY_JS . 'select2.min.js', array(), WOO_MULTI_CURRENCY_VERSION );
		wp_enqueue_script( 'wmcjs', WOO_MULTI_CURRENCY_JS . 'woo-multi-currency.js', array(), WOO_MULTI_CURRENCY_VERSION );
		wp_enqueue_style( 'select2css', WOO_MULTI_CURRENCY_CSS . 'select2.min.css', array(), WOO_MULTI_CURRENCY_VERSION );
		wp_enqueue_style( 'wmccss', WOO_MULTI_CURRENCY_CSS . 'wmc.css', array(), WOO_MULTI_CURRENCY_VERSION );
	}

	/**
	 * Add Multi Woo Currency tab to WooCommerce setting tab
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function wmc_add_tab( $tabs ) {
		$tabs['wmc'] = esc_attr__( 'Woo Multi Currency', 'woo-multi-currency' );

		return $tabs;
	}

	/**
	 * Add field
	 */
	public function wmc_add_setting_fields() {
		$sections        = array(
			'wmc_currency' => 'Currencies',
			//'woocurrency_options' => 'Options',
		);
		$array_keys      = array_keys( $sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'wmc_currency';
		$args            = array();
		$args            = wp_parse_args(
			$args, apply_filters(
				'wmc_admin_setting_arg', array(
					'currencies_list'            => $this->wmc_get_currencies_list(),
					'currencies_symbol'          => $this->wmc_get_currencies_symbol(),
					'selected_currencies'        => $this->selected_currencies,
					'setting_fields'             => $this->wmc_get_settings_fields(),
					'wmc_auto_update_rates_time' => get_option( 'wmc_auto_update_rates_time' ),
					'wmc_auto_update_time_type'  => get_option( 'wmc_auto_update_time_type' ),
				)
			)
		);

		$this->wmc_get_template_admin( 'woo-multi-currency_admin_setting.php', $args );

	}

	/**
	 * Update setting fields
	 */
	public function wmc_update_settings_fields() {
		$fields = $this->wmc_get_settings_fields();
		woocommerce_update_options( $fields );
	}

	/*
	 * Get setting fields
	 */
	public function wmc_get_settings_fields() {
		$setting_fields   = array();
		$setting_fields[] = array(
			'title' => esc_attr__( 'GENERAL OPTIONS', 'woo-multi-currency' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'woo-multi-currency_ganeral'
		);
		$setting_fields   = apply_filters( 'woo_multi_currency_tab_options_before', $setting_fields );
		$setting_fields[] = array(
			'title'    => esc_attr__( 'Allow multi currency payment', 'woo-multi-currency' ),
			'id'       => 'wmc_allow_multi',
			'default'  => 'no',
			'desc'     => esc_attr__( 'Enable multi currency payment on your shop page.', 'woo-multi-currency' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
		);
		$setting_fields[] = array(
			'title'    => esc_attr__( 'Recalculate coupon value', 'woo-multi-currency' ),
			'id'       => 'wmc_recalculate_coupon',
			'default'  => 'no',
			'desc'     => esc_attr__( 'Recalculate coupon value base on rate of order currency with main currency. Example: coupon AAA value=10, main currency=USD, order currency=EUR, rate: 1 USD = 0.8 EUR, so when "Allow multi currency payment" enabled, client apply coupon code AAA, discount value is 10*0.8=8 EUR', 'woo-multi-currency' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
		);
		$setting_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'woo-multi-currency_ganeral'
		);

		return apply_filters( 'woo_multi_currency_tab_options', $setting_fields );
	}

	public function currency_option_title( $data ) {
		$new_data = array(
			array(
				'title' => esc_attr__( 'CURRENCY OPTIONS', 'woo-multi-currency' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'woo-multi-currency_select_currency'
			),
			array(
				'type' => 'sectionend',
				'id'   => 'woo-multi-currency_select_currency'
			)
		);

		return array_merge( $data, $new_data );
	}

	/*
	 * Get all supported currency symbol
	 */
	public function wmc_get_currencies_symbol() {

		$symbols = array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&fnof;',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'DKK',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x10da;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'Kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x639;.&#x62f;',
			'IRR' => '&#xfdfc;',
			'ISK' => 'Kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x43b;&#x432;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => 'KZT',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;. &#x645;.',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'L',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRO' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/.',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#x434;&#x438;&#x43d;.',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STD' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'L',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'Fr',
			'XCD' => '&#36;',
			'XOF' => 'Fr',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		);

		return $symbols;
	}

	/*
	 * Get all supported currency
	 */
	public function wmc_get_currencies_list() {
		return get_woocommerce_currencies();
		/*return array_unique(
			apply_filters(
				'woocommerce_currencies',
				array(
					'AED' => esc_attr__( 'United Arab Emirates Dirham', 'woo-multi-currency' ),
					'ARS' => esc_attr__( 'Argentine Peso', 'woo-multi-currency' ),
					'AUD' => esc_attr__( 'Australian Dollars', 'woo-multi-currency' ),
					'BDT' => esc_attr__( 'Bangladeshi Taka', 'woo-multi-currency' ),
					'BGN' => esc_attr__( 'Bulgarian Lev', 'woo-multi-currency' ),
					'BRL' => esc_attr__( 'Brazilian Real', 'woo-multi-currency' ),
					'CAD' => esc_attr__( 'Canadian Dollars', 'woo-multi-currency' ),
					'CHF' => esc_attr__( 'Swiss Franc', 'woo-multi-currency' ),
					'CLP' => esc_attr__( 'Chilean Peso', 'woo-multi-currency' ),
					'CNY' => esc_attr__( 'Chinese Yuan', 'woo-multi-currency' ),
					'COP' => esc_attr__( 'Colombian Peso', 'woo-multi-currency' ),
					'CZK' => esc_attr__( 'Czech Koruna', 'woo-multi-currency' ),
					'DKK' => esc_attr__( 'Danish Krone', 'woo-multi-currency' ),
					'DOP' => esc_attr__( 'Dominican Peso', 'woo-multi-currency' ),
					'EGP' => esc_attr__( 'Egyptian Pound', 'woo-multi-currency' ),
					'EUR' => esc_attr__( 'Euros', 'woo-multi-currency' ),
					'GBP' => esc_attr__( 'Pounds Sterling', 'woo-multi-currency' ),
					'HKD' => esc_attr__( 'Hong Kong Dollar', 'woo-multi-currency' ),
					'HRK' => esc_attr__( 'Croatia kuna', 'woo-multi-currency' ),
					'HUF' => esc_attr__( 'Hungarian Forint', 'woo-multi-currency' ),
					'IDR' => esc_attr__( 'Indonesia Rupiah', 'woo-multi-currency' ),
					'ILS' => esc_attr__( 'Israeli Shekel', 'woo-multi-currency' ),
					'INR' => esc_attr__( 'Indian Rupee', 'woo-multi-currency' ),
					'ISK' => esc_attr__( 'Icelandic krona', 'woo-multi-currency' ),
					'JPY' => esc_attr__( 'Japanese Yen', 'woo-multi-currency' ),
					'KES' => esc_attr__( 'Kenyan shilling', 'woo-multi-currency' ),
					'KRW' => esc_attr__( 'South Korean Won', 'woo-multi-currency' ),
					'LAK' => esc_attr__( 'Lao Kip', 'woo-multi-currency' ),
					'MXN' => esc_attr__( 'Mexican Peso', 'woo-multi-currency' ),
					'MYR' => esc_attr__( 'Malaysian Ringgits', 'woo-multi-currency' ),
					'NGN' => esc_attr__( 'Nigerian Naira', 'woo-multi-currency' ),
					'NOK' => esc_attr__( 'Norwegian Krone', 'woo-multi-currency' ),
					'NPR' => esc_attr__( 'Nepali Rupee', 'woo-multi-currency' ),
					'NZD' => esc_attr__( 'New Zealand Dollar', 'woo-multi-currency' ),
					'PHP' => esc_attr__( 'Philippine Pesos', 'woo-multi-currency' ),
					'PKR' => esc_attr__( 'Pakistani Rupee', 'woo-multi-currency' ),
					'PLN' => esc_attr__( 'Polish Zloty', 'woo-multi-currency' ),
					'PYG' => esc_attr__( 'Paraguayan Guaraní', 'woo-multi-currency' ),
					'RON' => esc_attr__( 'Romanian Leu', 'woo-multi-currency' ),
					'RUB' => esc_attr__( 'Russian Ruble', 'woo-multi-currency' ),
					'SAR' => esc_attr__( 'Saudi Riyal', 'woo-multi-currency' ),
					'SEK' => esc_attr__( 'Swedish Krona', 'woo-multi-currency' ),
					'SGD' => esc_attr__( 'Singapore Dollar', 'woo-multi-currency' ),
					'THB' => esc_attr__( 'Thai Baht', 'woo-multi-currency' ),
					'TRY' => esc_attr__( 'Turkish Lira', 'woo-multi-currency' ),
					'TWD' => esc_attr__( 'Taiwan New Dollars', 'woo-multi-currency' ),
					'UAH' => esc_attr__( 'Ukrainian Hryvnia', 'woo-multi-currency' ),
					'USD' => esc_attr__( 'US Dollars', 'woo-multi-currency' ),
					'VND' => esc_attr__( 'Vietnamese Dong', 'woo-multi-currency' ),
					'ZAR' => esc_attr__( 'South African rand', 'woo-multi-currency' ),
				)
			)
		);*/
	}

	/**
	 * Get path of template
	 *
	 * @param        $template_name
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return mixed
	 */
	public function wmc_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = '/woo-multi-currency/';
		}
		if ( ! $default_path ) {
			$default_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/';
		}
		// Look within passed path within the theme - this is priority.
		$template = locate_template( array( trailingslashit( $template_path ) . $template_name, $template_name ) );
		// Get default template/
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters( 'wmc_locate_template', $template, $template_name, $template_path );
	}

	/**
	 * Get template
	 *
	 * @param        $template_name
	 * @param array  $args
	 * @param string $template_path
	 * @param string $default_path
	 */
	public function wmc_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
		$located = $this->wmc_locate_template( $template_name, $template_path, $default_path );
		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );

			return;
		}
		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'wmc_get_template', $located, $template_name, $args, $template_path, $default_path );
		do_action( 'wmc_before_template_part', $template_name, $template_path, $located, $args );
		include( $located );
		do_action( 'wmc_template_part', $template_name, $template_path, $located, $args );
	}

	/**
	 * Get template for admin. Do not allow overwrite
	 *
	 * @param        $template_name
	 * @param array  $args
	 * @param string $template_path
	 * @param string $default_path
	 */
	public function wmc_get_template_admin( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
		$default_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/';
		$located      = $default_path . $template_name;;
		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );

			return;
		}
		do_action( 'wmc_before_template_part', $template_name, $template_path, $located, $args );
		include( $located );
		do_action( 'wmc_template_part', $template_name, $template_path, $located, $args );
	}

	/**
	 * Get template html
	 *
	 * @param        $template_name
	 * @param array  $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return string
	 */
	public function wmc_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		ob_start();
		$this->wmc_get_template( $template_name, $args, $template_path, $default_path );

		return ob_get_clean();
	}

	public function get_price_thousand_separator() {
		$separator = stripslashes( get_option( 'woocommerce_price_thousand_sep' ) );

		return $separator;
	}

	public function get_price_decimal_separator() {
		$separator = stripslashes( get_option( 'woocommerce_price_decimal_sep' ) );

		return $separator ? $separator : '.';
	}

	public function get_price_decimals() {
		try {
			return absint( $this->selected_currencies[$this->current_currency]['num_of_dec'] );
		}
		catch ( Exception $e ) {
			return absint( $this->selected_currencies[$this->main_currency]['num_of_dec'] );
		}
	}

}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$WMC = new Woo_Multi_Currency();
}
?>
