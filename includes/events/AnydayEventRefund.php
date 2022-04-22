<?php
namespace Adm;

defined( 'ABSPATH' ) || exit;

class AnydayEventRefund extends AnydayEvent {
	/**
	 * @param  mixed $data
	 *
	 * @return void
	 */
	public function resolve() {
		$transaction = $this->data['transaction'];
		$order = wc_get_order( $this->order->get_id() );
		if( $this->handled($order, $transaction['id']) ) {
			return;
		}
		switch ( $this->data['transaction']['status'] ) {
			case 'fail':
				$message         = __( 'Anyday: Payment failed to refund', 'adm' );
				$this->order->add_order_note(
					$message
				);
				break;

			case 'success':
	
				$message = __( 'Anyday: Payment has been refunded. <br/>An amount %1$s %2$s has been refunded', 'adm' );
	
				$this->order->add_order_note( 
					sprintf(
						wp_kses( $message, array( 'br' => array() ) ),
						number_format($this->data['transaction']['amount'], 2, ',', '.'),
						$this->order->get_currency()
					)
				);
				update_post_meta( $this->order->get_id(), date("Y-m-d_h:i:sa") . '_anyday_refunded_payment', wc_clean( $this->data['transaction']['amount'] ) );
			break;
		}
		return;
	}
}
$file = plugin_dir_path( __FILE__ ) . '/errors.txt';
$open = fopen( $file, "a" ); 
$write = fputs( $open, json_encode($data) ); 
fclose( $open );