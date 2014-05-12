<?php
/**
 * Plugin Name: InPost Shipping Plugin
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: This plugin allows shoppers to pick an InPost location for their parcel.
 * Version: 1.0
 * Author: InPost, David Arthur
 * Author URI: http://www.inpost.co.uk
 * License: GPL2
 */
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Check if WooCommerce is active
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
{
	define("INPOST_TABLE_NAME",     "order_shipping_inpostparcels");

	//include_once('includes/inpostparcelsHelper.php');

	function inpost_shipping_init()
	{
		if ( ! class_exists( 'WC_InPostShippingMethod' ) )
		{
class WC_InPostShippingMethod extends WC_Shipping_Method
{
	private $inpost_db_version = '1.0.0';

	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->id = 'inpost_shipping_method';
		// Id for your shipping method. Should be uunique.
		$this->method_title = __( 'InPost Shipping' ); // Title shown in admin
		$this->method_description = __( 'InPost Shipping to a Locker' ); // Description shown in admin

		$this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled
		$this->title = "InPost Shipping"; // This can be added as an setting but for this example its forced.

		//************************************************************
		// The Ajax add actions that would allow us to get data back
		// and forward between the checkout Java Script will NOT WORK
		// inside this class. I "think" it is to do with the way that
		// a plugin for a Shipping Method is instantiated.
		// I am not 100% sure but I cannot get this to work.
		//************************************************************
		//add_action( 'wp_ajax_inpost_get_machines' , array( $this, 'get_machines' ) );
		//add_action( 'wp_ajax_nopriv_inpost_get_machines' , array( $this, 'get_machines' ) );

		$this->init();
	}
 
	/**
	* Init your settings
	*
	* @access public
	* @return void
	*/
	function init()
	{
		// Load the settings API
		$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->type         = $this->get_option( 'type' );
		$this->fee          = $this->get_option( 'fee' );
		$this->type         = $this->get_option( 'type' );
		$this->codes        = $this->get_option( 'codes' );
		$this->api_url      = $this->get_option( 'api_url' );
		$this->api_key      = $this->get_option( 'api_key' );
		$this->max_weight   = $this->get_option( 'max_weight' );
		$this->max_sizea    = $this->get_option( 'max_sizea' );
		$this->max_sizeb    = $this->get_option( 'max_sizeb' );
		$this->max_sizec    = $this->get_option( 'max_sizec' );

		$this->inpost_install();

		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	
	}

	///
	// inpost_install function
	// 
	// @brief The table for the parcels is created.
	// @params none
	// @return none
	// 
	private function inpost_install()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . INPOST_TABLE_NAME;

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id int(11) unsigned NOT NULL auto_increment,
			order_id int(11) NOT NULL,
			parcel_id varchar(200) NOT NULL default '',
			parcel_status varchar(200) NOT NULL default '',
			parcel_detail text NOT NULL default '',
			parcel_target_machine_id varchar(200) NOT NULL default '',
			parcel_target_machine_detail text NOT NULL default '',
			sticker_creation_date TIMESTAMP NULL DEFAULT NULL,
			creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			api_source varchar(3) NOT NULL default '',
			variables text NOT NULL default '',
			PRIMARY KEY  id  (id)
		) DEFAULT CHARSET=utf8;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		add_option( "inpost_db_version", $this->inpost_db_version );
	}

	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package )
	{
		$shipping_total = 0;
		$fee = ( trim( $this->fee ) == '' ) ? 0 : $this->fee;

		if ( $this->type =='fixed' )
			$shipping_total 	= $this->fee;

		if ( $this->type =='percent' )
			$shipping_total 	= $package['contents_cost'] * ( $this->fee / 100 );

		if ( $this->type == 'product' )
		{
			foreach ( WC()->cart->get_cart() as $item_id => $values ) {
				$_product = $values['data'];

				if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {
					$shipping_total += $this->fee * $values['quantity'];
                		}
			}
		}

		$rate = array(
			'id'    => $this->id,
			'label' => $this->title,
			'cost'  => $shipping_total
		);

		// Register the rate
		$this->add_rate( $rate );
	}

	/**
	* init_form_fields function.
	*
	* @access public
	* @return void
	*/
	function init_form_fields()
	{
		$this->form_fields = array(
		'enabled' => array(
			'title'       => __( 'Enable', 'inpostplugin' ),
			'type'        => 'checkbox',
			'label'       => __( 'Delivery to an InPost Locker', 'inpostplugin' ),
			'default'     => 'no'
		),
		'title' => array(
			'title'       => __( 'Title', 'inpostplugin' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'inpostplugin' ),
			'default'     => __( 'InPost Parcel Lockers 24/7', 'inpostplugin' ),
			'desc_tip'    => true,
		),
		'type' => array(
			'title'       => __( 'Fee Type', 'inpostplugin' ),
			'type'        => 'select',
			'description' => __( 'How to calculate delivery charges', 'inpostplugin' ),
			'default'     => 'fixed',
			'options'     => array(
				'fixed'       => __( 'Fixed amount', 'inpostplugin' ),
				'percent'     => __( 'Percentage of cart total', 'inpostplugin' ),
				'product'     => __( 'Fixed amount per product', 'inpostplugin' ),
				),
			'desc_tip'    => true,
		),
		'fee' => array(
			'title'       => __( 'Delivery Fee', 'inpostplugin' ),
			'type'        => 'price',
			'description' => __( 'What fee do you want to charge for InPost locker delivery, disregarded if you choose free. Leave blank to disable.', 'inpostplugin' ),
			'default'     => '',
			'desc_tip'    => true,
			'placeholder' => wc_format_localized_price( 0 )
		),
		'codes' => array(
			'title'       => __( 'Post Codes', 'inpostplugin' ),
			'type'        => 'textarea',
			'description' => __( 'What post codes would you like to offer delivery to? Separate codes with a comma. Accepts wildcards, e.g. P* will match a postcode of PE30.', 'inpostplugin' ),
			'default'     => '',
			'desc_tip'    => true,
			'placeholder' => 'SW0, G42 etc'
		),
		'api_url' => array(
			'title'       => __( 'Api Url', 'inpostplugin' ),
			'type'        => 'text',
			'description' => __( 'This controls where the REST calls go.', 'inpostplugin' ),
			'default'     => __( 'http://api-uk.easypack24.net/', 'inpostplugin' ),
			'desc_tip'    => true,
		),
		'api_key' => array(
			'title'       => __( 'Api Key', 'inpostplugin' ),
			'type'        => 'text',
			'class'       => 'validate-required',
			'description' => __( 'This is your unique key.', 'inpostplugin' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'max_weight' => array(
			'title'       => __( 'Max Weight', 'inpostplugin' ),
			'type'        => 'text',
			'description' => __( 'The maximum weight of a parcel is 25kg.', 'inpostplugin' ),
			'default'     => __( '25', 'inpostplugin' ),
			'desc_tip'    => true,
		),
		'max_sizea' => array(
			'title'       => __( 'Max dimension - size A', 'inpostplugin' ),
			'type'        => 'text',
			'description' => __( 'The maximum dimension of a Size A parcel is 8x38x64.', 'inpostplugin' ),
			'default'     => __( '8x38x64', 'inpostplugin' ),
			'desc_tip'    => true,
		),
		'max_sizeb' => array(
			'title'       => __( 'Max dimension - size B', 'inpostplugin' ),
			'type'        => 'text',
			'description' => __( 'The maximum dimension of a Size B parcel is 19x38x64.', 'inpostplugin' ),
			'default'     => __( '19x38x64', 'inpostplugin' ),
			'desc_tip'    => true,
		),
		'max_sizec' => array(
			'title'       => __( 'Max dimension - size C', 'inpostplugin' ),
			'type'        => 'text',
			'description' => __( 'The maximum dimension of a Size C parcel is 41x38x64.', 'inpostplugin' ),
			'default'     => __( '41x38x64', 'inpostplugin' ),
			'desc_tip'    => true,
		),
		);
	}

	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	function admin_options()
	{
		global $woocommerce; ?>
		<h3><?php echo $this->method_title; ?></h3>
		<p><?php _e( 'InPost Shipping method is for delivering orders to InPost lockers.', 'inpostplugin' ); ?></p>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table> <?php
	}

	/**
	 * is_available function.
	 *
	 * @access public
	 * @param array $package
	 * @return bool
	 */
	function is_available( $package )
	{
		if ($this->enabled=="no") return false;

		// If post codes are listed, let's use them.
		$codes = '';
		if ( $this->codes != '' )
		{
			foreach( explode( ',', $this->codes ) as $code )
			{
				$codes[] = $this->clean( $code );
			}
		}

		if ( is_array( $codes ) )
		{
			$found_match = false;

			if ( in_array( $this->clean( $package['destination']['postcode'] ), $codes ) )
			{
				$found_match = true;
			}

			// Pattern match
			if ( ! $found_match )
			{
				$customer_postcode = $this->clean( $package['destination']['postcode'] );
				foreach ($codes as $c)
				{
					$pattern = '/^' . str_replace( '_', '[0-9a-zA-Z]', $c ) . '$/i';
					if ( preg_match( $pattern, $customer_postcode ) ) {
						$found_match = true;
						break;
					}
				}
			}


			// Wildcard search
			if ( ! $found_match ) {

				$customer_postcode = $this->clean( $package['destination']['postcode'] );
				$customer_postcode_length = strlen( $customer_postcode );

				for ( $i = 0; $i <= $customer_postcode_length; $i++ ) {

					if ( in_array( $customer_postcode, $codes ) ) {
						$found_match = true;
                    }

					$customer_postcode = substr( $customer_postcode, 0, -2 ) . '*';
				}
			}

			if ( ! $found_match )
			{
				return false;
            		}
		}

		// Yay! We passed!
		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true );
	}

	/**
	 * clean function.
	 *
	 * @access public
	 * @param mixed $code
	 * @return string
	 */
	function clean( $code )
	{
		return str_replace( '-', '', sanitize_title( $code ) ) . ( strstr( $code, '*' ) ? '*' : '' );
	}
 
} // End of the InPost Shipping Class

		} // End of if(!class_exists())
	} // End function inpost_shipping_init

	///
	// add_my_js function
	//
	// @brief Add the Java Script for the InPost Locker selection.
	//
	// @access public
	// @return null
	//
	function add_my_js()
	{
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$assets_path = str_replace( array( 'http:', 'https:' ), '', plugins_url('/assets/js/checkout', __FILE__) );

		if(is_checkout())
		{
			// We will load up our Java Script only when the user
			// is on the checkout panel.
			//
			//****************************************************
			// NB The order that the java scripts are loaded is
			// very important. Our script relies on document.ready
			// but so does the calculation for the Order Total.
			//
			// This makes our script load AFTER the checkout one.
			//
			//****************************************************
			//error_log("assets_path = " . $assets_path);
			
			//wp_enqueue_script('checkout_machines', 'https://geowidget.inpost.co.uk/dropdown.php?dropdown_name=machine', array('jquery'), WC_VERSION, true);

			wp_enqueue_script('checkout_js', $assets_path . $suffix . '.js', array('jquery', 'chosen', 'woocommerce', 'wc-checkout', 'wc-add-to-cart', 'wc-chosen', 'wc-cart-fragments'), WC_VERSION, true);

			$ret = check_weight();

			// Output a hidden field
			if($ret == true)
			{
				echo '<input type="hidden" name="inpost_can_use" id="inpost_can_use" value="1">';
			}
			else
			{
				echo '<input type="hidden" name="inpost_can_use" id="inpost_can_use" value="0">';
			}

		}

		// in javascript, object properties are accessed as
		// ajax_object.ajax_url, ajax_object.we_value
		//wp_localize_script( 'checkout_js', 'ajax_object',
		//            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );	
		}

	add_action('wp_enqueue_scripts', 'add_my_js');

	add_action( 'woocommerce_shipping_init', 'inpost_shipping_init' );
 
	function inpost_shipping( $methods )
	{
		$methods[] = 'WC_InPostShippingMethod';
		return $methods;
	}
 
	add_filter( 'woocommerce_shipping_methods', 'inpost_shipping' );
}

