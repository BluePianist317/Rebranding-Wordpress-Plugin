<?php 
class BFTProReport {
	// runs the report for an autoresponder campaign
	// select emails in the campaign along with: num sent, num read, open rate
	// and later if Intelligence is available, num link clicks
	function campaign_report($from, $to) {
		global $wpdb;
		
		$_mail = new BFTProMailModel();
		$mails = $_mail->select($_GET['campaign_id']);
		
		// to avoid SQL queries let's just select all sent and read mails for the given period
		$sent_mails = $wpdb -> get_results( $wpdb->prepare("SELECT * FROM ".BFTPRO_SENTMAILS."	WHERE mail_id!=0
			AND date>=%s AND date<=%s AND user_id!=0 AND errors='' ", $from, $to));
		
		$read_mails = $wpdb -> get_results( $wpdb->prepare("SELECT * FROM ".BFTPRO_READMAILS."	WHERE mail_id!=0
			AND date>=%s AND date<=%s AND user_id!=0 ", $from, $to));					
			
		foreach($mails as $cnt=>$mail) {
			$mails[$cnt]->num_sent = $mails[$cnt]->num_read = 0;
			foreach($sent_mails as $sent_mail) {
				if($sent_mail->mail_id == $mail->id) $mails[$cnt]->num_sent++;
			}
			
			foreach($read_mails as $read_mail) {
				if($read_mail->mail_id == $mail->id) $mails[$cnt]->num_read++;
			}
			
			// now calculate % open rate
			if($mails[$cnt]->num_sent == 0) $percent = 0;
			else $percent = round( ($mails[$cnt]->num_read / $mails[$cnt]->num_sent) * 100 );
			
			$mails[$cnt]->open_rate = $percent;
		}	
		
		return $mails;
	} // end campaign report
	
	// newsletter report. It loads all newsletters unless we come from a specific newsletter link
	// in which case one newsletter is pre-selected
	function newsletter_report($from, $to) {
		global $wpdb;
		
		// select all newsletters
		$newsletters = $wpdb -> get_results( "SELECT * FROM ".BFTPRO_NEWSLETTERS." ORDER BY id");
		
		// to avoid SQL queries let's just select all sent and read mails for the given period
		$sent_nls = $wpdb -> get_results( $wpdb->prepare("SELECT newsletter_id FROM ".BFTPRO_SENTMAILS."	WHERE newsletter_id!=0
			AND date>=%s AND date<=%s AND errors='' ", $from, $to));
			
		$read_nls = $wpdb -> get_results( $wpdb->prepare("SELECT newsletter_id FROM ".BFTPRO_READNLS."	WHERE newsletter_id!=0
			AND date>=%s AND date<=%s", $from, $to));			
				
		foreach($newsletters as $cnt=>$newsletter) {
			// lets calculate only for the one that we need, if one is selected
			if(!empty($_GET['newsletter_id']) and $_GET['newsletter_id'] != $newsletter->id) continue;
			
			$num_sent = $num_read = 0;
			
			foreach($sent_nls as $sent_nl) {
				if($sent_nl->newsletter_id == $newsletter->id) $num_sent ++;
			} 
			
			foreach($read_nls as $read_nl) {
				if($read_nl->newsletter_id == $newsletter->id) $num_read++;
			}
			
			if($num_sent == 0) $percent = 0;
			else $percent = round( ($num_read / $num_sent) * 100);
			
			$newsletters[$cnt]->num_sent = $num_sent;
			$newsletters[$cnt]->num_read = $num_read;
			$newsletters[$cnt]->open_rate = $percent;
		}			
		
		return $newsletters;	
	}
	
	// newsletter report per email domain
	function newsletter_by_domain($from, $to, $id) {
		return $this->stats_by_domain($from, $to, $id, 'newsletter');
	} 
	
	// armail report per email domain
	function armail_by_domain($from, $to, $id) {
		return $this->stats_by_domain($from, $to, $id, 'armail');
	}
	
	// report per email domain
	function stats_by_domain($from, $to, $id, $type = 'newsletter') {
		global $wpdb;
		
		$field = ($type == 'newsletter') ? 'newsletter_id' : 'mail_id';
		$table = ($type == 'newsletter') ? BFTPRO_READNLS : BFTPRO_READMAILS;
		
		// select all sent and read mails for the given period for this newsletter
		$sent_nls = $wpdb -> get_results( $wpdb->prepare("SELECT tS.id as id, tU.email as email 
			FROM ".BFTPRO_SENTMAILS." tS LEFT JOIN ".BFTPRO_USERS." tU ON tU.id = tS.user_id 
			WHERE ".$field."=%d AND tS.date>=%s AND tS.date<=%s AND errors='' ", $id, $from, $to));
		
		$read_nls = $wpdb -> get_results( $wpdb->prepare("SELECT tR.id as id, tU.email as email  
		   FROM ".$table." tR LEFT JOIN ".BFTPRO_USERS." tU ON tU.id = tR.user_id 
		   WHERE ".$field."=%d AND tR.date>=%s AND tR.date<=%s", $id, $from, $to));	
		   
		$email_domains = array();
		
		// now we have to fill array with all email domains and the number of emails sent to each of them
		foreach($sent_nls as $sent) {
			$parts = explode('@', $sent->email);
			$domain = trim($parts[1]);
			if(isset($email_domains[$domain])) $email_domains[$domain]['sent_emails']++;
			else $email_domains[$domain]['sent_emails'] = 1;
			$email_domains[$domain]['read_emails']=0;
		}
		// sort them		
		uasort($email_domains, array(__CLASS__, 'sort_by_sent'));
		
		foreach($read_nls as $read) {
			$parts = explode('@', $read->email);
			$domain = trim($parts[1]);
			if(isset($email_domains[$domain]['read_emails'])) $email_domains[$domain]['read_emails']++;
			else $email_domains[$domain]['read_emails'] = 1;
		}
		
		// cut to only the top five (by sent), all rest goes to "other"
		$top_domains = array_slice($email_domains, 0, 5);
		$domains_left = array_slice($email_domains, 4);
		$other_domains = array('sent_emails' => 0, 'read_emails'=>0, 'open_rate'=>0);
		
		// fill other domains
		foreach($domains_left as $domain) {
			$other_domains['sent_emails'] += $domain['sent_emails'];
			$other_domains['read_emails'] += $domain['read_emails'];
		}
		
		// calculate open rates on both vars
		$other_domains['open_rate'] = empty($other_domains['sent_emails']) ? 0 : round(100 * $other_domains['read_emails'] / $other_domains['sent_emails']);
		
		foreach($top_domains as $cnt => $domain) {
			$top_domains[$cnt]['open_rate'] = empty($domain['sent_emails']) ? 0 : round(100 * $domain['read_emails'] / $domain['sent_emails']);
		}
		
		// return the data to be used in the ajax function
		$all_domains = $top_domains;
		$all_domains['other'] = $other_domains;
		
		return $all_domains;  	
	}
	
	// sort email domains by number of sent emails 
	static function sort_by_sent($a, $b) {
		 if ($a['sent_emails'] == $b['sent_emails']) {
        return 0;
	    }
	    return ($a['sent_emails'] > $b['sent_emails']) ? -1 : 1;
	}
	
	// this tracks open emails
	static function track() {		
		if(empty($_GET['bftpro_track'])) return true;
		
		// now track the readmail
		global $wpdb;
		if($_GET['type'] == 'nl') {
			$table = BFTPRO_READNLS;
			$field = 'newsletter_id';
			$userfield = 'read_nls';
		} else {
			$table = BFTPRO_READMAILS;
			$field = 'mail_id';
			$userfield = 'read_armails';
			
			do_action('bftpro_read_armail', $_GET['uid'], $_GET['id']);
		}
		
		$_GET['uid'] = intval($_GET['uid']);
		$_GET['id'] = intval($_GET['id']);
		
		// exists?
		$exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM $table WHERE user_id=%d AND $field = %d", $_GET['uid'], $_GET['id']));
		
		if($exists) {
			$wpdb->query("UPDATE $table SET date=CURDATE() WHERE id='$exists'");
		}
		else {			
			$wpdb->query( $wpdb->prepare("INSERT INTO $table SET 
				$field=%d, user_id=%d, date=CURDATE()", $_GET['id'], $_GET['uid']));
				
			// update also in users table, only when the mail is read for the 1st time
			$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_USERS." SET $userfield = $userfield + 1 WHERE id=%d", $_GET['uid']));	
		}
		
		// output image and exit
		$im = imagecreatetruecolor(1, 1);
		header('Content-Type: image/jpeg');
		imagejpeg($im);
		exit;
	} // end track()
}