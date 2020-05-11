<?php
class BFTProNLController {
	// manage newsletters
	static function manage() {
		require_once(BFTPRO_PATH."/models/newsletter.php");
		require_once(BFTPRO_PATH."/models/list.php");		
		require_once(BFTPRO_PATH."/models/user.php");
		require_once(BFTPRO_PATH."/models/attachment.php");				
		$_list=new BFTProList();	
		$_nl = new BFTProNLModel();
		$_user = new BFTProUser();
		$_att = new BFTProAttachmentModel();
		global $wpdb, $user_ID;
		$dateformat = get_option('date_format');
		
		$multiuser_access = 'all';
		$multiuser_access = BFTProRoles::check_access('nl_access');		
		
		bftpro_enqueue_datepicker();
		
		switch(@$_GET['do']) {
			case 'edit':
				if($multiuser_access == 'own') {
					$mail = $_nl->select($_GET['id']);
					if(@$mail->editor_id != $user_ID) wp_die(__('You can manage only your own newsletters.', 'bftpro'));
				}				
			
				if(!empty($_POST['del']) and check_admin_referer('bftpro_newsletter')) {
					$_nl->delete($_GET['id']);
					$_SESSION['flash'] = __("Newsletter deleted", 'bftpro');
					bftpro_redirect("admin.php?page=bftpro_newsletters");		
				}			
			
				if(!empty($_POST['cancel'])) {
					// cancel current sending of this newsletter
					$_nl->cancel($_GET['id']);
				}
			
				if(!empty($_POST['ok']) and check_admin_referer('bftpro_newsletter')) {
					try {
						$_nl->edit($_POST, $_GET['id']);	
						$_SESSION['flash'] = __("The newsletter has been saved", 'bftpro');
						
						// send test
						if(!empty($_POST['test_newsletter'])) {
							unset($_POST['send']);
							$_SESSION['flash'] = __("A test email with this newsletter has been sent to you.", 'bftpro');
							self :: send_test($_GET['id']);
						}
						
						// save and send pressed
						if(!empty($_POST['send'])) {
							$_SESSION['flash'] = __("The newsletter has been saved and is being sent to selected mailing list.", 'bftpro');
							$_nl->send($_GET['id']);
						}						
						
						bftpro_redirect("admin.php?page=bftpro_newsletters");					
					}
					catch(Exception $e) {
						$error=$e->getMessage();			
					}
				}
				
				// select campaign
				$mail = $_nl->select($_GET['id']);
								
				// select lists		
				$lists=$_list->select();		
				// attachments
				$attachments = $_att->select("newsletter", $mail->id);
				
				// if it's a global newsletter, let's prepare the "still to go" text
				if($mail->is_global) {
						$lids = explode('|', $mail->lists_to_go);
						$lids = array_filter($lids);
						if(sizeof($lids)) {
							$togo_str = __('After sending to the current mailing list it will also be sent to:', 'bftpro').' ';
							foreach($lids as $lct=>$lid) {
								if($lct) $togo_str .= ', ';
								foreach($lists as $list) {
									if($list->id == $lid) $togo_str .= "<b>".$list->name."</b>";
								}
							}
						}
						else $togo_str = __('Currently sending to the last mailing list. When done, the newsletter will be completed.', 'bftpro');
						
						$mail->togo_str = $togo_str;
				} // end is_global
				
				// select custom fields
				if(!empty($mail->list_id)) {
					$custom_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_FIELDS." WHERE list_id=%d ORDER BY name", $mail->list_id));
				}
				
				if(!empty($mail->skip_lists)) {
					$skip_lists = explode(",", $mail->skip_lists);
				}
				
				require(BFTPRO_PATH."/views/newsletter-form.php");
			break;
			
