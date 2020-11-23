<?php
namespace Adm;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

class AnydayPayment
{
	private $client;
	private $authorization_token;

	public function __construct()
	{
		// Capture AnyDay payment
		add_action( 'wp_ajax_adm_capture_payment', array( $this, 'adm_capture_payment' ) );
		add_action( 'wp_ajax_nopriv_adm_capture_payment', array( $this, 'adm_capture_payment' ) );

		// Cancel AnyDay payment
		add_action( 'wp_ajax_adm_cancel_payment', array( $this, 'adm_cancel_payment' ) );
		add_action( 'wp_ajax_nopriv_adm_cancel_payment', array( $this, 'adm_cancel_payment' ) );

		// Refund AnyDay payment
		add_action( 'wp_ajax_adm_refund_payment', array( $this, 'adm_refund_payment' ) );
		add_action( 'wp_ajax_nopriv_adm_refund_payment', array( $this, 'adm_refund_payment' ) );

		$environment = get_option('adm_environment');

		switch ( $environment ) {

			case 'live':

				$this->authorization_token = $this->adm_get_api_key('live');

				break;

			case 'test':

				$this->authorization_token = $this->adm_get_api_key('test');

				break;
		}

		$this->client = new Client();
	}

	/**
	 * Get the API key bases on the enviroment and authentication type
	 */
	private function adm_get_api_key( $environment )
	{
		if ( get_option('adm_authentication_type') == 'auth_manual' ) {

			if ( $environment == 'live' ) {

				return get_option('adm_manual_prod_api_key');

			} elseif ( $environment == 'test' ) {

				return get_option('adm_manual_test_api_key');

			}

		} elseif ( get_option('adm_authentication_type') == 'auth_account' ) {

			if ( $environment == 'live' ) {

				return get_option('adm_api_key');

			} elseif ( $environment == 'test' ) {

				return get_option('adm_test_api_key');

			}
		}
	}

	/**
	 * Authorize a payment
	 *@method adm_authorize_payment
	 *@param  object $order
	 */
	public function adm_authorize_payment($order, $successURL, $cancelURL)
	{
		try {

			$response = $this->client->request('POST', ADM_API_BASE_URL . '/v1/payments', [
				'headers' => [
			        'Content-Type' => 'application/json',
			        'Authorization' => 'Bearer ' .  $this->authorization_token
			    ],
			    "json" => [
					"Amount" => $order->get_total(),
					"Currency" => get_option('woocommerce_currency'),
					"OrderId" => $order->get_id(),
					"SuccessRedirectUrl" => $successURL,
					"CancelPaymentRedirectUrl" => $cancelURL
			    ]
			]);

			$response = json_decode( $response->getBody()->getContents() );

			if( $response->errorCode === 0 ) {

				update_post_meta( $order->get_id(), 'anyday_payment_transaction', wc_clean( $response->transactionId ) );

				return $response->authorizeUrl;
			}

		} catch ( RequestException $e ) {

			$this->adm_log_anyday_error( Psr7\str($e->getResponse()) );

			$order->update_status( 'failed', __( 'ANYDAY payment failed!', 'adm' ) );

		}
	}

	/**
	 * Capture a payment
	 *@method adm_capture_payment
	 *@return json
	 */
	public function adm_capture_payment()
	{
		$order = wc_get_order( $_POST['orderId'] );

		$request = $this->adm_api_capture( $order, $_POST['amount'] );

		if ( $request ) {

			update_post_meta( $order->get_id(), date("Y-m-d_h:i:sa") . '_anyday_captured_payment', wc_clean( $_POST['amount'] ) );

			$order->update_status( 'completed', __( 'ANYDAY payment captured!', 'adm' ) );

			$order->add_order_note( __( date("Y-m-d, h:i:sa") . ' - Captured amount: ' . number_format($_POST['amount'], 2, ',', ' ') . get_option('woocommerce_currency'), 'adm') );

			echo json_encode( ["success" => __('ANYDAY payment successfully captured.', 'adm')] );

		} else {

			echo json_encode( ["error" => __('Payment could not be captured. Please contact ANYDAY support.', 'adm')] );

		}

		exit;
	}

