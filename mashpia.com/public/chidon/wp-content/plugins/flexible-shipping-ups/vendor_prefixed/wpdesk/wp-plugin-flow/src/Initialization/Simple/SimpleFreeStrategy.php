<?php

namespace UpsFreeVendor\WPDesk\Plugin\Flow\Initialization\Simple;

use UpsFreeVendor\WPDesk\Plugin\Flow\Initialization\InitializationStrategy;
use UpsFreeVendor\WPDesk\PluginBuilder\BuildDirector\LegacyBuildDirector;
use UpsFreeVendor\WPDesk\PluginBuilder\Builder\InfoBuilder;
use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
/**
 * Initialize free plugin
 * - just build it already
 */
class SimpleFreeStrategy implements \UpsFreeVendor\WPDesk\Plugin\Flow\Initialization\InitializationStrategy
{
    use HelperInstanceAsFilter;
    use TrackerInstanceAsFilter;
    /** @var \WPDesk_Plugin_Info */
    protected $plugin_info;
    public function __construct(\UpsFreeVendor\WPDesk_Plugin_Info $plugin_info)
    {
        $this->plugin_info = $plugin_info;
    }
    /**
     * Initializes and builds plugin
     *
     * @return AbstractPlugin
     */
    public function run()
    {
        $this->prepare_helper_action();
        $this->prepare_tracker_action();
        $builder = new \UpsFreeVendor\WPDesk\PluginBuilder\Builder\InfoBuilder($this->plugin_info);
        $build_director = new \UpsFreeVendor\WPDesk\PluginBuilder\BuildDirector\LegacyBuildDirector($builder);
        $build_director->build_plugin();
        return $build_director->get_plugin();
    }
}
