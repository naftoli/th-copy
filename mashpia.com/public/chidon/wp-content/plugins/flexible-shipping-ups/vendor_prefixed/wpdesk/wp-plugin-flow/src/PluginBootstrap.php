<?php

namespace UpsFreeVendor\WPDesk\Plugin\Flow;

use UpsFreeVendor\WPDesk\Plugin\Flow\Initialization\InitializationFactory;
/**
 * Bootstrap plugin loading
 * - check requirements
 * - prepare plugin info
 * - delegate plugin building to the initializator
 */
final class PluginBootstrap
{
    const LIBRARY_TEXT_DOMAIN = 'wp-plugin-flow';
    const PRIORITY_BEFORE_SHARED_CLASS_LOADER = -40;
    /** @var string */
    private $plugin_version;
    /** @var string */
    private $plugin_release_timestamp;
    /** @var string */
    private $plugin_name;
    /** @var string */
    private $plugin_class_name;
    /** @var string */
    private $plugin_text_domain;
    /** @var string */
    private $plugin_dir;
    /** @var string */
    private $plugin_file;
    /** @var array */
    private $requirements;
    /** @var string */
    private $product_id;
    /**
     * Factory to build strategy how initialize that plugin
     *
     * @var InitializationFactory
     */
    private $initialization_factory;
    /**
     * WPDesk_Plugin_Bootstrap constructor.
     *
     * @param string                $plugin_version
     * @param string                $plugin_release_timestamp
     * @param string                $plugin_name
     * @param string                $plugin_class_name
     * @param string                $plugin_text_domain
     * @param string                $plugin_dir
     * @param string                $plugin_file
     * @param array                 $requirements
     * @param string                $product_id
     * @param InitializationFactory $build_factory
     */
    public function __construct($plugin_version, $plugin_release_timestamp, $plugin_name, $plugin_class_name, $plugin_text_domain, $plugin_dir, $plugin_file, array $requirements, $product_id, \UpsFreeVendor\WPDesk\Plugin\Flow\Initialization\InitializationFactory $build_factory)
    {
        $this->plugin_version = $plugin_version;
        $this->plugin_release_timestamp = $plugin_release_timestamp;
        $this->plugin_name = $plugin_name;
        $this->plugin_class_name = $plugin_class_name;
        $this->plugin_text_domain = $plugin_text_domain;
        $this->plugin_dir = $plugin_dir;
        $this->plugin_file = $plugin_file;
        $this->requirements = $requirements;
        $this->product_id = $product_id;
        $this->initialization_factory = $build_factory;
    }
    /**
     * Run the plugin bootstrap
     */
    public function run()
    {
        $this->init_translations();
        \add_action('plugins_loaded', [$this, 'action_check_requirements_and_load'], self::PRIORITY_BEFORE_SHARED_CLASS_LOADER);
    }
    /**
     * Adds text domain used in a library
     */
    private function init_translations()
    {
        if (\function_exists('determine_locale')) {
            $locale = \determine_locale();
        } else {
            // before WP 5.0 compatibility
            $locale = \get_locale();
        }
        $locale = \apply_filters('plugin_locale', $locale, self::LIBRARY_TEXT_DOMAIN);
        $lang_mo_file = __DIR__ . '/../../lang/' . self::LIBRARY_TEXT_DOMAIN . '-' . $locale . '.mo';
        if (\file_exists($lang_mo_file)) {
            \load_textdomain(self::LIBRARY_TEXT_DOMAIN, $lang_mo_file);
        }
    }
    /**
     * Hook to check if plugin requirements passes and plugin can be initialized
     */
    public function action_check_requirements_and_load()
    {
        $requirements_checker = $this->create_requirements_checker();
        if (!$requirements_checker->are_requirements_met()) {
            $requirements_checker->render_notices();
            return;
        }
        $plugin_info = $this->get_plugin_info();
        if (\apply_filters('wpdesk_can_initialize_plugin', \true, $plugin_info)) {
            $strategy = $this->initialization_factory->create_initialization_strategy($plugin_info);
            $plugin = $strategy->run();
            \do_action('wpdesk_plugin_initialized', $plugin, $plugin_info);
        }
    }
    /**
     * Factory method creates requirement checker to run the checks
     *
     * @return \WPDesk_Requirement_Checker
     */
    private function create_requirements_checker()
    {
        /** @var \WPDesk_Requirement_Checker_Factory $requirements_checker_factory */
        $requirements_checker_factory = new \UpsFreeVendor\WPDesk_Basic_Requirement_Checker_Factory();
        return $requirements_checker_factory->create_from_requirement_array(__FILE__, $this->plugin_name, $this->requirements);
    }
    /**
     * Factory method creates \WPDesk_Plugin_Info to bootstrap info about plugin in one place
     *
     * TODO: move to WPDesk_Plugin_Info factory
     *
     * @return \WPDesk_Plugin_Info
     */
    private function get_plugin_info()
    {
        $plugin_info = new \UpsFreeVendor\WPDesk_Plugin_Info();
        $plugin_info->set_plugin_file_name(\plugin_basename($this->plugin_file));
        $plugin_info->set_plugin_name($this->plugin_name);
        $plugin_info->set_plugin_dir($this->plugin_dir);
        $plugin_info->set_class_name($this->plugin_class_name);
        $plugin_info->set_version($this->plugin_version);
        $plugin_info->set_product_id($this->product_id);
        $plugin_info->set_text_domain($this->plugin_text_domain);
        $plugin_info->set_release_date(new \DateTime($this->plugin_release_timestamp));
        $plugin_info->set_plugin_url(\plugins_url(\dirname(\plugin_basename($this->plugin_file))));
        return $plugin_info;
    }
}
