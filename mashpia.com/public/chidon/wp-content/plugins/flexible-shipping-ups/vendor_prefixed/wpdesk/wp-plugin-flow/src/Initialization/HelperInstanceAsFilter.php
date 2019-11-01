<?php

namespace UpsFreeVendor\WPDesk\Plugin\Flow\Initialization\Simple;

use UpsFreeVendor\WPDesk\Helper\PrefixedHelperAsLibrary;
/**
 * Trait helps with helper initialization
 *
 * @package WPDesk\Plugin\Flow\Initialization\Simple
 */
trait HelperInstanceAsFilter
{
    /** @var \WPDesk\Helper\PrefixedHelperAsLibrary */
    private static $helper_instance;
    /**
     * Returns filter action name for helper instance
     *
     * @return string
     */
    private function get_helper_action_name()
    {
        return 'wpdesk_helper_instance';
    }
    /**
     * Instantiate helper and return it
     *
     * @return PrefixedHelperAsLibrary
     */
    private function get_helper_instance()
    {
        return \apply_filters($this->get_helper_action_name(), null);
    }
    /**
     * Prepare helper to be instantiated using wpdesk_helper_instance filter
     *
     * @return void|PrefixedHelperAsLibrary
     */
    private function prepare_helper_action()
    {
        \class_exists(\WPDesk\Helper\HelperAsLibrary::class);
        // autoload this class
        \add_filter($this->get_helper_action_name(), function ($helper_instance) {
            if (\is_object($helper_instance)) {
                return $helper_instance;
            }
            if (\is_object(self::$helper_instance)) {
                return self::$helper_instance;
            }
            if (\apply_filters('wpdesk_can_start_helper', \true, $this->plugin_info)) {
                self::$helper_instance = new \UpsFreeVendor\WPDesk\Helper\PrefixedHelperAsLibrary();
                self::$helper_instance->hooks();
                \do_action('wpdesk_helper_started', self::$helper_instance, $this->plugin_info);
                return self::$helper_instance;
            }
        });
    }
}
