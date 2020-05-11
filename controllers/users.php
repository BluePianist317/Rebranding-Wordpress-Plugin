<?php
class BFTProUsers {
	// shows log of the received emails of a given user
	static function log() {
		global $wpdb;
		
		// select subscriber
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_USERS." WHERE id=%d", $_GET['id']));
		
		$sent_mails = $wpdb->get_results( $wpdb->prepare("SELECT tM.subject as ar_subject, tN.subject as n_subject,
			tS.date as date, tS.mail_id as mail_id, tS.newsletter_id as newsletter_id, tS.errors as errors
			FROM ".BFTPRO_SENTMAILS." tS LEFT JOIN ".BFTPRO_MAILS." tM ON tM.ID = tS.mail_id
			LEFT JOIN ".BFTPRO_NEWSLETTERS." tN ON tN.id = tS.newsletter_id
			WHERE tS.user_id=%d	ORDER BY tS.ID DESC", $user->id) );
		
		include(BFTPRO_PATH."/views/user-log.html.php");
	} // end log()
	
	// handle unsubscribe form
	static function unsubscribe_form() {
		global $wpdb;
   	require_once(BFTPRO_PATH."/models/user.php");
	   require_once(BFTPRO_PATH."/models/list.php");
   	$_user = new BFTProUser();
   	$_list = new BFTProList();
   	
   	// select user registrations with this email
		$error = false;	
		$users = $message = $title = NULL;	
		if(!empty($_GET['email'])) {
			$users = $wpdb -> get_results($wpdb->prepare("SELECT tU.*, tL.name as list_name 
			FROM ".BFTPRO_USERS." tU JOIN ".BFTPRO_LISTS." tL ON tL.id = tU.list_id
			WHERE tU.email LIKE %s AND tU.status=1 ORDER BY tL.name", @$_GET['email']));
			if(!sizeof($users) and empty($_POST['ok'])) {
				$message = __('There is no subscriber with this email address', 'bftpro');
				$error = true;
			}
		}
		else {
			$users = $wpdb->get_results("SELECT name as list_name, id as list_id FROM ".BFTPRO_LISTS." ORDER BY name");
		}	
		
		$unsubscribe_page = get_option('bftpro_unsubscribe_page');
		
		// exit poll?
		$unsubscribe_reasons = get_option('bftpro_unsubscribe_reasons');
		if(!empty(trim($unsubscribe_reasons))) $unsubscribe_reasons = explode(PHP_EOL, $unsubscribe_reasons); // turn into array
		$unsubscribe_reasons_other = get_option('bftpro_unsubscribe_reasons_other');
				
		$template = 'bftpro-unsubscribe.php';
		if($unsubscribe_page == 'single') $template = 'bftpro-unsubscribe-single.php';
		if($error) $template = 'bftpro-error.php';
		else { // all OK
			if(!empty($_POST['ok']) and is_array(@$_POST['list_ids'])) {
	   		foreach($users as $user) {
	   			if(in_array($user->list_id, $_POST['list_ids'])) $_user->unsubscribe($user);
	   		}		
	   		$title = __("Thank you.", 'bftpro');
	   		$message = __("You have been unsubscribed.", 'bftpro');
	   		$template = 'bftpro-message.php';
			}
		}
		
		return array($template, $users, $error, $message, $title, $unsubscribe_reasons, $unsubscribe_reasons_other);
	}
}