<?php
class BFTProUser {
	// get users from a mailing list
	function select($list_ids, $orderby, $orderdir, $offset=0, $limit=0) {
		global $wpdb;		
		
		// define $list_id_sql
		$list_id_sql=is_array($list_ids)?" list_id IN (".implode(",",$list_ids).") " : $wpdb->prepare("list_id=%d", $list_ids);
		
		// limit per page?
		$limit_sql=$limit?$wpdb->prepare(" LIMIT %d, %d ", $offset, $limit):"";
		
		// filters
		$filters_sql="";
		if(!empty($_GET['filter_email'])) {
			$filter_email="%$_GET[filter_email]%";
			$filters_sql.=$wpdb->prepare(" AND email LIKE %s ", $filter_email); 
		}
		if(!empty($_GET['filter_name'])) {
			$filter_name="%$_GET[filter_name]%";
			$filters_sql.=$wpdb->prepare(" AND name LIKE %s ", $filter_name); 
		}
		if(!empty($_GET['filter_ip'])) {
			$filter_ip="%$_GET[filter_ip]%";
			$filters_sql.=$wpdb->prepare(" AND ip LIKE %s ", $filter_ip); 
		}
		
		if(isset($_GET['filter_status']) and intval($_GET['filter_status'])!=-1) {
			if($_GET['filter_status'] == -2) {
				$_GET['filter_status'] = 0;				
				$filters_sql .= " AND unsubscribed = 1 ";	
			}
			$filters_sql.=$wpdb->prepare(" AND status=%d ", $_GET['filter_status']); 
		}
		
		if(isset($_GET['readmails_from']) and $_GET['readmails_from']!=='') {
			$filters_sql .= $wpdb->prepare(" AND (read_nls + read_armails) >= %d", $_GET['readmails_from']);
		}
		
		if(isset($_GET['readmails_to']) and $_GET['readmails_to']!=='') {
			$filters_sql .= $wpdb->prepare(" AND (read_nls + read_armails) <= %d", $_GET['readmails_to']);
		}
		
		// link clicks - this part is activated by the Intelligence module but the code is simple so we'll include it here
		if(isset($_GET['clicks_from']) and $_GET['clicks_from']!=='') {
			$filters_sql .= $wpdb->prepare(" AND clicks >= %d", $_GET['clicks_from']);
		}
		
		if(isset($_GET['clicks_to']) and $_GET['clicks_to']!=='') {
			$filters_sql .= $wpdb->prepare(" AND clicks <= %d", $_GET['clicks_to']);
		}
		
		if(!empty($_GET['filter_source'])) {
			switch($_GET['filter_source']) {
				case '_web': $filters_sql .= " AND source LIKE 'http%' "; break;
				case '_other': 
					$filters_sql .= " AND source NOT LIKE 'http%' 
						AND source NOT IN ('_admin', '_import', '_auto', '_email') ";
				break;	 
				default: 
					$filters_sql .= $wpdb->prepare(" AND source=%s ", $_GET['filter_source']);
				break;
			}
		}
		
		if(!empty($_GET['signup_date_cond']) and !empty($_GET['filter_signup_date'])) {			
			switch($_GET['signup_date_cond']) {
				case 'before': $signup_date_cond = '<'; break;
				case 'after': $signup_date_cond = '>'; break;
				case 'on': default: $signup_date_cond = '='; break;
			}
			$filters_sql .= $wpdb->prepare(" AND date ".$signup_date_cond." %s ", $_GET['filter_signup_date']);			
		}
		
		if(!empty($_GET['filter_tags'])) {
			$tags = explode(",", sanitize_text_field($_GET['filter_tags']));
			$tags = array_map('trim', $tags);
			
			$tags_sql = " AND (";
			
			$connect_tags_sql = $_GET['filter_tags_mode'] == 'any' ? 'OR' : 'AND';
			
			foreach($tags as $cnt => $tag) {
				if($cnt > 0) $tags_sql .= ' '.$connect_tags_sql.' ';
				$tags_sql .= $wpdb->prepare(" (tags LIKE %s OR tags LIKE %s OR tags LIKE %s 
					OR tags LIKE %s OR tags LIKE %s OR tags LIKE %s) ", 
					$tag, '%, '.$tag, '%,'.$tag, $tag.',%', '%, '.$tag.',', '%,'.$tag.',');
			} 			
			
			$tags_sql .=") "; 
			
			$filters_sql .= $tags_sql;
		}
				
