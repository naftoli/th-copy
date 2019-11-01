<?php
/**
 * Plugin.
 *
 * @package Flexible Shipping Ups
 */

use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\TemplateLoad;

/**
 * Class Flexible_Shipping_UPS_Plugin
 */
class Flexible_Shipping_UPS_Plugin extends AbstractPlugin implements HookableCollection {

	use HookableParent;
	use TemplateLoad;


	const LOGGING_CONTEXT               = 'flexible-shipping-ups';
	const PRIORITY_BEFORE_SHARED_HELPER = -35;

	/**
	 * Plugin info.
	 *
	 * @var \UpsFreeVendor\WPDesk_Plugin_Info
	 */
	protected $plugin_info;

	/**
	 * Scripts version.
	 *
	 * @var string
	 */
	private $scripts_version = '18';

	/**
	 * Checkout.
	 *
	 * @var Flexible_Shipping_UPS_Checkout
	 */
	private $checkout;

	/**
	 * Access points helper.
	 *
	 * @var Flexible_Shipping_UPS_Access_Points_Helper
	 */
	private $access_points_helper;

	/**
	 * Flexible_Shipping_UPS_Plugin constructor.
	 *
	 * @param \UpsFreeVendor\WPDesk_Plugin_Info $plugin_info .
	 */
	public function __construct( \UpsFreeVendor\WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
		parent::__construct( $this->plugin_info );
	}

	/**
	 * Load dependencies.
	 */
	public function load_dependencies() {
	}

	/**
	 * Init plugin.
	 */
	public function init() {
		$this->init_base_variables();
		$this->load_dependencies();
		$this->checkout = new Flexible_Shipping_UPS_Checkout( $this );
		$this->checkout->hooks();

		$order_data = new Flexible_Shipping_UPS_Order_Meta( $this );
		$order_data->hooks();

		$this->add_hookable( new Flexible_Shipping_UPS_Connect_Platform_Info( strtotime( '2018-10-26 23:59:59' ) ) );

		$this->add_hookable( new \UpsFreeVendor\WPDesk\Notice\AjaxHandler( trailingslashit( $this->get_plugin()->get_plugin_url() ) . 'vendor_prefixed/wpdesk/wp-notice/assets' ) );

		$this->add_hookable( new Flexible_Shipping_UPS_Activation_Date() );

		$this->add_hookable( new Flexible_Shipping_UPS_Order_Counter() );

		$this->add_hookable( new Flexible_Shipping_UPS_Rate_Notice() );

		$method_watcher = new Flexible_Shipping_UPS_Shipping_Method_Watcher();
		$this->add_hookable( $method_watcher );
		$this->add_hookable( new Flexible_Shipping_UPS_Rating_Based_On_Method( $method_watcher ) );

		$this->hooks();
	}

	/**
	 * Init base variables for plugin
	 */
	public function init_base_variables() {
		$this->plugin_url = $this->plugin_info->get_plugin_url();

		$this->plugin_path   = $this->plugin_info->get_plugin_dir();
		$this->template_path = $this->plugin_info->get_text_domain();

		$this->plugin_namespace = $this->plugin_info->get_text_domain();
		$this->template_path    = $this->plugin_info->get_text_domain();

		$locale             = get_locale();
		$this->settings_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=flexible_shipping_ups' );
		$this->docs_url     = 'pl_PL' === $locale ? 'https://www.wpdesk.pl/docs/ups-woocommerce-docs/' : 'https://docs.flexibleshipping.com/category/122-ups/';
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		parent::hooks();
		add_filter( 'woocommerce_shipping_methods', array( $this, 'woocommerce_shipping_methods_filter' ), 20, 1 );
		add_action( 'wp_ajax_flexible_shipping_ups_api_status', array( $this, 'wp_ajax_flexible_shipping_ups_api_status' ) );
		add_filter( 'woocommerce_order_shipping_method', array( $this, 'woocommerce_order_shipping_method_filter' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_action' ), self::PRIORITY_BEFORE_SHARED_HELPER );
		add_action( 'admin_notices', array( $this, 'admin_notices_action' ) );
		$this->hooks_on_hookable_objects();
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		$current_screen = get_current_screen();

		if ( $current_screen instanceof WP_Screen && 'woocommerce_page_wc-settings' === $current_screen->id ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_style( 'ups_admin_css', $this->get_plugin_assets_url() . 'css/admin' . $suffix . '.css', array(), $this->scripts_version );
			wp_enqueue_style( 'ups_admin_css' );

			wp_enqueue_script( 'flexible_shipping_ups_admin', trailingslashit( $this->get_plugin_assets_url() ) . 'js/admin' . $suffix . '.js', array(), $this->scripts_version );
			wp_localize_script( 'flexible_shipping_ups_admin', 'flexible_shipping_ups_admin', array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'flexible_shipping_ups_api_status' ),
			) );
		}
	}

