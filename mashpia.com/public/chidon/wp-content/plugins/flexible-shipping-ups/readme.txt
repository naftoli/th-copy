=== WooCommerce UPS Shipping – Live Rates and Access Points ===
Contributors: wpdesk,dyszczo,grola,potreb
Donate link: https://wordpress.org/plugins/flexible-shipping-ups/
Tags: woocommerce ups, ups, ups woocommerce, ups shipping, ups api, shipping rates, shipping method, flexible shipping, woocommerce shipping, UPS Access Points, access point
Requires at least: 4.5
Tested up to: 5.2.3
Stable tag: 1.7.0
Requires PHP: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

UPS WooCommerce plugin lets you offer a full range of UPS shipping options. UPS Access Points support and Live Shipping Rates, integrate in 5 minutes.

== Description ==

= Most powerful UPS WooCommerce integration =

UPS WooCommerce plugin lets you offer a full range of UPS shipping options. You’ll integrate the plugin in just 5 minutes.

Your clients will see every UPS shipping option in the checkout with its real price. The shipping cost is calculated automatically online due to UPS API connection. You can **offer delivery to UPS Access Point**, too. Also, points selected by customers save to ooCommerce order.

**Give your customers the opportunity to pick up packages when and where it's best for them.** Enable Access Points support to show store customers the option to choose the UPS Access Point service. The plugin suggests the nearest points for the customer's address and saves the point to the customer’s order.

