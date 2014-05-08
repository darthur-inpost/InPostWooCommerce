/*
///
// Checkout.js file
//

///
// do_processing function
//
function do_processing()
{
	// Check that a Locker has been selected.
	var name = jQuery('#name').val();
	if(name == '')
	{
		alert('Please click on MAP to pick a Locker location.');
		var aTag = jQuery("input[id='name']");
		jQuery('html,body').animate({scrollTop: aTag.offset().top},'slow');
		jQuery('#name').focus();
		return false;
	}

	// Check that the mobile number is entered.
	var mobile = jQuery('#mobile').val();
	if(mobile == '')
	{
		alert('Please enter a mobile phone number\n(remove the 07)');
		var aTag = jQuery("input[id='mobile']");
		jQuery('html,body').animate({scrollTop: aTag.offset().top},'slow');
		jQuery('#mobile').focus();
		return false;
	}

	var city = '';
	// We must send the details to the controller. Somehow.
	var data = { 
		action : 'inpost_get_machines',
		dataType: 'json',
		city : ajax_object.we_value
	};

	jQuery.post( ajax_object.ajax_url, data, function(response) {
		alert('in here ' + response);
	});

}

*/
///
// user_function function
//
// @param value mixed string
// @return none
//
function user_function(value)
{
        var address = value.split(';');
        document.getElementById('town').value=address[1];
        document.getElementById('street').value=address[2]+address[3];
}

///
// add_inpost_fields function
//
// @params none
// @return none
//
function add_inpost_fields()
{
	var base_url = document.location.origin + document.location.pathname;

	var html = '<li id="li_machine">'

  	html += '<table border="0px">';
  
              		html += '<tr>';
                  	html += '<td>';
                    	html += '<label for="terminal" >Select a Locker: </label><br>';
              			html += '<div align="center">';
                    	html += '<script type="text/javascript" src="https://geowidget.inpost.co.uk/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function"></script>            ';
                    	html += '<a href="#" onclick="openMap(); return false;"> <span style="font-size:15px; color:blue;">';
		       html += '<img src="';
		       html+= base_url + 'wp-content/plugins/woocommerce-inpost-shipping-plugin/assets/images/map_icon.png"  alt="InPost 24/7 Parcel Lockers" title="InPost 24/7 Parcel Lockers" </span></a>';
                    	html += '</div>';
                    	html += '</td>';
                    	html += '</tr>';
                    	html += '<tr>';
                  	html += '<td>';
                    	html += '<label for="name" ><span style="color:grey;">Locker ID:</span> </label><br><input placeholder="Select from MAP" style="color:grey;" type="text" size="15" id="name" name="attributes[inpost_dest_machine]">';
                  	html += '<input type="hidden" id="address" name="address">';
                  	html += '<input type="hidden" style="color:grey;" type="text" size="30" id="street" name="attributes[inpost_terminal_street]">';
                  	html += '<input type="hidden" style="color:grey;" type="text" size="20" id="town" name="attributes[inpost_terminal_town]">';
                  	html += '</td>';
                  	html += '</tr>';
                  	html += '<tr>';
                  	html += '<td>';
                  		html += '<label for="mobile" >Enter Mobile (07): </label><input type="text" size="10" maxlength="9" id="mobile" name="attributes[inpost_cust_mobile]">';
                  	html += '</td>';
                  	html += '</tr>';
			// Email is already on the form.
                  	//html += '<tr>';
                  	//html += '<td>';
                  	//html += '<label for="email" >Enter Email: </label><input type="text" class="input-text" size="10" required="required" id="email" name="attributes[inpost_cust_email]">';
                  	//html += '</td>';
                  	//html += '</tr>';
              	html += '</table>';
	jQuery('#shipping_method').append(html);


	// Add my replacement button.
	//html = '<input type="button" name="inpost_submit" id="inpost_submit" ';
	//html += 'value="Place Order" data-value="Place Order" class="button alt " ';
	//html += 'onclick="do_processing();" >';

	//jQuery('#place_order').after(html);

	// Remove the WooCommerce Place Order button with my own.
	//jQuery('#place_order').remove();

}

/*

///
// remove_inpost_fields function
//
// @params none
// @return none
//
function remove_inpost_fields()
{
	alert('Removing InPost fields.');
	jQuery('#shipping_method').remove('#li_machine');
	//jQuery('#shipping_method').remove('#shipping_method_0_inpost_shipping_method');
	jQuery('#shipping_method_0_inpost_shipping_method').prop('checked',
			false);
	jQuery('#shipping_method_0_inpost_shipping_method').prop('disabled',
			true);
	//jQuery('#shipping_method_0_inpost_shipping_method').remove();
	//jQuery('label[for=shipping_method_0_inpost_shipping_method]').remove();

	//jQuery('.shipping_method').add('required', 'required');

	var data = {
		action : 'inpost_remove_inpost_shipping_method',
		dataType: 'json'
	};

	//jQuery.post( ajax_object.ajax_url, data, function(response) {
		// Don't really care what the response is.
	//});
}

*/

jQuery(document).ready(function() {

	//jQuery.getScript("https://geowidget.inpost.co.uk/dropdown.php?dropdown_name=machine", function() { alert('Script Loaded.'); });

	// wc_cart_params is required to continue, ensure the object exists
	if ( typeof wc_add_to_cart_params === 'undefined' )
	{
		alert('Parameter NOT defined');
		return false;
	}

	var use_inpost = jQuery('#inpost_can_use').val();

	if(use_inpost == '0')
	{
		alert("The parcel is too large for InPost");

		jQuery('#shipping_method_0_inpost_shipping_method').prop('disabled',
			true);

	}
	else
	{
		var inpost = jQuery('#shipping_method_0_inpost_shipping_method').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
	}

	jQuery('#billing_phone').change(function() {
		// The user has changed the city and the Ajax call has removed
		// our new fields.

	var use_inpost = jQuery('#inpost_can_use').val();

	if(use_inpost == '0')
	{
		alert("The parcel is too large for InPost");

		jQuery('#shipping_method_0_inpost_shipping_method').prop('disabled',
			true);

	}
	else
	{
		var inpost = jQuery('#shipping_method_0_inpost_shipping_method').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
	}

	});
});

jQuery( function( $ ) {
	// Shipping calculator
	$( document ).on( 'change', 'select.shipping_method, input[name^=shipping_method]', function() {
	var use_inpost = jQuery('#inpost_can_use').val();

	if(use_inpost == '0')
	{
		alert("The parcel is too large for InPost");

		jQuery('#shipping_method_0_inpost_shipping_method').prop('disabled',
			true);

	}
	else
	{
		var inpost = jQuery('#shipping_method_0_inpost_shipping_method').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
	}

	});
});

