<?php
/**
 * Plugin Name: Send Email Received Gift
 * Plugin URI: https://github.com/eltondev/Send-Email-Received-Gift
 * Description: Add custom fields at checkout , sending via email the request to another email in the presented case.
 * Author: EltonDEV / Marco Frasson
 * Author URI: http://eltondev.com.br
 * Version: 0.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



function custom_override_checkout_fields( $fields ) {
		$fields['billing']['billing_presente_nome'] = array(
			'label'					=> __('Nome do presenteado', 'woocommerce'),
			'placeholder'		=> _x('Nome do presenteado', 'placeholder', 'woocommerce'),
			'required'			=> false,
			'class'					=> array('form-row-wide'),
			'clear'					=> true
		);

		$fields['billing']['billing_presente_email'] = array(
			'label'					=> __('E-mail do presenteado', 'woocommerce'),
			'placeholder'		=> _x('E-mail do presenteado', 'placeholder', 'woocommerce'),
			'required'			=> false,
			'class'					=> array('form-row-wide'),
			'clear'					=> true
		);

		return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

/**
 * 
 * @param  int  $customer_id Current customer ID.
 * @return void
 */
 
function wc_save_gift_fields( $customer_id ) {
        if ( isset( $_POST['billing_presente_nome'] ) ) {
                update_user_meta( $customer_id, 'billing_presente_nome', sanitize_text_field( $_POST['billing_presente_nome'] ) );
        }
        if ( isset( $_POST['billing_presente_email'] ) ) {
                update_user_meta( $customer_id, 'billing_presente_email', sanitize_text_field( $_POST['billing_presente_email'] ) );
        }           
}
add_action( 'woocommerce_created_customer', 'wc_save_gift_fields' );


add_action( 'woocommerce_admin_order_data_after_billing_address', 'view_gift_admin_new_fields', 10, 1 );
function view_gift_admin_new_fields($order){
    echo '<p><h4><strong>'.__('Nome do presenteado').'</strong> </h4>' . get_post_meta( $order->id, '_billing_presente_nome', true ) . '</p>';
	echo '<p><h4><strong>'.__('E-mail do presenteado').'</strong> </h4>' . get_post_meta( $order->id, '_billing_presente_email', true ) . '</p>';
}




function gift_user_mail_notification($order_id, $checkout=null) {
   global $woocommerce;
   $order = new WC_Order( $order_id );
   if($order->status === 'processing' ) {
        
      $mailer = $woocommerce->mailer();
      $message_body = __( 'Você foi presenteado' );
      $message = $mailer->wrap_message(
        
		

        sprintf( __( 'Presente %s recebido' ), $order->get_order_number() ), $message_body );

     $mailer->send( $order->billing_presente_email, sprintf( __( 'Presente %s Recebido' ), $order->get_order_number() ), $message );
     }

   }

add_action('woocommerce_order_status_processing', 'gift_user_mail_notification', 10, 1);
