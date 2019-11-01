<?php

namespace UpsFreeVendor;

if (!\class_exists('UpsFreeVendor\\Basic_Requirement_Checker')) {
    require_once 'Basic_Requirement_Checker.php';
}
if (!\class_exists('UpsFreeVendor\\WPDesk_Basic_Requirement_Checker_With_Update_Disable')) {
    require_once 'Basic_Requirement_Checker_With_Update_Disable.php';
}
/**
 * Falicitates createion of requirement checker
 */
class WPDesk_Basic_Requirement_Checker_Factory
{
    const LIBRARY_TEXT_DOMAIN = 'wp-basic-requirements';
    /**
     * Creates a simplest possible version of requirement checker.
     *
     * @param string $plugin_file
     * @param string $plugin_name
     * @param string|null $text_domain Text domain to use. If null try to use library text domain.
     *
     * @return WPDesk_Requirement_Checker
     */
    public function create_requirement_checker($plugin_file, $plugin_name, $text_domain = null)
    {
        return new \UpsFreeVendor\WPDesk_Basic_Requirement_Checker($plugin_file, $plugin_name, $this->initialize_translations($text_domain), null, null);
    }
    /**
     * Creates a requirement checker according to given requirements array info.
     *
     * @param string $plugin_file
     * @param string $plugin_name
     * @param string $text_domain Text domain to use. If null try to use library text domain.
     * @param array $requirements Requirements array as given by plugin.
     *
     * @return WPDesk_Requirement_Checker
     */
    public function create_from_requirement_array($plugin_file, $plugin_name, $requirements, $text_domain = null)
    {
        $requirements_checker = new \UpsFreeVendor\WPDesk_Basic_Requirement_Checker_With_Update_Disable($plugin_file, $plugin_name, $this->initialize_translations($text_domain), $requirements['php'], $requirements['wp']);
        if (isset($requirements['plugins'])) {
            foreach ($requirements['plugins'] as $requirement) {
                $requirements_checker->add_plugin_require($requirement['name'], $requirement['nice_name']);
            }
        }
        if (isset($requirements['repo_plugins'])) {
            foreach ($requirements['repo_plugins'] as $requirement) {
                $requirements_checker->add_plugin_repository_require($requirement['name'], $requirement['version'], $requirement['nice_name']);
            }
        }
        if (isset($requirements['modules'])) {
            foreach ($requirements['modules'] as $requirement) {
                $requirements_checker->add_php_module_require($requirement['name'], $requirement['nice_name']);
            }
        }
        return $requirements_checker;
    }
    /**
     * Tries to initialize translations for requirement checker. If not given then default library translation is used.
     *
     * @param string|null $text_domain
     *
     * @return string
     */
    private function initialize_translations($text_domain = null)
    {
        if ($text_domain === null) {
            $text_domain = self::LIBRARY_TEXT_DOMAIN;
            if (\function_exists('determine_locale')) {
                $locale = \determine_locale();
            } else {
                // before WP 5.0 compatibility
                $locale = \get_locale();
            }
            $locale = \apply_filters('plugin_locale', $locale, self::LIBRARY_TEXT_DOMAIN);
            $lang_mo_file = __DIR__ . '/../lang/' . self::LIBRARY_TEXT_DOMAIN . '-' . $locale . '.mo';
            if (\file_exists($lang_mo_file)) {
                \load_textdomain(self::LIBRARY_TEXT_DOMAIN, $lang_mo_file);
            }
        }
        return $text_domain;
    }
}
