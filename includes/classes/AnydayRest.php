<?php
namespace Adm;

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'AnydayRest' ) ) {
	return;
}

/**
 * class handling all callback actions.
 */
class AnydayRest {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	const ENDPOINT_NAMESPACE = 'anyday';

	/**
	 * @var string
	 */
	const ENDPOINT = 'webhook';

  /**
   * @var string
   */
  const EVENT_CLASS_PREFIX = 'AnydayEvent';

  public function __construct() {
    add_action( 'rest_api_init', function () {
			$this->register_routes();
		} );
  }

	/**
	 * Registering the routes for webhooks.
	 */
	public function register_routes() {
		register_rest_route(
			self::ENDPOINT_NAMESPACE,
			'/' . self::ENDPOINT,
			array(
				'methods' => \WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'callback' ),
				'permission_callback' => '__return_true'
			)
		);
	}

	/**
	 * @param  \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function callback( $request ) {
		if ( !$this->validSignature($request) || 'application/json' !== $request->get_header( 'Content-Type' ) ) {
			return new \WP_Error( 'anyday_rest_wrong_header', __( 'Wrong header type.', 'adm' ), array( 'status' => 400 ) );
		}

    $data = $request->get_json_params();
		
		if ( !isset($data['Transaction']) ) {
			return new \WP_Error( 'anyday_rest_wrong_object', __( 'Wrong object type.', 'adm' ), array( 'status' => 400 ) );
		}

		$event = new AnydayEvents;
    $eventType = self::EVENT_CLASS_PREFIX.ucfirst($data['Transaction']['Type']);
		$event = $event->handle( $eventType, $data );

		return rest_ensure_response( $event );
	}

	/**
	 * checks the signature is valid and returns true if valid.
	 * @param \WP_REST_Request $request
	 * @return boolean
	 */
	private function validSignature( $request ) {
		$private    = get_option('adm_private_key');
		$signature  = $request->get_header('x_anyday_signature');
		if(empty($private)) {
			return false;
		}
		$signedBody = hash_hmac('sha256', $request->get_body(), $private);
		if($signature === strtoupper($signedBody)) {
			return true;
		}
		return false;
	}
}
