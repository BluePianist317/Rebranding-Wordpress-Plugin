<?php
// integration with contact form 7
class BFTProContactForm7 {
	static function signup($contactform) {
		global $wpdb;
		
		$data = $_POST;

		if(empty($data['bftpro_integrated_lists'])) return true;
		
		// field names default to 'your-name' and 'your-email' but user can change them.
		// in such case they need to setup this in the integrations page
		$custom_name_field_name = get_option('bftpro_cf7_name_field');
		$name_name = !empty($custom_name_field_name) ? $custom_name_field_name : 'your-name'; 
		$custom_email_field_name = get_option('bftpro_cf7_email_field');
		$email_name = !empty($custom_email_field_name) ? $custom_email_field_name : 'your-email';
		
		// there are some lists, so let's get user data		
		$user = array('email' => "", "name"=>"");	
		$user['email'] = !empty( $data[$email_name] ) ? trim( $data[$email_name] ) : '';
	   $user['name'] = !empty( $data[$name_name] ) ? trim( $data[$name_name] ) : '';
	   
		if ( !empty( $data['your-first-name'] ) and !empty( $data['your-last-name'] ) ) {
			$user['name'] = trim( $data['your-first-name']).' '.trim($data['your-last-name']) ;
		}
		
		BFTProIntegrations :: signup($data, $user);		
	} // end signup
	
	static function shortcode_filter($form) {
		return do_shortcode( $form );
	}
	
	// apply filters to the CF7 message so mailing list fields are replaced with their values
	static function message_filter($components, $form, $cf7) {
		if(empty($_POST['bftpro_integrated_lists']) or !is_array($_POST['bftpro_integrated_lists'])) return $components;
		
		// go through all list checkboxes and replace the shortcodes
		foreach($_POST['bftpro_integrated_lists'] as $l_id) {
			$components['body'] = str_replace('[bftpro-int-chk list_id="'.$l_id.'"]', __('Yes', 'bftpro'), $components['body']);
		}
		
		// now if there are any left unchecked, replace with No
		$components['body'] = preg_replace('/\[bftpro-int-chk list_id=\"\d\"\]/', __('No', 'bftpro'), $components['body']);
		return $components;
	}
}