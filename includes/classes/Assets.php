<?php
namespace Adm;

class Assets
{
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'adm_enque_public_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'adm_enqueue_admin_scripts' ) );
    }

    public function adm_enqueue_admin_scripts()
    {
    	wp_enqueue_script( 'anyday-admin-javascript', ADM_URL . 'assets/admin/js/anyday-admin.js', array(), false, true );
        wp_localize_script( 'anyday-admin-javascript', 'anyday', array(
            "ajaxUrl" => admin_url( 'admin-ajax.php' ),
            "capturePrompt" => __( "Enter amount to be captured. Please note the amount cannot be higher than the order total!", "adm" ),
            "capturePromptValidation" => __( "Please enter numeric value!", "adm" ),
            "cancelConfirmation" => __( "Are you sure you want to cancel this order? This action cannot be undone.", "adm" ),
            "refundConfirmation" => __( "Are you sure you want to refund this order? This action cannot be undone.", "adm" )
        ));

        wp_enqueue_style( 'anyday-admin-stylesheet', ADM_URL . 'assets/admin/css/anyday-admin.css' );
    }

    public function adm_enque_public_scripts()
    {
        $position_selector = '';

        if ( is_product() && !empty( get_option('adm_price_tag_product_selector') ) ) {
            $position_selector = get_option('adm_price_tag_product_selector');
        }elseif ( is_cart() && !empty( get_option('adm_price_tag_cart_selector') ) ) {
            $position_selector = get_option('adm_price_tag_cart_selector');
        }elseif ( is_checkout() && !empty( get_option('adm_price_tag_checkout_selector') ) ) {
            $position_selector = get_option('adm_price_tag_checkout_selector');
        }

        wp_enqueue_script( 'anyday-public-javascript', ADM_URL . 'assets/public/js/anyday-public.js', array(), false, true );
        wp_localize_script( 'anyday-public-javascript', 'anyday', array(
            "positionSelector" => $position_selector
        ));

        wp_enqueue_style( 'anyday-public-stylesheet', ADM_URL . 'assets/public/css/anyday-public.css' );

    }
}