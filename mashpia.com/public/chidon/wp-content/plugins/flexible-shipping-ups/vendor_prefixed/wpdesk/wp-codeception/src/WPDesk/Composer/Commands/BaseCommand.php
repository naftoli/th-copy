<?php

namespace UpsFreeVendor\WPDesk\Composer\Codeception\Commands;

use UpsFreeVendor\Composer\Command\BaseCommand as CodeceptionBaseCommand;
use UpsFreeVendor\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base for commands - declares common methods.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
abstract class BaseCommand extends \UpsFreeVendor\Composer\Command\BaseCommand
{
    /**
     * @param string $command
     * @param OutputInterface $output
     */
    protected function execAndOutput($command, \UpsFreeVendor\Symfony\Component\Console\Output\OutputInterface $output)
    {
        \passthru($command);
    }
}