			case 'add':
				if(!empty($_POST['ok'])) {
					try {
						$nid = $_nl->add($_POST);	
						$_SESSION['flash'] = __("The newsletter has been created", 'bftpro');
						
						// send test
						if(!empty($_POST['test_newsletter'])) {
							unset($_POST['send']);
							$_SESSION['flash'] = __("A test email with this newsletter has been sent to you.", 'bftpro');
							self :: send_test($nid);
						}
						
						// save and send pressed
						if(!empty($_POST['send'])) {
							$_SESSION['flash'] = __("The newsletter has been created and is being sent to selected mailing list.", 'bftpro');
							$_nl->send($nid);
						}							
						
						bftpro_redirect("admin.php?page=bftpro_newsletters");					
					}
					catch(Exception $e) {
						$error=$e->getMessage();			
					}
				}
				
				// select lists
				$lists=$_list->select();		
				
				if(!empty($_GET['preset_id'])) {
					$mail = (object)array("preset_id"=>intval($_GET['preset_id']));
				}
				
				require(BFTPRO_PATH."/views/newsletter-form.php");
			break;			
			
			default:
				// list my newsletters - subject, sending to, current status
				$_nl = new BFTProNLModel();
				
				if(!empty($_POST['mass_delete']) and check_admin_referer('bftpro_newsletters')) {
					$nids = empty($_POST['nl_ids']) ? array() : $_POST['nl_ids'];
					foreach($nids as $nid) $_nl->delete($nid);
				}	
				
				if(!empty($_GET['clone']) and check_admin_referer('bftpro_newsletters')) {
					self :: clone_newsletter($_GET['id']);
					bftpro_redirect('admin.php?page=bftpro_newsletters');
				}								
				
				$newsletters = $_nl -> select();
				
				// select all mailing lists - this will be useful for global newsletters
				$lists = $wpdb->get_results("SELECT id, name FROM ".BFTPRO_LISTS." ORDER BY name");
				
				// now select send and read newsletters to calculate open rates
				$sentnls = $wpdb->get_results("SELECT COUNT(id) as cnt, newsletter_id FROM ".BFTPRO_SENTMAILS."
					WHERE newsletter_id !=0 AND errors='' GROUP BY newsletter_id");
					
				$readnls = 	$wpdb->get_results("SELECT COUNT(id) as cnt, newsletter_id FROM ".BFTPRO_READNLS."
					GROUP BY newsletter_id");
					
				// select num unsubscribed by newsletter
				$num_unsubs = $wpdb->get_results("SELECT COUNT(id) as cnt, mail_id 
					FROM ".BFTPRO_UNSUBS." WHERE mcat='nl' GROUP BY mail_id");	
					
				foreach($newsletters as $cnt=>$newsletter) {
					$num_sent = $num_read =0;
					
					foreach($sentnls as $sentnl) {
						if($sentnl->newsletter_id == $newsletter->id) $num_sent += $sentnl->cnt;
					}
					
					foreach($readnls as $readnl) {
						if($readnl->newsletter_id == $newsletter->id) $num_read += $readnl->cnt;
					}
					
					$percent_read = empty($num_sent) ? 0 : round(100 * $num_read / $num_sent);
					$newsletters[$cnt]->percent_read = $percent_read; 
					$newsletters[$cnt]->num_sent = $num_sent;
					$newsletters[$cnt]->num_unsubs = 0;
					
					foreach($num_unsubs as $num_unsub) {
						if($num_unsub->mail_id == $newsletter->id) $newsletters[$cnt]->num_unsubs = $num_unsub->cnt;  
					}
					
					// is this a global newsletter? Then we need "still to go" info showing which lists it will be sent to
					if($newsletter->is_global) {
						$lids = explode('|', $newsletter->lists_to_go);
						$lids = array_filter($lids);
						if(sizeof($lids)) {
							$togo_str = __('After sending to the current mailing list it will also be sent to:', 'bftpro').' ';
							foreach($lids as $lct=>$lid) {
								if($lct) $togo_str .= ', ';
								foreach($lists as $list) {
									if($list->id == $lid) $togo_str .= "<b>".$list->name."</b>";
								}
							}
						}
						else $togo_str = __('Currently sending to the last mailing list. When done, the newsletter will be completed.', 'bftpro');
						
						$newsletters[$cnt]->togo_str = $togo_str;
					} // end is_global
				} // end foreach newsletter	
								
				// select lists
				$lists=$_list->select();
				
				require(BFTPRO_PATH."/views/newsletters.php");
			break;
		}
	}
	
	// shows the "log" for in-progress newsletter, i.e. which emails have still to be sent
	static function log() {
		global $wpdb;
		require_once(BFTPRO_PATH."/models/newsletter.php");
		require_once(BFTPRO_PATH."/models/user.php");
		
		$_nl = new BFTProNLModel();
		$_user = new BFTProUser();
		
		// select newsletter
		$mail = $_nl->select($_GET['id']);
		
		// select all receivers, limit 100 per page
		$limit = 100;
		$offset = empty($_GET['offset'])?0:$_GET['offset'];
		
		$extra_sql = '';
		if($mail->has_date_limit) $extra_sql .= $wpdb->prepare(" AND date <= %s ", $mail->date_limit);  
		

		if(!empty($mail->never_duplicate)) {
					$extra_sql .= $wpdb->prepare(" AND email NOT IN (SELECT tU.email FROM  ".BFTPRO_USERS." as tU
						JOIN ".BFTPRO_SENTMAILS." as tS ON tS.user_id=tU.id AND tS.newsletter_id=%d) ",
						$mail->id);
					
		}
		
		// the option "don't send email if the receiver is in any of these lists
		if(!empty($mail->skip_lists)) {
			$extra_sql .= " AND email NOT IN (SELECT email FROM  ".BFTPRO_USERS." 
					WHERE list_id IN (".$mail->skip_lists.") ) ";
		}		
		
