<?php
/**
 * Order meta data.
 *
 * @package Flexible Shipping Ups
 */

/**
 * Displays order meta data.
 */
class Flexible_Shipping_UPS_Order_Meta {

	const META_FALLBACK_REASON = 'fallback_reason';

	/**
	 * Plugin.
	 *
	 * @var Flexible_Shipping_UPS_Plugin
	 */
	private $plugin;

	/**
	 * Flexible_Shipping_UPS_Checkout constructor.
	 *
	 * @param Flexible_Shipping_UPS_Plugin $plugin .
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'maybe_display_access_point_data' ) );
		add_action( 'woocommerce_email_order_meta', array( $this, 'maybe_display_access_point_data' ) );
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'display_meta_key' ), 10, 3 );
	}

	/**
	 * Maybe display access point data.
	 *
	 * @param WC_Order $order .
	 */
	public function maybe_display_access_point_data( $order ) {
		$shipping_methods = $order->get_shipping_methods();
		/** @var WC_Order_Item_Shipping $shipping_method */ // phpcs:ignore
		foreach ( $shipping_methods as $shipping_method ) {
			if ( $shipping_method->meta_exists( Flexible_Shipping_UPS_Shipping_Method::META_UPS_ACCESS_POINT_ADDRESS ) ) {
				$args['ups_access_point_address'] = $shipping_method->get_meta( Flexible_Shipping_UPS_Shipping_Method::META_UPS_ACCESS_POINT_ADDRESS );
				echo $this->plugin->load_template( 'order-details-after-table', '', $args );  // WPCS: XSS ok.
			}
		}
	}

	/**
	 * Display meta key.
	 *
	 * @param string        $display_key .
	 * @param WC_Meta_Data  $meta .
	 * @param WC_Order_Item $order_item .
	 *
	 * @return mixed|string|void
	 */
	public function display_meta_key( $display_key, $meta, $order_item ) {
		if ( $order_item instanceof WC_Order_Item_Shipping && $meta instanceof  WC_Meta_Data ) {
			$data = $meta->get_data();
			if ( Flexible_Shipping_UPS_Shipping_Method::META_UPS_ACCESS_POINT_ADDRESS === $data['key'] ) {
				$display_key = __( 'UPS Access Point Address', 'flexible-shipping-ups' );
			}
			if ( Flexible_Shipping_UPS_Shipping_Method::META_UPS_ACCESS_POINT === $data['key'] ) {
				$display_key = __( 'UPS Access Point', 'flexible-shipping-ups' );
			}
			if ( Flexible_Shipping_UPS_Shipping_Method::META_UPS_SERVICE_CODE === $data['key'] ) {
				$display_key = __( 'UPS Service Code', 'flexible-shipping-ups' );
			}
			if ( self::META_FALLBACK_REASON === $data['key'] ) {
				$display_key = __( 'Fallback reason', 'flexible-shipping-ups' );
			}
		}
		return $display_key;
	}

}
