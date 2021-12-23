# Anyday WooCommerce

Description: Anyday is a new way to pay. An interest-free financing solution with no fees or interest for your customers.

Version: 1.7.3

## Plugin installation

1. Navigate to the `am-wordpress` folder and run `composer install`
2. Upload the `am-wordpress` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to `WooCommerce > Settings > Anyday > Anyday Payment Gateway Settings` to authenticate with your Anyday merchant account and configure the plugin.

## Plugin requirements

1. Your **PHP** >= 7.1.33
2. **Composer**
3. **WordPress** >= 5.51
4. **WooCommerce** >= 4.5.2

## Changelog

Version 1.0 - Initial plugin upload.

Version 1.1 - Updating plugin assets and description.

Version 1.2 - Adding caching functionality to store external JS to the Wordpress server.

Version 1.3 - Fixing minor versioning.

Version 1.4 - Fixing JS caching bug which happens after deactivation of plugin.

Version 1.5 - Updating plugin to accept purchases up to 30k of order amount, Updated translations, Refresh the cached anyday js script on the upgrade of the plugin.

Version 1.6 - Updating plugin description appearing in plugin directory.

Version 1.7 - Disabling SSL verification while fetching public script from the server.

Version 1.7.1 - Updating not to fetch scripts on update immediatly on plugin upgrade.

Version 1.7.2 - Changed JS file cached with CURL.

Version 1.7.3 - Adding bulk order status update in backend, Anyday payment is enabled for orders with payments greater than and equals to 300 DKK, Stock levels should update only when order is completed or processing. Stock added back on order cancelled and refunded, Fixing number format throughout the plugin, Adding validation to check number format while input for capture/refund from the backend, Fixing cached JS URL for some instances using permalinks, Fixed backend configuration page which was broken, Minimum price limit for pricetag isn't working for on-sale product.
