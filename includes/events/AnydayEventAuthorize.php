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
		$transaction = $this->data['transaction'];
		$this->order->add_order_note( __( 'Anyday: Received auth webhook event.', 'adm' ) );
    $order = wc_get_order( $this->order->get_id() );
		if( $this->handled($order, $transaction['Id']) && !$order->has_status( get_option( 'adm_order_status_before_authorized_payment' )) ) {
			return;
		}

		switch ( $this->data['transaction']['status'] ) {
			case 'fail':
				$message         = __( 'Anyday: Payment failed to authorize', 'adm' );
				$this->order->add_order_note( $message );
				break;

			case 'success':
				$message = __( 'Anyday: Payment has been authorized.', 'adm' );
	
				$this->order->add_order_note( $message );
        if( !$order->has_status( get_option( 'adm_order_status_after_authorized_payment' ) ) && ! $this->get_is_pending()) {
          $order->update_status( get_option( 'adm_order_status_after_authorized_payment' ) );
        }
			break;
		}
		return;
	}
}