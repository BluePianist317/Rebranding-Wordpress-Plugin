<?php
// the class that sends emails
class BFTProSender {
	function __construct() {
		global $wpdb;
		
		// let's save a  bunch of queries every time. When starting the cron we'll select all current lists 
		// along with any custom fields that could be there		
		$this->lists = $wpdb->get_results("SELECT * FROM ".BFTPRO_LISTS." ORDER BY id");
		$fields = $wpdb->get_results("SELECT * FROM ".BFTPRO_FIELDS." ORDER BY id");
		foreach($this->lists as $cnt=>$list) {
			 $this->lists[$cnt]->fields = array();
			 foreach($fields as $field) {
			 	  if($field->list_id == $list->id) $this->lists[$cnt]->fields[] = $field;
			 }
		}
		
		$this->debug = get_option('bftpro_debug_mode');
		$this->return_path = get_option('bftpro_bounce_email');
		$this->total_mails_sent = 0;
	}			
	
	// replacing static and dynamic masks in text
	function replace_masks($text, $receiver, $list) {
		 $text = str_replace("{{firstname}}", ucfirst($receiver->firstname), $text);
		 $text = str_replace("{{name}}", ucwords($receiver->name), $text);
		 $text = str_replace("{{email}}", $receiver->email, $text);
		 $text = str_replace("{{list-name}}", stripslashes($list->name), $text);
		 $dateformat = get_option('date_format');		 
		 $text = str_replace('{{date}}', date_i18n($dateformat, strtotime($receiver->date)), $text);
		 
		 foreach($list->fields as $field) {
		 	  if(strstr($text, "{{".$field->name."}}")) {
		 	  	 foreach($receiver->fields as $key => $data) {
		 	  	 		if($key == $field->id) {
		 	  	 			if($field->ftype == 'date') {
		 	  	 				// handle dates entered without year
		 	  	 				if(preg_match("/^1900/", $data)) {
		 	  	 					$dateformat = str_ireplace("y", '', $dateformat);
		 	  	 					$data = str_replace("1900", date("Y"), $data);
		 	  	 				}
		 	  	 				$text = str_replace("{{".$field->name."}}", date($dateformat, strtotime($data)), $text);
		 	  	 			}
		 	  	 			else $text = str_replace("{{".$field->name."}}", $data, $text);
		 	  	 		}
		 	  	 }
		 	  }
		 }
		 
		 return $text;
	}	
		
	// adds unsubscribe link
	function add_unsubscribe_link($receiver, $mailtype, $list, $mail_cat = '', $mail_id=0, $mail = null, $message = '') {
		 global $wpdb;
		 if(!empty($list->no_unsubscribe_link) or strstr($message, '?bftpro_rmvmmbr=1')) return "";
		 
		 // if there is a template and it contains {{unsubscribe-url}} we should not add the link
		 if(!empty($mail->template_id) and class_exists('BFTISender')) {
		 	$template_contents = $wpdb->get_var($wpdb->prepare("SELECT contents FROM ".BFTI_TEMPLATES." WHERE id=%d", $mail->template_id));
		 	//echo "TEMPLATE CONTENTS: $template_contents EMPLATE CONTENTS<br><br><br>";
		 	if(strstr($template_contents, '{{unsubscribe-url}}')) return '';
		 }
		 
		 $url = home_url("/?bftpro_rmvmmbr=1&email={$receiver->email}&list_id={$list->id}&mcat=$mail_cat&mail_id=$mail_id");
		 	
		 if($mailtype=='text/html' or $mailtype=='both') {
            $unsub="<br><br>-------------------<br>";
            $clickable_text = $url;
				            
            // clickable unsubscribe link instead of text + URL?
            if(!empty($list->unsubscribe_text_clickable)) {
            	$clickable_text = $list->unsubscribe_text_clickable;
            }
            
            if($list->unsubscribe_text) $unsub .= $this->maybe_nl2br($list->unsubscribe_text);
            else $unsub.=__("To unsubscribe from this list please click the link below:", 'bftpro');               
            
            if(strstr($unsub, '{{{unsubscribe-url}}}') or strstr($unsub, '{{{unsubscribe-link}}}')) {    
            	$unsub = str_replace('{{{unsubscribe-url}}}', $url, $unsub);
					$unsub = str_replace('{{{unsubscribe-link}}}', '<a href="'.$url.'">'.$clickable_text.'</a>', $unsub); 
            }       
            else $unsub.="<br /><a href=$url>$clickable_text</a>";
      }      
      else {
            $unsub="\n\n-------------------\n";
            
            if($list->unsubscribe_text) $unsub.=$list->unsubscribe_text;
            else $unsub.=__("To unsubscribe from this list please click the link below:", 'bftpro');            
            
             if(strstr($unsub, '{{{unsubscribe-url}}}') or strstr($unsub, '{{{unsubscribe-link}}}')) {    
            	$unsub = str_replace('{{{unsubscribe-url}}}', $url, $unsub);
					$unsub = str_replace('{{{unsubscribe-link}}}', '<a href="'.$url.'">'.$clickable_text.'</a>', $unsub); 
            }       
            else $unsub.="\n".$url;            
      }      
      
      return $unsub;
	}	
	
