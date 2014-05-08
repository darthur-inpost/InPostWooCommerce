<?php
 
/**
 * Add checkbox field to the checkout
 **/
add_action('woocommerce_after_order_notes', 'my_custom_checkout_field');
function my_custom_checkout_field( $checkout )
{
	echo '<div id="my-new-field"><h3>'.__('My Checkbox: ').'</h3>';
	woocommerce_form_field( 'my_checkbox',
		array(
			'type' => 'checkbox',
			'class' => array('input-checkbox'),
			'label' => __('I have read and agreed.'),
			'required' => true,
		),
		$checkout->get_value( 'my_checkbox' ));
	echo '</div>';
}
/**
 * Process the checkout
 **/
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');
function my_custom_checkout_field_process()
{
	global $woocommerce;
	// Check if set, if its not set add an error.
	if (!$_POST['my_checkbox'])
	{
		$woocommerce->add_error( __('Please agree to my checkbox.') );
	}
}
/**
 * Update the order meta with field value
 **/
add_action('woocommerce_checkout_update_order_meta',
	'my_custom_checkout_field_update_order_meta');
function my_custom_checkout_field_update_order_meta( $order_id )
{
	if ($_POST['my_checkbox'])
	{
		update_post_meta( $order_id, 'My Checkbox',
			esc_attr($_POST['my_checkbox']));
	}
}
 
?>