///
// add the Ajax callback actions
//
add_action( 'wp_ajax_inpost_get_machines', 'get_machines');
add_action( 'wp_ajax_nopriv_inpost_get_machines', 'get_machines');

	function get_machines()
	{
		echo json_encode(false);

		die(); // Required
	}

	///
	// check_weight function
	//
	// @return true or false.
	//
	function check_weight()
	{
		// Defaults used at the end.
		$parcelSize = 'A';
		$is_dimension = true;

		// Get the shipping method's configuration data.
		$config_data = get_option('woocommerce_inpost_shipping_method_settings');

		// Read the maximum weight
		$maxWeightFromConfig = (float)strtolower(trim($config_data['max_weight']));

		// Process the various possible product sizes.
		$maxDimensionFromConfigSizeA = explode('x',
			strtolower(trim($config_data['max_sizea'])));
		$maxWidthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[0]);
		$maxHeightFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[1]);
		$maxDepthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[2]);
    
		// flattening to one dimension
		$maxSumDimensionFromConfigSizeA = $maxWidthFromConfigSizeA +
			$maxHeightFromConfigSizeA + $maxDepthFromConfigSizeA;

		$maxDimensionFromConfigSizeB = explode('x', strtolower(trim($config_data['max_sizeb'])));
		$maxWidthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[0]);
		$maxHeightFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[1]);
		$maxDepthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[2]);
		// flattening to one dimension
		$maxSumDimensionFromConfigSizeB = $maxWidthFromConfigSizeB +
			$maxHeightFromConfigSizeB + $maxDepthFromConfigSizeB;
		
		$maxDimensionFromConfigSizeC = explode('x', strtolower(trim($config_data['max_sizec'])));
		$maxWidthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[0]);
		$maxHeightFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[1]);
		$maxDepthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[2]);
		
		// flattening to one dimension
		$maxSumDimensionFromConfigSizeC = $maxWidthFromConfigSizeC +
			$maxHeightFromConfigSizeC + $maxDepthFromConfigSizeC;

		// Check if any of the dimensions are not set up correctly.
		if($maxWidthFromConfigSizeA == 0 ||
			$maxHeightFromConfigSizeA == 0 ||
		       	$maxDepthFromConfigSizeA  == 0 ||
			$maxWidthFromConfigSizeB  == 0 ||
			$maxHeightFromConfigSizeB == 0 ||
			$maxDepthFromConfigSizeB  == 0 ||
			$maxWidthFromConfigSizeC  == 0 ||
			$maxHeightFromConfigSizeC == 0 ||
			$maxDepthFromConfigSizeC  == 0)
		{
			// bad format in admin configuration
			$is_dimension = false;
		}
    
		$maxSumDimensionsFromProducts = 0;

		// Go through the products and check their dimensions and
		// weights.
		// size=10 x 20 x 10 cm weight=.32
		foreach ( WC()->cart->get_cart() as $item_id => $values )
		{
			$_product = $values['data'];

			if ( $values['quantity'] > 0 && $_product->needs_shipping() )
			{
				error_log('item id=' . $item_id .
					' item size=' . $_product->get_dimensions() .
					' weight='. $_product->get_weight() .
			       	' quant=' .  $values['quantity'] );

				$dimension = explode(' ', $_product->get_dimensions());

				$width  = trim(@$dimension[0]);
				$height = trim(@$dimension[2]);
				$depth  = trim(@$dimension[4]);

				error_log('width=' . $width .
					' height=' . $height .
					' depth=' . $depth );

				if($width == 0 || $height == 0 || $depth == 0)
				{
					// empty dimension for product
					continue;
				}

				$calc_width  = $width  * $values['quantity'];
				$calc_height = $height * $values['quantity'];
				$calc_depth  = $depth  * $values['quantity'];

				error_log('cwidth=' . $calc_width .
					' cheight=' . $calc_height .
					' cdepth=' . $calc_depth );

				if( $calc_width > $maxWidthFromConfigSizeC ||
					$calc_height > $maxHeightFromConfigSizeC ||
					$calc_depth  > $maxDepthFromConfigSizeC)
				{
					error_log('setting to false.');

					$is_dimension = false;
				}
				$maxSumDimensionsFromProducts += $width + $height + $depth;
				if($maxSumDimensionsFromProducts > $maxSumDimensionFromConfigSizeC)
				{
					$is_dimension = false;
				}
				if((float)$_product->get_weight() > $maxWeightFromConfig)
				{
					$is_dimension = false;
				}
			}
		}
		
		if($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeA)
		{
			$parcelSize = 'A';
		}
		elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeB)
		{
			$parcelSize = 'B';
		}
		elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeC)
		{
			$parcelSize = 'C';
		}

		// Save the parcel size to the session for retreival later
		WC()->session->set('inpost_parcel_size', $parcelSize);

		//echo json_encode($is_dimension);
		return $is_dimension;
	}