	// if a message contains the {{unsubscribe-url}} variable we'll use this function instead of add_unsubscribe_link to just replace the URL
	function replace_unsubscribe_link($receiver, $mailtype, $list, $mail_cat = '', $mail_id=0, $message) {
		 $url = home_url("/?bftpro_rmvmmbr=1&email={$receiver->email}&list_id={$list->id}&mcat=$mail_cat&mail_id=$mail_id");
		 $message = str_replace('{{unsubscribe-url}}', $url, $message);
		 return $message;
	}	
	
	// customizes the email with the masks etc
	// adds unsubscribe and other links
	function customize($mail, $receiver) {
		 global $wpdb;
		 $GLOBALS['bftpro_current_receiver'] = $receiver; // for use of external shortcodes
		 $subject = $mail->subject;
		 $message = $mail->message;
		 
		 // find list and custom fields if any		 
		 $list=null;
		 foreach($this->lists as $l) {
		 	 if($l->id == $receiver->list_id) $list = $l;
		 }
		 
		 if(!$list) return array("","");
		 
		 // extracts or sets a "firstname" field regardless the fact it does not exist
		 if(strstr($receiver->name," ")) {
				$parts=explode(" ",$receiver->name);
				$receiver->firstname=$parts[0];
		 }
		 else $receiver->firstname=$receiver->name;
		 
		 // now replace masks
		 $subject = $this->replace_masks($subject, $receiver, $list);
		 $message = $this->replace_masks($message, $receiver, $list);
		 
		 // nl2br message?
		 if(($mail->mailtype=='text/html' or $mail->mailtype=='both') and $mail->force_wpautop) $message = wpautop($message);
		 if(($mail->mailtype=='text/html' or $mail->mailtype=='both') and !$mail->force_wpautop) $message = $this->maybe_nl2br($message);
		 
		 // add signature if any
		 $signature = '';
		 if(!isset($this->signature)) $this->signature = stripslashes(get_option('bftpro_signature'));
		 if(!empty($this->signature)) {
		 		if($mail->mailtype=='text/html' or $mail->mailtype=='both') {
		 			 $signature = "<p>&nbsp;</p>".$this->maybe_nl2br($this->signature); 
		 			 $message.=$signature;
		 		}
		 		else {
		 			$signature = "\n\n".$this->signature;
		 			$message.= $signature;
		 		}
		 }
		 
 		 // add unsubscribe link
 		 $mail_cat = empty($mail->ar_id) ? 'nl' : 'ar'; // if ar_id is empty, it's a newsletter, otherwise it's autoresponder email
 		 
	    if(strstr($message, '{{unsubscribe-url}}')) $message = $this->replace_unsubscribe_link($receiver, $mail->mailtype, $list, $mail_cat, $mail->id, $message); 
	    else $message.= $this->add_unsubscribe_link($receiver, $mail->mailtype, $list, $mail_cat, $mail->id, $mail, $message); 
	   
	   // add tracking code
	   $type = isset($mail->artype) ? 'ar' : 'nl';
	    // add open tracker			 
	    $open_tracker = "\n<img src='".home_url("?bftpro_track=1&id=".$mail->id."&uid=".$receiver->id."&type=".$type)."' width='1' height='1' border='0'>";
		 if($mail->mailtype!='text/plain') $message .= $open_tracker;
	   
		 if(class_exists('BFTISender')) $message = apply_filters('bftpro_message_filters', $message, $type, $mail->id, $receiver->id);		 
		 if(class_exists('BFTISender')) $subject = apply_filters('bftpro_subject_filters', $subject, $type, $mail->id, $receiver->id);
		 if(class_exists('BFTISegment') and !BFTISegment :: apply_segments($mail, $receiver)) {
		 		return array('', ''); // blank out subject & message so it won't be sent
		 }
		 if(class_exists('BFTISender')) $message = apply_filters('bftpro_template_filter', $message, $mail->template_id);
		 
		 // handle preset if any
		 if(!empty($mail->preset_id)) $message = ArigatoPROPresets :: match_blocks($message, $mail->preset_id, $signature, $open_tracker);
		
		 // let's repeat this because if using template we may have the unsubscribe	{{unsubscribe_url}} tag
		 if(strstr($message, '{{unsubscribe-url}}')) $message = $this->replace_unsubscribe_link($receiver, $mail->mailtype, $list, $mail_cat, $mail->id, $message);
		 
		 $message = stripslashes($message);
		 $message = do_shortcode($message);
		 
		 // sleep?
		 if(BFTPRO_SLEEP) usleep(BFTPRO_SLEEP * 1000000);
		 
		 // return		 
		 return array(stripslashes($subject), $message);
	}
	
