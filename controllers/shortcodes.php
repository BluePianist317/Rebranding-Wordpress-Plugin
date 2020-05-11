<?php
class BFTProShortcodes {
	// outputs the openning form tag
	static function form_start($atts) {
		global $wpdb;
		
		if(get_option('bftpro_no_scripts') == 1) BFTPro :: scripts();		
		
		$list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $atts[0]));
		$action = '';		
		if(!empty($atts[1])) {
			$form_action = BFTProList :: get_form_action();
			$action = " action = '$form_action' ";
		}
		
		// any file fields?
		$any_files = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_FIELDS." 
			WHERE list_id=%d AND ftype='file'", $list->id));
		$enctype = $any_files ? 'enctype="multipart/form-data"' : ''; 	
		
		$orientation_class = empty($atts['orientation']) ? ' bftpro-vertical ' : ' bftpro-'.$atts['orientation'].' ';
		$label_class = empty($atts['labels']) ? ' bftpro-inline-label ' : ' bftpro-'.$atts['labels'].'-label ';
		
		// handle the same parameter but called label_style with attributes above / next for consistency with the short shortcode
		if(empty($atts['labels']) and !empty($atts['label_style'])) {
			if( $atts['label_style'] == 'above') $label_class = 'bftpro-block-label';
			else $label_class = 'bftpro-inline-label';
		}
		
		$content = '<form method="post" class="bftpro-front-form'.$orientation_class.$label_class.'" '.$enctype.' onsubmit="return validateBFTProUser(this,'.($list->require_name?'true':'false').');" '.$action.'>';
		return $content;
	}	
	
	// outputs a static field
	static function static_field($atts) {
		$placeholder = empty($atts['placeholder']) ? '' : ' placeholder="'.$atts['placeholder'].'" ';
		$onkeyup = empty($atts['onkeyup']) ? '' : ' onkeyup="'.$atts['onkeyup'].'" ';
		$value = empty($atts['value']) ? '' : ' value="'.$atts['value'].'" ';
		
		if($atts[0] == 'name') return '<input type="text" name="bftpro_name"'.$placeholder.$onkeyup.$value.'>';
		if($atts[0] == 'redirect_url') return '<input type="hidden" name="bftpro_redirect_url" value="'.$atts['value'].'">';
		else return '<input type="text" name="email"'.$placeholder.$onkeyup.$value.'>';
	}
	
	// non static field	
	static function field($atts) {
		global $wpdb;
		$placeholder = empty($atts['placeholder']) ? '' : ' placeholder="'.$atts['placeholder'].'" ';
		$onkeyup = empty($atts['onkeyup']) ? '' : ' onkeyup="'.$atts['onkeyup'].'" ';
		
		$field = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE id=%d", $atts[0]));		
		if(!empty($atts['value'])) {
			$user["field_".$field->id] = $atts['value'];
		}
		
		if($atts[0] == '_1') {
			$field = (object)array('name' => 'name', 'special_name'=>'bftpro_name', 'label' => __('Name', 'bftpro'), 'id'=>'_1', 'ftype'=>'textfield', 
					'is_required'=>(empty($list->require_name) ? 0 : 1)); 
		}
		if($atts[0] == '_2') {
			$field = (object)array('name' => 'email', 'special_name'=>'email', 'label' => __('Email', 'bftpro'), 'id'=>'_2', 'ftype'=>'textfield', 'is_required'=>1);
		}
		$field->placeholder_attr = $placeholder;
				
		$fields = array($field);
		ob_start();
		$nolabel = true;
		require(BFTPRO_PATH."/views/partial/list-fields.php");
		$content = ob_get_clean();
		return $content;
	}
	
	// closes the signup form
	static function form_end($atts) {
		return '<input type="hidden" name="bftpro_subscribe" value="1">			
				<input type="hidden" name="list_id" value="'.$atts[0].'">			
			<input type="hidden" name="required_fields[]" value="">
		</form>';
	}
	
	static function recaptcha() {
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
			if(!empty($recaptcha_version) and $recaptcha_version == 2) {
				// recaptcha v 2
				wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $recaptcha_lang);
				$recaptcha_html = '<div class="g-recaptcha" data-sitekey="'.$recaptcha_public.'" data-size="'.$recaptcha_size.'"></div>';
			}	
			
			if(!empty($recaptcha_version) and $recaptcha_version == 3) {
				// recaptcha v 2
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
		
		return $recaptcha_html;
	}
	
	static function submit_button($atts) {
		global $wpdb;
		
		$list_id = empty($atts[0]) ? 0 : $atts[0];
		if(!empty($list_id)) {
			$list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $list_id));
		}	
		
		$text = empty($atts[1]) ? __('Subscribe', 'bftpro') : $atts[1];	
		
		if(empty($list->signup_graphic)) return '<input type="submit" value="'.$text.'">';
		else return '<input type="image" src="'.$list->signup_graphic.'">';
	}
	
	static function text_captcha() {
		return BFTProTextCaptcha :: generate();	
	}
	
	// displays checkbox for the mailing list that can be used in integrations
	static function int_chk($atts) {
		$list_id = intval(@$atts['list_id']);
		
		if(empty($list_id)) return '';
		
		// allow passing CSS, ID, onlick, default checked, etc		
		$classes = $chedked = $style = '';
		if(!empty($atts['required']) and $atts['required'] == 'true') $classes .= ' wpcf7-validates-as-required ';
		if(!empty($atts['css_classes'])) $classes .= ' '.$atts['css_classes'].' ';
		if(!empty($atts['html_id'])) $html_id = $atts['html_id'];
		
		if(!empty($atts['checked']) and $atts['checked'] == 'true') $checked = ' checked="checked" ';
		if(!empty($atts['hidden']) and $atts['hidden'] == 'true') $style .= 'display:none;';
		
		// now output the checkbox
		return '<input type="checkbox" name="bftpro_integrated_lists[]" value="'.$list_id.'" class="'.$classes.'" id="'.@$html_id.'" '.@$checked.' style="'.$style.'">';
   } // end int_chk
   
   // display checkboxes for the other mailing lists
   static function other_lists($atts) {
   	global $wpdb;
   	
   	$list_id = intval(@$atts['list_id']);		
		if(empty($list_id)) return '';
		
		$other_lists = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM ".BFTPRO_LISTS." 
			WHERE id!=%d ORDER BY name", $list_id));
		if(!sizeof($other_lists)) return '';
			
		$html = '';
		foreach($other_lists as $l) {	
			$html .= '<input type="checkbox" name="other_lists[]" value="'.$l->id.'"> '.stripslashes($l->name).'<br>';
		}	
		
		return $html;
   } // end other lists
   
   // shows unsubscribe form on custom page/post
   // this is redundancy with the template_redirect function in the basic model   
   static function unsubscribe($atts) {
   	global $wpdb;
   	list($template, $users, $error, $message, $title, $unsubscribe_reasons, $unsubscribe_reasons_other) = BFTProUsers :: unsubscribe_form();
		
		// get the current list from $_GET - used mostly for bftpro-unsubscribe-single.php
		$selected_list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", @$_GET['list_id']));		
		
		ob_start();
		$in_shortcode = true;
		include(BFTPRO_PATH."/views/templates/".$template);
		$content = ob_get_clean();
		return $content;
   }
   
   // shows the content of user field
   static function user_field($atts) {
   	global $wpdb;
   	$default_value = empty($atts['default']) ? '' : $atts['default'];
   	
   	// find user ID - coming from atts or from GET
   	$user_id = 0;
   	if(!empty($_GET['arigatopro_id'])) {   		
   		$user_id = intval($_GET['arigatopro_id']);
   		
   		// verify hash
   		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE id=%d", $user_id));
   		if(empty($_GET['hash']) or md5($user->hash) != $_GET['hash']) $user_id = 0; 
   	}
   	if(!empty($atts['user_id'])) $user_id = intval($atts['user_id']);
   	
   	if(empty($user_id)) return $default_value;
   	if(empty($atts['field'])) return $default_value;
   	
   	// get user
   	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE id=%d", $user_id));
   	if($atts['field']=='name') return $user->name;
   	if($atts['field']=='email') return $user->email;
   	if($atts['field']=='ip') return $user->ip;
   	if($atts['field']=='date') return date_i18n(get_option('date_format'), strtotime($user->date));
   	
   	// none of the above? it's a custom field, try to find it & its data
   	$field = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d AND name=%s", $user->list_id, $atts['field']));
   	if(empty($field->id)) return $default_value;
   	
   	$data = $wpdb->get_var($wpdb->prepare("SELECT data FROM ".BFTPRO_DATAS." WHERE field_id=%d AND user_id=%d", $field->id, $user_id));
   	if(empty($data)) return $default_value;
   	if($field->ftype == 'simple_textarea' or $field->ftype == 'textarea') return nl2br($data);
   	return $data;
   }
   
   // number of subscribers in a given mailing list
   static function num_subscribers($atts) {
   	global $wpdb;
   	
   	$list_id = intval(@$atts['list_id']);
   	if(empty($list_id)) return '';
   	
   	$num = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_USERS." WHERE list_id=%d AND status=1", $list_id));
   	
   	return $num;
   } 
}