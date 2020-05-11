<?php
class BFTProList {
	// select mailing lists along with num ARs and num subscribers
	function select($id=0) {
		global $wpdb, $user_ID;		
		$multiuser_access = 'all';
		// check if we can use all lists but don't exit because this function is also used by newsletters for example
	   $multiuser_access = BFTProRoles::check_access('lists_access', true);
		
		$id_sql="";
		if($id) {			
			$id_sql=$wpdb->prepare(" AND id=%d ", $id);
		}		
		
		$own_sql = '';
		if($multiuser_access == 'own') {
			$own_sql = $wpdb->prepare(' AND editor_id=%d ', $user_ID);
		}		
		
		$lists = $wpdb->get_results("SELECT tL.*, (SELECT COUNT(tA.id) FROM ".BFTPRO_ARS." tA WHERE list_ids LIKE CONCAT('%|', tL.id, '|%')) 
			as responders, (SELECT COUNT(tU.id) FROM ".BFTPRO_USERS." tU WHERE tU.list_id=tL.id) as subscribers,
			(SELECT COUNT(tU2.id) FROM ".BFTPRO_USERS." tU2 WHERE tU2.list_id=tL.id AND status=1) as active_subscribers,
			(SELECT COUNT(tUN.id) FROM ".BFTPRO_UNSUBS." tUN WHERE tUN.list_id=tL.id) as num_unsubscribed
			FROM ".BFTPRO_LISTS." tL WHERE id>0 $id_sql $own_sql ORDER BY name");
		
		if($id) return @$lists[0];	
		
		// figure out % unsubscribed
		foreach($lists as $cnt => $list) {
			// depending on the setting "unsubscribe action" we delete either on num subscriebrs or subscribers + unsubscribed
			if(get_option('bftpro_unsubscribe_action') == 'delete') $var = $list->subscribers + $list->num_unsubscribed;
			else $var = $list->subscribers;		
			
			$percent = $var ? round(100 * $list->num_unsubscribed / $var) : 0;
			
			$lists[$cnt]->percent_unsubscribed = $percent;
		}
			
		return $lists;
	}
	
	// adds a mailing list
	function add($vars) {
		global $wpdb, $user_ID;
		$this->prepare_vars($vars);
		
		// list name already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_LISTS." WHERE name=%s", $vars['name']));
		if($exists) throw new Exception(__('Another mailing list with this name already exists', 'bftpro'));
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_LISTS." SET
		name=%s, description=%s, date=%s, do_notify=%d, notify_email=%s, redirect_to=%s, redirect_confirm=%s, 
		unsubscribe_notify=%d, confirm_email_subject=%s, confirm_email_content=%s, unsubscribe_text=%s, require_recaptcha=%d,
		optin=%d, sender=%s, require_name=%d, auto_subscribe=%s, require_text_captcha=%d, 
		subscribe_to_blog=%d, signup_graphic=%s, editor_id=%d, auto_subscribe_role=%s, 
		subscribe_to_blog_role=%s, auto_subscribe_on_signup=%d, unsubscribe_text_clickable=%s, unsubscribe_redirect=%s,
		notify_signup_subject=%s, notify_signup_message=%s, redirect_to_prepend_uid=%d, redirect_confirm_prepend_uid=%d,
		no_unsubscribe_link=%d, redirect_duplicate=%s, woo_products=%s, woo_products_unsub=%s", 
		$vars['name'], $vars['description'], date("Y-m-d"), $vars['do_notify'], 
		$vars['notify_email'], $vars['redirect_to'], $vars['redirect_confirm'], $vars['unsubscribe_notify'], 
		$vars['confirm_email_subject'], $vars['confirm_email_content'], $vars['unsubscribe_text'], 
		$vars['require_recaptcha'], $vars['optin'], $vars['sender'], $vars['require_name'], $vars['auto_subscribe'], 
		$vars['require_text_captcha'], $vars['subscribe_to_blog'], $vars['signup_graphic'], $user_ID,
		$vars['auto_subscribe_role'], $vars['subscribe_to_blog_role'], $vars['auto_subscribe_on_signup'], 
		$vars['unsubscribe_text_clickable'], $vars['unsubscribe_redirect'], $vars['notify_signup_subject'],
		$vars['notify_signup_message'], $vars['redirect_to_prepend_uid'], $vars['redirect_confirm_prepend_uid'], 
		$vars['no_unsubscribe_link'], $vars['redirect_duplicate'], $vars['woo_products'], $vars['woo_products_unsub']));
		
