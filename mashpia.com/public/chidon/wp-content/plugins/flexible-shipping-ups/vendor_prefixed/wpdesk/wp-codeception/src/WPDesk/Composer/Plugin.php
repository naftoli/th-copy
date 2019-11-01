<?php

namespace UpsFreeVendor\WPDesk\Composer\Codeception;

use UpsFreeVendor\Composer\Composer;
use UpsFreeVendor\Composer\IO\IOInterface;
use UpsFreeVendor\Composer\Plugin\Capable;
use UpsFreeVendor\Composer\Plugin\PluginInterface;
/**
 * Composer plugin.
 *
 * @package WPDesk\Composer\Codeception
 */
class Plugin implements \UpsFreeVendor\Composer\Plugin\PluginInterface, \UpsFreeVendor\Composer\Plugin\Capable
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    public function activate(\UpsFreeVendor\Composer\Composer $composer, \UpsFreeVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    public function getCapabilities()
    {
        return [\UpsFreeVendor\Composer\Plugin\Capability\CommandProvider::class => \UpsFreeVendor\WPDesk\Composer\Codeception\CommandProvider::class];
    }
}
