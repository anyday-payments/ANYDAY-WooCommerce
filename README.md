# Anyday WooCommerce

Description: A fair and transparent online payment solution for you and your customers

Version: 1.5

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
