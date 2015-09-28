=== InPost ===
Contributors: darthurinpost.co.uk
Donate link: http://inpost.co.uk
Tags: e-commerce, woo-commerce, shop, parcel, lockers, shipping
Requires at least: 3.7
Tested up to: 4.3.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Inpost is a free plugin that allows parcel creation and label printing for
delivery to an InPost locker.

== Description ==

Inpost has created this plugin to allow it's WooCommerce clients to manage the
packages their customers create. From registering the package as a parcel for
Inpost delivery to printing out the package labels it's all there.

== Installation ==

= Minimum Requirements =

* WordPress 3.8 or greater
* WooCommerce 2.1 or greater

1. Upload the folder 'inpost' to the '/wp-content/plugins/' directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Make sure that the pdf_files subfolder has 777 permisions.
1. Set up the InPost Shipping method.
1. If you have your product weight in anything other than kilograms you
**must** convert the maximum parcel weight from kg to your unit. E.g. 20,000 if you are using gramms.
1. If you are using a Barcode printer then pick that option on the InPost
shipping method options screen.

== Frequently Asked Questions ==

= Is this a stand alone plugin? =

No, the plugin requires WooCommerce.

= What is InPost =

InPost is a company that provides customers a means of delivering a parcel to 
one of our lockers for later collection. This removes the need for the 
customer to stay in and wait for a delivery person.

= What Else Is Required =

You will need to talk to a sales representitive to get an InPost Account. This
will allow you to connect to our servers for parcel and label creation.

Please call 033 033 52024 (UK only) or contact our sales team on
ecommerce.team@inpost.co.uk

== Screenshots ==

1. This shows the new fields that the customer is asked to fill in for an
InPost parcel delivery.

== Changelog ==

= 1.0.2 - 26/06/2014 =
* New Feature - Add the ability to select the type of printer that the
customer needs to print their labels on.

= 1.0.1 - 27/05/2014 =
* Fix - The PDF creation is changed to save to a local (server) file with a 
download instead of trying to do direct PDF page.
* Fix - The includes are found correctly.

= 1.0 =

* Created the plugin

== Upgrade Notice ==

= 1.0.4 =

The Woo Commerce plugin was updated and it caused some issues with the InPost
plugin. The maximum weight for parcel and the maximum size is adjusted.

= 1.0.3 =

Added the cURL option which allows us to send / receive REST API calls using
https.

= 1.0.2 =

Added the ability to select the kind of printer that the customer has for
printing out their labels on.

= 1.0.1 =

Changed the database table to allow the saving of the filename.

= 1.0 =

Created this initial version.