		$users = $_user->select_receivers($wpdb->prepare("AND list_id = %d $extra_sql ", $mail->list_id), 0);	
		
		// segmentation required?
		if(class_exists('BFTISegment')) {
			foreach($users as $cnt=>$user) {
				if(!BFTISegment :: apply_segments($mail, $user)) unset($users[$cnt]);
			}
		}
			
		// find num sent emails and num total emails	
		$total_users = count($users);
		
		// now slice to 100 per page
		$users = array_slice($users, $offset, $limit);
		
		// num sent
		$num_sent = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_USERS."
			WHERE list_id=%d AND status=1 AND id<=%d", $mail->list_id, $mail->last_user_id));
			
		require(BFTPRO_PATH."/views/newsletter-log.php");	
	}
	
	// immediately send test email
	static function send_test($nid) {
		global $wpdb;
		
		// select newsletter
		$newsletter = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_NEWSLETTERS." WHERE id=%d", $nid));
		$attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS."
					WHERE nl_id = %d ORDER BY id", $newsletter->id));	
		$subject = '[TEST] '.stripslashes($newsletter->subject);			
		$message = stripslashes($newsletter->message);
		
		BFTProTests :: test_email($newsletter->sender, $subject, $message, $newsletter->mailtype, $newsletter->list_id, @$newsletter->template_id, $attachments, $newsletter->preset_id, $newsletter->send_test);
	
 	} // end send_test()
 	
 	// clone a single newsletter
 	static function clone_newsletter($id) {
 	   global $wpdb;
 	   $id = intval($id);
 	   
 	   // select email 
 	   $email = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_NEWSLETTERS." WHERE id=%d", $id), ARRAY_A);
 	
 	   $email['subject'] .= ' ' . __('(Copy)', 'bftpro');
 	   self :: copy_nl($email);
 	}
 	
 	// a helper that actually copies the email
 	static function copy_nl($email) {
 	   global $wpdb;
 	   $_nl = new BFTProNLModel();
 	   
 	   if(!empty($email['preset_id'])) {
         $preset_id = $email['preset_id'];
         $email['preset_id'] = 0;       
      }
      $mid = $_nl->add($email);
      
      // copy attachments if any
      $attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS." 
         WHERE nl_id=%d ORDER BY id", $email['id']));
     
      foreach($attachments as $att) {
         $wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_ATTACHMENTS." SET
            nl_id=%d, file_name=%s, file_path=%s, url=%s",
            $mid, $att->file_name, $att->file_path, $att->url));
      }   
      
      if(!empty($preset_id)) {
         $wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET preset_id=%d WHERE id=%d", $preset_id, $mid));
      } // end re-saving the preset
 	} // end copy_email helper
}