<?php
class BFTProARController {
	// manage AR campaigns
	static function manage() {
		require_once(BFTPRO_PATH."/models/ar.php");
		require_once(BFTPRO_PATH."/models/list.php");		
		$_list=new BFTProList();	
		$_ar = new BFTProARModel();
		global $wpdb, $user_ID;
		
		$multiuser_access = 'all';
		$multiuser_access = BFTProRoles::check_access('ar_access');
		
		switch(@$_GET['do']) {
			case 'edit':
				if($multiuser_access == 'own') {
					$campaign = $_ar->select($_GET['id']);
					if(@$campaign->editor_id != $user_ID) wp_die(__('You can manage only your own autoresponder campaigns.', 'bftpro'));
				}	
				if(!empty($_POST['del'])) {
					$_ar->delete($_GET['id']);
					$_SESSION['flash'] = __("Autoresponder campaign deleted", 'bftpro');
					bftpro_redirect("admin.php?page=bftpro_ar_campaigns");		
				}
			
				if(!empty($_POST['ok'])) {
					try {
						$_ar->edit($_POST, $_GET['id']);	
						$_SESSION['flash'] = __("Autoresponder campaign saved", 'bftpro');
						bftpro_redirect("admin.php?page=bftpro_ar_campaigns");					
					}
					catch(Exception $e) {
						$error = $e->getMessage();		
						$campaign = (object)$_POST;	
						$_SESSION['flash'] = $error;
						$list_ids = $_POST['list_ids'];
					}
				}
				
				// select campaign
				if(empty($error)) {
				   $campaign = $_ar->select($_GET['id']);
   				$list_ids = explode("|", $campaign->list_ids);
				}
				
				// select lists		
				$lists=$_list->select();		
				
				require(BFTPRO_PATH."/views/ar-form.php");
			break;
			
			case 'add':
				if(!empty($_POST['ok'])) {
					try {
						$_ar->add($_POST);	
						$_SESSION['flash'] = __("Autoresponder campaign created", 'bftpro');
						bftpro_redirect("admin.php?page=bftpro_ar_campaigns");					
					}
					catch(Exception $e) {
						$error=$e->getMessage();		
						$campaign = (object)$_POST;		
						$_SESSION['flash'] = $error;
						$list_ids = $_POST['list_ids'];
					}
				}
				
				// select lists
				$lists=$_list->select();		
				
				require(BFTPRO_PATH."/views/ar-form.php");
			break;			
			
			default:
            if(!empty($_POST['import']) and check_admin_referer('bftpro_import')) self :: import();			
            if(!empty($_GET['export'])) return self :: export();			
			
				// list my campaigns
				$campaigns = $_ar->select();
				
				if(!empty($_GET['list_id'])) {
					// filter by list ID
					$filtered=array();
					foreach($campaigns as $campaign) {
						if(strstr($campaign->list_ids, "|".$_GET['list_id']."|")) $filtered[] = $campaign;
					}
					
					$campaigns = $filtered;
					
					$filter_list = $_list->select($_GET['list_id']);					
				}				
				
				// select lists
				$lists=$_list->select();	
				
				// match lists to campaigns
				foreach($campaigns as $cnt=>$campaign) {
					$campaigns[$cnt]->lists = array();
					foreach($lists as $list) {
						if(strstr($campaign->list_ids, "|".$list->id."|")) $campaigns[$cnt]->lists[] = $list;
					}
					
					// and select num emails
					$num_mails = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_MAILS." 
						WHERE ar_id=%d", $campaign->id));						
					$campaigns[$cnt]->num_mails = $num_mails;	
					
					// select sent mails and read mails to figure out % opened
					$sent_mails = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tS.id) FROM ".BFTPRO_SENTMAILS."
						tS JOIN ".BFTPRO_MAILS." tM ON tM.id = tS.mail_id 
						WHERE tM.ar_id=%d AND tS.errors='' ", $campaign->id));
						
					$read_mails = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tR.id) FROM ".BFTPRO_READMAILS."
						tR JOIN ".BFTPRO_MAILS." tM ON tM.id = tR.mail_id 
						WHERE tM.ar_id=%d", $campaign->id));	
					
					$percent_read = empty($sent_mails) ? 0 : round(100 * $read_mails / $sent_mails);
					$campaigns[$cnt]->percent_read = $percent_read; 
					
				}
				require(BFTPRO_PATH."/views/ars.php");
			break;
		}
	}
	
	// manage emails
	static function mails() {
		global $wpdb, $user_ID;			
		require_once(BFTPRO_PATH."/models/attachment.php");
		$_mail = new BFTProMailModel();	
		$_ar = new BFTProARModel();
		$_att = new BFTProAttachmentModel();
		
		$multiuser_access = 'all';
		$multiuser_access = BFTProRoles::check_access('ar_access');
		
		// campaign ID should always be in GET
		$ar = $_ar->select($_GET['campaign_id']);
		if($multiuser_access == 'own' and @$ar->editor_id != $user_ID) wp_die(__('You can manage only your own autoresponder campaigns.', 'bftpro'));
		
		$_POST['ar_id'] = $ar->id;
		$dateformat = get_option("date_format");
		
		// select custom fields from the lists assigned to this autoresponder
		$list_ids=explode("|",$ar->list_ids);
		$list_ids = array_filter($list_ids);
		if(!empty($list_ids)) {
			$fields = $wpdb->get_results("SELECT tF.*, tL.name as list_name
				FROM ".BFTPRO_FIELDS." tF JOIN ".BFTPRO_LISTS." tL ON tL.id=tF.list_id
				WHERE tF.list_id IN (".implode(",", $list_ids).")");
		}
		
		// accordingly to what time will emails be sent
		if(get_option('bftpro_use_wp_time') == 1) {
		   $server_time = date("H:i", current_time('timestamp'));
		   $use_wp_time = true;
		}
		else {
		   // current mysql time
   		$server_time = $wpdb->get_var("SELECT NOW()");
   		list($date, $time) = explode(" ", $server_time);
   		list($h, $m, $s) = explode(":", $time);
   		$server_time = $h.":".$m;
   		$use_wp_time = false;
		}		
		
		switch(@$_GET['do']) {
			case 'edit':
				if(!empty($_POST['del'])) {
					$_mail->delete($_GET['id']);
					$_SESSION['flash'] = __("Email message deleted", 'bftpro');
					bftpro_redirect("admin.php?page=bftpro_ar_mails&campaign_id=$_GET[campaign_id]");				
				}					
			
			
				if(!empty($_POST['ok'])) {
					try {
						$_mail->edit($_POST, $_GET['id']);	
						$_SESSION['flash'] = __("Email message saved", 'bftpro');
						
						// send test
						if(!empty($_POST['test_email'])) {
							unset($_POST['send']);
							$_SESSION['flash'] = __("A test email has been sent to you.", 'bftpro');
							self :: send_test($_GET['id']);
						}						
						
						bftpro_redirect("admin.php?page=bftpro_ar_mails&campaign_id=$_GET[campaign_id]");					
					}
					catch(Exception $e) {
						$error=$e->getMessage();			
					}
				}
				
				// select this email
				$mail =$_mail->select($ar->id, $_GET['id']);
				
				// select attachments
				$attachments = $_att->select("mail", $mail->id);
				
				require(BFTPRO_PATH."/views/mail-form.php");
			break;
			
			case 'add':
				if(!empty($_POST['ok'])) {
					try {
						$mid = $_mail->add($_POST);	
						$_SESSION['flash'] = __("Email message created", 'bftpro');
						// send test
						if(!empty($_POST['test_email'])) {
							unset($_POST['send']);
							$_SESSION['flash'] .= '<br>' . __("A test email has been sent to you.", 'bftpro');
							self :: send_test($mid);
						}	
						bftpro_redirect("admin.php?page=bftpro_ar_mails&campaign_id=$_GET[campaign_id]");					
					}
					catch(Exception $e) {
						$error=$e->getMessage();			
					}
				}
				
				if(!empty($_GET['preset_id'])) {
					$mail = (object)array("preset_id"=>intval($_GET['preset_id']));
				}
				
				require(BFTPRO_PATH."/views/mail-form.php");
			break;		
			
			case 'copy':
			   if(check_admin_referer('bftpro_clone_campaign')) {
			      self :: clone_email(intval($_GET['id']));
			      bftpro_redirect("admin.php?page=bftpro_ar_mails&campaign_id=". $_GET['campaign_id']);
			   }			   
			break;	
			
			default:
				// list my campaigns
				$mails = $_mail->select($ar->id);		
				
				require(BFTPRO_PATH."/views/mails.php");
			break;
		}
	} // end manage emails
	
	// show log of sent emails
	static function log() {
		global $wpdb;
		require_once(BFTPRO_PATH."/models/ar.php");
		$_ar = new BFTProARModel();
		// select email message
		$mail = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_MAILS." WHERE id=%d", $_GET['id']));
		
		// select campaign
		$ar = $_ar -> select($mail->ar_id);
		
		$date_sql = '';
		if(!empty($_GET['from']) and !empty($_GET['to'])) {
			$date_sql = $wpdb->prepare(" AND tM.date >= %s AND tM.date <= %s ", $_GET['from'], $_GET['to']); 
		}
		
		$isread_sql = empty($_GET['is_read']) ? "" : " AND tR.id IS NOT NULL ";
		
		$dateformat = get_option('date_format');
				
		// now select 100 sent mails
		$limit = 100;
		$offset = empty($_GET['offset'])?0:intval($_GET['offset']);
		$sent_mails = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tU.email as email, tM.date as date, 
			tM.user_id as user_id, tU.list_id as list_id, tL.name as list_name, tR.id as is_read
			FROM ".BFTPRO_SENTMAILS." tM LEFT JOIN ".BFTPRO_USERS." tU ON tU.id=tM.user_id
			LEFT JOIN ".BFTPRO_LISTS." tL ON tL.id = tU.list_id
			LEFT JOIN ".BFTPRO_READMAILS." tR ON tR.mail_id = tM.mail_id AND tR.user_id=tM.user_id 
			WHERE tM.mail_id=%d AND tM.errors='' $date_sql $isread_sql
			ORDER BY tM.id DESC LIMIT $offset, $limit", $mail->id));
		$cnt_mails=$wpdb->get_var("SELECT FOUND_ROWS()");		
		
		require(BFTPRO_PATH."/views/ar-log.php");			
	}
	
	// immediately send test email
	static function send_test($mid) {
		global $wpdb;
		
		// select message
		$mail = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_MAILS." WHERE id=%d", $mid));
		$attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS."
					WHERE mail_id = %d ORDER BY id", $mail->id));	
		$subject = '[TEST] '.stripslashes($mail->subject);			
		$message = stripslashes($mail->message);
		
		BFTProTests :: test_email($mail->sender, $subject, $message, $mail->mailtype, 0, @$mail->template_id, $attachments, $mail->preset_id, $mail->send_test);
 	} // end send_test()
 	
 	// export campaign as PHP serialized txt file
 	static function export() {
 	   global $wpdb;
 	   
 	   // select campaign
 	   $ar = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_ARS." WHERE id=%d", $_GET['campaign_id']));
 	   
 	   // select emails in this campaign
 	   $_mail = new BFTProMailModel();	
 	   $mails = $_mail->select($ar->id);	
 	   foreach($mails as $cnt => $mail) {
 	      $mails[$cnt] = (array) $mail;
 	   }
  	   $ar->emails = $mails;
 	   
 	   $ar = (array) $ar;
 	   
 	   $content = serialize($ar);	 	   

		$now = gmdate('D, d M Y H:i:s') . ' GMT';
		header('Content-Type: ' . bftpro_get_mime_type());
		header('Expires: ' . $now);
		header('Content-Disposition: attachment; filename="campaign-'.$_GET['campaign_id'].'.txt"');
		header('Pragma: no-cache');
		echo $content;
		exit;			
 	}
 	
 	// imports a campaign from a serialized txt file
 	static function import() {
 	   global $wpdb;
 	   $_ar = new BFTProARModel();
 	   $_mail = new BFTProMailModel();
 	   
 	   if(!empty($_FILES['import_file']['tmp_name'])) {
 	      $content = file_get_contents($_FILES['import_file']['tmp_name']);     
 	      
 	      $ar = unserialize($content);
 	      $ar = stripslashes_deep($ar);
 	      if(empty($ar['id'])) wp_die(__('The file is in invalid format or broken. You can import only unedited campaign files exported by Arigato PRO.', 'bftpro'));
 	      
         // now do the import
         $ar['name'] .= ' '.__('(Imported)', 'bftpro');
         try {
         	$id = $_ar->add($ar);
         }
         catch(Exception $e) {
         	wp_die($e->getMessage());
         }
         
         foreach($ar['emails'] as $email) {
            $preset_id = 0; // presets must be cleaned up to avoid preparing the vars, but then re-saved 
            $email['ar_id'] = $id;
            self :: copy_email($email);
         }  // end foreach email 
      } // end if
 	} // end import
 	
 	// clone a single email
 	static function clone_email($id) {
 	   global $wpdb;
 	   
 	   // select email 
 	   $email = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_MAILS." WHERE id=%d", $id), ARRAY_A);
 	   $email['subject'] .= ' ' . __('(Copy)', 'bftpro');
 	   self :: copy_email($email);
 	}
 	
 	// a helper that actually copies the email
 	static function copy_email($email) {
 	   global $wpdb;
 	   $_ar = new BFTProARModel();
 	   $_mail = new BFTProMailModel();
 	   
 	   if(!empty($email['preset_id'])) {
         $preset_id = $email['preset_id'];
         $email['preset_id'] = 0;       
      }
      $mid = $_mail->add($email);
      
      // copy attachments if any
      $attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS." 
         WHERE mail_id=%d ORDER BY id", $email['id']));
     
      foreach($attachments as $att) {
         $wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_ATTACHMENTS." SET
            mail_id=%d, file_name=%s, file_path=%s, url=%s",
            $mid, $att->file_name, $att->file_path, $att->url));
      }   
      
      if(!empty($preset_id)) {
         $wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_MAILS." SET preset_id=%d WHERE id=%d", $preset_id, $mid));
      } // end re-saving the preset
 	} // end copy_email helper
}