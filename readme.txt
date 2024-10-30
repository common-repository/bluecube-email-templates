=== Blue Cube Email Templates ===
Contributors: thebluecube
Donate link:
Tags: email, template, email template
Requires at least: 4.4
Tested up to: 4.4.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides an email template system for developers to send programmatic emails which their clients can easily update the emails content.

== Description ==

This plugin is intended to be useful more for developers than other types of users.

Have you ever had a client who likes to be able to independently update the content of the emails that your code is sending out programmatically? If so, this plugin might help you make your client happy.

How to use:

When you activate the plugin, it adds a custom post type to your website called "Email Templates". You can create your templates using this custom post type. Templates can contain placeholders for your code to fill them up with desired values. A placeholder sample would be {USERNAME} or {USER_EMAIL}. Please note, placeholders should not contain lowercase letters.

This plugin creates a global instance of the BlueCube\Email_Template class which is called $bluecube_email_template. Once you have created a template, you can use the sendEmail() method of the instance to send out your emails.

`$email_args = array(
	'template_title' 	=> 'your-template-title',
	'to' 				=> $email,
	'variables' 		=> array('username' => 'JohnDoe', 'user_email' => 'test@test.com'),
);
$bluecube_email_template->sendEmail($email_args);`



== Installation ==

Download and unzip the plugin. Then upload the `"bluecube-email-templates"` folder to the `/wp-content/plugins/` directory (or the relevant directory if your WordPress file structre is different), or you can simply install it using WordPress plugin installer.

== Frequently asked questions ==


== Screenshots ==


== Changelog ==

= 1.0 =
* This is the first version of the plugin, providing a very simple template system

== Upgrade notice ==