	// actually sends email and marks it as sent
	function send($sender, $receiver, $subject, $message, $ctype = 'text/html', $attachments = NULL, $reply_to = '') {
		global $wpdb;
		
		// because in the free version $sender is in format Name, email@dot.com we have to convert it
		$sender = bftpro_convert_sender($sender);		
		
		// if both subject and message are empty this means the email has been filtered out by segmentation
		// or something else and should not be sent. We shouldn't send empty emails anyway.
		if(empty($subject) and empty($message)) return false;
		
		// check if $this->mails_left is set. if so, check if we can send more mails
		if(isset($this->mails_left) and empty($this->mails_left)) throw new Exception("No more mails can be sent");
				
		// update today's mails and $this->total_emails_sent
		if(!isset($this->today_mails)) $this->today_mails = 0;
		$this->today_mails++;
		$this->total_mails_sent++;
		
		$plain_text = strip_tags(str_replace("<br>", "\n", $message));
		$receiver = trim($receiver);
		
		// handle text-only and "both" mail types
		if($ctype=='text/plain') $message = $plain_text;
		else $message = $this->maybe_nl2br($message);
				
		if($ctype=='both') {
			// thanks to http://www.tek-tips.com/faqs.cfm?fid=2681	
				
			$semi_rand = md5(time());
			$mime_boundary = "==MULTIPART_BOUNDARY_$semi_rand";
			$mime_boundary_header = chr(34) . $mime_boundary . chr(34);
			
			// construct the body
			$body = "This is a multi-part message in MIME format.

			--$mime_boundary
			Content-Type: text/plain; charset=\"UTF-8\"
			Content-Transfer-Encoding: 8bit
			
			$plain_text
			
			--$mime_boundary
			Content-Type: text/html; charset=utf8
			Content-Transfer-Encoding: 8bit
			
			$message
			
			--$mime_boundary--";
			
			$body = str_replace("\t", "" ,$body);
			
			// now replace the vars
			$message = $body;
			$ctype = "multipart/alternative;\n" . 
      "     boundary=" . $mime_boundary_header;			
		}
		
		// set return-path from $sender only if it's empty	
		if(empty($this->return_path)) {
			// is $sender combination of name and email?
			if(strstr($sender, '<')) {
				$parts = explode('<', $sender);
				$sender_email = trim(str_replace('>','', $parts[1]));
			}
			else $sender_email = $sender;
			$sender_email = trim($sender_email);
			$this->return_path = $sender_email;
		}
		
		$sender = trim(stripslashes($sender));
						
		$headers = array();
		$headers[] = "Content-Type: $ctype";
		$headers[] = 'From: '.$sender;
		//$headers[] = 'Reply-To: '. '=?UTF-8?B?'.base64_encode($replyto) . '?='; // Do not use this, causes problems with UTF-8 encoded from names
		if(!empty($reply_to)) $headers[] = 'Reply-To: '.$reply_to; // Careful with this, causes problems with UTF-8 encoded from names
		$headers[] = 'Return-Path: '.$this->return_path;
		$headers[] = 'sendmail_from: '.$this->return_path;
		$headers[] = 'X-Bftpro-b: '.$receiver;
		$headers[] = 'X-Bftpro-id: '.md5(microtime().$receiver).'-'.$receiver;
		if(BFTPRO_BCC_ALL) $headers[] = 'Bcc: ' . BFTPRO_BCC_ALL;
		
		// $this->debug = true;
		if(!empty($this->debug)) {
  	   	 echo "FROM: $sender<br>";
  	   	 echo "TO: $receiver<br>";
			 echo "SUBJECT: $subject<br>";
			 echo "MESSAGE: $message<br>";
			// print_r($headers);			 
		}		
		
		// update today mails
		$today_mails = get_option('bftpro_today_mails');
		$new_today_mails = intval($today_mails + 1);		
		update_option('bftpro_today_mails', $new_today_mails);
				
		// prepare attachments if any	
		if($attachments and is_array($attachments)) {
			$atts = array();
			foreach($attachments as $attachment) $atts[] = $attachment->file_path;
			$attachments = $atts;
		}
		 
		$message = do_shortcode($message);
		// echo $message;
		add_action( 'phpmailer_init', array( $this, 'fix_return_path' ) );
		if(!$this->check_valid_email($receiver)) {
			$status = __('Email not sent because receiver email is not valid email address.', 'bftpro');
			$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_EMAILLOG." SET
   			sender=%s, receiver=%s, subject=%s, date=%s, status=%s, unique_id=%s, micro_time=%s",
   			$sender, $receiver, $subject, date('Y-m-d', current_time('timestamp')), $status, uniqid('', true) . '-' . getmypid() , microtime()));
   		return false;
		}
		
		// finally, send the email
		$result = wp_mail($receiver, $subject, $message, $headers, $attachments);				
		
		// error from phpmailer
		if(!$result) {
			 if(empty($this->phpmailer_errors)) $this->phpmailer_errors = ' <br>'.__('The following errors occured when sending emails:', 'bftpro');
          $this->phpmailer_errors .= '<br>'.sprintf(__('Sending to %s returned error "%s". Please ensure the receiver email address is valid.', 'bftpro'), $receiver, $GLOBALS['phpmailer']->ErrorInfo)."<br>";
      }

		// insert into the raw email log
   	$status = $result ? 'OK' : "Error: ".$GLOBALS['phpmailer']->ErrorInfo;
   	$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_EMAILLOG." SET
   		sender=%s, receiver=%s, subject=%s, date=%s, status=%s, unique_id=%s, micro_time=%s",
   		$sender, $receiver, $subject, date('Y-m-d', current_time('timestamp')), $status, uniqid('', true) . '-' . getmypid() , microtime()));

		if($result) do_action('arigatopro-sent-email', $sender, $receiver, $subject, $message);

		return $result;
	}
	
	// the cron job that gets emails from the queue and sends them
	function cron() {
		global $wpdb;
		
		$bounce_email = get_option('bftpro_bounce_email');
		if(!empty($bounce_email)) $this->return_path = $bounce_email;
		$upload_dir = wp_upload_dir();
		$lock_file = $upload_dir['basedir'].'/arigatopro.lock';
				
		require_once(BFTPRO_PATH."/models/user.php");
		$_user = new BFTProUser();
		
		// see if the cron can run now	
		// 1. make sure there is no other instance running that's less than 5 minutes old
		// 2. respect 'cron minutes' option, minimum 1 minute
		// 3. if all is fine, mark the current  cron instance as running
		$last_cron_run = get_option('bftpro_last_cron_run');
		$cron_status = get_option('bftpro_cron_status');
		
		BFTPro::log("Cron job (".getmypid().") starting at ".date("Y-m-d H:i:s", current_time('timestamp')));
		
		$cron_minutes = get_option('bftpro_cron_minutes');
		if(empty($cron_minutes)) $cron_minutes = 3;
		$unlock_minutes = (get_option('bftpro_cron_mode') == 'web') ? $cron_minutes : BFTPRO_LOCK_FILE_MINUTES; // BFTPRO_LOCK_FILE_MINUTES minutes when using cron job because it's unlikely to get unreleased lock file
		
		// 1
		if( ((time() - $last_cron_run) < ($unlock_minutes * 60)) and $cron_status == 'running') throw new Exception("Another instance running");
		
		// locking by file is good but we must make sure that a script which could not finish won't block the cron job forever
		// so let's delete it if there is a file already and it's more than $cron_minutes old
		if(file_exists($lock_file)) {
		   $created_on = filectime($lock_file);
		   // if one hour passed and the file is still here, delete it
		   if($created_on < time() - $unlock_minutes * 60) @unlink($lock_file);
		}
						
		// lock by file			
		$f = @fopen($lock_file, 'x'); 
		if(!$f) throw new Exception("Temporary lock file protection in effect (This is not error! <a href='https://calendarscripts.info/bft-pro/howto.html?q=17#faq17' target='_blank'>Learn more</a>) - ".date("Y-m-d H:i:s", current_time('timestamp')));
		if (!flock($f, LOCK_EX | LOCK_NB)) {
		   BFTPro::log("Another instance running (lock file cannot be accessed) ".date("Y-m-d H:i:s", current_time('timestamp')));
		   throw new Exception("Another instance running");
		} 
		
		// 2
		if((time() - $last_cron_run) < $cron_minutes * 60)  {
		   // unlink unsuccessfully deleted lock file here but when it's at least 60 minutes old
		   if(!empty($created_on) and $created_on < time() - $unlock_minutes * 60) {
		      BFTPro::log("Releasing the lock at ".date("Y-m-d H:i:s", current_time('timestamp')));
		      @unlink($lock_file);
		   }
		   throw new Exception( "Starting too soon - we have a limit of $cron_minutes minutes" );
		}
		
		// first cleanup bounces if required
		try {
			BFTProBounceController :: handle_bounces();
		}
		catch(Exception $e) {
			BFTPro::log($e->getMessage().' '.date("Y-m-d H:i:s", current_time('timestamp')));
		}
		
		// check subscribe by email
		try {
			BFTProSubscribeEmailController :: handle_signups();
		}
		catch(Exception $e) {
			BFTPro::log($e->getMessage().' '.date("Y-m-d H:i:s", current_time('timestamp')));
		}

		
		// now check if we are allowed to send more emails today
		$mails_per_day = get_option('bftpro_mails_per_day');
		$mails_per_day = $mails_per_day?$mails_per_day:100000;
		$cron_date = get_option('bftpro_cron_date');
		$this->today_mails = 0;
		$this->mails_left = $mails_per_day;	 // later updates to mails left for this run
			
		// ignore if $mails_per_day == 0	
		if($cron_date==date("Y-m-d") and $mails_per_day) {
			// this means we have recorded some emails sent today
			$this->today_mails = get_option('bftpro_today_mails');
			// echo $this->today_mails.'<br>';
			if($this->today_mails >= $mails_per_day) throw new Exception("Sent enough mails for today");
			$this->mails_left = $mails_per_day - $this->today_mails;
		} 
		else update_option('bftpro_cron_date', date('Y-m-d')); // set todays date as cron date
		
		// 3
		update_option('bftpro_last_cron_run', time());
		update_option('bftpro_cron_status', 'running');	
		
		// mails per run start counting the emails
		// no matter what, keep mails per run up to 100,000 to avoid RAM overloads
		$this->mails_per_run = get_option('bftpro_mails_per_run');		
		if($this->mails_per_run==0 or $this->mails_per_run>100000) $this->mails_per_run=100000;		
		
		if($this->mails_per_run > $this->mails_left) $this->mails_per_run = $this->mails_left;
		else $this->mails_left = $this->mails_per_run; 
		
		$this->total_emails_sent=0;
		
		$nowtime_sql = (get_option('bftpro_use_wp_time') == 1) ? "'".date('H:i:s', current_time('timestamp'))."'" : 'NOW()';
		
		// send autoresponder emails
		// 1. first select fixed dates mails and weekday mails for today
		$ar_mails1 = $wpdb->get_results("SELECT tM.*, tA.list_ids as list_ids 
			FROM ".BFTPRO_MAILS." tM JOIN ".BFTPRO_ARS." tA ON tA.id=tM.ar_id 
			WHERE ( (tM.artype='date' AND tM.send_on_date='".date('Y-m-d', current_time('timestamp'))."') OR (tM.artype='every_weekday' AND tM.every='".date('l')."') )
			AND ( tM.daytime='' OR tM.daytime=0 OR tM.daytime<=HOUR(".$nowtime_sql.") ) AND tM.is_paused=0
			ORDER BY tM.daytime DESC, tM.id");	
		
		// 2. then select all sequential mails no matter the date they have to be sent at
		// and all mails sent "every X days"
		$ar_mails2 = $wpdb->get_results("SELECT tM.*, tA.list_ids as list_ids, tA.sender as ar_sender
			FROM ".BFTPRO_MAILS." tM JOIN ".BFTPRO_ARS." tA	ON tM.ar_id=tA.id
			WHERE (tM.artype='days' OR tM.artype='every_days') AND ( tM.daytime='' OR tM.daytime=0 OR tM.daytime<=HOUR(".$nowtime_sql.") ) 
			AND tM.is_paused=0
			ORDER BY tM.id");
		
		// merge both in a single array to process once
		$ar_mails = array_merge($ar_mails1, $ar_mails2);	
		
		// mails to be sent by the Intelligence module?
		if(class_exists('BFTISender')) $ar_mails = apply_filters('bftpro-send-ar-mails', $ar_mails);
	
		foreach($ar_mails as $cnt => $mail) {			
			// select receivers limited by mails left for this run
			$receivers = array();
			$list_ids=explode("|", $mail->list_ids);
			$list_ids=array_filter($list_ids);
			if(empty($list_ids)) $list_ids[]=0;
			if(empty($mail->sender)) $mail->sender = $mail->ar_sender; // if by any chance the sender of the email is empty, use the AR sender
			
			// avoid duplicates
			$noduplicate_sql = $wpdb->prepare(" AND id NOT IN (SELECT user_id FROM ".BFTPRO_SENTMAILS."
				WHERE mail_id=%d AND date='".date('Y-m-d', current_time('timestamp'))."') ", $mail->id);
						
			if($mail->artype=='every_weekday' or $mail->artype=='date') {
				$receivers=$_user->select_receivers(" AND list_id IN (".implode(",", $list_ids).") 
				$noduplicate_sql", $this->mails_left);			
			}
			
			if($mail->artype == 'days') {				
				// standard query like before, for taking users registered X days before
				$receivers_sql = " AND list_id IN (".implode(",", $list_ids).") 			
						AND date = '".date('Y-m-d', current_time('timestamp'))."' - INTERVAL {$mail->days} DAY $noduplicate_sql";
						
				// in case we have a 0 days mail with "minutes after registration" limit, we'll change the query
				if($mail->days == 0 and $mail->mins_after_reg > 0) {
					$receivers_sql  =  "AND list_id IN (".implode(",", $list_ids).") 
						AND DATE(datetime + INTERVAL ".$mail->mins_after_reg." MINUTE) = '".date('Y-m-d', current_time('timestamp'))."'
						AND datetime + INTERVAL ".$mail->mins_after_reg." MINUTE <='".current_time('mysql')."' $noduplicate_sql";
				}

				$receivers = $_user->select_receivers($receivers_sql, $this->mails_left);

			}
			
			if($mail->artype == 'every_days') {
				$receivers=$_user->select_receivers(" AND list_id IN (".implode(",", $list_ids).") 
					AND MOD(TO_DAYS(date) - TO_DAYS('".date('Y-m-d', current_time('timestamp'))."'), {$mail->every})=0
      		AND (TO_DAYS('".date('Y-m-d', current_time('timestamp'))."')-TO_DAYS(date))>={$mail->every}
      		$noduplicate_sql", $this->mails_left);
			}
			
			if(class_exists('BFTISender')) $receivers = apply_filters('bftpro-armail-receivers', $mail, $receivers, $list_ids, $noduplicate_sql, $this);
			
			// filter receivers based on split testing
			if(!empty($mail->split_testing) and class_exists('BFTISender') and method_exists('BFTISender', 'split_test')) {
				$receivers = BFTISender :: split_test($receivers, $mail, $ar_mails);
				$rids = array();
				foreach($receivers as $receiver) $rids[] = $receiver->id;
				$ar_mails[$cnt]->receiver_ids = $rids; // this will store IDs of the receivers of this email
			}			
			
			// attachments 
			$attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS."
					WHERE mail_id = %d ORDER BY id", $mail->id));	
			$mail->attachments = $attachments;		
			
			// try to send the emails, return if limit exhausted
			foreach($receivers as $receiver) {
				list($subject, $message) = $this->customize($mail, $receiver);
				
				try {
					// echo 'sending<br>';					
					$result = $this->send($mail->sender, $receiver->email, $subject, $message, $mail->mailtype, $attachments, $mail->reply_to);

					// insert into sent mails
					$sending_errors = $result ? '' : 'skipped';
					$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_SENTMAILS." SET
						mail_id=%d, user_id=%d, date='".date('Y-m-d', current_time('timestamp'))."', errors=%s", $mail->id, $receiver->id, $sending_errors));		
					// $count ++;
				}
				catch(Exception $e) {
					BFTPro::log($e->getMessage());
				} 
			}
		}	
	
		// after that send newsletter emails
		$newsletters = $wpdb->get_results("SELECT * FROM ".BFTPRO_NEWSLETTERS." 
		 WHERE status='in progress' AND (is_scheduled=0 OR scheduled_for <= '" .date('Y-m-d', current_time('timestamp')). "') 
		 ORDER BY id");		
		if(count($newsletters)) {
			// select receivers and try to run
			foreach($newsletters as $newsletter) {				
				$extra_sql = '';
				// this is for the option "Do not send this newsletter to email address that already received it."
				if(!empty($newsletter->never_duplicate)) {
						$extra_sql .= $wpdb->prepare(" AND email NOT IN (SELECT tU.email FROM  ".BFTPRO_USERS." as tU
							JOIN ".BFTPRO_SENTMAILS." as tS ON tS.user_id=tU.id AND tS.newsletter_id=%d) ",
							$newsletter->id);
						
				}
				
				// the option "don't send email if the receiver is in any of these lists
				if(!empty($newsletter->skip_lists)) {
					$extra_sql .= " AND email NOT IN (SELECT email FROM  ".BFTPRO_USERS." 
							WHERE list_id IN (".$newsletter->skip_lists.") ) ";
				}
				
				if($newsletter->has_date_limit) $extra_sql .= $wpdb->prepare(" AND date <= %s ", $newsletter->date_limit);  
				$receivers = $_user->select_receivers($wpdb->prepare(" AND list_id=%d AND id > %d ".$extra_sql,
					$newsletter->list_id, $newsletter->last_user_id),
					$this->mails_left);
					
				// attachments 
				$attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS."
					WHERE nl_id = %d ORDER BY id", $newsletter->id));	
					
				// completed lists if this newsletter is global
				if($newsletter->is_global) {
					$completed_lids = explode('|', $newsletter->completed_lists);
					$completed_lids = array_filter($completed_lids);
				}	
								
				$count = 0;	
				foreach($receivers as $receiver) {	
					list($subject, $message) = $this->customize($newsletter, $receiver);
					
					try {
						// global newsletter? Then check if we really need to send to this user (is it sending again?)			
						$dont_send = false;			
						if($newsletter->is_global and sizeof($completed_lids)) {
							$dont_send = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_USERS." 
								WHERE email=%s AND list_id IN (".implode(', ', $completed_lids).")", $receiver->email));	
							$result = true; // set result to true to store it like it was sent								
						}					
						
						if(empty($dont_send)) {
							$result = $this->send($newsletter->sender, $receiver->email, $subject, $message, $newsletter->mailtype, $attachments, $newsletter->reply_to);
							$count ++;
						}
						
						// update $receiver id in newsletters table
						$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET last_user_id=%d WHERE id=%d", 
							$receiver->id, $newsletter->id));
							
						// insert in sent mails
						$send_errors = $result ? '' : 'skipped';
						$wpdb->query( $wpdb->prepare("INSERT INTO ".BFTPRO_SENTMAILS." SET
							mail_id=0, user_id=%d, date='".date('Y-m-d', current_time('timestamp'))."', newsletter_id=%d, errors=%s", 
							$receiver->id, $newsletter->id, $send_errors));	
					}
					catch(Exception $e) {
						$errmsg = $e->getMessage();
						BFTPro::log($errmsg);
						return $errmsg;
					} 
				}					
            
				// in case we have sent to no one in this list, means that mailing to the list is completed
   			// then we need to update the list status as sent
   			if($count==0 or $count < $this->mails_left) {
					// but if this is a global newsletter, maybe we should just change the list
					if($newsletter->is_global) {
						$lists_to_go = explode('|', $newsletter->lists_to_go);
						$lists_to_go = array_filter($lists_to_go);
					} 
					if($newsletter->is_global and !empty($lists_to_go)) {						
						$completed_lids = explode('|', $newsletter->completed_lists);
						$completed_lids = array_filter($completed_lids);
						$lid = array_shift($lists_to_go);
						$completed_lids[] = $newsletter->list_id;
						$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET
							last_user_id=0, list_id=%d, lists_to_go=%s, completed_lists=%s WHERE id=%d", 
							$lid, implode('|', $lists_to_go), implode('|', $completed_lids), $newsletter->id));
					}   				
   				else {
   					$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET status = 'completed', last_user_id = 0 
   						WHERE id=%d", $newsletter->id));
   				}
   			} // end marking as complete or changing the list
			} // end foreach newsletter
		} // end sending newsletters
		
		// unlock script
		fclose($f);
   	@unlink($lock_file);
      BFTPro::log("Releasing the lock at ".date("Y-m-d H:i:s", current_time('timestamp'))." PID (".getmypid().")");
		
		return "Success";
	} // end cron()
	
	// immediate emails - 0 days after registration
	static function immediate_mails($user_id) {		
		global $wpdb;
		require_once(BFTPRO_PATH."/models/user.php");
		$_user = new BFTProUser();		
		$user = $_user->select_receivers($wpdb->prepare(" AND id=%d ", $user_id),1);
		$user = $user[0];
		$_sender = new BFTProSender();
		
		$nowtime_sql = (get_option('bftpro_use_wp_time') == 1) ? "'".date('H:i:s', current_time('timestamp'))."'" : 'NOW()';
			
		$mails = $wpdb->get_results("SELECT tM.*, tR.sender as sender FROM ".BFTPRO_MAILS." tM
			JOIN ".BFTPRO_ARS." tR ON tR.id=tM.ar_id 
			WHERE tR.list_ids LIKE '%|{$user->list_id}|%'
			AND tM.days=0 AND tM.artype='days'
			AND ( tM.daytime='' OR tM.daytime=0 OR tM.daytime<=HOUR(".$nowtime_sql.") ) AND tM.is_paused=0
			AND tM.mins_after_reg=0
			ORDER BY tM.id");
		
				
		foreach($mails as $mail) {		
			// avoid duplicates - beware of potential problems with not sent welcome emails!
			$already_sent = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_SENTMAILS."
				WHERE mail_id=%d AND user_id=%d", $mail->id, $user->id));	
			if(!empty($already_sent)) continue;	
		
			// attachments 
			$attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS."
					WHERE mail_id = %d ORDER BY id", $mail->id));	
			
			list($subject, $message) = $_sender->customize($mail, $user);
					
			try {
				$result = $_sender->send($mail->sender, $user->email, $subject, $message, $mail->mailtype, $attachments, $mail->reply_to);
				//$count ++;
				if(!$result) BFTPro::log(sprintf(__("Immediate email from %s to %s was not send. Error from phpmailer: %s", 'bftpro'), $mail->sender, $user->email, $GLOBALS['phpmailer']->ErrorInfo));
				
				// insert into sent mails
				$sending_errors = $result ? '' : 'skipped';
				$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_SENTMAILS." SET
					mail_id=%d, user_id=%d, date='".date('Y-m-d', current_time('timestamp'))."', errors=%s", $mail->id, $user->id, $sending_errors));						
						
			}
			catch(Exception $e) {
				$errmsg = $e->getMessage();
				BFTPro::log($errmsg);
				return $errmsg;
			} 
		}	
	}
	
	// this is called instead of directly $this->cron because
	// we want to get the return from $this->cron and update the log
	function start_cron() {		
		global $wpdb;
		
		// first run for today? clear today mails
		if(get_option('bftpro_cron_date')!=date('Y-m-d')) {
			update_option('bftpro_today_mails', 0);
			
			// run BFTI triggers
			if(class_exists('BroadfastIntelligence')) {
				BroadfastIntelligence :: init();
				BFTITrigger :: daily_driggers();
			}
			
			// use this also to cleanup unconfirmed users if this option is selected
			$cleanup_unconfirmed_emails = get_option('bftpro_cleanup_unconfirmed_emails');
			if($cleanup_unconfirmed_emails > 0) {
				$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_USERS." 
					WHERE status=0 AND unsubscribed=0 AND date < '".date("Y-m-d", current_time('timestamp'))."' - INTERVAL %d DAY ", $cleanup_unconfirmed_emails));
			}
		}
				
		$this->signature = get_option('bftpro_signature');
		
		// actually run the sender procedure
		try {
			$result = $this->cron();
			
			// complete the current cron instance	
			update_option('bftpro_cron_status', 'completed');
			BFTPro::log("Cron job ran at ".date("Y-m-d H:i:s", current_time('timestamp'))." (".microtime() .'-'. getmypid().") with result: $result".@$this->phpmailer_errors);
		}
		catch(Exception $e) {
		   // even when error is logged, we should complete the cron job!!!
		   // status "running" remains only when the script died in the middle for some reason
         update_option('bftpro_cron_status', 'completed'); 		   
		   
			// log error			
			BFTPro::log("Cron job failed at ".date("Y-m-d H:i:s", current_time('timestamp'))." (".microtime() .'-'. getmypid().") with result: ".$e->getMessage());
			if(!empty($_GET['bftpro_cron'])) die($e->getMessage());
		}	
		
		if(!empty($_GET['bftpro_cron'])) die("Running in cron job mode"); 
	}
	
	// adds nl2br only when needed
	function maybe_nl2br($text) {
	     if(!stristr($text,"<br>") and !stristr($text,"<p") 
	          and !stristr($text,"<br />") and !stristr($text, "<table")) {
	    
	          $from = array(
					'#<ul>(\s)*(<br\s*/?>)*(\s)*<li>#si',
					'#</li>(\s)*(<br\s*/?>)*(\s)*<li>#si',
					'#</li>(\s)*(<br\s*/?>)*(\s)*</ul>#si',
					'#<ol>(\s)*(<br\s*/?>)*(\s)*<li>#si',
					'#</li>(\s)*(<br\s*/?>)*(\s)*</ol>#si',
					'#(<br\s*/?>){1}\s*<ul>#si',
					'#(<br\s*/?>){1}\s*<ol>#si',
					'#</ul>\s*(<br\s*/?>){1}#si',
					'#</ol>\s*(<br\s*/?>){1}#si',
					);
					$to = array(
					'<ul><li>',
					'</li><li>',
					'</li></ul>',
					'<ol><li>',
					'</li></ol>',
					'<ul>',
					'<ol>',
					'</ul>',
					'</ol>'
					);	
					
				$text = preg_replace( $from, $to ,$text);		
				$text = nl2br($text);			
	     }
	     
	     return $text;
	}
	
	// fix phpmailer return path
	function fix_return_path($phpmailer) {		
		$phpmailer->Sender = $this->return_path;
		//$phpmailer->FromName = mb_convert_encoding($phpmailer->FromName, "UTF-8", "auto");
		
		// set reply to
		/*if(strstr($this->full_sender, '<')) {
			$parts = explode('<', $this->full_sender);
			$reply_to_address = mb_convert_encoding(trim(str_replace(">", '', $parts[1])), "UTF-8", "auto");
			$reply_to_name = mb_convert_encoding(trim($parts[0]), "UTF-8", "auto");
			$phpmailer->ClearReplyTos();
			$phpmailer->AddReplyTo($reply_to_address, $reply_to_name);
			//$phpmailer->SetFrom($reply_to_address, $reply_to_name, false);
		}*/
		
		// keep SMTP connection alive?
		// $phpMailer->SMTPKeepAlive = true;
		
		return $phpmailer;
	}
	
	// check receiver email address for validness
	// let's not be too picky we don't want to falsely skip valid addresses. 
	// just make sure there are no spaces and there is @ and .
	function check_valid_email($email) {
		if(strstr($email, ' ')) return false;
		if(!strstr($email, '@') or !strstr($email, '.')) return false; 
		
		return true;
	}
}