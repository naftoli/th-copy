<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Flexible_Shipping_UPS_Checkout' ) ) {

	class Flexible_Shipping_UPS_Checkout {

		const ACCESS_POINTS_ZERO_DATE       = '2019-05-21';
		const OPTION_PLUGIN_ACTIVATION_TIME = 'plugin_activation_flexible-shipping-ups/flexible-shipping-ups.php';

		/**
		 * @var Flexible_Shipping_UPS_Plugin
		 */
		private $plugin;

		/**
		 * Flexible_Shipping_UPS_Checkout constructor.
		 *
		 * @param Flexible_Shipping_UPS_Plugin $plugin
		 */
		public function __construct( $plugin ) {
			$this->plugin = $plugin;
		}

		/**
		 * Hooks.
		 */
		public function hooks() {
			add_action( 'woocommerce_review_order_after_shipping', array( $this, 'woocommerce_review_order_after_shipping_action' ) );
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'woocommerce_checkout_update_order_review_action' ) );

			add_action( 'woocommerce_checkout_process', array( $this, 'woocommerce_checkout_process_action' ) );

			add_action( 'woocommerce_checkout_create_order', array( $this, 'woocommerce_checkout_create_order_action' ), 10, 2 );
		}

		/**
		 * On create order - Woocommerce 3.0 and later.
		 *
		 * @param WC_Order $order .
		 * @param array    $data .
		 */
		public function woocommerce_checkout_create_order_action( $order, $data ) {
			if ( $this->is_ups_access_point_in_selected_shipping_method() ) {
				if ( isset( $_POST['ups_access_point'] ) ) {
					$order->add_meta_data( '_ups_access_point', $_POST['ups_access_point'] );
				}
			}
		}

		/**
		 * Validate checkout for selected access point.
		 */
		public function woocommerce_checkout_process_action() {
			if ( WC()->cart->needs_shipping() ) {
				if ( $this->is_ups_access_point_in_selected_shipping_method() ) {
					if ( empty( $_POST['ups_access_point'] ) ) {
						$message = __( 'Please select UPS Access Point', 'flexible-shipping-ups' );
						$type    = 'error';
						if ( ! wc_has_notice( $message, $type ) ) {
							wc_add_notice( $message, $type );
						}
					}
				}
			}
		}

		/**
		 * Force shipping recalculation.
		 *
		 * @param array $post_data Post data.
		 */
		public function woocommerce_checkout_update_order_review_action( $post_data ) {
			/*
			 * Force shipping recalculation!
			 * https://stackoverflow.com/a/45763102
			 */
			foreach ( WC()->cart->get_cart() as $key => $value ) {
				WC()->cart->set_quantity( $key, $value['quantity'] + 1 );
				WC()->cart->set_quantity( $key, $value['quantity'] );
				break;
			}
		}

		/**
		 * Check if UPS shipping method with access point is selected in checkout.
		 *
		 * @return bool
		 */
		private function is_ups_access_point_in_selected_shipping_method() {
			$ups_access_point_in_selected_shipping_method = false;

			$shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

			if (is_array($shipping_methods)) {
				foreach ( $shipping_methods as $shipping_method ) {
					if ( strpos( $shipping_method, 'flexible_shipping_ups' ) === 0 ) {
						if ( strpos( $shipping_method, '_access_point' ) !== false ) {
							$ups_access_point_in_selected_shipping_method = true;
						}
					}
				}
			}

			return $ups_access_point_in_selected_shipping_method;
		}

		/**
		 * Is single Access Point available.
		 *
		 * @return bool
		 */
		private function is_single_access_point_available() {
			$activation_time = strtotime(
				get_option(
					self::OPTION_PLUGIN_ACTIVATION_TIME,
					date( 'Y-m-d', (int) current_time( 'timestamp' ) )
				)
			);
			if ( defined( 'FLEXIBLE_SHIPPING_UPS_PRO_VERSION' ) || $activation_time < strtotime( self::ACCESS_POINTS_ZERO_DATE ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Prepare post data.
		 *
		 * @return array
		 */
		private function prepare_post_data() {
			if ( !empty( $_REQUEST['post_data'] ) ) {
				parse_str( $_REQUEST['post_data'], $post_data );
			} else {
				$post_data = array();
			}
			return $post_data;
		}

		/**
		 * Displays select item with UPS access points.
		 *
		 * @throws Exception .
		 */
		public function woocommerce_review_order_after_shipping_action() {
			if ( ! $this->is_ups_access_point_in_selected_shipping_method() ) {
				return;
			}

			$customer = WC()->customer;

			$post_data = $this->prepare_post_data();

			if ( empty( $_REQUEST['s_country'] ) ) {
				$country = $customer->get_shipping_country();
				if ( empty( $country ) ) {
					$country = WC()->countries->get_base_country();
				}
			} else {
				$country = $_REQUEST['s_country'];
			}
			if ( empty( $_REQUEST['s_postcode'] ) ) {
				$postcode = $customer->get_shipping_postcode();
				if ( empty( $postcode ) ) {
					$postcode = get_option( 'woocommerce_store_postcode', '' );
				}
			} else {
				$postcode = $_REQUEST['s_postcode'];
			}

			$args = array();

			$access_points = $this->plugin->get_access_points_helper();

			$nearest_location = $access_points->get_nearest_access_point_for_postcode( $country, $postcode );

			$args['nearest_location'] = $nearest_location;

			if ( $this->is_single_access_point_available() ) {
				$this->display_single_point( $nearest_location, $access_points );
			} else {
				$this->display_multiple_points( $country, $postcode, $post_data, $access_points );
			}

		}

		/**
		 * Display single point.
		 *
		 * @param stdClass                                   $nearest_location .
		 * @param Flexible_Shipping_UPS_Access_Points_Helper $access_points .
		 */
		private function display_single_point( $nearest_location, $access_points ) {
			$args['select_options'] = array();
			if ( null !== $nearest_location ) {
				$selected_access_point_id      = $access_points->get_public_access_point_id_from_location( $nearest_location );
				$args['selected_access_point'] = $selected_access_point_id;

				$args['select_options'][ $selected_access_point_id ] = $access_points->prepare_access_point_address_as_label( $nearest_location );
			}
			echo $this->plugin->load_template( 'shipping-method-after-single-access-point', '', $args );  // WPCS: XSS ok.
		}

		/**
		 * Display multiple points.
		 *
		 * @param string                                     $country .
		 * @param string                                     $postcode .
		 * @param array                                      $post_data .
		 * @param Flexible_Shipping_UPS_Access_Points_Helper $access_points .
		 * @throws Exception .
		 */
		private function display_multiple_points( $country, $postcode, $post_data, $access_points ) {
			$locations = $access_points->get_access_points_for_postcode( $country, $postcode, 50 );

			$args['select_options'] = $access_points->prepare_items_for_select_field( $locations );
			$args['select_options'] = array( '0' => __( 'Select access point', 'flexible-shipping-ups' ) ) + $args['select_options'];

			$args['selected_access_point'] = '0';
			if ( isset( $post_data['ups_access_point'] ) && array_key_exists( $post_data['ups_access_point'], $args['select_options'] ) ) {
				$args['selected_access_point'] = $post_data['ups_access_point'];
			}
			echo $this->plugin->load_template( 'shipping-method-after', '', $args ); // WPCS: XSS ok.
		}

	}

}
