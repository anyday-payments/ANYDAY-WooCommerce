<?php
namespace Adm;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

 class MerchantAuthentication
 {
 	/**
 	 * Authentication
 	 *@method adm_authenticate
 	 *@return json
 	 */
 	public function adm_authenticate($username, $password)
 	{
		$client = new Client();

	    try {

			$response = $client->request('POST', ADM_API_BASE_URL . '/api/v1/authentication/login', [
			    "json" => [
			        "grant_type" => "password",
					"username" => $username,
					"password" => $password,
					"userType" => "merchant"
			    ]
			]);

			$access_token = json_decode( $response->getBody()->getContents() )->access_token;

		} catch ( RequestException $e ) {

			if ( $e->hasResponse() ) {

				update_option( 'adm_merchant_authenticated', 'false' );

				return Psr7\str($e->getResponse());

		    }

		}

		try {

			$response = $client->request('GET', ADM_API_BASE_URL . '/api/v1/webshop/mine', [
			    'headers' => [
			        'Content-Type' => 'application/json',
			        'Authorization' => 'Bearer ' .  $access_token
			    ]
			]);

			$result = json_decode($response->getBody()->getContents())->data[0];

			update_option( 'adm_api_key', $result->apiKey );
	    	update_option( 'adm_test_api_key', $result->testAPIKey );
	    	update_option( 'adm_pricetag_token', $result->priceTagToken );

	    	return true;

		} catch ( RequestException $e ) {

	    	return Psr7\str( $e->getResponse() );

		}

	}

	/**
	 * Authenticate the merchant upon plugin settings save
	 *@method adm_merchant_authenticate
	 */
	public function adm_merchant_authenticate( $current_section, $settings )
	{
		if( $current_section == "" && get_option('adm_merchant_authenticated') == 'false' ) {

		    $options = ["adm_merchant_username" => __("Merchant username is required!", "adm"), "adm_merchant_password" => __("Merchant password is required!", "adm")];

			foreach( $options as $option_id => $option_name ) {

			    add_filter( "woocommerce_admin_settings_sanitize_option_" . $option_id, function( $value, $option, $raw_value ) use ( $option_name ) {

			        add_action( 'admin_notices', function() use( $value, $option_name ) {

			            if( $value == "" ){

			                echo '<div id="message" class="notice notice-error is-dismissible"><p><strong>'. $option_name .'</strong></p></div>';

			            }

			        });

			        return $value;

			    }, 10, 3 );
			}

			\WC_Admin_Settings::save_fields( $settings );

			$merchant_authentication = $this->adm_authenticate( get_option('adm_merchant_username'), get_option('adm_merchant_password') );

			if ( $merchant_authentication === true ) {

				update_option( 'adm_merchant_authenticated', 'true' );

				add_action( 'admin_notices', function() {
			        echo '<div id="message" class="notice notice-success is-dismissible"><p><strong>'. __( "Merchant authenticaton successful.", "adm" ) .'</strong></p></div>';
			    });

			} else {

				add_action( 'admin_notices', function() use ( $merchant_authentication ) {
			        echo '<div id="message" class="notice notice-error is-dismissible">
			        <p><strong>'. __( "An error occurred. Please contact Anyday support.", "adm" ) .'</strong></p>
			        <p>'. __( sanitize_text_field( $merchant_authentication ), "adm" ) .'</p>
			        </div>';
			    });
			}
		}
	}
 }
