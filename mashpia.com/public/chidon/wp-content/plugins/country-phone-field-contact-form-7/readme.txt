=== Country & Phone Field Contact Form 7 ===
Contributors: narinderbisht,webgrex
Donate link: https://www.paypal.me/webgrex
Tags: country dropdown, flags, phone extension, form, contact form
Requires at least: 4.8
Tested up to: 5.2.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add country drop down with flags and phone number with country phone extensions field in contact form 7.

== Description ==
Plugin is type of addon of wordpress contact form 7 plugin. This plugin add new two fields. Country list and country phone extensions list. This plugin help user create a country drop-down list with country flags. User have no needs manually adding countries name in standard drop-down field of contact form 7.

Requirments:
* Contact form 7 must be active plugin.

= Recommended Plugins =

The following plugins are recommended for Contact Form 7 users:

* [Contact form 7](https://wordpress.org/plugins/contact-form-7/) by takayukister - With Conact form 7, you can use this plugin. Without contact form 7 this plugin have no needs.

== Installation ==

1. Upload the entire `country-phone-field-contact-form-7` folder to the `/wp-content/plugins/` directory.
1. Kindly make sure 'contact form 7' plugin active before activate this plugin.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Setup default country, include, preferred and exclude countries settings from contact >> CPF settings.

You will find two new fields type in your contact form 7 field list.

== Frequently Asked Questions ==

= How to set default selected country? =

Add default country iso code from settings section. Contact >> CPF Settings

= How to set preferred countries list? =

Add preferred countries iso code from settings section. Contact >> CPF Settings. Example: us,in,ca,gb

= How to set only selected or exclude countries list? =

Add only selected countries iso code from settings section. Contact >> CPF Settings. Example: us,in,ca,gb

= How disable country dial code (+)? =

Enable option for dial code disable form plugin settings. Contact >> CPF Settings.

= How to apply number only validation on phone field? =

While adding the field, enable number only validation checkbox. If you already added the field. Just add "numberonly" option in field shortcode. e.g [phonetext phonetext-178 numberonly]

= How maxlength and minlength validation apply on phone field? =

Please use contact form 7 standard featured minlength and maxlength option. It will work for you.

== Screenshots ==

1. screenshot-1.png
1. screenshot-2.png
1. screenshot-3.png
1. screenshot-4.png

== Changelog ==

1.0.0

First version of plugin.

1.0.1
* Update FAQs
* Tested with wordpress 5.0

2.0.0
* Add phone and country dropdown settings.
* Made countries include, exclude and preferred list dynamic and admin managable.
* Resolve support issues.

2.0.1
* Add geo location functionality for default country select.
2.0.2
* fix settings fatal error.
2.0.3
* Add plugin profile icon
2.0.4
* fixed php warning message for file_get_contents() call geo_ip location.
2.0.5
* add option for disable country dial code from phone extension drop-down.
* add option for enable auto country select.
* tested required attribute. It is working fine.
* placeholder option tested and it is working fine.
2.0.6
* fixed and warning message error.
2.0.7
* added new faq.
2.0.8
* Added phone number field number values validation.
2.0.9
* Bug fix phone number field number values validation.
2.1.0
* fix countries code issues. Convert to lowercase.
2.1.1
* fixed phone number validation bug.
2.1.2
* fixed IP Address issue. It was PHP based. Now I convert to JavaScript based. So client end IP tracking is working fine.
2.1.3
* plugin is translate ready now.
* phone number validation has updated. Now user can manage number only validation from field settings.
2.1.4
* settings page php warning message has fixed.