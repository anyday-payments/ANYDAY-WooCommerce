<?php
namespace Adm;

class Settings extends \WC_Settings_Page
{
	public function __construct()
	{
		$this->id    = 'anydaypricetag';
	    $this->label = __( 'ANYDAY', 'adm' );

	    add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
	    add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
	    add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	    add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
	}

	/**
	 * Define the plugin sections
	 *@method get_sections
	 */
	public function get_sections()
	{
	    $sections = array(
	    	'' => __( 'ANYDAY Payment Gateway Settings', 'adm' ),
	    );

	    $sections['adm_pricetag_settings'] = __( 'ANYDAY Pricetag Settings', 'adm' );

	    return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Register the plugin settings per section
	 *@method get_settings
	 */
	public function get_settings( $current_section = '' )
	{
		switch ( $current_section ) {
			case '':
				return $this->adm_get_payment_gateway_settings( $current_section );
				break;
			case 'adm_pricetag_settings':
				return $this->adm_get_pricetag_settings( $current_section );
				break;
		}
	}

	/**
	 * Define the general plugin settings
	 *@method adm_get_pricetag_settings
	 */
	private function adm_get_pricetag_settings( $current_section )
	{
		$settings = apply_filters( 'adm_general_section', array(
			array(
				'name'	=> __( 'General Settings', 'adm' ),
				'type'	=> 'title',
				'id'	=> 'adm_general_options',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_language_locale',
				'name'	=> __( 'Language Localization', 'adm' ),
				'options'	=> array(
					'da'	=> __( 'da', 'adm' ),
					'en'	=> __( 'en', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Choose the ANYDAY Pricetag language', 'adm' ),
				'default'  => 'da',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_price_format_locale',
				'name'	=> __( 'Price Format Locale', 'adm' ),
				'options'	=> array(
					'da'	=> __( 'da', 'adm' ),
					'en'	=> __( 'en', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Choose the ANYDAY Pricetag format locale', 'adm' ),
				'default'  => 'da',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'pricetag_general_settings_end'
			),
			array(
				'name'	=> __( 'Product Page', 'adm' ),
				'type'	=> 'title',
				'id'	=> 'adm_product_page_title',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_select_product',
				'name'	=> __( 'Visibility', 'adm' ),
				'options'	=> array(
					'enabled'	=> __( 'Enabled', 'adm' ),
					'disabled'	=> __( 'Disabled', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Disable/enable the ANYDAY Pricetag on product page', 'adm' ),
				'default'  => 'enabled',
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_product_selector',
				'name'	=> __( 'Position Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector before which the ANYDAY Pricetag will be loaded', 'adm' ),
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_price_product_selector',
				'name'	=> __( 'Product Price Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector from where the price will be taken', 'adm' ),
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_price_variable_product_selector',
				'name'	=> __( 'Variant Product Price Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector from where the price will be taken', 'adm' ),
			),
			array(
				'type'	=> 'textarea',
				'id'	=> 'adm_pricetag_product_styles',
				'name'	=> __( 'Styles', 'adm' ),
				'desc_tip' => __( 'Enter any valid CSS to update the ANYDAY Pricetag wrapper element. Pricetag font styles will inherit from these styles if specified.', 'adm' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'pricetag_product_page_end'
			),
			array(
				'name'	=> __( 'Cart Page', 'adm' ),
				'type'	=> 'title',
				'id'	=> 'adm_cart_page_title',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_select_cart',
				'name'	=> __( 'Visibility', 'adm' ),
				'options'	=> array(
					'enabled'	=> __( 'Enabled', 'adm' ),
					'disabled'	=> __( 'Disabled', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Disable/enable the ANYDAY Pricetag on cart page', 'adm' ),
				'default'  => 'enabled',
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_cart_selector',
				'name'	=> __( 'Position Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector before which the ANYDAY Pricetag will be loaded', 'adm' ),
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_price_cart_selector',
				'name'	=> __( 'Price Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector from where the price will be taken', 'adm' ),
			),
			array(
				'type'	=> 'textarea',
				'id'	=> 'adm_pricetag_cart_styles',
				'name'	=> __( 'Styles', 'adm' ),
				'desc_tip' => __( 'Enter any valid CSS to update the ANYDAY Pricetag wrapper element. Pricetag font styles will inherit from these styles if specified.', 'adm' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'pricetag_cart_page_end'
			),
			array(
				'name'	=> __( 'Checkout Page', 'adm' ),
				'type'	=> 'title',
				'id'	=> 'adm_checkout_page_title',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_select_checkout',
				'name'	=> __( 'Visibility', 'adm' ),
				'options'	=> array(
					'enabled'	=> __( 'Enabled', 'adm' ),
					'disabled'	=> __( 'Disabled', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Disable/enable the ANYDAY Pricetag on checkout page', 'adm' ),
				'default'  => 'enabled',
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_checkout_selector',
				'name'	=> __( 'Position Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector before which the ANYDAY Pricetag will be loaded', 'adm' ),
			),
			array(
				'type'	=> 'text',
				'id'	=> 'adm_price_tag_price_checkout_selector',
				'name'	=> __( 'Price Selector', 'adm' ),
				'desc_tip' => __( 'Choose a CSS selector from where the price will be taken', 'adm' ),
			),
			array(
				'type'	=> 'textarea',
				'id'	=> 'adm_pricetag_checkout_styles',
				'name'	=> __( 'Styles', 'adm' ),
				'desc_tip' => __( 'Enter any valid CSS to update the ANYDAY Pricetag wrapper element. Pricetag font styles will inherit from these styles if specified.', 'adm' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'pricetag_checkout_page_end'
			)
		) );

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Define the plugin paymnet gateway settings
	 *@method adm_get_payment_gateway_settings
	 */
	private function adm_get_payment_gateway_settings( $current_section )
	{
		$gateway_settings = array(
			array(
				'name'	=> __( 'Merchant Authentication', 'adm' ),
				'type'	=> 'title',
				'id'	=> 'adm_general_options',
			),
			"authentication_type" => array(
				'type'	=> 'select',
				'id'	=> 'adm_authentication_type',
				'name'	=> __( 'Authentication Type', 'adm' ),
				'options'	=> array(
					'auth_manual'	=> __( 'Manual', 'adm' ),
					'auth_account'	=> __( 'ANYDAY Merchant Acount', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Choose a method how to authenticate in order to save the API keys and Pricetag token', 'adm' ),
				'default'  => 'auth_account'
			),
			"merchant_username" => array(),
			"merchant_password" => array(),
			"prod_api_key" => array(),
			"test_api_key" => array(),
			"pricetag_token" => array(),
			array(
				'type' => 'sectionend',
				'id'   => 'pricetag_checkout_page_end'
			),
			array(
				'name'	=> __( 'General Settings', 'adm' ),
				'type'	=> 'title',
				'id'	=> 'adm_general_options',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_environment',
				'name'	=> __( 'Mode', 'adm' ),
				'options'	=> array(
					'live'	=> __( 'Live', 'adm' ),
					'test'	=> __( 'Test', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Choose ANYDAY Environment', 'adm' ),
				'default'  => 'live',
			),
			array(
				'type'	=> 'select',
				'id'	=> 'adm_module_error_log',
				'name'	=> __( 'Error Log', 'adm' ),
				'options'	=> array(
					'enabled'	=> __( 'Enabled', 'adm' ),
					'disabled'	=> __( 'Disabled', 'adm' )
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Log each ANYDAY API error in a debug.log file which is located in the plugin root directory', 'adm' ),
				'default'  => 'disabled',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'pricetag_checkout_page_end'
			)
		);

		if ( get_option('adm_authentication_type') == 'auth_manual' ) {

			$gateway_settings['prod_api_key']['type']	= 'textarea';
			$gateway_settings['prod_api_key']['id']		= 'adm_manual_prod_api_key';
			$gateway_settings['prod_api_key']['name']	= __( 'ANYDAY Production API key', 'adm' );
			$gateway_settings['test_api_key']['type']	= 'textarea';
			$gateway_settings['test_api_key']['id']		= 'adm_manual_test_api_key';
			$gateway_settings['test_api_key']['name']	= __( 'ANYDAY Test API key', 'adm' );
			$gateway_settings['pricetag_token']['type']	= 'text';
			$gateway_settings['pricetag_token']['id']	= 'adm_manual_pricetag_token';
			$gateway_settings['pricetag_token']['name']	= __( 'ANYDAY Pricetag token', 'adm' );

		} elseif ( get_option('adm_authentication_type') == 'auth_account' ) {

			$gateway_settings['merchant_username']['type']		= 'text';
			$gateway_settings['merchant_username']['id']		= 'adm_merchant_username';
			$gateway_settings['merchant_username']['name']		= __( 'Merchant Username', 'adm' );
			$gateway_settings['merchant_username']['desc_tip'] 	= __( 'Enter your ANYDAY merchant account username', 'adm' );
			$gateway_settings['merchant_password']['type']		= 'password';
			$gateway_settings['merchant_password']['id']		= 'adm_merchant_password';
			$gateway_settings['merchant_password']['name']		= __( 'Merchant Password', 'adm' );
			$gateway_settings['merchant_password']['desc_tip'] 	= __( 'Enter your ANYDAY merchant account password', 'adm' );

		}

		if ( get_option('adm_merchant_authenticated') == 'true' ) {

			$gateway_settings['authentication_type']['custom_attributes'] = array('disabled' => 'disabled');
			$gateway_settings['merchant_username']['custom_attributes'] = array('readonly' => 'readonly');
			$gateway_settings['merchant_password']['custom_attributes'] = array('readonly' => 'readonly');

		}

		if ( !empty(get_option('adm_manual_prod_api_key')) && !empty(get_option('adm_manual_test_api_key')) && !empty(get_option('adm_manual_pricetag_token')) ) {

			$gateway_settings['authentication_type']['custom_attributes'] = array('disabled' => 'disabled');
			$gateway_settings['prod_api_key']['custom_attributes'] = array('readonly' => 'readonly');
			$gateway_settings['test_api_key']['custom_attributes'] = array('readonly' => 'readonly');
			$gateway_settings['pricetag_token']['custom_attributes'] = array('readonly' => 'readonly');

		}

		$settings = apply_filters( 'adm_general_section', $gateway_settings );

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Output the plugin settings
	 *@method output
	 */
	public function output()
	{
	    global $current_section;

	    $settings = $this->get_settings( $current_section );

	    \WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save the plugin settings
	 *@method save
	 */
	public function save() {

	    global $current_section;

	    $settings = $this->get_settings( $current_section );

		\WC_Admin_Settings::save_fields( $settings );

		if ( $current_section == "" && get_option('adm_authentication_type') == 'auth_account' && get_option('adm_merchant_authenticated') == 'false' ) {

	    	$auth = new MerchantAuthentication;

	   		$auth->adm_merchant_authenticate( $current_section, $settings );

	   		update_option( 'adm_merchant_password', 'Silence' );


	    }

	}
}