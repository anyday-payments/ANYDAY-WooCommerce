# ANYDAY WooCommerce

Description: A fair and transparent online payment solution for you and your customers

Version: 1.0

## Plugin installation

1. Navigate to the `am-wordpress` folder and run `composer install`
2. Upload the `am-wordpress` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to `WooCommerce > Settings > ANYDAY > ANYDAY Payment Gateway Settings` to authenticate with your ANYDAY merchant account and configure the plugin.

## Plugin requirements

1. Your **PHP** >= 7.1.33
2. **Composer**
3. **WordPress** >= 5.51
4. **WooCommerce** >= 4.5.2

## Changelog

Version 1.0 - Initial plugin creation.

Version 1.1 - Fixed the order refund status. Fixed the ANYDAY payment gateway logo on the checkout styling.

Version 1.2

- **Bug** - ANYDAY Capture/Refund buttons hide after capturing/refunding the full amount.
- **New Feature** - Add variant price selector input in Product Pricetag settings which accepts a CSS selector to target the price of an element containing a variant price. This selector should be used as the value of the **total-price-selector** attribute on the Pricetag element ***only\*** on products with variants. If the product does not have variants, the Pricetag element should use the default price config or Price selector if specified.
- **New Feature** - Add option in plugin gateway settings to enable/disable logging. Default to disabled.
- **Update** - Add amount in Capture/refund notes.
- **Update** - Multiple capture/refunds update order notes.
- **Update** - Order details ANYDAY captured/refund amount include hundredths place.
- **Update** - Order details ANYDAY captured/refund amount uses **,** (comma) now

Version 1.3 - Fixed a bug related to registering the ANYDAY payment gateway. Now the plugin is compatiable with WPML plugin and included the new plugin settings strings.

Version 1.4 - Created two types of authentication: manually and with ANYDAY merchant account. Fixed a bug related to the variable products pricetag.

Version 1.5 - Disable the authentication type and fields after successful authentication.

Version 1.6

- Disable the authentication fields after successful authentication manually or with ANYDAY merchant account
- Create a new field for the variant product position selector
- Change the languages files with correct names
- Increase the order stock upon cancel API request or payment rejection by the user

Version 1.6.1 - Fixed warning messages in the order admin page. Included a new styling input field for the variant products.

Version 1.6.2 - Bug fixing.

Version 1.7 - Creates in the plugin settings fields for each order status. If configured from there the default order statuses will be overwritten. Fixed issues with the variant pricetag.

Version 1.8 - Creates custom order bulk action for capturing ANYDAY payments

Version 1.9

- ANYDAY payment can be captured upon order status change from the order details page
- Disable the ANYDAY payment gateway in case of currency different from DKK

Version 2.0 - Hide the ANYDAY payment gateway from the checkout if the order total is above 2500 DKK