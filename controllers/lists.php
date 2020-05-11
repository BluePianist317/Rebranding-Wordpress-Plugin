<?php
// mailing lists controller
function bftpro_mailing_lists() {
	require_once(BFTPRO_PATH."/models/list.php");
	$_list=new BFTProList();
	global $wpdb, $user_ID, $wp_roles;
	$roles = $wp_roles->roles;
	
	$do=empty($_GET['do'])?"list":$_GET['do'];
	$multiuser_access = 'all';
	$multiuser_access = BFTProRoles::check_access('lists_access');
	
	// for admin notification emails
	$default_signup_message = __("New member signed up for your mailing list.", 'bftpro') ."\n";
	$default_signup_message .= __("Email: {{{email}}}", 'bftpro')."\n";
	$default_signup_message .= __("Name: {{{name}}}", 'bftpro')."\n";
	$default_signup_message .= '{{{custom-fields}}}';
	
	// Woocommerce installed and integration selected? Select products
	$integrate_woocommerce = get_option('bftpro_integrate_woocommerce');
	
	if(class_exists('Woocommerce') and function_exists('wc_get_products')) {
		// select woocommerce products
		$args = array('limit'=>1000, 'orderby' => 'name', 'order' => 'ASC');
		$woo_products = wc_get_products($args);
	}
	
	switch($do) {
		case 'add':
			if(!empty($_POST['ok'])) {		
			   try {
			      $id = $_list->add($_POST);
   				do_action('bftpro-list-saved', $id, $_POST);
   				$_SESSION['flash']=__("Mailing list created", 'bftpro');
   				bftpro_redirect("admin.php?page=bftpro_mailing_lists");
			   }		
            catch(Exception $e) {
               $_SESSION['flash'] = $e->getMessage();
               $list = (object)$_POST;
            }				
			}
			$notify_email = get_option('bftpro_sender');
			// make sure the email is not entered as Name <email@dot.com>
			if(strstr($notify_email, "<")) {
				$parts = explode("<", $notify_email);
				$parts[1] = str_replace(">", "", $parts[1]);
				$notify_email = trim($parts[1]);
			}	
			require(BFTPRO_PATH."/views/list.php");
		break;		
		
		case 'edit':
			$list = $_list->select($_GET['id']);
			
			if($multiuser_access == 'own') {				
				if(@$list->editor_id != $user_ID) wp_die(__('You can manage only your own mailing lists.', 'bftpro'));
			}		
		
			if(!empty($_POST['del'])) {
				$_list->delete($_GET['id']);
				$_SESSION['flash']=__("Mailing list deleted", 'bftpro');
				bftpro_redirect("admin.php?page=bftpro_mailing_lists");
			}			
		
			if(!empty($_POST['ok']) and check_admin_referer('bftpro_list')) {
			   try {
			      $_list->save($_POST, $_GET['id']);
   				do_action('bftpro-list-saved', $_GET['id'], $_POST);
   				
					// change editor if required
					if($multiuser_access == 'all' and $list->editor_id != $_POST['editor_id']) {
						$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_LISTS." SET editor_id=%d WHERE id=%d",
							intval($_POST['editor_id']), $list->id));
					}  				
   				
   				$_SESSION['flash']=__("Mailing list updated", 'bftpro');
   				bftpro_redirect("admin.php?page=bftpro_mailing_lists");
			   } 
            catch(Exception $e) {
               $_SESSION['flash'] = $e->getMessage();
               $list = (object)$_POST;
               $list->id = $_GET['id'];              
            }				
			}
			
			// select list
			if(empty($list->id)) $list = $_list->select($_GET['id']);
			
			$list->notify_email = empty($list->notify_email) ? get_option('bftpro_sender') : $list->notify_email;
			// make sure the email is not entered as Name <email@dot.com>
			if(strstr($list->notify_email, "<")) {
				$parts = explode("<", $list->notify_email);
				$parts[1] = str_replace(">", "", $parts[1]);
				$list->notify_email = trim($parts[1]);
			}			
			
			if($multiuser_access == 'all') {
				$editors = get_users(array("role" => 'administrator'));
				$more_roles = false;		
				foreach($roles as $key=>$r) {			
					$role = get_role($key);
					if(empty($role->capabilities['bftpro_manage'])) continue;
					
					// add users to $editors array
					$users = get_users(array("role" => $key)); 
					$editors = array_merge($editors, $users);
					$more_roles = true;
				}
			}			
			
			require(BFTPRO_PATH."/views/list.php");
		break;
		
		case 'duplicate':
			if(!empty($_GET['duplicate']) and check_admin_referer('bftpro_lists')) {
				$_list->duplicate($_GET['id']);
				bftpro_redirect('admin.php?page=bftpro_mailing_lists');
			}
		break;
		
		default:
			if(!empty($_POST['mass_delete']) and check_admin_referer('bftpro_lists')) {
				$lids = empty($_POST['list_ids']) ? array() : $_POST['list_ids'];
				foreach($lids as $lid) $_list->delete($lid);
			}		
		
			// select existing lists
			$lists = $_list->select();
			
			// select % open rate in each list
			foreach($lists as $cnt=>$list) {
				$sent_mails = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tS.id) FROM ".BFTPRO_SENTMAILS." tS
					JOIN ".BFTPRO_USERS." tU ON tU.id = tS.user_id
					WHERE tU.list_id = %d AND tS.errors='' ", $list->id));
				
				$read_mails = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tR.id) FROM ".BFTPRO_READMAILS." tR
					JOIN ".BFTPRO_USERS." tU ON tU.id = tR.user_id
					WHERE tU.list_id = %d", $list->id));
					
				$read_nls = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tR.id) FROM ".BFTPRO_READNLS." tR
					JOIN ".BFTPRO_USERS." tU ON tU.id = tR.user_id
					WHERE tU.list_id = %d", $list->id));	
					
				$open_rate = empty($sent_mails) ? 0 : round( 100 * ($read_mails + $read_nls) / $sent_mails);
				$lists[$cnt]->open_rate = $open_rate;
				
				// auto subscribes users by woocommerce?
				if(class_exists('Woocommerce') and $integrate_woocommerce and !empty($list->woo_products)) {
					$woo_ids = explode('|', $list->woo_products);
					$woo_ids = array_filter($woo_ids);
					
					$woo_msg = __('Users automatically subscribe to this list when they purchase any of the following products:', 'bftpro').' ';
			
					foreach($woo_ids as $wct => $woo_id) {
						
						$woo_product = wc_get_product($woo_id);
						
						if($wct > 1) $woo_msg .= ', ';
						$woo_msg .= stripslashes($woo_product->name);	
					}
					
					$lists[$cnt]->woo_msg = $woo_msg;
				} // end $woo_msg
				
				// auto unsubscribes users by woocommerce?
				if(class_exists('Woocommerce') and $integrate_woocommerce and !empty($list->woo_products_unsub)) {
					$woo_ids_unsub = explode('|', $list->woo_products_unsub);
					$woo_ids_unsub = array_filter($woo_ids_unsub);
					
					$woo_msg_unsub = __('Users automatically un-subscribe from this list when they purchase any of the following products:', 'bftpro').' ';
			
					foreach($woo_ids_unsub as $wct => $woo_id_unsub) {
						
						$woo_product_unsub = wc_get_product($woo_id_unsub);
						
						if($wct > 1) $woo_msg_unsub .= ', ';
						$woo_msg .= stripslashes($woo_product_unsub->name);	
					}
					
					$lists[$cnt]->woo_msg_unsub = $woo_msg_unsub;
				} // end $woo_msg
				
				// if Gozaimasu exists count the existing designs
				if(class_exists('Gozaimasu')) {
					$num_designs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM " . BFTPRO_FORM_DESIGNS." WHERE list_id=%d", $list->id));
					$lists[$cnt]->num_designs = $num_designs;
				}	// end counting designs			
				
			}	// end foreach list
			
			
			require(BFTPRO_PATH."/views/lists.php");
		break;
	}	
}