		// if single list is passed check for extra field filters
		if(!is_array($list_ids)) {
			$_field=new BFTProField();
			$fields = $_field->select($list_ids);
			
			// now if there are filters we'll add the user IDs in array and add ID filters
			// otherwise if we do joins we risk to interfere with field names passed by get etc
			$filter_by_custom_field = false;
			
			foreach($fields as $field) {
				if(!empty($_GET['filter_field_' . $field->id])) {
					$field_uids = array(0);
					if($field->ftype == 'checkbox') $datalike = ($_GET['filter_field_' . $field->id] == -1) ? 'data = 0' : 'data = 1'; 
					else $datalike = $wpdb->prepare("data LIKE %s", "%" . $_GET['filter_field_' . $field->id] . "%");
					
					$cids = $wpdb->get_results("SELECT DISTINCT(user_id) FROM ".BFTPRO_DATAS."
						WHERE list_id=" . $field->list_id ." AND field_id=".intval($field->id)." AND $datalike");
												
					if(!empty($cids)) {
						foreach($cids as $cid) $field_uids[] = $cid->user_id;
					}
					
					// now add the SQL
					$filters_sql .= " AND id IN (" . implode(',', $field_uids) . ") ";
				} // end if !empty filter
			} // end foreach custom field		
		}	// end handling single list and eventual filters by custom field	
		 
		$users = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS * FROM ".BFTPRO_USERS." 
			WHERE $list_id_sql $filters_sql
			ORDER BY $orderby $orderdir $limit_sql", ARRAY_A);
		$cnt_users=$wpdb->get_var("SELECT FOUND_ROWS()");	
			
		// match data from custom fields to users		
		$datas=$wpdb->get_results("SELECT * FROM ".BFTPRO_DATAS." WHERE $list_id_sql ");
		
		foreach($users as $cnt=>$user) {
			foreach($datas as $data) {
				if($data->user_id==$user['id']) {
					$users[$cnt]['field_'.$data->field_id]=$data->data;
				}
			}
		}			
			
		return array($users, $cnt_users);
	}
	
