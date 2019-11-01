<?php
/**
	Plugin Name: Flexible Shipping UPS
	Plugin URI: https://wordpress.org/plugins/flexible-shipping-ups/
	Description: WooCommerce UPS Shipping Method and live rates.
	Version: 1.7.0
	Author: WP Desk
	Author URI: https://www.wpdesk.net/
	Text Domain: flexible-shipping-ups
	Domain Path: /lang/
	Requires at least: 4.5
	Tested up to: 5.2.3
	WC requires at least: 3.1.0
	WC tested up to: 3.7.0
 *
	@package Flexible_Shipping_UPS

	Copyright 2017 WP Desk Ltd.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THESE TWO VARIABLES CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '1.7.0';

$plugin_release_timestamp = '2019-10-01 09:15';

$plugin_name        = 'Flexible Shipping UPS';
$plugin_class_name  = 'Flexible_Shipping_UPS_Plugin';
$plugin_text_domain = 'flexible-shipping-ups';
$product_id         = 'Flexible Shipping UPS';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

define( 'FLEXIBLE_SHIPPING_UPS_VERSION', $plugin_version );
define( $plugin_class_name, $plugin_version );

$requirements = array(
	'php'     => '5.5',
	'wp'      => '4.5',
	'plugins' => array(
		array(
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		),
	),
);

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52-free.php';

require_once __DIR__ . '/vendor_prefixed/guzzlehttp/guzzle/src/functions_include.php';
require_once __DIR__ . '/vendor_prefixed/guzzlehttp/promises/src/functions_include.php';
require_once __DIR__ . '/vendor_prefixed/guzzlehttp/psr7/src/functions_include.php';

if ( ! function_exists( 'flexible_shipping_ups_plugin' ) ) {
	/**
	 * Share plugin instance with Pro version.
	 *
	 * @return Flexible_Shipping_UPS_Plugin
	 */
	function flexible_shipping_ups_plugin() {
		$storage_factory = new \UpsFreeVendor\WPDesk\PluginBuilder\Storage\StorageFactory();
		$storage         = $storage_factory->create_storage();
		return $storage->get_from_storage( Flexible_Shipping_UPS_Plugin::class );
	}
}

$ups_tracker = new Flexible_Shipping_UPS_Tracker();
$ups_tracker->hooks();
