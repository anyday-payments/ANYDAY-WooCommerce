<?php
namespace Adm;

defined( 'ABSPATH' ) || exit;

class AnydayEventCancel extends AnydayEvent {
	/**
	 * @param  mixed $data
	 *
	 * @return void
	 */
	public function resolve() {
		$transaction = $this->data['Transaction'];
		$this->order->add_order_note( __( 'Anyday: Received cancel webhook event.', 'adm' ) );
		$order = wc_get_order( $this->order->get_id() );
		if( $this->handled($order, $transaction['Id']) ) {
			return;
		}
		switch ( $this->data['Transaction']['Status'] ) {
			case 'fail':
				$message         = __( 'Anyday: Payment failed to cancel', 'adm' );
				$this->order->add_order_note( $message );
				break;

			case 'success':
				if ( $this->order->has_status( 'cancelled' ) ) {
					return;
				}
	
				$message = __( 'Anyday: Payment has been canceled.', 'adm' );
	
				$this->order->add_order_note( $message );
				$this->order->update_status( 'cancelled' );
			break;
		}
		return;
	}
}