	/**
	 * Links on plugins list.
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$is_pl        = 'pl_PL' === get_locale();
		$docs_link    = $this->docs_url;
		$docs_link   .= '?utm_source=ups&utm_medium=quick-link&utm_campaign=docs-quick-link';
		$support_link = $is_pl ? 'https://www.wpdesk.pl/support/' : 'https://flexibleshipping.com/support/';

		$plugin_links = array(
			'<a href="' . $this->settings_url . '">' . __( 'Settings', 'flexible-shipping-ups' ) . '</a>',
			'<a href="' . $docs_link . '" target="_blank">' . __( 'Docs', 'flexible-shipping-ups' ) . '</a>',
			'<a href="' . $support_link . '" target="_blank">' . __( 'Support', 'flexible-shipping-ups' ) . '</a>',
		);

		if ( ! defined( 'FLEXIBLE_SHIPPING_UPS_PRO_VERSION' ) ) {
			$upgrade_link   = $is_pl ? 'https://www.wpdesk.pl/sklep/ups-woocommerce/' : 'https://flexibleshipping.com/products/flexible-shipping-ups-pro/';
			$upgrade_link  .= '?utm_source=ups&utm_medium=quick-link&utm_campaign=upgrade-quick-link';
			$plugin_links[] = '<a target="_blank" href="' . $upgrade_link . '" style="color:#d64e07;font-weight:bold;">' . __( 'Upgrade', 'flexible-shipping-ups' ) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Return Access Points Helper.
	 *
	 * @return Flexible_Shipping_UPS_Access_Points_Helper
	 */
	public function get_access_points_helper() {
		if ( empty( $this->access_points_helper ) ) {
			$shipping_method            = $this->get_ups_shipping_method();
			$this->access_points_helper = new Flexible_Shipping_UPS_Access_Points_Helper(
				$shipping_method->settings['access_key'],
				$shipping_method->settings['user_id'],
				$shipping_method->settings['password']
			);
		}
		return $this->access_points_helper;
	}

	/**
	 * Get UPS shipping method.
	 *
	 * @return Flexible_Shipping_UPS_Shipping_Method
	 */
	public function get_ups_shipping_method() {
		$shipping_methods = WC()->shipping()->get_shipping_methods();
		/**
		 * IDE type hint.
		 *
		 * @var Flexible_Shipping_UPS_Shipping_Method $flexible_shipping_ups
		 */
		$flexible_shipping_ups = $shipping_methods['flexible_shipping_ups'];
		return $flexible_shipping_ups;
	}

