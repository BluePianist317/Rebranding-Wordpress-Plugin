<?php
class BFTProBounceController {
	// manage the bounce settings page
	// allows you to set how many bounces are required to be deleted from the mailing list
	// also to set different bounce email address
	// and to set the pop3 login details so the program checks once per day
	static function options() {
		global $wpdb;
		
		$multiuser_access = 'all';
		$multiuser_access = BFTProRoles::check_access('bounce_access');
		
		if(!empty($_POST['ok']) and check_admin_referer('bftpro_bounces')) {
			$handle_bounces = empty($_POST['handle_bounces']) ? 0 : 1;
			update_option('bftpro_bounce_email', sanitize_email($_POST['bounce_email']));
			update_option('bftpro_handle_bounces', $handle_bounces);
			
			if(!empty($_POST['handle_bounces'])) {				
			   $bounce_ssl = empty($_POST['bounce_ssl']) ? 0 : 1;
				update_option('bftpro_bounce_limit', intval($_POST['bounce_limit']));
				update_option('bftpro_bounce_host', sanitize_text_field($_POST['bounce_host']));
				update_option('bftpro_bounce_ssl', $bounce_ssl);
				update_option('bftpro_bounce_port', intval($_POST['bounce_port']));
				update_option('bftpro_bounce_login', sanitize_text_field($_POST['bounce_login']));
				update_option('bftpro_bounce_pass', sanitize_text_field($_POST['bounce_pass']));				
			}
			
			if(!empty($_POST['test_connection'])) {
				require_once(ABSPATH . WPINC . '/class-pop3.php');
		  		$pop3 = new POP3();
		  		$bounce_host = get_option('bftpro_bounce_host');
		  		if(get_option('bftpro_bounce_ssl') == 1 and !preg_match("/^ssl/", $bounce_host)) $bounce_host = 'ssl://'.$bounce_host;
				$bounce_port = get_option('bftpro_bounce_port');
				$bounce_login = get_option('bftpro_bounce_login');
				$bounce_pass = get_option('bftpro_bounce_pass');
		  		
		  		if (!$pop3->connect($bounce_host, $bounce_port) || !$pop3->user($bounce_login)) {
				    $error_msg = 'Unable to connect for bounce tracking: ' . $pop3->ERROR;
				}
				
				$count = $pop3->pass($bounce_pass);
				
				if (false === $count) {      
		      	$error_msg = 'Unable to authenticate for bounce tracking: ' . $pop3->ERROR;
		      } 
			}
		} // end saving the settings
		
		$bounce_email = get_option('bftpro_bounce_email');
		$handle_bounces = get_option('bftpro_handle_bounces');		
		$bounce_limit = get_option('bftpro_bounce_limit'); // after how many bounces to delete the user
		$bounce_host = get_option('bftpro_bounce_host');
		$bounce_ssl = get_option('bftpro_bounce_ssl');
		$bounce_port = get_option('bftpro_bounce_port');
		$bounce_login = get_option('bftpro_bounce_login');
		$bounce_pass = get_option('bftpro_bounce_pass');
		include(BFTPRO_PATH."/views/bounce-options.html.php");
	}
	
	// this is called by the cron job - checks for bounces once per day
	// removes these that bounce more than X times
	static function handle_bounces() {
		global $wpdb;
		// thanks to http://plugins.svn.wordpress.org/bounce/trunk/bounce.php
		$handle_bounces = get_option('bftpro_handle_bounces');
		if(!$handle_bounces) return false;
		
		// already handled today?
		$last_bounce_handling = get_option('bftpro_last_bounce_handling');
		//if(!empty($last_bounce_handling) and $last_bounce_handling == date('Y-m-d')) return false;
		update_option('bftpro_last_bounce_handling', date('Y-m-d'));		
		
		$bounce_limit = get_option('bftpro_bounce_limit'); 
		if(!$bounce_limit) return false;
		
		require_once(ABSPATH . WPINC . '/class-pop3.php');
  		$pop3 = new POP3();
  		$bounce_host = get_option('bftpro_bounce_host');
  		if(get_option('bftpro_bounce_ssl') == 1 and !preg_match("/^ssl/", $bounce_host)) $bounce_host = 'ssl://'.$bounce_host;
		$bounce_port = get_option('bftpro_bounce_port');
		$bounce_login = get_option('bftpro_bounce_login');
		$bounce_pass = get_option('bftpro_bounce_pass');
  		
  		if (!$pop3->connect($bounce_host, $bounce_port) || !$pop3->user($bounce_login)) {
		    throw new Exception('Unable to connect for bounce tracking: ' . $pop3->ERROR);
		}
		
		$count = $pop3->pass($bounce_pass);
		
		if (false === $count) {      
      	throw new Exception('Unable to authenticate for bounce tracking: ' . $pop3->ERROR);
      } 
      
      // now get messages     
      for ($i = 1; $i <= $count; $i++) {
        $message = $pop3->get($i);
        $email = $id = '';
        
        // find receiver 
        foreach ($message as $line) {
          if(strstr($line, 'X-Bftpro-b:')) {
          	 $email = trim( str_replace(array('X-Bftpro-b: '), '', $line) );          	
          }
          if(strstr($line, 'X-Bftpro-id:')) {
          	 $id = trim( str_replace(array('X-Bftpro-id: '), '', $line) );          	
          }
        } // end foreach line in the message 
        
        // now, if we have email and ID, let's process it
        if(!empty($email) and !empty($id)) {
        	  if($bounce_limit == 1) {
        	  	  self :: cleanup($email);
        	  	  continue; // continue for{}
        	  }
        	  
        	  // is this user already deleted? if yes, save some queries
        	  $is_deleted = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_BOUNCES."
        	  	WHERE email=%s AND is_deleted=1", $email));
        	  if($is_deleted) continue; // continue for{}
        	         	  
        	  // this is a new bounce - let's figure whether to cleanup or add it to bounces	
        	  $num_bounces = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_BOUNCES." 
        	  	WHERE email=%s AND is_deleted=0 ", $email));
        	  	
        	  if(($num_bounces + 1) >= $bounce_limit) self :: cleanup($email);
        	  else $wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_BOUNCES. " SET email=%s, x_id=%s, date=CURDATE()",
        	  			$email, $id));
        } // end if(!empty($email) and !empty($id)) 
      } // end foreach message

      // Reset the so it remains in the original status
      $pop3->reset();

	} // end handle bounces
	
	// cleanup email from bounces and all mailing lists
	static function cleanup($email) {		
		global $wpdb;
		include_once(BFTPRO_PATH."/models/user.php");
		// mark as deleted
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_BOUNCES." SET is_deleted=1 WHERE email=%s", $email));				
		
		$_user = new BFTProUser();
		// echo $wpdb->prepare("SELECT id FROM ".BFTPRO_USERS." WHERE email=%s", $email);
		$uids = $wpdb->get_results($wpdb->prepare("SELECT id FROM ".BFTPRO_USERS." WHERE email=%s", $email)); 
		 
		foreach($uids as $uid) {
			$_user->delete($uid->id);
		}
	} // end cleanup
}