		return $wpdb->insert_id;
	}
	
	function save($vars, $id) {
		global $wpdb;
		$id = intval($id);
		$this->prepare_vars($vars);
		
		// list name already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_LISTS." WHERE name=%s AND id!=%d", $vars['name'], $id));
		if($exists) throw new Exception(__('Another mailing list with this name already exists', 'bftpro'));
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_LISTS." SET
			name=%s, description=%s, do_notify=%d, notify_email=%s, redirect_to=%s, redirect_confirm=%s, 
			unsubscribe_notify=%d, confirm_email_subject=%s, confirm_email_content=%s, unsubscribe_text=%s, 
			require_recaptcha=%d, optin=%d, sender=%s, require_name=%d, auto_subscribe=%s, 
			require_text_captcha=%d, subscribe_to_blog=%d, signup_graphic=%s, auto_subscribe_role=%s, 
			subscribe_to_blog_role=%s, auto_subscribe_on_signup=%d, unsubscribe_text_clickable=%s, unsubscribe_redirect=%s,
			notify_signup_subject=%s, notify_signup_message=%s, redirect_to_prepend_uid=%d, redirect_confirm_prepend_uid=%d,
			no_unsubscribe_link=%d, redirect_duplicate=%s, woo_products=%s, woo_products_unsub=%s
			WHERE id=%d", $vars['name'], $vars['description'], $vars['do_notify'], $vars['notify_email'],
			$vars['redirect_to'], $vars['redirect_confirm'], $vars['unsubscribe_notify'], $vars['confirm_email_subject'],
			$vars['confirm_email_content'], $vars['unsubscribe_text'], $vars['require_recaptcha'], 
			$vars['optin'], $vars['sender'], $vars['require_name'], $vars['auto_subscribe'], 
			$vars['require_text_captcha'], $vars['subscribe_to_blog'], $vars['signup_graphic'],
			$vars['auto_subscribe_role'], $vars['subscribe_to_blog_role'], $vars['auto_subscribe_on_signup'], 
			$vars['unsubscribe_text_clickable'], $vars['unsubscribe_redirect'],  $vars['notify_signup_subject'],
			$vars['notify_signup_message'], $vars['redirect_to_prepend_uid'], $vars['redirect_confirm_prepend_uid'], 
			$vars['no_unsubscribe_link'], $vars['redirect_duplicate'], $vars['woo_products'], $vars['woo_products_unsub'], $id));
			
		return true;	
	}
	
	// sanitize and prepare variables
	function prepare_vars(&$vars) {
	   $vars['name'] = sanitize_text_field(@$vars['name']);
	   $vars['sender'] = empty($vars['sender']) ? '' : bftpro_strip_tags($vars['sender']);
	   $vars['description'] = bftpro_strip_tags(@$vars['description']);
	   $vars['do_notify'] = empty($vars['do_notify']) ? 0 : 1;
	   $vars['notify_email'] = sanitize_text_field(@$vars['notify_email']);
	   $vars['redirect_to'] = esc_url_raw(@$vars['redirect_to']);
	   $vars['redirect_confirm'] = esc_url_raw(@$vars['redirect_confirm']);
	   $vars['redirect_duplicate'] = esc_url_raw(@$vars['redirect_duplicate']);
	   $vars['unsubscribe_notify'] = empty($vars['unsubscribe_notify']) ? 0 : 1;
      $vars['confirm_email_subject'] = sanitize_text_field(@$vars['confirm_email_subject']);
      $vars['confirm_email_content'] = empty($vars['confirm_email_content']) ? '' : bftpro_strip_tags($vars['confirm_email_content']);
      $vars['unsubscribe_text'] = bftpro_strip_tags(@$vars['unsubscribe_text']);
      $vars['require_recaptcha'] = empty($vars['require_recaptcha']) ? 0 : 1;
      $vars['optin'] = empty($vars['optin']) ? 0 : 1;
      $vars['require_name'] = empty($vars['require_name']) ? 0 : 1;
      $vars['auto_subscribe'] = sanitize_text_field(@$vars['auto_subscribe']);
      $vars['require_text_captcha'] = empty($vars['require_text_captcha']) ? 0 : 1;
      $vars['subscribe_to_blog'] = empty($vars['subscribe_to_blog']) ? 0 : 1;
      $vars['signup_graphic'] = sanitize_text_field(@$vars['signup_graphic']);
      $vars['auto_subscribe_role'] = empty($vars['auto_subscribe_role']) ? '' : sanitize_text_field($vars['auto_subscribe_role']);
      $vars['subscribe_to_blog_role'] = empty($vars['subscribe_to_blog_role']) ? '' : sanitize_text_field($vars['subscribe_to_blog_role']);
      $vars['auto_subscribe_on_signup'] = empty($vars['auto_subscribe_on_signup']) ? 0 : 1;
      $vars['unsubscribe_text_clickable'] = empty($vars['unsubscribe_text_clickable']) ? '' : bftpro_strip_tags($vars['unsubscribe_text_clickable']);
      $vars['unsubscribe_redirect'] = empty($vars['unsubscribe_redirect']) ? '' : esc_url_raw($vars['unsubscribe_redirect']); 
      $vars['notify_signup_subject'] = empty($vars['notify_signup_subject']) ? '' : sanitize_text_field($vars['notify_signup_subject']);
      $vars['notify_signup_message'] = empty($vars['notify_signup_message']) ? '' : bftpro_strip_tags($vars['notify_signup_message']);
      $vars['redirect_to_prepend_uid'] = empty($vars['redirect_to_prepend_uid']) ? 0 : 1;
      $vars['redirect_confirm_prepend_uid'] = empty($vars['redirect_confirm_prepend_uid']) ? 0 : 1;
      $vars['no_unsubscribe_link'] = empty($vars['no_unsubscribe_link']) ? 0 : 1;
      $vars['woo_products'] = empty($_POST['woo_products']) ? '' : '|'.implode('|', bftpro_int_array($vars['woo_products'])).'|';
      $vars['woo_products_unsub'] = empty($_POST['woo_products_unsub']) ? '' : '|'.implode('|', bftpro_int_array($vars['woo_products_unsub'])).'|';
	}
	
	function delete($id) {
		// delete this mailing list + all subscribers
		global $wpdb;
		$id = intval($id);
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_LISTS." WHERE id=%d", $id));		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_USERS." WHERE list_id=%d", $id));
	}
	
	// outputs extra fields on registration form etc
	function fields($list_id = 0, $user = NULL, $visual_mode = null, $remote_placement = null) {
		global $wpdb;
				
		// select extra fields in the given list
		if(empty($this->form_fields)) {
			$main_fields = array(
				(object)array('name' => 'name', 'special_name'=>'bftpro_name', 'label' => __('Name', 'bftpro'), 'id'=>'_1', 'ftype'=>'textfield', 
					'is_required'=>(empty($list->require_name) ? 0 : 1) ),		
				(object)array('name' => 'email', 'special_name'=>'email', 'label' => __('Email', 'bftpro'), 'id'=>'_2', 'ftype'=>'textfield', 'is_required'=>1),
			);
	
			if(!empty($list_id)) {
				$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list_id));
				$fields = array_merge($main_fields, $fields);
			}
			else $fields = $main_fields;
		}
		else {
			$fields = unserialize($this->form_fields); // fields passed by Gozaimasu
			foreach($fields as $cnt => $field) {
				if($field->ftype == 'static' and $field->name == 'email') {
					$field->ftype = 'textfield';
					$field->special_name = 'email';
					$fields[$cnt] = $field;
				}
				
				if($field->ftype == 'static' and $field->name == 'name') {
					$field->ftype = 'textfield';
					$field->special_name = 'bftpro_name';
					$fields[$cnt] = $field;
				}
			}
		}
		
		// if passing fields trough $_GET is allowed, we'll prefill the values
		if(get_option('bftpro_allow_get')) {
			if(empty($user)) $user = array();

			// to allow easier URL param, many ppl will get the default wrong
			if(!empty($_GET['arigato_name']) and empty($_GET['arigato_bftpro_name'])) $_GET['arigato_bftpro_name'] = $_GET['arigato_name'];
			 
			foreach($fields as $field) {
				$field_name = empty($field->special_name) ? 'field_'.$field->id : $field->special_name;
				if(!empty($_GET['arigato_'.$field_name]) and empty($user[$field_name])) $user[$field_name] = esc_attr($_GET['arigato_'.$field_name]);
			}
		} // end prefilling data
				
		if(empty($this->visual)) require(BFTPRO_PATH."/views/partial/list-fields.php");		
		else require(BFTPRO_PATH."/views/partial/list-fields-visual.php");
	} // end fields()
	
	
	// outputs extra fields on registration form etc
	function extra_fields($list_id, $user = NULL) {
		global $wpdb;
				
		// select extra fields in the given list		
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list_id));
		require(BFTPRO_PATH."/views/partial/list-fields.php");		
	}
	
	
	// generates the sign-up form for this list
	// will be used from widget, shortcode or template tag
	// $remote_placement is used when the form will be placed as HTML on other site
	function signup_form($list_id, $remote_placement = false, $visual_mode = false, $atts = null) {
		global $wpdb;
		
		if(empty($list_id)) $lists = $this->select();
		else $list = $this->select($list_id);
		
		if(!empty($list_id) and empty($list->id)) {
			_e('Invalid mailing list code', 'bftpro');
			return false;
		}
		
		if(!empty($list->require_recaptcha)) {
			$recaptcha_public = get_option('bftpro_recaptcha_public');
			$recaptcha_private = get_option('bftpro_recaptcha_private');
			$recaptcha_version = get_option('bftpro_recaptcha_version');
			$recaptcha_lang = get_option('bftpro_recaptcha_lang');
			$recaptcha_size = get_option('bftpro_recaptcha_size');
			if(empty($recaptcha_size)) $recaptcha_size = 'normal';
			
			if($recaptcha_public and $recaptcha_private) {
				
				if(!empty($recaptcha_version) and $recaptcha_version == 1) {
					require_once(BFTPRO_PATH."/recaptcha/recaptchalib.php");
					$use_ssl = (get_option('bftpro_recaptcha_ssl') == 1) ? true : false;
					$recaptcha_html = recaptcha_get_html($recaptcha_public, null, $use_ssl);
				}
				
				if($recaptcha_version == 2) {
					// recaptcha v 2
					wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $recaptcha_lang);
					$recaptcha_html = '<div class="g-recaptcha bftpro-recaptcha-list-'.$list_id.'" data-sitekey="'.$recaptcha_public.'" data-size="'.$recaptcha_size.'"></div>';
					$recaptcha2 = true;
				}	
				
				if($recaptcha_version == 3) {
					// recaptcha v 3
					wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_public);
					$recaptcha_html = "
					<input type='hidden' name='g-recaptcha-response' id='gRecaptchaResponseArigato".$list->id."'>
					<script>
					jQuery(function(){
						grecaptcha.ready(function() {
						    grecaptcha.execute('".$recaptcha_public."', {action: 'register'}).then(function(token) {
						       // fill the hidden field with the token
						       jQuery('#gRecaptchaResponseArigato".$list->id."').val(token);
						    });
						});
					}); // jQuery Wrapper	
						</script>";
					$recaptcha3 = true;
				}				
			}
		} // end recaptha HTML
		
		if(!empty($list->require_text_captcha)) {
			$text_captcha_html = BFTProTextCaptcha :: generate();
		}
		
		// select extra fields in the given list
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list_id));		
		
		if($remote_placement) $form_action = BFTProList :: get_form_action();
		
		// Gozaimasu module and design ID?
		$orientation_class = $label_class = $inline_style = '';
		if(!empty($atts['design_id']) and class_exists('Gozaimasu')) {
			// unsets $atts['design_id'] and sets all new $atts.
			GozaimasuFormDesigner :: load_design($atts);	
		}
		
		if(!empty($atts['orientation'])) $orientation_class = 'bftpro-'.$atts['orientation'];		
		if(!empty($atts['label_style']) and $atts['label_style'] == 'above') $label_class = 'bftpro-block-label';
		else $label_class = 'bftpro-inline-label';	

		// handle the same parameter but called labels with attributes block / inline for consistency with the short shortcode
		if(!empty($atts['labels']) and empty($atts['label_style'])) {
			if( $atts['labels'] == 'block') $label_class = 'bftpro-block-label';
			else $label_class = 'bftpro-inline-label';
		}		
				
		if(!empty($atts['form_max_width'])) $inline_style .= 'max-width:'.intval($atts['form_max_width']).'px;';
		if(!empty($atts['form_fields'])) $this->form_fields = $atts['form_fields'];
		if(!empty($atts['submit_btn_text']) and preg_match('/^http/i', $atts['submit_btn_text'])) $list->signup_graphic = $atts['submit_btn_text'];
		// print_r($atts);
				
		// include the form. allow styling
		if(class_exists('Gozaimasu') and !empty($atts['is_modal'])) GozaimasuFormDesigner :: modal_start($atts);
		if(empty($this->visual)) {
			if(!$remote_placement) require(BFTPRO_PATH."/views/signup-form.php");
			else require(BFTPRO_PATH."/views/signup-form-remote.php");
		}
		else require(BFTPRO_PATH."/views/signup-form-visual.php");
		if(class_exists('Gozaimasu') and !empty($atts['is_modal'])) GozaimasuFormDesigner :: modal_end($atts);
	}
	
	// notify admin on suscribe and unsubscribe
	// @param $action - suscribe and unsubscribe
	// @param $reason - optional reason to unsubscribe 
	function notify_admin($user_id, $action, $reason = '') {
		global $wpdb;
		
		if($action=='unsubscribe') {			
			$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE id=%d", $user_id));
			$list = $this->select($user->list_id);
			$subject = __("A member unsubscribed.", 'bftpro');
			$message = __(sprintf("The member with email %s has unsubscribed from \"%s\"", $user->email, $list->name), 'bftpro');	
			
			if(!empty($reason)) {
				$message .= '<br>'.sprintf(__('Reason to unsubscribe: %s', 'bftpro'), $reason);
			}	
		}
		
		if($action=='register') {
			// get user along with data
			require_once(BFTPRO_PATH."/models/user.php");
			$_user = new BFTProUser();
			$users = $_user->select_receivers($wpdb->prepare(" AND id=%d ", $user_id), 1);
			$user=$users[0];
					
			$list = $this->select($user->list_id);			
			
			if(empty($user->id)) return false;			
			if(empty($list->notify_signup_subject)) $subject = __("New member signed up", 'bftpro');
			else {
				$subject = stripslashes($list->notify_signup_subject);
				$subject = str_replace('{{{list-name}}}', stripslashes($list->name), $subject);
				$subject = str_replace('{{list-name}}', stripslashes($list->name), $subject);
			}
			
			if(empty($list->notify_signup_message)) {
				$message = __(sprintf("New member signed up for your mailing list \"%s\"", $list->name), 'bftpro');
				$message = "<p>".$message."</p>";
				
				// user status, code and list_id and add all other member data
				$message .= __("Email:", 'bftpro').' '.$user->email."<br>";
				$message .= __("Name:", 'bftpro').' ' .$user->name."<br>";
		
				foreach($user->pretty_fields as $key => $val) {
					$message.=$key.": ".$val."<br>";
				}
			}
			else {
				// get custom message
				$message = stripslashes($list->notify_signup_message);
				$message = str_replace('{{{list-name}}}', stripslashes($list->name), $message);
				$message = str_replace('{{list-name}}', stripslashes($list->name), $message);
				$message = str_replace('{{{email}}}', $user->email, $message);
				$message = str_replace('{{{name}}}', $user->name, $message);
				$message = str_replace('{{{date}}}', date_i18n(get_option('date_format', strtotime($user->date))), $message);
				$message = str_replace('{{{ip}}}', $_SERVER['REMOTE_ADDR'], $message);
				
				$custom_fields = '';
				foreach($user->pretty_fields as $key => $val) {
					$custom_fields.=$key.": ".$val."<br>";
				}
				// keep both versions of {{{custom-fields}}} tag for historical reasons
				$message = str_replace('{{{custom-fields}}}', $custom_fields, $message);
				$message = str_replace('{{{custom_fields}}}', $custom_fields, $message);
				$message = wpautop($message);
			}
		}
		
		// now send		
		$_sender = new BFTProSender();
		// $_sender->debug = true;
		
		if(strstr($list->notify_email,  ",")) {
			$notify_emails = explode(",", $list->notify_email);
			$sender_email = get_option('bftpro_sender');
			foreach($notify_emails as $notify_email) {
				$_sender->send($sender_email, trim($notify_email), $subject, $message);
			}
		}
		else $_sender->send(get_option('bftpro_sender'), $list->notify_email, $subject, $message);
	}
	
	// catches user_register action and calls auto_subscribe with proper arguments
	static function register_subscribe($user_id) {
		$user = get_userdata($user_id);
		self :: auto_subscribe($user->user_login, $user, false);
	}
	
	// this automatically subscribes users who login for the first time
	// for any mailing lists that have "auto-subscribe" selected
	static function auto_subscribe($user_login, $user, $logging_in = true) {
		global $wpdb;
		
		// if already logged in, return false to avoid needless queries
		if(get_user_meta($user->ID, 'bftpro_logged_in', true)==1) return false;
		
		require_once(BFTPRO_PATH."/models/user.php");
		$_user = new BFTProUser();
		
		if($logging_in) update_user_meta( $user->ID, 'bftpro_logged_in', 1);
		
		// any lists that require auto-subscribe?
		$lists = $wpdb -> get_results("SELECT * FROM ".BFTPRO_LISTS." WHERE auto_subscribe='1'");
		$name = empty($user->first_name) ? $user->user_login : $user->first_name.' '.$user->last_name;

		foreach($lists as $list) {
			// execute this only on the right event - register or first login			
			if($list->auto_subscribe_on_signup and $logging_in) continue;
			if(!$list->auto_subscribe_on_signup and !$logging_in) continue;					
		
			if(!empty($list->auto_subscribe_role)) {
				$role_found = false;
				foreach($user->roles as $role) {
					if($role == $list->auto_subscribe_role) $role_found = true;
				}
				
				if(!$role_found) continue; // skip this mailing list
			}			
			
			$vars = array("list_id"=>$list->id, "email"=>$user->user_email, "name"=>$name, "auto_subscribed"=>1, 'wp_user_id'=>$user->ID);

			// fill any required fields with "1" to avoid errors			
			$fields=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $list->id));
			foreach($fields as $field) $vars['field_'.$field->id] = 1;
			
			// ignore exceptions
			try {			
				$message = '';
				$_user->subscribe($vars, $message, true); // passes $in_admin as true to ignore captchas etc
			}
			catch(Exception $e) {}
		}
	}
	
	// duplicate a list together with the AR associatins & custom fields but not the subscribers
	function duplicate($id) {
		global $wpdb;
		include_once(BFTPRO_PATH . '/models/field.php');
		$id = intval($id);
		
		$list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $id), ARRAY_A);
		
		// change name
		$list['name'] = $list['name'] . ' ' . __('(Copy)', 'bftpro');		
		
		$new_id = $this->add($list);
		
		// transfer custom fields
		$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $id), ARRAY_A);
		$_field = new BFTProField();
		
		foreach($fields as $field) {
			$field['list_id'] = $new_id;
			$_field->add($field);
		}
		
		// assign to autoresponder
		$ars = $wpdb->get_results("SELECT id, list_ids FROM ".BFTPRO_ARS." WHERE list_ids LIKE '%|$id|%' ORDER BY id");
		foreach($ars as $ar) {
			$list_ids = explode('|', $ar->list_ids);
			$list_ids = array_filter($list_ids);
			$list_ids[] = $new_id;
			$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_ARS." SET list_ids='|".implode('|', $list_ids)."|' WHERE id=%d", $ar->id)); 
		}
		
		return $new_id;
	}
	
	// get random post for submit URL
	static function get_form_action() {		
		// I think the commented code below was needed only when we wrongly used "name" as field name?
		/*foreach ( get_posts ( array( 'numberposts' => 1, 'orderby' => 'rand' ) ) as $post ) { // wp query to get one post url on random basis
          $random_post = $post;
      }
      
      if(empty($random_post)) $form_action=site_url();
      else $form_action = get_permalink($post->ID);*/
       
       $form_action = home_url("/");
       return $form_action;  
	}
}