> **Upgrade to UPS PRO**<br />
> Get priority e-mail support and access all PRO features, upgrade to [Flexible Shipping UPS PRO now &rarr;](https://flexibleshipping.com/products/flexible-shipping-ups-pro/)


= Features =

* Automatic shipping costs calculator with UPS live rates
* Shipping cost for UPS services based on cart weight and shipping address
* Ability to enable UPS negotiated rates
* Nearest UPS Access Point
* Limiting services only for those available for the customer's address
* Manual UPS services limiting
* Ability to add insurance
* Fallback cost in case of connection problems with UPS API
* All currencies supported by UPS
* Debug mode
* Compatible with WooCommerce Shipping Zones


= PRO Features =

* Automatic box packing for multiple products based on weight and volume
* All UPS Access Points and search
* Fixed value and percentage handling fees/discounts for UPS rates
* Flat rate for UPS Access Points
* Estimated delivery date displayed in the checkout

[Upgrade to PRO Now &rarr;](https://flexibleshipping.com/products/flexible-shipping-ups-pro/)

= Actively developed and supported =

This UPS WooCommerce plugin is developed by WP Desk. Our plugins are used by **over 55.000 WooCommerce stores worldwide**. WP Desk is proven to offer high quality, stable plugins, and astonishing support. Choose the plugin of this trusty developer to avoid problems in the future.


= WooCommerce Compatibility =

**WooCommerce 3.6 ready!** Flexible Shipping UPS plugin is compatible with WooCommerce 3.2 - 3.6

= Docs =

[View Flexible Shipping UPS WooCommerce Docs](https://docs.flexibleshipping.com/category/122-ups)

= Support Policy =

We provide a limited support for the free version in the [plugin Support Forum](https://wordpress.org/support/plugin/flexible-shipping-ups/). Please upgrade to PRO version to get priority e-mail support as well as all pro features. [Upgrade Now &rarr;](https://flexibleshipping.com/products/flexible-shipping-ups-pro/)


= Why choose UPS as your WooCommerce integration =

UPS is a trusted brand. It is one of the leaders in its category. UPS delivers 18 million parcels and letters worldwide everyday. You’ll integrate UPS services with your WooCommerce store via the UPS API. **You’ll provide your clients with a choice of the brand they trust**.

You’ll integrate UPS services with your store **within a few moments** and will be able to offer dynamic UPS rates to your customers. Your customers will be able to choose Access Points service, too. **Give your customers access to more than 27,000 such locations across Europe and North America to pick up their online purchases**.

This plugin integrates well with WooCommerce. It lets you add UPS shipping methods to your store’s shipping zones in WooCommerce shipping settings.

== Installation	 ==

You can install this plugin like any other WordPress plugin.

1. Download and unzip the latest release zip file.
2. Upload the entire plugin directory to your /wp-content/plugins/ directory.
3. Activate the plugin through the Plugins menu in WordPress Administration.

You can also use WordPress uploader to upload plugin zip file in menu Plugins -> Add New -> Upload Plugin. Then go directly to point 3.

== Frequently Asked Questions ==

= What currencies does the plugin support? =

UPS WooCommerce plugin supports every currency which UPS supports. You can use currency switchers with no worries, they work well. If UPS doesn’t support a given currency, the UPS WooCommerce plugin won’t show it in the checkout.

= Do I need a UPS account? =
Yes. UPS WooCommerce uses your account to make a connection to the UPS API. This plugin shows you API Status in the settings section. You can sign up with UPS quickly and easily [on their site](https://www.ups.com/doapp/SignUp).

= How can I configure UPS services? =

There is an option to enable services custom settings. You can set which services are available for your customers. However, UPS WooCommerce shows only services available for a given customer based on their shipping address.

= What is a fallback cost? =

Sometimes API doesn’t respond or return an error. The UPS shipping method is not shown in the checkout by default in such situations. However, you can set the fallback cost. If it is enabled, then in case of any API errors, UPS is shown in the checkout with the fallback cost you set.

== Screenshots ==

1. UPS plugin settings.
2. Adding a new shipping method in WooCommerce.
3. UPS shipping method on the list.
4. UPS shipping method settings.
5. Services custom settings.
6. UPS shipping method in the checkout.
7. UPS Access Points settings.
8. UPS Access Points checkout.

== Changelog ==

= 1.7.0 - 2019-10-01 =
* Access point description
* New rating notice
* Added residential address indicator to rate request

= 1.6.0 - 2019-06-27 =
* Library code is prefixed
* Various phpstan related refactors

= 1.5.2 - 2019-08-12 =
* Added support for WooCommerce 3.7

= 1.5.1 - 2019-06-27 =
* Fixed fatal error in order meta.
* Added multi currency fallback

= 1.5.0 - 2019-06-12 =
* Fixed compatibility with the newest pro version

= 1.4.4 - 2019-05-21 =
* Fixed fallback for WooCommerce 3.6

= 1.4.3 - 2019-05-09 =
* Added compatibility with PRO plugin

= 1.4.2 - 2019-04-08 =
* Added support for WooCommerce 3.6

= 1.4.1 - 2019-03-11 =
* Fixed selected access point saved in order shipping meta data
* Fixed quick link to docs on plugins page

= 1.4.0 - 2019-02-27 =
* Added pickup type parameter

= 1.3.3 - 2019-01-17 =
* Updated library

= 1.3.2 - 2018-12-19 =
* Fixed Drag and Drop icon in custom services

= 1.3.1 - 2018-11-13 =
* Fixed missing tracker data

= 1.3.0 - 2018-11-13 =
* Fixed libraries compatibility problem

= 1.2.1 - 2018-08-20 =
* Added informations about new services

= 1.2 - 2018-08-20 =
* Added ability to show only Access Points Rates
* Fixed custom origin country when country is with state
* Fixed default units (metric/imperial)

= 1.1.3 - 2018-08-06 =
* Added enable custom services by default
* Tweaked display access point fallback rate only when no standard rates added
* Fixed fatal error on countries select box

= 1.1.2 - 2018-06-26 =
* Fixed error with conflict in tracker

= 1.1.1 - 2018-06-25 =
* Tweaked plugin description
* Tweaked tracker to Access Points
* Tweaked tracker data anonymization
* Fixed issue with select2 for Access Points
* Fixed tracker notice

= 1.1 - 2018-05-23 =
* Added functionality for UPS Access Points
* Added support for WooCommerce 3.4

= 1.0.3 - 2018-05-09 =
* Fixed missing state for negotiated rates

= 1.0.2 - 2018-03-06 =
* Fixed problems with deactivation plugin on multisite

= 1.0.1 - 2018-02-27 =
* Fixed warnings from WP Desk Tracker

= 1.0 - 2018-02-08 =
* First release!
