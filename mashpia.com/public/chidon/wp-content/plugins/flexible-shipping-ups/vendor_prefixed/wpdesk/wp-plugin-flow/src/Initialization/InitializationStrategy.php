<?php

namespace UpsFreeVendor\WPDesk\Plugin\Flow\Initialization;

use UpsFreeVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
/**
 * Interface for initialization strategy for plugin. How to initialize it?
 */
interface InitializationStrategy
{
    /**
     * @return AbstractPlugin
     */
    public function run();
}