// manage subscribers
function bftpro_subscribers() {
	global $wpdb, $user_ID;
	require_once(BFTPRO_PATH."/models/list.php");
	require_once(BFTPRO_PATH."/models/field.php");
	require_once(BFTPRO_PATH."/models/data.php");
	require_once(BFTPRO_PATH."/models/user.php");
	
	$multiuser_access = 'all';
	$multiuser_access = BFTProRoles::check_access('lists_access');
	
	$_list=new BFTProList();
	$_field=new BFTProField();
	$_user=new BFTProUser();
	$dateformat = get_option('date_format');
	
	// select this list
	$list=$_list->select($_GET['id']);
	if($multiuser_access == 'own') {		
		if(@$list->editor_id != $user_ID) wp_die(__('You can manage only your own mailing lists.', 'bftpro'));
	}	
	
	// select extra fields
	$fields=$_field->select($list->id);
	
	$do=empty($_GET['do'])?"list":$_GET['do'];
	switch($do) {
		case 'add':
			if(!empty($_POST['ok'])) {
				try {
					$message="";
					$_POST['list_id']=$list->id;
					$_POST['date']=$_POST['dateyear'].'-'.$_POST['datemonth'].'-'.$_POST['dateday'];	
					$_POST['source'] = '_admin';				
					$_user->subscribe($_POST, $message, true);
					bftpro_redirect("admin.php?page=bftpro_subscribers&id={$list->id}&message=".urlencode(__("User Added", 'bftpro')));
				}
				catch(Exception $e) {					
					$user=$_POST;
					$error=$e->getMessage();					
				}
			}		
		
			require(BFTPRO_PATH."/views/list-user.php");
		break;
		
		case 'edit':
			if(!empty($_POST['del'])) {
				$_user->delete($_GET['user_id']);
				bftpro_redirect("admin.php?page=bftpro_subscribers&id={$list->id}&message=".urlencode(__("User Deleted", 'bftpro')));
			}		
		
			if(!empty($_POST['ok'])) {
				try {
					$_POST['list_id']=$list->id;
					$_POST['date']=$_POST['dateyear'].'-'.$_POST['datemonth'].'-'.$_POST['dateday'];					
					$_user->save($_POST, $_GET['user_id'], true);					
					bftpro_redirect("admin.php?page=bftpro_subscribers&id={$list->id}&message=".urlencode(__("User Saved", 'bftpro')));
				}
				catch(Exception $e) {
					$user=$_POST;
					$error=$e->getMessage();					
				}
			}
		
			// select user
			$user=$_user->get($_GET['user_id']);		
					
			require(BFTPRO_PATH."/views/list-user.php");
		break;		
		
		case 'import':
			// select fields
			$fields = $_field->select($list->id);
			$cnt_fields = count($fields);
				
			if(!empty($_POST['import']) and check_admin_referer('bftpro_import')) {
				if(empty($_FILES["csv"]["name"])) {
					wp_die(__("Please upload file", 'bftpro'));
				}
				
				if(empty($_POST["delimiter"])) {
					wp_die(__("There must be a delimiter", 'bftpro'));
				}
			
				$row = $total = $invalid = 0;
				$invalid_emails = array();
				if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
					
					$delimiter = sanitize_text_field($_POST['delimiter']);					
					if($delimiter == 'tab') $delimiter = "\t";
					
					if(empty($_POST['import_fails'])) {	
						 while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {	    	
						  	 $row++;				    	 	
					       if(empty($data)) continue;
					       if(!empty($_POST['skip_first']) and $row==1) continue;	
					       bftpro_import_subscriber($data, $fields, $cnt_fields, $total, $invalid_emails, $invalid);							 
				       } // end while
				   }
					else {
				     // the customer says that import fails - let's try the handmade import function
				     $contents = fread($handle, filesize($_FILES['csv']['tmp_name']));
				     $contents = bftpro_normalize($contents);
				     $csv_lines = explode(PHP_EOL, $contents);
						 	foreach($csv_lines as $csv_line) {
						 		$row++;
						 		if(empty($csv_line)) continue;			  			  
						      if(!empty($_POST['skip_first']) and $row == 1) continue;
						      $data = bftpro_parse_csv_line($csv_line);		         
						      bftpro_import_subscriber($data, $fields, $cnt_fields, $total, $invalid_emails, $invalid);      
						 	} // end foreach line
					 }	// end alternate CSV parsing 

					 $success = sprintf(__("%d new subscribers imported.", 'bftpro'), $total);			
					 if($invalid) $success  .= '<br>'.sprintf(__('The following %d emails are invalid and were not imported: %s', 'bftpro'), 
						 $invalid, implode(', ', $invalid_emails));  
				} // end if
			}		
		
			require(BFTPRO_PATH."/views/list-import.php");
		break;
		
		case 'list':
			$orderby=empty($_GET['ob'])?"name":$_GET['ob'];
			$orderdir=empty($_GET['dir'])?"ASC":$_GET['dir'];
			$offset = empty($_GET['offset'])?0:$_GET['offset'];
			
			if(!empty($_GET['export'])) {
				$newline=bftpro_define_newline();
				list($users, $cnt_users) = $_user->select($list->id, $orderby, $orderdir);
				
				$titlerow = __("Email", 'bftpro').";".__("Name", 'bftpro').";".__("Date Signed", 'bftpro').";".__("Status", 'bftpro').";";
				foreach($fields as $field) $titlerow.=$field->label.";";
				
				
				$rows = array($titlerow);
				foreach($users as $user) {
					$row = $user['email'].";".$user['name'].";".$user['date'].";".($user['status']?__("Active", 'bftpro'):__("Inactive", 'bftpro')).";";
					
					foreach($fields as $field) $row .= @$user["field_".$field->id].";";
					
					$rows[]=$row;					
				}
				
				$csv=implode($newline, $rows);
				
				// credit to http://yoast.com/wordpress/users-to-csv/	
				$now = gmdate('D, d M Y H:i:s') . ' GMT';

				header('Content-Type: ' . bftpro_get_mime_type());
				header('Expires: ' . $now);
				header('Content-Disposition: attachment; filename="subscribers.csv"');
				header('Pragma: no-cache');
				echo $csv;
				exit;				
			}	
			
			// mass delete			
			if(!empty($_POST['mass_delete']) and is_array($_POST['ids']) and check_admin_referer('bftpro_subscribers')) {
				foreach($_POST['ids'] as $id) {					
					$_user->delete($id);	
				}
			}
			
			// mass move to other list
			if(!empty($_POST['mass_move']) and is_array($_POST['ids']) and check_admin_referer('bftpro_subscribers')) {
				foreach($_POST['ids'] as $id) {
					// if user exists in the target list, skip
					$user = $wpdb->get_row($wpdb->prepare("SELECT id, email FROM " . BFTPRO_USERS . " WHERE id=%d ", $id));
					
					$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . BFTPRO_USERS . " WHERE email=%s AND list_id=%d", $user->email, $_POST['move_to']));					
					if($exists) continue;			
					
					// delete data
					$wpdb->query($wpdb->prepare("DELETE FROM " . BFTPRO_DATAS . " WHERE user_id=%d", $id));
					
					// change list ID
					$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS . " SET list_id=%d WHERE id=%d ", intval($_POST['move_to']), $id));
				}
				
				$_SESSION['flash'] = __('Subscribers moved successfully.', 'bftpro');
			}
			
			// mass copy to other list
			if(!empty($_POST['mass_copy']) and is_array($_POST['ids']) and check_admin_referer('bftpro_subscribers')) {
				// select field names of custom fields in the target list. This is needed to avoid unnecessary queries and get only custom data that has matches
				$target_fields = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM ".BFTPRO_FIELDS." WHERE list_id=%d ORDER BY name", intval($_POST['move_to'])));		
				
				foreach($_POST['ids'] as $id) {
					// if user exists in the target list, skip
					$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . BFTPRO_USERS . " WHERE id=%d ", $id));
					
					$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . BFTPRO_USERS . " WHERE email=%s AND list_id=%d", $user->email, intval($_POST['move_to'])));					
					if($exists) continue;			

					// insert in the new list
					$wpdb->query($wpdb->prepare("INSERT INTO " . BFTPRO_USERS . " SET email=%s, name=%s, status=%d, date=%s, ip=%s,
						list_id=%d, auto_subscribed=1, source='copied', magnet_id=%d, status_info=%s, wp_user_id=%d",
						$user->email, $user->name, $user->status, $user->date, $user->ip, intval($_POST['move_to']), $user->magnet_id, $user->status_info, $user->wp_user_id));
						
					$user_id = $wpdb->insert_id;	
					
					// copy data if possible (must have same field names)
					// select custom data from the source list
					$datas = $wpdb->get_results($wpdb->prepare("SELECT tD.data as data, tD.fileblob as fileblob, tF.name as field FROM ".BFTPRO_DATAS." tD
						JOIN ".BFTPRO_FIELDS." tF ON tF.id = tD.field_id 
						WHERE tD.list_id = %d AND tD.user_id=%d ORDER BY tD.id", $user->list_id, $user->id));

					foreach($datas as $data) {
						// find target field ID if any
						foreach($target_fields as $field) {
							if($field->name === $data->field) {
								$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_DATAS." SET field_id=%d, user_id=%d, data=%s, list_id=%d, fileblob=%s",
									$field->id, $user_id, $data->data, intval($_POST['move_to']), $data->fileblob));
								break;
							}
						}	// end foreach target field					
					}	// end copying custom data
				} // end foreach ID
				
				$_SESSION['flash'] = __('Subscribers copied successfully.', 'bftpro');
			}
			
			// mass activate & deactivate
			if((!empty($_POST['mass_activate']) or !empty($_POST['mass_deactivate'])) and check_admin_referer('bftpro_subscribers') and is_array($_POST['ids']) ) {
				$ids = bftpro_int_array($_POST['ids']);
				if(count($ids) > 0) {
					$status = empty($_POST['mass_activate']) ? 0 : 1;
					$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET status=%d 
						WHERE id IN (".implode(',', $ids).") AND list_id=%d", $status, $list->id));
				}
			} // end activate/deactivate
			
			
			// resend activation email?
			if(!empty($_GET['resend_activation'])) {
				$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . BFTPRO_USERS . " WHERE id=%d", $_GET['user_id']));
				$_user->send_activation_email(array("name"=>$user->name, "email"=>$user->email), $list);
				$_SESSION['flash'] = sprintf(__("Activation message to %s has been sent.", 'bftpro'), $user->email);
			}
		
			$limit=20;
			if(!empty($_GET['filter_status']) and $_GET['filter_status'] == -2) $unsubscribed_filter = true;
			list($users, $cnt_users) = $_user->select($list->id, $orderby, $orderdir, $offset, $limit);
			
			// select sentmails and readmails to calculate open rate
			$sent_mails = $wpdb->get_results("SELECT COUNT(id) as cnt, user_id FROM ".BFTPRO_SENTMAILS." WHERE errors='' GROUP BY user_id");
			$read_mails =  $wpdb->get_results("SELECT COUNT(id) as cnt, user_id FROM ".BFTPRO_READMAILS." GROUP BY user_id");
			$read_nls =  $wpdb->get_results("SELECT COUNT(id) as cnt, user_id FROM ".BFTPRO_READNLS." GROUP BY user_id");
			
			$uids = array(0);
			foreach($users as $cnt => $user) {
				$uids[] = $user['id'];
				$num_sent = $num_read = 0;
				foreach($sent_mails as $sent_mail) {
					if($sent_mail->user_id == $user['id']) $num_sent += $sent_mail->cnt;
				}
				foreach($read_mails as $read_mail) {
					if($read_mail->user_id == $user['id']) $num_read += $read_mail->cnt;
				}
				foreach($read_nls as $read_nl) {
					if($read_nl->user_id == $user['id']) $num_read += $read_nl->cnt;
				}
				
				$open_rate = empty($num_sent) ? 0 : round( 100 * $num_read / $num_sent);
				
				$users[$cnt]['num_sent'] = $num_sent;
				$users[$cnt]['num_read'] = $num_read;
				$users[$cnt]['open_rate'] = $open_rate;
				
				// if user unsubscribed also add info where they did it from
				if($user['unsubscribed']) {
					$unsub_info = $wpdb->get_row($wpdb->prepare("SELECT tU.*, tM.subject as armail_subject, tM.ar_id as ar_id, tN.subject as nl_subject 
						FROM " . BFTPRO_UNSUBS ." tU
						LEFT JOIN ". BFTPRO_MAILS. " tM ON tM.id = tU.mail_id AND tU.mcat = 'ar'
						LEFT JOIN ". BFTPRO_NEWSLETTERS. " tN ON tN.id = tU.mail_id AND tU.mcat = 'nl'
						WHERE tU.email=%s AND tU.list_id=%d", $user['email'], $user['list_id']));
					if(!empty($unsub_info->id)) {
						$users[$cnt]['unsub_info_subject'] = ($unsub_info->mcat == 'nl') ? $unsub_info->nl_subject : $unsub_info->armail_subject;
						$users[$cnt]['unsub_info_url'] = ($unsub_info->mcat == 'nl') 
							? "admin.php?page=bftpro_newsletters&do=edit&id=" . $unsub_info->mail_id 
							: "admin.php?page=bftpro_ar_mails&do=edit&id=".$unsub_info->mail_id."&campaign_id=" . $unsub_info->ar_id;
					}	
				}
				
				// if user is auto subscribed from WP registration collect user data
				if(!empty($user['wp_user_id'])) {
					$wp_user = get_userdata($user['wp_user_id']);
					$users[$cnt]['wp_user'] = $wp_user;
				}
			} // end foreach user
			
			// select custom data if any			
			$datas = $wpdb->get_results($wpdb->prepare("SELECT tD.id as data_id, tD.data as data, tD.user_id as user_id, 
				tD.field_id as field_id, tF.label as label, tF.ftype as ftype, tF.field_date_format as field_date_format 
				FROM ".BFTPRO_DATAS." tD JOIN ".BFTPRO_FIELDS." tF ON tF.id = tD.field_id
				WHERE tD.user_id IN (".implode(",", $uids).") AND tD.list_id=%d
				ORDER BY tF.name", $list->id));
				
			if(count($datas)) {
				foreach($users as $cnt=>$user) {
					$custom_data = '';
					foreach($datas as $data) {
						if($data->user_id == $user['id']) {
							$data->data = BFTProField :: friendly($data->ftype, $data, $data->field_date_format);
							$custom_data .= sprintf(__("<b>%s:</b> %s<br>", 'bftpro'), stripslashes($data->label), stripslashes($data->data));
						}
					} // end foreach data
					
					$users[$cnt]['custom_data'] = $custom_data;
				} // end foreach booking
			} // end if custom data		
			

			// are there any filters? Used to know whether to display the filter box
			$any_filters = false;
			if(!empty($_GET['filter_email']) or !empty($_GET['filter_name']) or !empty($_GET['filter_ip'])
				or (isset($_GET['filter_status']) and intval($_GET['filter_status'])!=-1) 
				or !empty($_GET['readmails_from']) or !empty($_GET['readmails_to'])
				or !empty($_GET['filter_source'])
				or (isset($_GET['clicks_from']) and $_GET['clicks_from']!=='')
				or (isset($_GET['clicks_to']) and $_GET['clicks_to']!=='')
				or (!empty($_GET['signup_date_cond']) and !empty($_GET['filter_signup_date']))
				or (!empty($_GET['filter_tags'])) ) {
					$any_filters = true;
			}
			
			// filters  from custom fields?
			if(!$any_filters) {
				foreach($fields as $field) {
					if(!empty($_GET['filter_field_' . $field->id])) $any_filters = true;
				}
			}
			
			// select other mailing lists to allow moving subscribers	
			$other_lists = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM " . BFTPRO_LISTS . "
				WHERE id!=%d ORDER BY name", $list->id));		
			
			bftpro_enqueue_datepicker();
			require(BFTPRO_PATH."/views/list-users.php");
		default:
		break;
	}
}

function bftpro_fields() {
	global $wpdb;
	require(BFTPRO_PATH."/models/field.php");
	require_once(BFTPRO_PATH."/models/list.php");
	$_field=new BFTProField();
	$_list=new BFTProList();
	
	// select this mailing list
	$list=$_list->select($_GET['list_id']);

	// prepare vars for file fields	
	$filetypes = $filesize = '';
	
	switch(@$_GET['do']) {
		case 'add':
			if(!empty($_POST['ok'])) {
				$_POST['list_id']=$list->id;
				$_field->add($_POST);
				bftpro_redirect("admin.php?page=bftpro_fields&list_id={$list->id}&message=added");
			}
			
			require(BFTPRO_PATH."/views/list-field.php");
		break;

		case 'edit':
			if(!empty($_POST['del'])) {
				$_field->delete($_GET['id']);
				bftpro_redirect("admin.php?page=bftpro_fields&list_id={$list->id}&message=deleted");				
			}		
		
			if(!empty($_POST['ok']))
			{
				$_field->save($_POST, $_GET['id']);
				bftpro_redirect("admin.php?page=bftpro_fields&list_id={$list->id}&message=saved");
			}		
		
			$field=$_field->select($list->id, $_GET['id']);
			
			// file upload field?
			if($field->ftype == 'file') {
				list($filesize, $filetypes) = explode("|", $field->fvalues);
			}
			require(BFTPRO_PATH."/views/list-field.php");
		break;		
		
		default:
			$fields=$_field->select($list->id);
			require(BFTPRO_PATH."/views/list-fields.php");
		break;
	}	
}

function bftpro_import_subscriber($data, $fields, $cnt_fields, &$total, &$invalid_emails, &$invalid) {
	global $wpdb;
	
	  // get name and email
	 $parts=explode(",",$_POST["sequence"]);
	 
	 $email=@$data[trim($parts[0])-1];
	 $email=trim($email);
	
	 $nameparts=explode("+",$parts[1]);		
	 $name="";
	 foreach($nameparts as $npart) {
		$name.=@$data[(trim($npart)-1)].' ';
	 }
	 $name=trim($name);
	
	 if(preg_match("/^\"(.*)\"$/",$name)) {
		$name=str_replace("\"","",$name);
	 }		
	
	 if(preg_match("/^\"(.*)\"$/",$email)) {
		$email=str_replace("\"","",$email);
	 }		
	 
	 // very basic email validation
	 if(!strstr($email, '@') or !strstr($email, '.')) {
	 	 $invalid++;
	 	 $invalid_emails[] = $email;
	 	 return true;
	 }
	
	 $datepos=$_POST['date']-1;
	 $date=(empty($_POST['date']) or empty($data[$datepos]))?date("Y-m-d"):$data[$datepos];
	 
	 // ip address?
	 $ip = (!empty($_POST['ipnum']) and is_numeric($_POST['ipnum'])) ? $data[$_POST['ipnum']-1] : '';
	 
	 $exists = false;
	 
	 // handle duplicates
	 if(!empty($_POST['no_duplicates'])) {
	 	$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . BFTPRO_USERS." 
	 		WHERE email=%s AND list_id=%d", $email, intval($_GET['id'])));
	 		
	 	if($exists) {
	 		// update name, delete datas if any
	 		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET name=%s WHERE id=%d", $name, $exists));
	 		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_DATAS." WHERE user_id=%d AND list_id=%d", $exists, intval($_GET['id'])));
	 	}	
	 } // end duplicate handling
	 
	 if(!$exists) {	 	
		 // insert subscriber and get ID
		 $wpdb->query($wpdb->prepare("INSERT IGNORE INTO ".BFTPRO_USERS." SET
		 	email=%s, name=%s, status=1, date=%s, list_id=%d, ip=%s, source='_import'", $email, $name, $date, intval($_GET['id']), $ip));
		 
		 // insert datas if any
		 $mid = $wpdb->insert_id;		 
		 if($mid) $total++;
	}
	
	if($exists) $mid = $exists; // to re-insert the data of updated user
	 
	 if($mid and $cnt_fields) {
		 	$sql="INSERT INTO ".BFTPRO_DATAS." (field_id,user_id, data, list_id) VALUES ";
			$ins_sqls=array();
			$anyfield=false;
			
			foreach($fields as $field) {
				if(!empty($_POST['fieldseq_'.$field->id]))	
				{
					$seq=trim($_POST['fieldseq_'.$field->id])-1;
					$val=trim(@$data[$seq]);
					
					if(preg_match("/^\"(.*)\"$/",$val))
					{
						$val=substr($val,1,strlen($val)-2);
					}
					
					$ins_sqls[]=$wpdb->prepare(" (%d,%d,%s,%d) ", $field->id, $mid, $val, $_GET['id']);
										
					$anyfield=true;
				}
			}
			
			$sql.=implode(",",$ins_sqls);			
		   if($anyfield) $wpdb->query($sql);		 
	 } // end if
} // end import subscriber

function bftpro_parse_csv_line($line) {	
	$delimiter=$_POST['delimiter'];
	if($delimiter=="tab") $delimiter="\t";
	
   $fields = true ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
   return array_map(
       'bftpro_urlencode_csv_field',
       $fields
   );
}

function bftpro_urlencode_csv_field($field) {
 	 return str_replace('!!Q!!', '"', urldecode($field));
 }
 
 // thanks to https://www.darklaunch.com/2009/05/06/php-normalize-newlines-line-endings-crlf-cr-lf-unix-windows-mac
function bftpro_normalize($s) {
    // Normalize line endings
    // Convert all line-endings to UNIX format
    $s = str_replace("\r\n", "\n", $s);
    $s = str_replace("\r", "\n", $s);
    // Don't allow out-of-control blank lines
    $s = preg_replace("/\n{2,}/", "\n\n", $s);
    return $s;
}

// subscribe from WooCommerce
// the function will also handle the UN-subscribe selections
function bftpro_woo_subscribe($order_id) {
	global $wpdb;
	
	$integrate_woocommerce = get_option('bftpro_integrate_woocommerce');
	if($integrate_woocommerce == '') return false;
	
	require_once(BFTPRO_PATH."/models/user.php");
	$_user = new BFTProUser();
	
	// select line items (purchased products)
	$items = $wpdb->get_results($wpdb->prepare("SELECT tI.*, tM.meta_value as product_id 
			FROM {$wpdb->prefix}woocommerce_order_items tI JOIN {$wpdb->prefix}woocommerce_order_itemmeta tM
			ON tM.order_item_id = tI.order_item_id AND tM.meta_key='_product_id'
			WHERE tI.order_id = %d AND tI.order_item_type = 'line_item'", $order_id));
			
	$user_email = get_post_meta($order_id, "_billing_email", true);	
	$user_name = get_post_meta($order_id, "_billing_first_name", true). ' ' .get_post_meta($order_id, "_billing_last_name", true);	
	$list_ids = array();
			
	foreach($items as $item) {
		// search for lists with this woo_product_id
		$product_list_ids = $wpdb->get_results("SELECT id FROM ".BFTPRO_LISTS." WHERE woo_products LIKE '%|".$item->product_id."|%'");
		foreach($product_list_ids as $product_list) {
			if(!in_array($list_ids, $product_list->id)) $list_ids[] = $product_list->id;
		}
	}	// end foreach item
	
	// now subscribe the user	
	$vars = array("list_ids"=>$list_ids, "email"=>$user_email, "name"=>$user_name, "source"=>'_woo');
		
	// ignore exceptions
	try {			
		$message = '';
		$_user->ignore_redirect = true; 
		$_user->subscribe($vars, $message, true); // passes $in_admin as true to ignore captchas etc
	}
	catch(Exception $e) {}	
	
	// now Unsubscribe from any lists that should unsubscribe
	$list_ids = array();
	foreach($items as $item) {
		// search for lists with this woo_product_id
		$product_list_ids = $wpdb->get_results("SELECT id FROM ".BFTPRO_LISTS." WHERE woo_products_unsub LIKE '%|".$item->product_id."|%'");
		foreach($product_list_ids as $product_list) {
			if(!in_array($list_ids, $product_list->id)) $list_ids[] = $product_list->id;
		}
	}	// end foreach item
	
	// select users with this email address in any of the lists
	if(empty($list_ids)) return true;
	$list_id_sql = implode(',', $list_ids);
	$users = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE list_id IN ($list_id_sql) AND email=%s ORDER BY id", $user_email));

	// if no users return false
	if(!count($users)) return true;	
	
	$_user = new BFTProUser();
	foreach($users as $user) $_user -> unsubscribe($user); 
	
	return true;
} // end bftpro_woo_subscribe()