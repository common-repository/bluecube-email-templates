<?php

namespace BlueCube;


class Email_Template {

	public $template_id;
	public $template_content;
	public $email_content;
	public $email_subject;

	const TEMPLATE_POST_TYPE = 'bc-email-template';


	public function __construct() {

		// Create custom post types
		add_action('init', array($this, 'addCustomPostTypes'));

		// Create and save post meta boxes
		add_action( 'add_meta_boxes', array($this, 'addMetaBoxes') );
		add_action( 'save_post_'.self::TEMPLATE_POST_TYPE, array($this, 'saveSettingsMetaData'), 2, 2);
	}


	protected function startsWith($haystack, $needle) {

		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}


	/**
	 * To insert a wpnonce hidden field into the forms like post meta data
	 */
	public function printNonceInput() {

		// Noncename needed to verify where the data originated
		$name = 'bluecube_email_templates_meta_noncename';
		$nonce = wp_create_nonce( plugin_basename(__FILE__) );

		echo '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$nonce.'" />';
	}


	public function addCustomPostTypes() {

		// Register Custom Post types
		$email_template_labels = array(
			'name' => 'Email Templates',
			'singular_name' => 'Email Template',
			'add_new'	=> 'Add New Template',
			'add_new_item'	=> 'Add New Template',
			'edit_item'	=> 'Edit Email Template',
			'new_item'	=> 'New Email Template',
			'view_item'	=> 'View Email Template',
			'search_items'	=> 'Search Email Templates',
			'not_found'	=>	'No email templates found',
			'not_found_in_trash'	=>	'No email templates found in Trash',
		);
		register_post_type( self::TEMPLATE_POST_TYPE, array(
			'labels' => $email_template_labels,
			'description' => 'Email Template',
			'public' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_in_nav_menus' => false,
			'has_archive' => true,
			'hierarchical'	=> false,
			'rewrite'	=> array('slug' => 'email-templates', 'with_front' => true),
			'menu_position' => 5,
			'menu_icon'	=> 'dashicons-email-alt',
			'supports' => array ('title', 'editor'),
		));
	}


	public function addMetaBoxes() {

		add_meta_box('bc_email_template_settings', 'Settings', array($this, 'showSettingsMetaBoxes'), self::TEMPLATE_POST_TYPE, 'side', 'default');
	}


	public function showSettingsMetaBoxes() {

		global $post;

		$this->printNonceInput();

		$settings = get_post_meta($post->ID, 'bc_email_template_settings', true);
		$settings = unserialize($settings);

		$from = (!empty($settings['bc_email_template_from'])) ? $settings['bc_email_template_from'] : '';
		$subject = (!empty($settings['bc_email_template_subject'])) ? $settings['bc_email_template_subject'] : '';
		$sender_name = (!empty($settings['bc_email_template_sender_name'])) ? $settings['bc_email_template_sender_name'] : '';

		$html = '';

		$html .= '<p></p><label for="bc_email_template_from">From Address:</label><br>';
		$html .= '<input type="text" name="bc_email_template_from" class="widefat" value="'.$from.'"></p>';

		$html .= '<p></p><label for="bc_email_template_subject">Email Subject:</label><br>';
		$html .= '<input type="text" name="bc_email_template_subject" class="widefat" value="'.$subject.'"></p>';

		$html .= '<p></p><label for="bc_email_template_sender_name">Sender Name:</label><br>';
		$html .= '<input type="text" name="bc_email_template_sender_name" class="widefat" value="'.$sender_name.'"></p>';

		echo $html;
	}


	function saveSettingsMetaData($post_id, $post) {

		if ($_POST) {

			// Don't store custom data twice
			if( $post->post_type == 'revision' )
				return false;

			// verify this came from our screen and with proper authorization
			if (!isset($_POST['bluecube_email_templates_meta_noncename']) || !wp_verify_nonce( $_POST['bluecube_email_templates_meta_noncename'], plugin_basename(__FILE__) )) {
				return $post->ID;
			}

			// Is the user allowed to edit the post or page?
			if ( !current_user_can( 'edit_post', $post->ID ))
				return $post->ID;

			foreach ($_POST as $key => $value) {
				if ( $this->startsWith($key, 'bc_email_template') ) {
					$metadata[$key] = $value;
				}
			}

			$metadata = serialize($metadata);

			if(get_post_meta($post->ID, 'bc_email_template_settings', true)) {
				update_post_meta($post->ID, 'bc_email_template_settings', $metadata);
			} else {
				add_post_meta($post->ID, 'bc_email_template_settings', $metadata);
			}
		}
	}


	public function getTemplateContent($title) {

		$post = get_page_by_title( $title, OBJECT, self::TEMPLATE_POST_TYPE );
		if (is_null($post))
			return false;

		$template_content = wpautop( $post->post_content );
		$this->template_id = $post->ID;
		$this->template_content = $template_content;
		$this->email_content = $template_content;
		return true;
	}


	public function renderEmailContent($vars = array()) {

		$template_content = $this->template_content;

		foreach($vars as $key => $value){
			$template_content = str_replace('{'.strtoupper($key).'}', $value, $template_content);
		}

		$this->email_content = $template_content;
	}


	public function sendEmail($args = array()) {

		if ( empty($args) || empty($args['template_title']) || empty($args['to']) ) {
			die('Invalid args');
		}

		if ( !$this->getTemplateContent($args['template_title']) ) {
			return false;
		}
		$this->renderEmailContent($args['variables']);

		// Getting the template settings (metadata)
		$settings = get_post_meta($this->template_id, 'bc_email_template_settings', true);
		$settings = unserialize($settings);

		// Set the email subject
		$this->email_subject = $settings['bc_email_template_subject'];

		// Defining the sender name
		if ( !empty($args['sender_name']) )
			$sender_name = $args['sender_name'];
		if ( empty($sender_name) && !empty($settings['bc_email_template_sender_name']) )
			$sender_name = $settings['bc_email_template_sender_name'];
		if ( empty($sender_name) )
			$sender_name = get_option('blogname');
		// END: Defining the sender name

		// Defining the 'from' email address
		if ( !empty($args['from']) )
			$from = $args['from'];
		if ( empty($from) && !empty($settings['bc_email_template_from']) )
			$from = $settings['bc_email_template_from'];
		if ( empty($from) )
			$from = get_option('admin_email');
		// END: Defining the 'from' email address

		$headers  = '';
		$headers .= "From: $sender_name <$from>\r\n";
		$headers .= "Reply-To: $from\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		return \wp_mail($args['to'], $this->email_subject, $this->email_content, $headers);
	}

}
