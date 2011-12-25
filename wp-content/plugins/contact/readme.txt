=== Contact Details ===
Contributors: 36Flavours
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=HHRDCSQBLHFY4&lc=GB&item_name=Steve%20Whiteley%20%2836Flavours%29&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact, global, details, options, info, phone, fax, mobile, email, address, form
Requires at least: 2.8.2
Tested up to: 3.0.3
Stable tag: 0.7.1

Adds the ability to easily enter and display contact information.

== Description ==

Adds the ability to enter contact information and output the details in your posts, pages or templates.

Use the shortcode `[contact type="phone"]` to display any of the contact details, or use the function call `<?php if (function_exists('contact_detail')) { contact_detail('phone'); } ?>`.

Once you have defined a contact email address, use the shortcode `[contact type="form"]` to output the contact form.

== Installation ==

Here we go:

1. Upload the `contact` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Enter you contact details on the options page `Settings > Contact Details`.
4. Display the details using either the shortcodes or function calls.

== Frequently Asked Questions ==

= How do I edit my contact details? =

Navigate to the settings page by clicking on `Settings` on the left hand menu, and then the `Contact Details` option.

= How do I include details in my template? =

You can use the following function call to output details in your templates:

<?php if (function_exists('contact_detail')) { contact_detail('fax'); } ?>

= What contact details can I store? =

Current available contact fields are: `phone`, `fax`, `mobile`, `email` and `address`.

= How do you fetch contact details without outputting the value? =

The fourth parameter passed to `contact_detail()` determines whether the value is returned, by setting the value to false.

`$phone = contact_detail('phone', '<b>', '</b>', false);`

The above code will fetch the phone number stored and wrap the response in bold tags.

= How can I customise the contact form? =

If you require more customisation that cannot be achieved using CSS, you can define your own template file.

To do this add the the attribute `include` to the shortcode tag, e.g. `[contact type="form" include="myfile.php"]`.

This file should be placed within your theme directory and should include the processing and output of errors.

I suggest you use the `contact.php` file used by the plugin as a starting point / template.

== Screenshots ==

1. The contact details management page.

== Changelog ==

= 0.7.1 =
* Bug fix to shortcode function call that displays contact details.
= 0.7 =
* Integrated Akismet to check for SPAM submissions. (Requires Akismet Plugin) 
* Improved input / output escaping and added nonce field to contact form.
= 0.6 =
* Added functionality to include a website url as part of the email form.
* Submitted comments are now checked against the simple blacklist.
* Updated use of user levels (deprecated) to user roles and capabilities.
* Contact email address defaults to site admin email.
= 0.5 =
* Added ability to include custom form template.
= 0.4.3 =
* Added class names to contact form rows to allow easier customisation.
= 0.4.2 =
* Fixed PHP warning on settings page if no options existed.
= 0.4.1 =
* Fixed form input ids and labels.
= 0.4 =
* Added contact form.
= 0.3 =
* Fixed errors when error reporting is set to all.
* Added details screenshot.
= 0.2 =
* The function `contact_details` now outputs by default instead of having to echo the repsonse.
* Function calls now includes before, after and echo options.
= 0.1.1 =
* Updated/Corrected plugin name.
= 0.1 =
* This is the very first version.