	/**
	 * Request to ANYDAY API to capture a payment amount
	 *@method adm_api_capture
	 */
	public function adm_api_capture( $order, $amount )
	{
		try {

			$response = $this->client->request('POST', ADM_API_BASE_URL . '/v1/payments/' . get_post_meta( $order->get_id(), 'anyday_payment_transaction' )[0] . '/capture', [
				'headers' => [
			        'Content-Type' => 'application/json',
			        'Authorization' => 'Bearer ' .  $this->authorization_token
			    ],
			    "json" => [
					"Amount" => (float)$amount
			    ]
			]);

			$response = json_decode( $response->getBody()->getContents() );

			if( $response->errorCode === 0 )

			return true;

		} catch ( RequestException $e ) {

			$this->adm_log_anyday_error( Psr7\str($e->getResponse()) );

		}
	}

	/**
	 * Cancel a payment
	 *@method adm_cancel_payment
	 *@return json
	 */
	public function adm_cancel_payment()
	{
		$order = wc_get_order( $_POST['orderId'] );

		try {

			$response = $this->client->request('POST', ADM_API_BASE_URL . '/v1/payments/' . get_post_meta( $order->get_id(), 'anyday_payment_transaction' )[0] . '/cancel', [
				'headers' => [
			        'Content-Type' => 'application/json',
			        'Authorization' => 'Bearer ' .  $this->authorization_token
			    ]
			]);

			$response = json_decode( $response->getBody()->getContents() );

			if( $response->errorCode === 0 ) {

				$order->update_status( 'cancelled', __( 'ANYDAY payment cancelled!', 'adm' ) );

				echo json_encode( ["success" => __('ANYDAY payment successfully cancelled.', 'adm')] );

			}

		} catch ( RequestException $e ) {

			$this->adm_log_anyday_error( Psr7\str($e->getResponse()) );

			echo json_encode(["error" => __('Payment could not be cancelled. Please contact ANYDAY support.', 'adm')]);
		}

		exit;
	}


	/**
	 * Request to ANYDAY API to capture a payment amount
	 *@method adm_api_capture
	 */
	public function adm_api_refund( $order, $amount )
	{
		try {

			$response = $this->client->request('POST', ADM_API_BASE_URL . '/v1/payments/' . get_post_meta( $order->get_id(), 'anyday_payment_transaction' )[0] . '/refund', [
				'headers' => [
			        'Content-Type' => 'application/json',
			        'Authorization' => 'Bearer ' .  $this->authorization_token
			    ],
			    "json" => [
					"Amount" => (float)$amount
			    ]
			]);

			$response = json_decode( $response->getBody()->getContents() );

			if( $response->errorCode === 0 )

			return true;

		} catch ( RequestException $e ) {

			$this->adm_log_anyday_error( Psr7\str($e->getResponse()) );

		}
	}

	/**
	 * Refund a payment
	 *@method adm_refund_payment
	 *@return json
	 */
	public function adm_refund_payment()
	{
		$order = wc_get_order( $_POST['orderId'] );

		$request = $this->adm_api_refund( $order, $_POST['amount'] );

		if( $request ) {

			update_post_meta( $order->get_id(), date("Y-m-d_h:i:sa") . '_anyday_refunded_payment', wc_clean( $_POST['amount'] ) );

			$order->update_status( 'wc-adm-refunded', __( 'ANYDAY payment refunded!', 'adm' ) );

			$order->add_order_note( __( date("Y-m-d, h:i:sa") . ' - Refunded amount: ' . number_format($_POST['amount'], 2, ',', ' ') . get_option('woocommerce_currency'), 'adm') );

			echo json_encode( ["success" => __('ANYDAY payment successfully refunded.', 'adm')] );

		} else {

			echo json_encode(["error" => __('Payment could not be refunded. Please contact ANYDAY support.', 'adm')]);

		}

		exit;
	}

	/**
	 * Write ANYDAY event in a file for debuging purposes
	 */
	private function adm_log_anyday_error( $message )
	{
		if ( get_option('adm_module_error_log') == 'enabled' ) {

			$contents .= "$message\n";

			file_put_contents( ADM_PATH . "/debug.log", $contents, FILE_APPEND | LOCK_EX );

		}

	}
}