add_action( 'wp_ajax_inpost_remove_inpost_shipping_method', 'remove_inpost_shipping_method');
add_action( 'wp_ajax_nopriv_inpost_remove_inpost_shipping_method', 'remove_inpost_shipping_method');

	///
	// remove_inpost_shipping_method function
	//
	// @return json
	//
	function remove_inpost_shipping_method()
	{
		error_log('Removing the shipping method.');

		//WC()->session->set('chosen_shipping_methods', ' ');
		//$_POST['shipping_method'] = '';
	}

// ------------------------- Checkout Functionality --------------------------

///
// Process the checkout
//
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process()
{
	global $woocommerce;
	// Check if set, if its not set add an error.

	//error_log('Post=' . json_encode($_POST));

	//error_log('Post shipping POS = ' . pos($_POST['shipping_method']));
	if(pos($_POST['shipping_method']) == 'inpost_shipping_method')
	{
		// The user has selected the InPost shipping method we must
		// verfiy that the Mobile and Locker ID are filled.
		error_log('Checking the two fields.');
		if (!$_POST['attributes']['inpost_dest_machine'])
		{
			wc_add_notice( __('Locker ID is a required field.'), 'error' );
		}
		if (!$_POST['attributes']['inpost_cust_mobile'])
		{
			wc_add_notice( __('Mobile is a required field.'), 'error' );
		}
	}
}

