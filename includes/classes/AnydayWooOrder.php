<?php

namespace Adm;

class AnydayWooOrder
{
	public function __construct()
	{
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'adm_editable_order_meta_general' ) );
		add_action( 'woocommerce_order_item_add_action_buttons', array( $this, 'adm_wc_order_item_add_action_buttons_callback' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'adm_hide_woo_refund') );
		add_action( 'init', array( $this, 'adm_user_anyday_order_rejection' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'adm_user_anyday_order_approval' ) );
		add_action( 'admin_init', array( $this, 'adm_register_refund_order_status' ) );
	}

	/**
	 * Registers a new order status ANYDAY Refunded
	 */
	public function adm_register_refund_order_status()
	{
		register_post_status( 'wc-adm-refunded', array(
	        'label'                     => __( 'ANYDAY Refunded', 'adm' ),
	        'public'                    => true,
	        'exclude_from_search'       => false,
	        'show_in_admin_all_list'    => true,
	        'show_in_admin_status_list' => true,
	        'label_count'               => __( 'ANYDAY Refunded', 'adm' )
	    ) );

		add_filter( 'wc_order_statuses', function( $statuses ) {

		    $statuses['wc-adm-refunded'] = __( 'Refunded', 'adm' );

		    return $statuses;
		});
	}

	/**
	 * Add filed which holds the anyday transaction id
	 *@method adm_editable_order_meta_general
	 *@param  object                          $order
	 *@return html
	 */
	public function adm_editable_order_meta_general( $order )
	{
		$anyday_payment_transaction = get_post_meta( $order->get_id(), 'anyday_payment_transaction', true );

		if( $order->get_payment_method() == 'anyday_payment_gateway' ) {
			woocommerce_wp_text_input( array(
				'id' => 'anyday_payment_transaction',
				'label' => __('ANYDAY Transaction ID:'),
				'value' => $anyday_payment_transaction,
				'wrapper_class' => 'form-field-wide',
				'custom_attributes' => array('readonly' => 'readonly')
			) );
		}
	}

	/**
	 * Add ANYDAY action buttons along with capture and refund history sections
	 *@method adm_wc_order_item_add_action_buttons_callback
	 *@return html
	 */
	public function adm_wc_order_item_add_action_buttons_callback( $order )
	{
		$captured_amount = 0;
		$refunded_amount = 0;

		foreach( get_post_meta( $order->get_id() ) as $key => $meta ) {
			if( strpos($key, 'anyday_captured_payment') !== false ) {
				$captured_amount += $meta[0];
			}

			if( strpos($key, 'anyday_refunded_payment') !== false ) {
				$refunded_amount += $meta[0];
			}

			if ( ((float)$order->get_total() - (float)$captured_amount) == 0 ) {
				update_post_meta( $order->get_id(),'full_captured_amount', 'true' );
			}

			if ( ((float)$order->get_total() - (float)$refunded_amount) == 0 ) {
				update_post_meta( $order->get_id(),'full_refunded_amount', 'true' );
			}
		}


		if( $order->get_payment_method() == 'anyday_payment_gateway' ) {

			if ( get_post_meta( $order->get_id(), 'full_captured_amount' )[0] != 'true' ) {
				echo '<button type="button" class="button anyday-capture anyday-payment-action" data-anyday-action="adm_capture_payment" data-order-id="'.$order->get_id().'">'. __("ANYDAY Capture", "adm") .'</button>';
			}

			if ( $order->get_status() == 'on-hold' ) {
				echo '<button type="button" class="button anyday-cancel anyday-payment-action" data-anyday-action="adm_cancel_payment" data-order-id="'.$order->get_id().'">'. __("ANYDAY Cancel", "adm") .'</button>';
			}

			if ( get_post_meta( $order->get_id(), 'full_refunded_amount' )[0] != 'true' ) {
				echo '<button type="button" class="button anyday-refund anyday-payment-action" data-anyday-action="adm_refund_payment" data-order-id="'.$order->get_id().'">'. __("ANYDAY Refund", "adm") .'</button>';
			}

			$captured_amount = 0;
			$refunded_amount = 0;
			?>
			<div class="woocommerce_order_items_wrapper wc-order-items-editable" style="display: block;
width: 100%;margin-top: 20px;">
				<span id="anyday-order-message"></span>
				<table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
					<tbody id="order_refunds">
						<?php foreach( get_post_meta( $order->get_id() ) as $key => $meta ) :?>
							<?php if( strpos($key, 'anyday_captured_payment') !== false ) : $captured_amount = $captured_amount + $meta[0];?>
								<tr class="refund ">
									<td class="thumb">
										<div></div>
									</td>
									<td class="name">
										<?php echo substr($key, 0, 10) . ', ' . substr($key, 11, 10); ?>
										<p class="description">Captured amount.</p>
									</td>
									<td class="line_cost" width="1%">
										<div class="view">
											<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo number_format($meta[0], 2, ',', ' '); ?></span>
										</div>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if( $captured_amount > 0 ) : ?>
					<div class="wc-order-data-row wc-order-totals-items wc-order-items-editable">
						<table class="wc-order-totals">
							<tbody>
								<tr>
									<td class="label refunded-total">Total Captured Amount:</td>
									<td width="1%"></td>
									<td class="total refunded-total">
										<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo number_format((float)$captured_amount, 2, ',', ' '); ?></span>
									</td>
								</tr>
								<tr>
									<td class="label">Amount left to be Captured:</td>
									<td width="1%"></td>
									<td class="total">
										<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo number_format((float)$order->get_total() - (float)$captured_amount, 2, ',', ' ');

										if ( ((float)$order->get_total() - (float)$captured_amount) == 0 ) {
											update_post_meta( $order->get_id(),'full_captured_amount', 'true' );
										}
										?></span>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="clear"></div>
					</div>
				<?php endif; ?>
			</div>
			<div class="woocommerce_order_items_wrapper wc-order-items-editable" style="display: block;
width: 100%;margin-top: 20px;">
				<span id="anyday-order-message"></span>
				<table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
					<tbody id="order_refunds">
						<?php foreach( get_post_meta( $order->get_id() ) as $key => $meta ) :?>
							<?php if( strpos($key, 'anyday_refunded_payment') !== false ) : $refunded_amount = $refunded_amount + $meta[0];?>
								<tr class="refund ">
									<td class="thumb">
										<div></div>
									</td>
									<td class="name">
										<?php echo substr($key, 0, 10) . ', ' . substr($key, 11, 10); ?>
										<p class="description">Refunded amount.</p>
									</td>
									<td class="line_cost" width="1%">
										<div class="view">
											<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo number_format($meta[0], 2, ',', ' '); ?></span>
										</div>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if( $refunded_amount > 0 ) : ?>
					<div class="wc-order-data-row wc-order-totals-items wc-order-items-editable">
						<table class="wc-order-totals">
							<tbody>
								<tr>
									<td class="label refunded-total">Total Refunded Amount:</td>
									<td width="1%"></td>
									<td class="total refunded-total">
										<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo number_format((float)$refunded_amount, 2, ',', ' '); ?></span>
									</td>
								</tr>
								<tr>
									<td class="label">Amount left to be Refunded:</td>
									<td width="1%"></td>
									<td class="total">
										<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo  number_format((float)$order->get_total() - (float)$refunded_amount, 2, ',', ' ');

										if ( ((float)$order->get_total() - (float)$refunded_amount) == 0 ) {
											update_post_meta( $order->get_id(),'full_refunded_amount', 'true' );
										}
										?></span>
									</td>
								</tr>
								<tr>
									<td class="label label-highlight">Net Payment:</td>
									<td width="1%"></td>
									<td class="total">
									<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol"><?php echo $order->get_currency(); ?></span><?php echo number_format($captured_amount - $refunded_amount, 2, ',', ' '); ?></bdi></span></td>
								</tr>
							</tbody>
						</table>
						<div class="clear"></div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Hide the default admin order refunds items and refund button
	 *@method adm_hide_wc_refund_button
	 */
	public function adm_hide_woo_refund()
	{

		global $post;

		if ( $post ) {

			$order = wc_get_order( (int)$post->ID );

			if (!current_user_can('administrator') && !current_user_can('editor')) {
				return;
			}
			if (strpos($_SERVER['REQUEST_URI'], 'post.php?post=') === false) {
				return;
			}

			if (empty($post) || $post->post_type != 'shop_order') {
				return;
			}

			if( $order->get_payment_method() != 'anyday_payment_gateway' ) {
				?>
				<script type="text/javascript">
					jQuery(function () {
						var orderStatusField = $('#order_status'),
				        orderPendingOption = orderStatusField.find('option[value="wc-adm-refunded"]');

				    	orderPendingOption.remove();
					});
				</script>
				<?php
				return;
			}
			?>
			<script type="text/javascript">
				jQuery(function () {
					var orderStatusField = jQuery('#order_status'),
			        orderPendingOption = orderStatusField.find('option[value="wc-refunded"]');

			    	orderPendingOption.remove();

			    	jQuery('.refund-items').hide();
				});
			</script>
			<?php

		}

	}

	/**
	 * Update the order status after the user rejects the payment on ANYDAY portal
	 *@method adm_user_anyday_order_rejection
	 */
	public function adm_user_anyday_order_rejection()
	{
		if( isset($_GET['orderId']) && isset($_GET['orderKey']) && isset($_GET['anydayPayment']) ) {
			$order = wc_get_order( $_GET['orderId'] );

			$check_order_key = ($_GET['orderKey'] == $order->get_order_key()) ? true : false;

			if ( $order && $check_order_key && $order->get_payment_method() == 'anyday_payment_gateway' && $order->get_status() != 'cancelled' && $_GET['anydayPayment'] == 'rejected' ) {

				$order->update_status( 'cancelled', __( 'ANYDAY payment cancelled!', 'adm' ) );

			}
		}
	}

	/**
	 * Update the order status after the user approves the payment on ANYDAY portal
	 *@method adm_user_anyday_order_approval
	 */
	public function adm_user_anyday_order_approval( $order_id )
	{
		$order = wc_get_order( $order_id );

		if ( $order && $order->get_payment_method() == 'anyday_payment_gateway' && $order->get_status() == 'pending' && isset($_GET['anydayPayment']) && $_GET['anydayPayment'] == 'approved' ) {

			WC()->cart->empty_cart();

			$order->update_status( 'on-hold', __( 'ANYDAY payment approved!', 'adm' ) );

		}
	}
}