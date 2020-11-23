<?php
namespace Adm;

class PriceTag
{
	public function __construct()
	{
		add_action( 'wp_enqueue_scripts', array( $this, 'adm_inject_anyday_split_script' ) );
		add_filter( 'woocommerce_before_add_to_cart_button', array( $this, 'adm_append_anyday_price_tag' ) );
		add_filter( 'woocommerce_proceed_to_checkout', array( $this, 'adm_append_anyday_price_tag' ) );
		add_filter( 'woocommerce_review_order_before_payment', array( $this, 'adm_append_anyday_price_tag' ) );
		add_action( 'wp_head', array( $this, 'adm_price_tag_styles' ), 100 );
		add_filter( 'script_loader_tag', array( $this, 'adm_add_data_attribute' ), 10, 2 );
	}

	/**
	 * Add data attribute to a script
	 *@method adm_add_data_attribute
	 */
	public function adm_add_data_attribute( $tag, $handle )
	{
		if ( 'anyday-split-script' !== $handle ) {
			return $tag;
		}

	   return str_replace( ' src', ' type="module" src', $tag );
	}

	/**
	 * Load AnyDay JavaScript used for the price tag
	 * @method adm_inject_anyday_split_script
	 */
	public function adm_inject_anyday_split_script()
	{
		if( !$this->checkPluginConditions() ) return;

		wp_enqueue_script( 'anyday-split-script', "https://my.anyday.io/webshopPriceTag/anyday-price-tag-".$this->getPluginLocale()."-es2015.js");
	}

	/**
	 * Inject the html price tag
	 * @method adm_append_anyday_price_tag
	 * @return  html
	 */
	public function adm_append_anyday_price_tag()
	{
		if( !$this->checkPluginConditions() ) return;

		$product = wc_get_product( get_the_ID() );
		$currency = get_option('woocommerce_currency');
		$lang_locale = get_option('adm_price_format_locale');
		$price_selector = '';
		$visibility = "display:block";

		if ( get_option('adm_authentication_type') == 'auth_manual' ) {

			$token = get_option('adm_manual_pricetag_token');

		} elseif( get_option('adm_authentication_type') == 'auth_account' ) {

			$token = get_option('adm_pricetag_token');

		}

		if( $this->checkPricetagPositonSelector() ) {

			$visibility = "display:none";

		}

		if( $this->getPriceSelector( $product ) ) {

			$price = 'total-price-selector="' . $this->getPriceSelector( $product ) . '"';

			if ( is_product() && $product->is_type( 'variable' ) ) {

				$variations = $product->get_available_variations();

				$first_variation_prices = [];

				foreach ( $variations as $key => $variation ) {

					$first_variation_prices[] = $variation['display_price'];

				}

				if ( count(array_unique($first_variation_prices)) === 1 ) {

					$price = 'total-price-selector=".woocommerce-Price-amount.amount bdi"';

				}

			}


		} else {

			if( is_product() ) {

				if ( $product->get_sale_price() ) {

					$price = 'total-price="'. $product->get_sale_price() .'"';

				}else {

					$price = 'total-price="'. $product->get_regular_price() .'"';
				}

			} else {

				$price = 'total-price="'. (float)WC()->cart->total .'"';
			}
		}

		try {

			if ( is_product() && $product->is_type( 'variable' ) ) {

				echo sprintf( '<div class="anyday-price-tag-style-wrapper anyday-price-tag-style-wrapper--no-price-selected"><anyday-price-tag style="%s" total-price-selector=".woocommerce-Price-amount.amount bdi" price-tag-token="%s" currency="%s" price-format-locale="%s" environment="production"></anyday-price-tag></div>', $visibility, $token, $currency, $lang_locale );

			}

			echo sprintf( '<div class="anyday-price-tag-style-wrapper anyday-price-tag-style-wrapper--price-selected"><anyday-price-tag style="%s" %s price-tag-token="%s" currency="%s" price-format-locale="%s" environment="production"></anyday-price-tag></div>', $visibility, $price, $token, $currency, $lang_locale );

		} catch (Exception $e) {
			//ignore error
		}
	}

	/**
	 * Load inline css style for the price tag
	 * @method adm_price_tag_styles
	 * @return html
	 */
	public function adm_price_tag_styles()
	{
		if( !is_admin() ) {
			if( is_product() ) {
				echo sprintf( "<style>.anyday-price-tag-style-wrapper{%s}</style>", get_option('adm_pricetag_product_styles') );
			}elseif ( is_cart() ) {
				echo sprintf( "<style>.anyday-price-tag-style-wrapper{%s}</style>", get_option('adm_pricetag_cart_styles') );
			}elseif ( is_checkout() ) {
				echo sprintf( "<style>.anyday-price-tag-style-wrapper{%s}</style>", get_option('adm_pricetag_checkout_styles') );
			}
		}
	}

	/**
	 * Check where the price tag must be loaded
	 * @method checkPluginConditions
	 * @return bool
	 */
	private function checkPluginConditions()
	{
		if( (is_product() && get_option('adm_select_product') == 'enabled') || (is_cart() && get_option('adm_select_cart') == 'enabled') || (is_checkout() && get_option('adm_select_checkout') == 'enabled'))

			return true;
	}

	/**
	 * Check the pricetag selector position
	 *@method checkPricetagSelectorPositon
	 *@return bool
	 */
	private function checkPricetagPositonSelector()
	{
		if( is_product() && !empty( get_option('adm_price_tag_product_selector') ) || is_cart() && !empty( get_option('adm_price_tag_cart_selector') ) || is_checkout() && !empty( get_option('adm_price_tag_checkout_selector') ) ) {
			return true;
		}
	}

	/**
	 * Get the pricetag price selectors
	 *@method getPriceSelector
	 */
	private function getPriceSelector( $product )
	{
		if ( is_product() && $product->is_type( 'variable' ) === false && !empty( get_option('adm_price_tag_price_product_selector') ) ) {
			return get_option('adm_price_tag_price_product_selector');
		}elseif ( is_product() && $product->is_type( 'variable' ) === true && !empty( get_option('adm_price_tag_price_variable_product_selector') ) ) {
			return get_option('adm_price_tag_price_variable_product_selector');
		}elseif ( is_cart() && !empty( get_option('adm_price_tag_price_cart_selector') ) ) {
			return get_option('adm_price_tag_price_cart_selector');
		}elseif ( is_checkout() && !empty( get_option('adm_price_tag_price_checkout_selector') ) ) {
			return get_option('adm_price_tag_price_checkout_selector');
		} else {
			return false;
		}
	}

	/**
	 * Load the plugin languages based on user choice, if nothing
	 * matches what is  provided in the $supported_languages array
	 * it loads the default choice from the plugin settings
	 *@method getPluginLocale
	 */
	private function getPluginLocale()
	{
		$supported_languages = array();
		$lang_locale = substr(get_locale(), 0, 2);

		if( in_array($lang_locale, $supported_languages) ) {
			return $lang_locale;
		} else {
			return get_option('adm_language_locale');
		}
	}
}