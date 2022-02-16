<?php
namespace Adm;

defined( 'ABSPATH' ) || exit;

class AnydayEventAuthorize extends AnydayEvent {
	/**
	 * @param  mixed $data
	 *
	 * @return void
	 */
	public function resolve() {
		$transaction = $this->data['Transaction'];
		$this->order->add_order_note( __( 'Anyday: Received auth webhook event.', 'adm' ) );
    $order = wc_get_order( $this->order->get_id() );
		if( $this->handled($order, $transaction['Id']) ) {
			return;
		}

		switch ( $this->data['Transaction']['Status'] ) {
			case 'fail':
				$message         = __( 'Anyday: Payment failed to authorize', 'adm' );
				$this->order->add_order_note( $message );
				break;

			case 'success':
				$message = __( 'Anyday: Payment has been authorized.', 'adm' );
	
				$this->order->add_order_note( $message );
        if( !$order->has_status( get_option( 'adm_order_status_after_authorized_payment' ) ) ) {
          $order->update_status( get_option( 'adm_order_status_after_authorized_payment' ) );
        }
			break;
		}
		return;
	}
}