	// gets a single user along with their data
	function get($id) {
		global $wpdb;
		
		$user=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE id=%d", $id), ARRAY_A);	
		
		// get data
		$datas=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_DATAS." WHERE user_id=%d AND list_id=%d", $id, $user['list_id']));
		
		foreach($datas as $data) {
			$user['field_'.$data->field_id]=$data->data;
			$user['field_'.$data->field_id.'_dataid']=$data->id;
		}	
		
		return $user;
	}
	
	// adds in the DB
	function add($vars) {
		global $wpdb;

		$this->prepare_vars($vars);		
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_USERS." SET
			email=%s, name=%s, status=%d, date=%s, ip=%s, code=%s, list_id=%d, source=%s, 
			magnet_id=%d, form_name=%s, hash=%s, tags=%s",
			$vars['email'], $vars['name'], $vars['status'], $vars['date'], $vars['ip'], $vars['code'], 
			$vars['list_id'], $vars['source'], $vars['bftpro_magnet_id'], 
			$vars['bftpro_form_name'], $vars['hash'], $vars['tags']));
		return $wpdb->insert_id;	
	}
	
	function prepare_vars(&$vars) {
		$vars['email'] = sanitize_email($vars['email']);
		$vars['name'] = sanitize_text_field($vars['name']);
		$vars['status'] = intval(@$vars['status']);
		$vars['date'] = empty($vars['date']) ? '' : sanitize_text_field($vars['date']);
		$vars['ip'] = empty($vars['ip']) ? '' : sanitize_text_field($vars['ip']);
		$vars['code'] = empty($vars['code']) ? '' : sanitize_text_field($vars['code']);
		$vars['list_id'] = empty($vars['list_id']) ? 0 : intval($vars['list_id']);
		$vars['source'] = empty($vars['source']) ? '' : sanitize_text_field($vars['source']);
		$vars['bftpro_magnet_id'] = empty($vars['bftpro_magnet_id']) ? 0 : intval($vars['bftpro_magnet_id']);
		$vars['bftpro_form_name'] = empty($vars['bftpro_form_name']) ? '' : sanitize_text_field($vars['bftpro_form_name']);
		$vars['goz_design_id'] = empty($vars['goz_design_id']) ? 0 : intval($vars['goz_design_id']);
		$vars['goz_ab_test_id'] = empty($vars['goz_ab_test_id']) ? 0 : intval($vars['goz_ab_test_id']);
		$vars['tags'] = empty($vars['tags']) ? '' : trim(sanitize_text_field($vars['tags']));
		
		// create hash
		$vars['hash'] = substr(md5(time().$vars['email']), 0, 8);
	}
	
	// in practice = add user
	// param $in_admin defines whether to ignore captchas and accept $var['status']
	// $in_admin is true when adding the user from admin or when auto-subscribing from WP registration 
	function subscribe($vars, &$message=null, $in_admin = false) {
		global $wpdb;
		
		$_sender = new BFTProSender();
		
		// when coming from signup page we use "bftpro_name" instead of "name" to avoid "Page not found" problem with Wordpress
		if(!empty($vars['bftpro_name'])) $vars['name'] = $vars['bftpro_name'];	
		
		// when source is empty, use referring page as source
		if(empty($vars['source']) and !empty($vars['d'])) $vars['source'] = '_auto';
		if(empty($vars['source'])) $vars['source'] = $_SERVER['HTTP_REFERER']; 	
		$wp_user_id = empty($vars['wp_user_id']) ? 0 : intval($vars['wp_user_id']);

		// require valid non-empty email
		$vars['email'] = sanitize_email($vars['email']);
		if(empty($vars['email']) or !strstr($vars['email'], '@')) throw new Exception("Valid non-empty email is required");
		
		// blacklist?
		if(!ArigatoPROBlacklist :: check($vars)) {
			if(!empty($list->redirect_to) and empty($this->ignore_redirect) and !$in_admin) bftpro_redirect($list->redirect_to);
			return false;
		}
		
		if(!empty($vars['list_ids']) and is_array($vars['list_ids']) and !empty($vars['list_ids'][0])) {
			$list_ids = $vars['list_ids'];
			
			$num_lists = count($list_ids);
			unset($vars['list_ids']);
			foreach($list_ids as $cnt=>$id) {				
				$vars['list_id'] = $id;
				$this->ignore_redirect = ($cnt + 1 < $num_lists) ? true : false;  
				
				// fill any required fields with "1" to avoid errors			
				$fields=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d AND is_required=1", $id));
				foreach($fields as $field) $vars['field_'.$field->id] = 1;
				$this->subscribe($vars, $message, $in_admin);
			}
			
			// this happens in case of "other lists" checkboxes. We need to redirect to the original list, if there is redirect in it			
			if(!empty($this->delayed_redirect)) bftpro_redirect($this->delayed_redirect);		
			do_action('bfti-handle-magnet', $id);
			return $id; // return the last ID
		}
		
		// require valid list_id
		$list=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", @$vars['list_id']));
      if(empty($list->id)) throw new Exception("Invalid mailing list ID");
      
      // require name?
		if($list->require_name and empty($vars['name'])) throw new Exception(__("You must enter your name", 'bftpro'));
      
      // recaptcha?
      if($list->require_recaptcha and empty($in_admin)) {
      	$recaptcha_public = get_option('bftpro_recaptcha_public');
			$recaptcha_private = get_option('bftpro_recaptcha_private');
			$recaptcha_version = get_option('bftpro_recaptcha_version');
			
			if($recaptcha_public and $recaptcha_private) {
				if(!empty($recaptcha_version) and $recaptcha_version == 1) {
					require_once(BFTPRO_PATH."/recaptcha/recaptchalib.php");
					$resp = recaptcha_check_answer ($recaptcha_private,
	                                $_SERVER["REMOTE_ADDR"],
	                                $vars["recaptcha_challenge_field"],
	                                $vars["recaptcha_response_field"]);
	            if (!$resp->is_valid) {
	            	throw new Exception(__('The image verification code is not correct. Please go back and try again.', 'bftpro'));
	            }  
            }
            else {        
            	// recaptcha v 2 and v3, thanks to https://www.sitepoint.com/no-captcha-integration-wordpress/
	            $response = isset( $_POST['g-recaptcha-response'] ) ? esc_attr( $_POST['g-recaptcha-response'] ) : '';
					$remote_ip = $_SERVER["REMOTE_ADDR"];     
					// make a GET request to the Google reCAPTCHA Server
					$request = wp_remote_get(
						'https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_private.'&response=' . $response . '&remoteip=' . $remote_ip
					);       
					$response_body = wp_remote_retrieve_body( $request );
					$result = json_decode( $response_body, true );					
					if(!$result['success']) {
						throw new Exception(sprintf(__('The captcha verification is not correct. Please go back and try again. (%s)', 'bftpro'), @$result['error-codes'][0]));
					}
					
					if($recaptcha_version == 3) {
						// for version 3 we must verify score and action					
						$recaptcha_score = get_option('bftpro_recaptcha_score');
						if(empty($recaptcha_score)) $recaptcha_score = 0.5;
						if($result['score'] < $recaptcha_score or $result['action'] != 'register') {
							throw new Exception(__('The captcha verification is not correct. Please go back and try again.', 'bftpro'));
						}
					}
				}
			}
      } // end recaptcha
      
      // text captcha?
      if($list->require_text_captcha and empty($in_admin)) {
      	if(!BFTProTextCaptcha :: verify($_POST['bftpro_text_captcha_question'], $_POST['bftpro_text_captcha_answer'])) {
      		throw new Exception(__('The verification question was not answered correctly. Please go back and try again.', 'bftpro'));
      	}
      }
		
		// duplicate email? if yes, update fields and return ID
		$exists=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE email=%s AND list_id=%d",
			$vars['email'], $vars['list_id']));
			
		if(!empty($exists->id) and empty($exists->unsubscribed) and !empty($exists->status)) {
			$vars['status'] = $exists->status;
			$this->save($vars, $exists->id, $in_admin);
			
			// if this member is already registered AND confirmed make double optin = false, no matter what
			// otherwise let the double optin email be sent
			if($exists->status) $list->optin=0;
			
			$id=$exists->id;

			$message = __("You are already subscribed to this mailing list", 'bftpro');
			if(!empty($list->redirect_duplicate)) $list->redirect_to = $list->redirect_duplicate;
			// $this->ignore_redirect = true;
		}
		else {			
			// status? based on double opt-in and admin selection (admin selection has priority when admin adds user manually)
			if($in_admin and isset($vars['status'])) $status = $vars['status'];
			else $status=$list->optin?0:1;
						
			$code=substr(md5($vars['email'].microtime()),0,8);
				
			// insert member if not exist (the member CAN exist if they unsubscribed but were kept in the database
			if(!empty($exists->id)) {				
				$vars['status'] = $status;
				$this->save($vars, $exists->id, $in_admin);
				$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET unsubscribed=0 WHERE id=%d", $exists->id));
				$id = $exists->id;
			}
			else {	
			
				$date = (empty($vars['date']) or !$in_admin) ? date("Y-m-d", current_time('timestamp')) : sanitize_text_field($vars['date']);
				$datetime = (empty($vars['datetime']) or !$in_admin) ? current_time('mysql') : sanitize_text_field($vars['datetime']);				
				
				$this->prepare_vars($vars);		
							
				$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_USERS." SET
					email=%s, name=%s, status=%d, date=%s, ip=%s, code=%s, list_id=%d, 
					auto_subscribed=%d, source=%s, magnet_id=%d, wp_user_id=%d, 
					datetime=%s, form_name=%s, ab_test_id=%d, design_id=%d, hash=%s, tags=%s",
					$vars['email'], @$vars['name'], $status, $date, '', $code, $list->id, 
					@$vars['auto_subscribed'], @$vars['source'], intval(@$vars['bftpro_magnet_id']), $wp_user_id, $datetime, 
					$vars['bftpro_form_name'], $vars['goz_ab_test_id'], $vars['goz_design_id'], $vars['hash'], $vars['tags']));
			
				$id = $wpdb->insert_id;
				
				// add extra data
				$this->save_data($vars, $id, $in_admin);				
			}
				
			// double opt-in? send activation email
			if(!$status) {
				 $this->send_activation_email($vars, $list);
				 $message = __("Please check your email. A confirmation message has been sent to it. Your membership will not be activated before you click on the confirmation link inside the message", 'bftpro');
			}
			else {
				// paid list? if so, we have to overwrite the message with the payment buttons
				// and not execute any of the other things until the list is paid
				if(!empty($list->fee) and $list->fee > 0 and class_exists('BFTIList') and empty($in_admin)) {					
					// revert back to unconfirmed status and set the "status_info" field to 'acitvated unpaid'
					$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET status=0, status_info = 'pending payment' WHERE id=%d", $id));					
					
					// render buttons					
					$message = BFTIList :: payment_buttons($list, $id);
					return $id;
				}
				
				$_sender->immediate_mails($id);				
				$this->subscribe_triggers($id, $list);
								
				if($list->do_notify) {		
					// notify admin
					require_once(BFTPRO_PATH."/models/list.php");
					$_list = new BFTProList();
					$_list->notify_admin($id, 'register');
				}				
				
				do_action('bftpro_user_subscribed', $id, $list->id);				
				$message = __("You have been subscribed to the mailing list", 'bftpro');
			}		
		}
		
		// subscribing to multiple lists? Handle also the "other lists" setting when the default list is the leading one
		if(!empty($vars['other_lists']) and is_array($vars['other_lists']) and !empty($vars['other_lists'][0])) {
			$vars['list_ids'] = $vars['other_lists'];
			if(!empty($list->redirect_to) and empty($this->ignore_redirect)) $this->delayed_redirect = $list->redirect_to;
			$list->redirect_to = '';
			unset($vars['other_lists']);
			unset($vars['list_id']);
			
			$this->subscribe($vars); 
		}
		do_action('bfti-handle-magnet', $id);
		
		// hide Gozaimasu popup?
		if(!empty($_POST['goz_design_id'])) $_SESSION['gozaimasu_closed_'.intval($_POST['goz_design_id'])] = 1;
		
		// redirect?
		if(!empty($_POST['bftpro_redirect_url'])) $list->redirect_to = $_POST['bftpro_redirect_url']; // you can override redirect in each signup form
		if(!empty($list->redirect_to) and empty($this->ignore_redirect) and !$in_admin) {
			if(!empty($list->redirect_to_prepend_uid)) {				
				bftpro_redirect($list->redirect_to, array('arigatopro_id' => $id, 'hash' => md5($vars['hash'])));
			} 
			else bftpro_redirect($list->redirect_to);
		}		
		
		return $id;
	} // end subscribe
	
	function save($vars, $id, $in_admin = false) {
		global $wpdb;
		
		$this->prepare_vars($vars);
		
		$date_sql = $code_sql = $ip_sql = $source_sql = $magnet_sql = $form_name_sql = "";
		if(!empty($vars['date'])) $date_sql = $wpdb->prepare(", date=%s", $vars['date']);
		if(!empty($vars['ip'])) $ip_sql = $wpdb->prepare(", ip=%s", $vars['ip']);
		if(!empty($vars['source'])) $source_sql = $wpdb->prepare(", source=%s", $vars['source']);
		if(!empty($vars['form_name'])) $form_name_sql = $wpdb->prepare(", form_name=%s", $vars['form_name']);
		if(!empty($vars['magnet_id'])) $magnet_sql = $wpdb->prepare(", magnet_id=%d ", $vars['bftpro_magnet_id']);
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET
			email=%s, name=%s, status=%d, tags=%s 
			$ip_sql $date_sql $code_sql $source_sql $magnet_sql $form_name_sql
			WHERE id=%d",
			$vars['email'], $vars['name'], $vars['status'], $vars['tags'], $id));
			
		$this->save_data($vars, $id, $in_admin);
						
		return true;	
	}
	
	function delete($id) {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_USERS." WHERE id=%d", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_DATAS." WHERE user_id=%d", $id));
		
		return true;
	}
	
	function save_data($vars, $uid, $in_admin = false) {
		global $wpdb;
	
		// select fields in the given list ID
		$fields=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d", $vars['list_id']));
		
		foreach($fields as $field) {	
			$fileblob = '';
					
			if($field->ftype == 'date') {
				$day = empty($vars['field_'.$field->id.'day']) ? "01" :  $vars['field_'.$field->id.'day'];
				$month = empty($vars['field_'.$field->id.'month']) ? "01" :  $vars['field_'.$field->id.'month'];
				$year = empty($vars['field_'.$field->id.'year']) ? "1900" :  $vars['field_'.$field->id.'year'];
				$data = $year.'-'.$month.'-'.$day;
			}		
			else $data = @$vars['field_'.$field->id];
			
			// handle files
			if($field->ftype == 'file' and !empty($_FILES['field_'.$field->id]['name'])) {
				// check size and extension
				list($max_upload, $allowed_types) = explode("|", $field->fvalues);				
				$filesize = round($_FILES['field_'.$field->id]['size'] / 1024);
				
				// check file size
				if(!empty($max_upload) and $max_upload < $filesize) {
					throw new Exception(sprintf(__('Your file is %d KB while the maximum accepted size is %d KB.', 'bftpro'), $filesize, $max_upload));
				}
				
				// check extension
				$allowed_types = preg_replace("/\s/", '', strtolower($allowed_types)); // remove spaces
				$allowed_types = explode(',', $allowed_types);
				$parts = explode('.', $_FILES['field_'.$field->id]['name']);
				$extenstion = array_pop($parts);
				if(!in_array($extenstion, $allowed_types)) {
					throw new Exception(__('The file you are trying to upload is not within the allowed file types.', 'bftpro'));
				}			
				
				$fileblob = file_get_contents($_FILES['field_'.$field->id]['tmp_name']);
				$data = $_FILES['field_'.$field->id]['name'];
			}

			// required field?
			if(!$in_admin and $field->is_required and empty($data)) throw new Exception(__('You have missed a required field', 'bftpro'));			
			
			if(empty($data) and $field->ftype != 'checkbox') continue;
			
			// replace/insert data			
			$wpdb->query($wpdb->prepare("REPLACE INTO ".BFTPRO_DATAS." (field_id, list_id, user_id, data, fileblob)
				VALUES (%d, %d, %d, %s, %s)", $field->id, $vars['list_id'], $uid, $data, $fileblob));
		}
	}
	
	function unsubscribe($user) {
		global $wpdb;
		
		$unsubscribe_action = get_option('bftpro_unsubscribe_action');
		
		// if user is already inactive and we shouldn't delete, don't run queries
		if(!$user->status and $unsubscribe_action == 'deactivate') return false;
			
		// select number of AR emails received 
		$num_emails = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_SENTMAILS."
			WHERE user_id=%d AND errors='' ", $user->id)); 	
			
		$reason = empty($_POST['reason']) ? '' : sanitize_text_field($_POST['reason']);
		if($reason == 'other')	$reason = empty($_POST['other_reason']) ? __('Other', 'bftpro') : sanitize_text_field($_POST['other_reason']); 
			
		// insert into unsubscribed table
		$unsub_email = ($unsubscribe_action == 'deactivate') ? $user->email : md5($user->email); // obfuscate user email in case we have chosen to delete (right to be forgotten)
		$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_UNSUBS." SET
			email=%s, list_id=%d, date=CURDATE(), ar_mails=%d, mcat=%s, mail_id=%d, reason=%s", 
			$unsub_email, $user->list_id, $num_emails, sanitize_text_field(@$_GET['mcat']), 
			intval(@$_GET['mail_id']), $reason));
			
		// unsubscribe notify?
		require_once(BFTPRO_PATH."/models/list.php");
		$_list = new BFTProList();
		$list = $_list->select($user->list_id);	
		if($list->unsubscribe_notify) $_list->notify_admin($user->id, 'unsubscribe', $reason);
		
		do_action('bftpro_user_unsubscribed', $user->id, $list->id);
		
		// now delete or deactivate
		if($unsubscribe_action == 'deactivate') {
			$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET status=0, unsubscribed=1 WHERE id=%d", $user->id));						
		}
		else {
			$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_USERS." WHERE id=%d", $user->id));
		}
		
		// redirect?
		if(!empty($list->unsubscribe_redirect)) bftpro_redirect($list->unsubscribe_redirect);
			
		return true;	
	}
	
	// send double opt-in email	
	function send_activation_email($vars, $list) {
		 global $wpdb;
		 
		 $_sender = new BFTProSender();
		 
		 if(!empty($list->confirm_email_subject)) {
		 		$subject = $list->confirm_email_subject;
		 		$message = $list->confirm_email_content;
		 }
		 else {
		 	  $subject = get_option('bftpro_optin_subject');
		 	  $message = get_option('bftpro_optin_message');
		 }
		 
		 if(!strstr($message, "{{url}}")) $message.="<br>\n<br>\n<a href='{{url}}'>{{url}}</a>";
		 
		 // extracts or sets a "firstname" field regardless the fact it does not exist
		 if(strstr(@$vars['name']," ")) {
				$parts=explode(" ",$vars['name']);
				$firstname=$parts[0];
		 }
		 else $firstname=@$vars['name'];
		 $message = str_replace('{{firstname}}', $firstname, $message);
		 $message = str_replace('{{name}}', @$vars['name'], $message);
		 $subject = str_replace('{{firstname}}', $firstname, $subject);
		 $subject = str_replace('{{name}}', @$vars['name'], $subject);
		 $message = str_replace('{{date}}', date_i18n(get_option('date_format'), current_time('timestamp')), $message);
		 
		 $subject = stripslashes($subject);
		 $message = stripslashes($message);
		 
		 // generate code for this user
		 $code = substr(md5($vars['email'].time()), 0, 8);
		 
		 $wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET
		 	code=%s WHERE email=%s AND list_id=%d", $code, $vars['email'], $list->id));
		 
		 $url = home_url("?bftpro_confirm=1&email=$vars[email]&list_id={$list->id}&code=$code");
		 
		 $message = str_replace("{{url}}", $url, $message);
		 $message = str_replace("{{list-name}}", stripslashes($list->name), $message);
		 $subject = str_replace("{{list-name}}", stripslashes($list->name), $subject);
		 
		 $list->sender = empty($list->sender)?get_option('bftpro_sender'):$list->sender;
		 $_sender->send($list->sender, $vars['email'], $subject, $message);
	}
	
	// double opt-in confirmtion
	function confirm() {
		// find this user
		global $wpdb;
			
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." 
			WHERE email=%s AND list_id=%d", sanitize_email($_GET['email']), intval($_GET['list_id'])));
		if(empty($user->id)) return false;
	
		if($user->status) return __("Your account is already active.", 'bftpro');
		
		// check code
		
		if($user->code!=$_GET['code']) return false;
		
		require_once(BFTPRO_PATH."/models/list.php");
		$_sender = new BFTProSender();
		$_list = new BFTProList();
		$list = $_list->select($user->list_id);
		
		// now update user and send immediate emails
		$code=substr(md5(@$vars['email'].microtime()),0,8);
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET 
			status=1, code=%s, datetime=%s WHERE id=%d", $code, current_time('mysql'), $user->id));		
			
		// paid list? if so, we have to overwrite the message with the payment buttons
		// and not execute any of the other things until the list is paid		
		if(!empty($list->fee) and $list->fee > 0 and class_exists('BFTIList')) {
			// revert back to unconfirmed status and set the "status_info" field to 'pending payent'
			$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET status=0, status_info = 'pending payment' WHERE id=%d", $user->id));					
			
			// render buttons
			$message = BFTIList :: payment_buttons($list, $user->id);
			return $message;
		}	
		
		$_sender->immediate_mails($user->id);				
		
		$this->subscribe_triggers($user->id, $list);
		
		if($list->do_notify) $_list->notify_admin($user->id, 'register');
		
		do_action('bftpro_user_subscribed', $user->id, $list->id);	
		do_action('bfti-handle-magnet', $user->id);	
		
		// redirect?
		if($list->redirect_confirm) {
			if(!empty($list->redirect_confirm_prepend_uid)) {
				$id = $user->id;				 
				bftpro_redirect($list->redirect_confirm, array('arigatopro_id' => $id, 'hash' => md5($user->hash)));
			} 
			else bftpro_redirect($list->redirect_confirm);
		}
		
		$message = __("You have successfully confirmed your subscription.", 'bftpro');
		return $message;
	}	
	
	// selects receivers for given email message along with custom fields data
	function select_receivers($extra_conditions, $limit) {
		global $wpdb;
		$limit = intval($limit);
		$limit_sql = $limit ? "LIMIT $limit" : "";
				
		$users = $wpdb->get_results("SELECT * FROM ".BFTPRO_USERS." 
		WHERE status=1 $extra_conditions ORDER BY id $limit_sql");		
		
		$users = $this->add_extra_data($users);
		
		return $users;
	}
	
	// adds the extra data to $users
	function add_extra_data($users) {
		global $wpdb;
		
		$ids=array(0);
		foreach($users as $user) $ids[]=$user->id;
		$id_sql=implode(",", $ids);
		
		// now select all datas for these receivers
		$datas = $wpdb->get_results("SELECT tD.*, tF.name as field_name, tF.label as field_label 
				FROM ".BFTPRO_DATAS." tD JOIN ".BFTPRO_FIELDS." tF ON tF.id=tD.field_id
				WHERE tD.user_id IN ($id_sql)");
		
		// now match the datas on $object->fields[ID]
		foreach($users as $cnt => $user) {
			$fields = array();
			$pretty_fields = array();
			$named_fields = array();
			foreach($datas as $data) {
				if($data->user_id!=$user->id) continue;
				$fields[$data->field_id]=$data->data;
				$pretty_fields[$data->field_label] = $data->data;
				$named_fields[$data->field_name] = $data->data;
			}
			
			$users[$cnt]->fields = $fields;
			$users[$cnt]->pretty_fields = $pretty_fields;
			$users[$cnt]->named_fields = $named_fields;
		} // end foreach user
		
		return $users;
	}
	
	// do actions when user subscribes - for example maybe sign them as WP user
	function subscribe_triggers($user_id, $list) {
		global $wpdb;
		
		// auto-subscribe to blog
		if($list->subscribe_to_blog == 1) {			
			// select user
			$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE id=%d", $user_id));
			
			// if user is auto-subscribed in this list, we should not continue with this procedure to avoid endless loop
			if($user->d) $action_completed = true;
			
			// if the user is already registered, do nothing
			$wp_user = get_user_by('email', $user->email);
			if(!empty($wp_user->ID)) $action_completed = true;
			// die("we are here");
			if(empty($action_completed)) {
				// prepare desired username
				$target_username = empty($user->name) ? strtolower(substr($user->email, 0, strpos($user->email, '@')+1)) : strtolower(preg_replace("/\s/",'_',$user->name));
				
				// check if target username is available
				$wp_user = get_user_by('login', $target_username);
				
				// if not, find how many users whose username starts with this are available, and add a number to make it unique
				// then again check if it's unique, and if not, add timestamp
				if(!empty($wp_user->ID)) {
					$num_users = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->users} WHERE user_login LIKE '$target_username%'");
					
					if($num_users) {
						$num = $num_users+1;
						$old_target_username = $target_username;
						$target_username = $target_username."_".$num;
						
						$wp_user = get_user_by('login', $target_username);
					
						// still not unique? Add timestamp and hope no one is crazy enough to have the same
						if(!empty($wp_user->ID)) $target_username = $old_target_username . '_' . time(); 
					}
				}
				
				// finally use the username to create the user
				$random_password = wp_generate_password();
				$user_id = wp_create_user( $target_username, $random_password, $user->email );
				
				// update name if any
				if(!empty($user->name)) {
					@list($fname, $lname) = explode(" ", $user->name);
					wp_update_user(array("ID"=>$user_id, "first_name"=>$fname, "last_name"=>$lname));
				}
				
				// update role if required
				if(!empty($list->subscribe_to_blog_role)) {
					wp_update_user(array("ID"=>$user_id, "role"=>$list->subscribe_to_blog_role));
				}
			}
		} // end subscribing as WP user
		
		return true;
	}
}