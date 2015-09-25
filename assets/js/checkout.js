///
// Checkout.js file
//

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

	var html = '';

  	html += '<table id="li_machine" border="0px">';
  
              		html += '<tr>';
                  	html += '<td>';
                    	html += '<label for="terminal" >Select a Locker: </label><br>';
              			html += '<div align="center">';
                    	html += '<script type="text/javascript" src="https://geowidget.inpost.co.uk/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function"></script>            ';
                    	html += '<a href="#" onclick="openMap(); return false;"> <span style="font-size:15px; color:blue;">';
		       html += '<img src="';
		       html+= base_url + 'wp-content/plugins/inpost/assets/images/map_icon.png"  alt="InPost 24/7 Parcel Lockers" title="InPost 24/7 Parcel Lockers" </span></a>';
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
              	html += '</table>';
	//jQuery('#shipping_method').append(html);
	jQuery('.woocommerce-billing-fields').append(html);
}

jQuery(document).ready(function() {

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
		remove_inpost_fields();

	}
	else
	{
		var inpost = jQuery('#shipping_method_0_inpost_shipping_method').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
		else
		{
			remove_inpost_fields();
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
		remove_inpost_fields();

	}
	else
	{
		var inpost = jQuery('#shipping_method_0_inpost_shipping_method').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
		else
		{
			remove_inpost_fields();
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
		remove_inpost_fields();

	}
	else
	{
		var inpost = jQuery('#shipping_method_0_inpost_shipping_method').prop('checked');
		if(inpost == true)
		{
			alert("Please select a Locker to send the Parcel to.");
			add_inpost_fields();
		}
		else
		{
			remove_inpost_fields();
		}
	}

	});
});

///
// remove_inpost_fields function
//
// @params none
// @return none
//
function remove_inpost_fields()
{
	// Check that the fields are still there before trying to remove them.
	var field = jQuery('#mobile').get();

	if(field == '')
	{
		return;
	}

	jQuery('#li_machine').remove();
}
