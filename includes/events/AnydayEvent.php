<?php
namespace Adm;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'AnydayEvent' ) ) {
	return;
}

/**
 * @since 4.0
 */
class AnydayEvent {
	/**
	 * @var array  of Anyday event's payload.
	 */
	protected $data;

	/**
	 * @var \WC_Abstract_Order
	 */
	protected $order;

	public function __construct( $data ) {
		$this->data = $data;
	}
	
	/**
	 * validating if order Id does exists, orders transaction id identical with event transaction id and if valid order exists returning true
	 * 
	 * @return boolean
	 */
	public function validate() {
		if ( ! isset( $this->data['OrderId'] ) ) {
			return false;
		}

		if ( ! $this->order = wc_get_order( $this->data['OrderId'] ) ) {
			return false;
		}

		if ( get_post_meta( $this->order->get_id(), 'anyday_payment_transaction' )[0] !== $this->data['Id'] ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function resolve() {
		return true;
	}

	/**
	 * @return array  of Anyday event's payload.
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * @return \WC_Abstract_Order
	 */
	public function get_order() {
		return $this->order;
	}
}
