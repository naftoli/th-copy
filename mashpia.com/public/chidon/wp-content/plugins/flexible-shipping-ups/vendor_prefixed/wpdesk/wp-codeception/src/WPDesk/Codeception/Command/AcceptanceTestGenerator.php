<?php

namespace UpsFreeVendor\WPDesk\Codeception\Command;

use UpsFreeVendor\Codeception\Lib\Generator\Test;
/**
 * Class code for codeception example test for WP Desk plugin activation.
 *
 * @package WPDesk\Codeception\Command
 */
class AcceptanceTestGenerator extends \UpsFreeVendor\Codeception\Lib\Generator\Test
{
    protected $template = <<<EOF
<?php {{namespace}}

use WPDesk\\Codeception\\Tests\\Acceptance\\Cest;

class {{name}} extends Cest {

\t/**
\t * Deactivate plugins before tests.
\t *
\t * @param AcceptanceTester \$i .
\t *
\t * @throws \\Codeception\\Exception\\ModuleException .
\t */
\tpublic function _before( AcceptanceTester \$i ) {
\t\t\$i->loginAsAdmin();
\t\t\$i->amOnPluginsPage();
\t\t\$i->deactivatePlugin( \$this->getPluginSlug() );
\t\t\$i->amOnPluginsPage();
\t\t\$i->deactivatePlugin( 'woocommerce' );
\t}

\t/**
\t * Plugin activation.
\t *
\t * @param AcceptanceTester \$i .
\t *
\t * @throws \\Codeception\\Exception\\ModuleException .
\t */
\tpublic function pluginActivation( AcceptanceTester \$i ) {

\t\t\$i->loginAsAdmin();

\t\t\$i->amOnPluginsPage();
\t\t\$i->seePluginDeactivated( \$this->getPluginSlug() );

\t\t// This is an example and you should change it to current plugin.
\t\t\$i->activateWPDeskPlugin(
\t\t\t\$this->getPluginSlug(),
\t\t\tarray( 'woocommerce' ),
\t\t\tarray( 'The “WooCommerce Fakturownia” plugin cannot run without WooCommerce active. Please install and activate WooCommerce plugin.' )
\t\t);

\t}
}
EOF;
}