	/**
	 * Check api connection status.
	 *
	 * @param bool $return .
	 *
	 * @return mixed|string|void
	 */
	public function wp_ajax_flexible_shipping_ups_api_status( $return = false ) {
		check_ajax_referer( 'flexible_shipping_ups_api_status', 'security' );
		$flexible_shipping_ups = $this->get_ups_shipping_method();
		$json_response         = array(
			'connected'  => true,
			'status'     => 'OK',
			'class_name' => 'flexible_shipping_ups_api_status_ok',
		);
		$connection_errors     = $flexible_shipping_ups->check_connection_error();
		if ( $connection_errors ) {
			$json_response = array(
				'connected'  => false,
				'status'     => $connection_errors,
				'class_name' => 'flexible_shipping_ups_api_status_error',
			);
		}
		if ( ! $return ) {
			echo json_encode( $json_response );
			die();
		} else {
			return json_encode( $json_response );
		}
	}

	/**
	 * Adds shipping method to Woocommerce.
	 *
	 * @param string[] $methods .
	 *
	 * @return mixed
	 */
	public function woocommerce_shipping_methods_filter( $methods ) {
		include_once __DIR__ . '/shipping-method.php';
		$methods['flexible_shipping_ups'] = 'Flexible_Shipping_UPS_Shipping_Method';
		return $methods;
	}

	/**
	 * Add (Fallback) string on orders list when fallback action was taken for shipping.
	 *
	 * @param string   $shipping_method_name .
	 * @param WC_Order $order .
	 *
	 * @return string
	 */
	public function woocommerce_order_shipping_method_filter( $shipping_method_name, $order ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $shipping_method_name;
		}
		$current_screen = get_current_screen();
		if ( ! is_object( $current_screen ) || 'edit-shop_order' !== $current_screen->id || isset( $_GET['action'] ) ) {
			return $shipping_method_name;
		}
		$shipping_methods = $order->get_shipping_methods();
		$add_to_name = '';
		/** @var WC_Order_Item_Shipping $shipping_method */ // phpcs:ignore
		foreach ( $shipping_methods as $shipping_method ) {
			if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
				if ( isset( $shipping_method['method_id'] ) ) {
					$method_id          = $shipping_method['method_id'];
					$method_id_elements = explode( ':', $method_id );
					if ( isset( $method_id_elements[0] ) && isset( $method_id_elements[2] ) ) {
						if ( 'flexible_shipping_ups' === $method_id_elements[0] && 'fallback' === $method_id_elements[2] ) {
							$add_to_name = __( ' (Fallback)', 'flexible-shipping-ups' );
						}
					}
				}
			} else {
				$method_id          = $shipping_method->get_method_id();
				$method_id_elements = explode( ':', $method_id );
				if ( isset( $method_id_elements[0] ) && isset( $method_id_elements[2] ) ) {
					if ( 'flexible_shipping_ups' === $method_id_elements[0] && 'fallback' === $method_id_elements[2] ) {
						$add_to_name = __( ' (Fallback)', 'flexible-shipping-ups' );
					}
				}
			}
		}
		$add_to_name = trim( $add_to_name, ',' );
		return $shipping_method_name . $add_to_name;
	}

	/**
	 * Plugins loaded hook.
	 */
	public function plugins_loaded_action() {
		apply_filters( 'wpdesk_tracker_enabled', function() {
			return ( ! empty( $_SERVER['SERVER_ADDR'] ) && '127.0.0.1' === $_SERVER['SERVER_ADDR'] );
		} );

		$tracker_factory = new WPDesk_Tracker_Factory();
		$tracker_factory->create_tracker( basename( dirname( __FILE__ ) ) );
	}

	/**
	 * Display admin notice.
	 */
	public function admin_notices_action() {
		$wc_message = false;
		if ( ! function_exists( 'WC' ) ) {
			$wc_message = true;
		} else {
			if ( version_compare( WC_VERSION, '2.6', '<' ) ) {
				$wc_message = true;
			}
		}
		if ( $wc_message ) {
			$class   = 'notice notice-error';
			$message = __( 'Flexible Shipping UPS requires at least version 2.6 of WooCommerce plugin.', 'flexible-shipping-ups' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); // phpcs: XSS ok.
		}
	}

}