///
// Update the order meta with field value
//
add_action('woocommerce_checkout_update_order_meta',
	'my_custom_checkout_field_update_order_meta');

function my_custom_checkout_field_update_order_meta( $order_id )
{
	// We can also save the details into the InPost parcel table.
	global $wpdb;

	if(pos($_POST['shipping_method']) == 'inpost_shipping_method')
	{
		// The user has selected the InPost shipping method we must
		// verfiy that the Mobile and Locker ID are filled.
		error_log('Saving the two fields.');
		if ($_POST['attributes']['inpost_dest_machine'])
		{
			error_log('Saving the Locker ID.');
			update_post_meta( $order_id, 'Locker ID',
				esc_attr($_POST['attributes']['inpost_dest_machine']));
		}
		if ($_POST['attributes']['inpost_cust_mobile'])
		{
			error_log('Saving the InPost Mobile.');
			update_post_meta( $order_id, 'InPost Mobile',
				esc_attr($_POST['attributes']['inpost_cust_mobile']));
		}

		$parcel_size = WC()->session->get('inpost_parcel_size');
		update_post_meta( $order_id, '_inpost_parcel',
			esc_attr($parcel_size));

		$sql_data = array(
			'order_id'      => $order_id,
			'parcel_status' => 'Prepared',
			'parcel_target_machine_id' => $_POST['attributes']['inpost_dest_machine'],
			'api_source'    => 'UK',
			'variables'     =>  $_POST['attributes']['inpost_cust_mobile'] .
			':' . $parcel_size .
			':' . $_POST['billing_email'],
		);
		$wpdb->insert($wpdb->prefix . INPOST_TABLE_NAME, $sql_data);
	}
}

