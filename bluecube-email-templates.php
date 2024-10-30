<?php

/**
 * Plugin Name: BlueCube Email Templates
 * Plugin URI: https://wp.me/p77SoR-6n
 * Description: This plugin adds an email template system to your website so you can use them for programmatic email sending
 * Author: The Blue Cube
 * Version: 1.0
 * Author URI: https://thebluecube.com
 * License: GPLv2 or later
 */


require_once __DIR__ . '/class/Email_Template.php';

$bluecube_email_template = new BlueCube\Email_Template();

