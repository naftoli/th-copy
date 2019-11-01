<?php

namespace UpsFreeVendor\WPDesk\Codeception\Command;

use UpsFreeVendor\Codeception\Lib\Generator\Test;
/**
 * Class code for codeception example test for WP Desk plugin activation.
 *
 * @package WPDesk\Codeception\Command
 */
class WooCommerceTestGenerator extends \UpsFreeVendor\Codeception\Lib\Generator\Test
{
    protected $template = <<<EOF
<?php {{namespace}}

use WPDesk\\Codeception\\Tests\\Acceptance\\WooCommerceCest as WooCommerceCestAlias;

/**
 * Common WooCommerce tests.
 */
class {{name}} extends WooCommerceCestAlias {

}
EOF;
}
