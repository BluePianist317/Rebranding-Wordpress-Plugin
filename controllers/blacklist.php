<?php
// blacklist management
class ArigatoPROBlacklist {
	static function main() {
		if(!empty($_POST['ok']) and check_admin_referer('arigatopro_blacklist')) {			
			update_option('bftpro_blacklisted_ips', bftpro_strip_tags($_POST['ips']));
			update_option('bftpro_blacklisted_emails', bftpro_strip_tags($_POST['emails']));
			$settings = array("behavior" => sanitize_text_field($_POST['behavior']), 'error_msg' => bftpro_strip_tags($_POST['error_msg']));
			update_option('bftpro_blacklist_settings', $settings);
		}
		
		$settings = get_option('bftpro_blacklist_settings');
		
		include(BFTPRO_PATH . '/views/blacklist.html.php');
	}
	
	// check member details for blacklist
	// returns true on success (not blacklisted, continue), false on failure (blacklisted)
	static function check($vars) {
		$settings = get_option('bftpro_blacklist_settings');
		
		// check IP
		$ips = get_option('bftpro_blacklisted_ips');
		if(!empty($ips)) {
			$ips = explode(PHP_EOL, $ips);
			foreach($ips as $ip) {
				if(empty($ip)) continue;
				$ip = str_replace('.', '\.', $ip);
				$ip = str_replace('*', '[\d\.]+', $ip);
				$ip = str_replace('?', '[\d\.]', $ip);
				if(preg_match("/$ip/", $_SERVER['REMOTE_ADDR'])) return self :: fail();
			}
		}
		
		// check email
		$emails = get_option('bftpro_blacklisted_emails');
		if(!empty($emails)) {			
			$emails = explode(PHP_EOL, $emails);
			foreach($emails as $email) {
				if(empty($email)) continue;
				$email = str_replace('.', '\.', $email);
				$email = str_replace('*', '(.*)+', $email);
				$email = str_replace('?', '.', $email);
				if(preg_match("/$email/", $vars['email'])) return self :: fail();
			}
		}
		
		return true;
	} // end checking
	
	// just return false or throw error depending on settings
	static function fail() {
		$settings = get_option('bftpro_blacklist_settings');
		if($settings['behavior'] == 'exit') {
			throw new Exception(stripslashes($settings['error_msg']));
		}
		
		return false;
	} // end fail()
}