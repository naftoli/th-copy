<?php
/**
 * Shipping method watcher.
 *
 * @package Flexible Shipping Ups
 */

use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can watch sjipping method creation.
 */
class Flexible_Shipping_UPS_Shipping_Method_Watcher implements Hookable {

	const OPTION_NAME_WATCHING       = 'flexible_shipping_ups_method_watching';
	const OPTION_NAME_METHOD_CREATED = 'flexible_shipping_ups_method_created';

	const OPTION_PLUGIN_ACTIVATION_TIME = 'plugin_activation_flexible-shipping-ups/flexible-shipping-ups.php';

	const METHOD_WATCHER_ZERO_DATE = '2019-09-24';

	/**
	 * First method added time.
	 *
	 * @var string
	 */
	private $first_method_creation_time = '';

	/**
	 * First method watching.
	 *
	 * @var int
	 */
	private $first_method_watching = 0;

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'maybe_init_watching' ), 10, 3 );
		add_action( 'woocommerce_shipping_zone_method_added', array( $this, 'watch_added_shipping_method' ), 10, 3 );
	}

	/**
	 * Init watching.
	 */
	public function maybe_init_watching() {
		$this->first_method_watching = intval( get_option( self::OPTION_NAME_WATCHING, 0 ) );
		if ( 0 === $this->first_method_watching ) {
			$ups_free_activation_time = get_option( self::OPTION_PLUGIN_ACTIVATION_TIME, current_time( 'mysql' ) );
			if ( strtotime( $ups_free_activation_time ) < strtotime( self::METHOD_WATCHER_ZERO_DATE ) ) {
				$this->init_watching_from_existing_shipping_methods();
			}
			update_option( self::OPTION_NAME_WATCHING, 1 );
			$this->first_method_watching = 1;
		}
	}

	/**
	 * First time init watching.
	 * Sets first time to current time when UPS shipping method already exists.
	 */
	private function init_watching_from_existing_shipping_methods() {
		$shipping_zones = WC_Shipping_Zones::get_zones();
		/** @var WC_Shipping_Zone $shipping_zone */ // phpcs:ignore
		foreach ( $shipping_zones as $shipping_zone_data ) {
			$shipping_zone = WC_Shipping_Zones::get_zone( $shipping_zone_data['id'] );
			if ( $shipping_zone && $shipping_zone instanceof WC_Shipping_Zone ) {
				$shipping_methods = $shipping_zone->get_shipping_methods();
				foreach ( $shipping_methods as $shipping_method ) {
					if ( $shipping_method instanceof Flexible_Shipping_UPS_Shipping_Method ) {
						update_option( self::OPTION_NAME_METHOD_CREATED, current_time( 'mysql' ) );
					}
				}
			}
		}
	}

	/**
	 * Watch added shipping method.
	 * Set first time to current time when first UPS shipping method created.
	 *
	 * @param int    $instance_id .
	 * @param string $type .
	 * @param int    $zone_id .
	 */
	public function watch_added_shipping_method( $instance_id, $type, $zone_id ) {
		$this->first_method_creation_time = get_option( self::OPTION_NAME_METHOD_CREATED, '' );
		if ( Flexible_Shipping_UPS_Shipping_Method::METHOD_ID === $type ) {
			if ( '' === $this->first_method_creation_time ) {
				$this->first_method_creation_time = (string) current_time( 'mysql' );
				update_option( self::OPTION_NAME_METHOD_CREATED, $this->first_method_creation_time );
			}
		}
	}

	/**
	 * Get first method creation time.
	 *
	 * @return string
	 */
	public function get_first_method_creation_time() {
		$this->first_method_creation_time = get_option( self::OPTION_NAME_METHOD_CREATED, '' );
		return $this->first_method_creation_time;
	}


}
