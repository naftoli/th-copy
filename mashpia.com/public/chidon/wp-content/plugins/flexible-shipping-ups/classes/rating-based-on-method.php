<?php
/**
 * Repository rating.
 *
 * @package Flexible Shipping Ups
 */

/**
 * Can display rating notices based on shipping method creation time.
 */
class Flexible_Shipping_UPS_Rating_Based_On_Method implements \UpsFreeVendor\WPDesk\PluginBuilder\Plugin\Hookable {

	const CLOSE_TEMPORARY_NOTICE = 'close-temporary-notice';

	const SECOND_NOTICE_START_TIME_OPTION = 'flexible_shipping_ups_second_notice_time';

	const FIRST_NOTICE_NAME  = 'ups_rating_first_method_1';
	const SECOND_NOTICE_NAME = 'ups_rating_first_method_2';

	const NOTICES_OFFSET = 1209600; // Two weeks in seconds.

	/**
	 * Method watcher.
	 *
	 * @var Flexible_Shipping_UPS_Shipping_Method_Watcher
	 */
	private $method_watcher;

	/**
	 * First notice start time.
	 *
	 * @var string
	 */
	private $first_notice_start_time = '';

	/**
	 * Second notice start time.
	 *
	 * @var string
	 */
	private $second_notice_start_time = '';

	/**
	 * Flexible_Shipping_UPS_Rating_Based_On_Method constructor.
	 *
	 * @param Flexible_Shipping_UPS_Shipping_Method_Watcher $method_watcher .
	 */
	public function __construct( Flexible_Shipping_UPS_Shipping_Method_Watcher $method_watcher ) {
		$this->method_watcher = $method_watcher;

		$this->second_notice_start_time = get_option( self::SECOND_NOTICE_START_TIME_OPTION, '' );

		if ( '' === $this->second_notice_start_time && '' !== $method_watcher->get_first_method_creation_time() ) {
			$this->first_notice_start_time = gmdate( 'Y-m-d H:i:s', strtotime( $method_watcher->get_first_method_creation_time() ) + self::NOTICES_OFFSET );
		}
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_notices', array( $this, 'maybe_show_first_notice' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_second_notice' ) );
		add_action(
			'wpdesk_notice_dismissed_notice',
			array( $this, 'maybe_start_second_notice_on_dismiss_first_notice' ),
			10,
			2
		);
	}

	/**
	 * Maybe reset counter.
	 *
	 * @param string $notice_name .
	 * @param string $source .
	 */
	public function maybe_start_second_notice_on_dismiss_first_notice( $notice_name, $source = null ) {
		if ( self::FIRST_NOTICE_NAME === $notice_name && ( empty( $source ) || self::CLOSE_TEMPORARY_NOTICE === $source ) ) {
			update_option( self::SECOND_NOTICE_START_TIME_OPTION, gmdate( 'Y-m-d H:i:s', intval( current_time( 'timestamp' ) ) + self::NOTICES_OFFSET ) );
		}
	}

	/**
	 * Action links
	 *
	 * @return array
	 */
	protected function action_links() {
		$actions[] = sprintf(
			// Translators: link.
			__( '%1$sOk, you deserved it%2$s', 'flexible-shipping-ups' ),
			'<a class="fs-ups-deserved" target="_blank" href="' . esc_url( 'https://wpde.sk/fs-ups-rate' ) . '">',
			'</a>'
		);
		$actions[] = sprintf(
			// Translators: link.
			__( '%1$sNope, maybe later%2$s', 'flexible-shipping-ups' ),
			'<a class="fs-ups-close-temporary-notice notice-dismiss-link" data-source="' . self::CLOSE_TEMPORARY_NOTICE . '" href="#">',
			'</a>'
		);
		$actions[] = sprintf(
			// Translators: link.
			__( '%1$sI already did%2$s', 'flexible-shipping-ups' ),
			'<a class="fs-ups-already-did notice-dismiss-link" data-source="already-did" href="#">',
			'</a>'
		);
		return $actions;
	}

	/**
	 * Get notice content.
	 *
	 * @return string
	 */
	private function get_notice_content() {
		$content  = __( 'Awesome, you\'ve been using Flexible Shipping UPS for more than 2 weeks. Could you please do me a BIG favor and give it a 5-star rating on WordPress? ~ Peter', 'flexible-shipping-ups' );
		$content .= '<br/>';
		$content .= implode( ' | ', $this->action_links() );
		return $content;
	}

	/**
	 * Should display notice.
	 *
	 * @return bool
	 */
	private function should_display_notice() {
		$current_screen     = get_current_screen();
		$display_on_screens = [ 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-settings' ];
		if ( ! empty( $current_screen ) && in_array( $current_screen->id, $display_on_screens, true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Maybe show first notice.
	 */
	public function maybe_show_first_notice() {
		if ( $this->should_display_notice() ) {
			if ( '' !== $this->first_notice_start_time && current_time( 'mysql' ) > $this->first_notice_start_time ) {
				$this->show_notice( self::FIRST_NOTICE_NAME );
			}
		}
	}

	/**
	 * Maybe show second notice.
	 */
	public function maybe_show_second_notice() {
		if ( $this->should_display_notice() ) {
			if ( ( '' !== $this->second_notice_start_time ) && ( current_time( 'mysql' ) > $this->second_notice_start_time ) ) {
				$this->show_notice( self::SECOND_NOTICE_NAME );
			}
		}
	}

	/**
	 * Show notice.
	 *
	 * @param string $notice_name .
	 */
	private function show_notice( $notice_name ) {
		new \UpsFreeVendor\WPDesk\Notice\PermanentDismissibleNotice(
			$this->get_notice_content(),
			$notice_name,
			\UpsFreeVendor\WPDesk\Notice\Notice::NOTICE_TYPE_INFO
		);
	}

}
