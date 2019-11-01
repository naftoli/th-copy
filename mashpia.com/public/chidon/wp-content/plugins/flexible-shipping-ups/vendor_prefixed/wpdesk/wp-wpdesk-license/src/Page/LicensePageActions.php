<?php

namespace UpsFreeVendor\WPDesk\License\Page;

use UpsFreeVendor\WPDesk\License\Page\License\Action\LicenseActivation;
use UpsFreeVendor\WPDesk\License\Page\License\Action\LicenseDeactivation;
use UpsFreeVendor\WPDesk\License\Page\License\Action\Nothing;
/**
 * Action factory.
 *
 * @package WPDesk\License\Page\License
 */
class LicensePageActions
{
    /**
     * Creates action object according to given param
     *
     * @param string $action
     *
     * @return Action
     */
    public function create_action($action)
    {
        if ($action === 'activate') {
            return new \UpsFreeVendor\WPDesk\License\Page\License\Action\LicenseActivation();
        }
        if ($action === 'deactivate') {
            return new \UpsFreeVendor\WPDesk\License\Page\License\Action\LicenseDeactivation();
        }
        return new \UpsFreeVendor\WPDesk\License\Page\License\Action\Nothing();
    }
}
