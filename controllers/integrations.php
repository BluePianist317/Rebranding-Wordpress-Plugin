<?php
// manages integration settings, gives shortcodes etc
class BFTProIntegrations {
	// currently integrates with contact form 7
	static function contact_form() {
		global $wpdb;		
		
		// selected list_id - $_POST has priority
		$list_id = empty($_POST['list_id']) ? intval(@$_GET['id']) : intval($_POST['list_id']);
		
		$shortcode_atts = '';
		if(!empty($_POST['checked_by_default'])) {
			$shortcode_atts .= ' checked="true" ';
		}

      if(!empty($_POST['is_hidden'])) {
			$shortcode_atts .= ' hidden="true" ';
		}		
		
		if(!empty($_POST['required'])) {
			$shortcode_atts .= ' required="true" ';
		}
		
		if(!empty($_POST['classes'])) {
			$shortcode_atts .= ' css_classes="'.$_POST['classes'].'" ';
		}
		
		if(!empty($_POST['html_id'])) {
			$shortcode_atts .= ' html_id="'.$_POST['html_id'].'" ';
		}
		
		// change your-name and your-email to custom field names
		if(!empty($_POST['change_defaults'])) {
			update_option('bftpro_cf7_name_field', $_POST['cf7_name_field']);
			update_option('bftpro_cf7_email_field', $_POST['cf7_email_field']);
		}
		
		// select  mailing lists
		$lists = $wpdb->get_results("SELECT * FROM ".BFTPRO_LISTS." ORDER BY name"); 
		
		// selected mailing list
		$list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $list_id));
		
		// select custom fields in the selected mailing list
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list_id));
		
		// load default field names
		$custom_name_field_name = get_option('bftpro_cf7_name_field');
		$name_name = !empty($custom_name_field_name) ? $custom_name_field_name : 'your-name'; 
		$custom_email_field_name = get_option('bftpro_cf7_email_field');
		$email_name = !empty($custom_email_field_name) ? $custom_email_field_name : 'your-email';
		
		require(BFTPRO_PATH."/views/integration-contact-form.html.php");
	}
	
	// signup user from contact form 7 or jetpack
	// $data - $_POST data
	static function signup($data, $user) {
		global $wpdb;
		
		// now, as we have the user, let's subscribe them
		require_once(BFTPRO_PATH."/models/user.php");
		$_user = new BFTProUser();
	
		foreach($data['bftpro_integrated_lists'] as $l_id) {
			$vars = array("list_id"=>$l_id, "email"=>$user['email'], "name"=>$user['name'], "source"=>"contact form");
			
			// fill any required fields with "1" to avoid errors			
			$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $l_id));
			foreach($fields as $field) {
				$vars['field_'.$field->id] = empty($data['field_'.$field->id]) ? 1 : $data['field_'.$field->id];
			}
			
			// ignore exceptions
			try {
				$message = '';
				$_user->subscribe($vars, $message, true);
			}
			catch(Exception $e) {}
		}
	}
	
	// Ninja forms integration
	static function ninja() {
		global $wpdb;		
		
		// selected list_id - $_POST has priority
		$list_id = intval(@$_GET['id']);
		
		// select  mailing lists
		$lists = $wpdb->get_results("SELECT * FROM ".BFTPRO_LISTS." ORDER BY name");
		
		// select custom fields in the selected mailing list
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list_id));
		
		if(!empty($_POST['ok'])) {
			// save integration
			$integration = array("form_id" => $_POST['form_id'], "fields"=>array());
			$integration['fields']['email'] = $_POST['field_email'];
			$integration['fields']['name'] = $_POST['field_name'];
			$integration['fields']['checkbox'] = $_POST['field_checkbox'];
			
			foreach($fields as $field) {
				$integration['fields']['field_' . $field->id] = $_POST['field_'.$field->id];
			}
			
			$integration_str = serialize($integration);
			
			$wpdb->query($wpdb->prepare("UPDATE " . BFTPRO_LISTS . " SET
				ninja_integration=%s WHERE id=%d", $integration_str, $list_id));
		} 
		
		// selected mailing list
		$list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $list_id));
		
		// unserialize current integration
		$integration = unserialize(stripslashes($list->ninja_integration));
		
		// if another form is selected by post, it overwrites the integration
		$selected_form_id = 0;
		if(!empty($integration['form_id'])) $selected_form_id = $integration['form_id'];
		if(isset($_POST['form_id'])) $selected_form_id = $_POST['form_id'];
		
		
		// select ninja forms
		$forms = ninja_forms_get_all_forms();		
		
		// form selected? get fields
		$ninja_fields = array();		
		if($selected_form_id) {
			$ninja_fields = ninja_forms_get_fields_by_form_id( $selected_form_id );			
		}
		// print_r($ninja_fields);
		require(BFTPRO_PATH."/views/integration-ninja-form.html.php");
	}
	
	
	// integrate ninja form signup
	static function ninja_signup($submission_id) {
		global  $wpdb;	
		$form_id = get_post_meta($submission_id, '_form_id', true);	
		$ninja_fields = ninja_forms_get_fields_by_form_id( $form_id );	
		$post_fields = json_decode(stripslashes($_POST['formData']));	
		$post_fields = $post_fields->fields;
	
		if( !is_array( $ninja_fields ) ) return false;
		
		require_once(BFTPRO_PATH."/models/user.php");
		$_user = new BFTProUser();
		
		$lists = $wpdb->get_results("SELECT id, ninja_integration FROM ".BFTPRO_LISTS." ORDER BY id");
		
		foreach($lists as $list) {
			//  is it integrated?
			$integration = unserialize($list->ninja_integration);
			if(empty($integration['form_id']) or $integration['form_id'] != $form_id) continue;
			
			// is checkbox required?
			if(!empty($integration['fields']['checkbox'])) {
				$integrate = false;
				foreach($ninja_fields as $field) {
					if($field['id'] == $integration['fields']['checkbox']) {													 
						 foreach($post_fields as $post_field) {					 	
							 if($post_field->id == $field['id'] and !empty($post_field->value))	$integrate = true;
						 }					 
					}
				}
				if(!$integrate) return false;			
			}
			
			$vars = array("list_id"=> $list->id, "source" => 'ninja_form');	
			$email = $name = '';	
			foreach($integration['fields'] as $key => $field_id) {
				
				// fill email
				if($key == 'email') {
					foreach($ninja_fields as $field) {
						if($field['id'] == $field_id) {						
							foreach($post_fields as $post_field) {					 	
							 	if($post_field->id == $field['id'] and !empty($post_field->value)) $vars['email'] = $post_field->value;
						 	}
						} // end if field found
					} // end foreach ninja field
				} // end email
				
				// fill name
				if($key == 'name') {
					foreach($ninja_fields as $field) {
						if($field['id'] == $field_id) {					
							foreach($post_fields as $post_field) {					 	
							 	if($post_field->id == $field['id'] and !empty($post_field->value)) $vars['name'] = $post_field->value;
						 	}
						} // end if field found
					} // end foreach ninja field
				}
				
				// skip name, email and checkbox at this point
				if($key == 'name' or $key == 'email' or $key == 'checkbox') continue;
				
				// fill other fields
				foreach($ninja_fields as $field) {					
					foreach($ninja_fields as $field) {
						if($field['id'] == $field_id) {					
							foreach($post_fields as $post_field) {					 	
							 	if($post_field->id == $field['id'] and !empty($post_field->value)) $vars[$key] = $post_field->value;
						 	}
						} // end if field found
					} // end foreach ninja field
				}
			}
			
			// subscribe
			if(empty($vars['email'])) continue;
			
			// ignore exceptions
			try {
				$message = '';
				$_user->subscribe($vars, $message, true);
			}
			catch(Exception $e) {}
			
		}
	} // end ninja_signup
	
	// Caldera forms integration
	static function caldera() {
		global $wpdb;		
		
		// selected list_id - $_POST has priority
		$list_id = intval(@$_GET['id']);
		
		// select  mailing lists
		$lists = $wpdb->get_results("SELECT * FROM ".BFTPRO_LISTS." ORDER BY name");
		
		// select custom fields in the selected mailing list
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list_id));
		
		if(!empty($_POST['ok'])) {
			// save integration
			$integration = array("form_id" => $_POST['form_id'], "fields"=>array());
			$integration['fields']['email'] = $_POST['field_email'];
			$integration['fields']['name'] = $_POST['field_name'];
			$integration['fields']['checkbox'] = $_POST['field_checkbox'];
			
			foreach($fields as $field) {
				$integration['fields']['field_' . $field->id] = $_POST['field_'.$field->id];
			}
			
			$integration_str = serialize($integration);
			
			$wpdb->query($wpdb->prepare("UPDATE " . BFTPRO_LISTS . " SET
				caldera_integration=%s WHERE id=%d", $integration_str, $list_id));
		} 
		
		// selected mailing list
		$list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $list_id));
		
		// unserialize current integration
		$integration = unserialize(stripslashes($list->caldera_integration));
		
		// if another form is selected by post, it overwrites the integration
		
		$selected_form_id = 0;
		if(!empty($integration['form_id'])) $selected_form_id = $integration['form_id'];
		if(isset($_POST['form_id'])) $selected_form_id = $_POST['form_id'];
		
		
		// select caldera forms
		$forms = Caldera_Forms_Forms::get_forms( true );

		// form selected? get fields
		if($selected_form_id != '') {
			$form = Caldera_Forms_Forms::get_form( $selected_form_id );
			$caldera_fields = $form['fields'];	
		}
		
		// print_r($ninja_fields);
		require(BFTPRO_PATH."/views/integration-caldera-form.html.php");
	} // end caldera forms setup
	
	// integrate ninja form signup
	static function caldera_signup($form) {
		global  $wpdb;	
		
		//put form field data into an array $data
	    $data= array();
	    foreach( $form[ 'fields' ] as $field_id => $field){
	        $data[ $field_id ] = Caldera_Forms::get_field_data( $field_id, $form );
	    }
		
		require_once(BFTPRO_PATH."/models/user.php");
		$_user = new BFTProUser();
		
		$lists = $wpdb->get_results("SELECT id, caldera_integration FROM ".BFTPRO_LISTS." ORDER BY id");
		
		foreach($lists as $list) {
			//  is it integrated?
			$integration = unserialize($list->caldera_integration);
			if(empty($integration['form_id']) or $integration['form_id'] != $form['ID']) continue;
			
			// is checkbox required?
			if(!empty($integration['fields']['checkbox'])) {
				$integrate = false;
				foreach($form[ 'fields' ] as $id => $field) {
					if($id == $integration['fields']['checkbox']) {													 
						 foreach($data as $key => $d) {					 	
							 if($key == $id and !empty($d))	$integrate = true;
						 }					 
					}
				}
				if(!$integrate) return false;			
			}
			
			$vars = array("list_id"=> $list->id, "source" => 'caldera_form');	
			$email = $name = '';	
			foreach($integration['fields'] as $key => $field_id) {
				
				// fill email
				if($key == 'email') {
					foreach($form[ 'fields' ] as $field) {
						if($field['ID'] == $field_id) {						
							foreach($data as $k => $d) {					 	
							 	if($k == $field['ID'] and !empty($d)) $vars['email'] = $d;
						 	}
						} // end if field found
					} // end foreach caldera field
				} // end email
				
				// fill name
				if($key == 'name') {
					foreach($form[ 'fields' ] as $field) {
						if($field['ID'] == $field_id) {						
							foreach($data as $k => $d) {					 	
							 	if($k == $field['ID'] and !empty($d)) $vars['name'] = $d;
						 	}
						} // end if field found
					} // end foreach caldera field
				}
				
				// skip name, email and checkbox at this point
				if($key == 'name' or $key == 'email' or $key == 'checkbox') continue;
				
				// fill other fields
				foreach($form[ 'fields' ] as $field) {
						if($field['ID'] == $field_id) {					
							foreach($data as $k => $d) {					 	
							 	if($k == $field['ID'] and !empty($d)) $vars[$key] = $d;
						 	}
						} // end if field found
				} // end foreach ninja field
				
			}
			
			// subscribe
			if(empty($vars['email'])) continue;
			
			// ignore exceptions
			try {
				$message = '';
				$_user->subscribe($vars, $message, true);
			}
			catch(Exception $e) {}
			
		}
	} // end caldera_signup
	
}