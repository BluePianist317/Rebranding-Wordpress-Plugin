<?php
// fine-tune role access and restrict it 
class BFTProRoles {
	static function manage() {
		global $wpdb, $wp_roles;
		$roles = $wp_roles->roles;
		
		// this sets the setting of a selected role
		if(!empty($_POST['config_role'])) {
			$role_settings = unserialize(get_option('bftpro_role_settings'));	
			
			// overwrite the settings for the selected role
			$role_settings[$_POST['role_key']] = array("settings_access" => $_POST['settings_access'], "lists_access" => $_POST['lists_access'], "ar_access" => $_POST['ar_access'],
				"nl_access" => $_POST['nl_access'], "bounce_access" => $_POST['bounce_access'], "subemail_access" => $_POST['subemail_access'],
				"squeeze_access" => $_POST['squeeze_access'], "trackable_access" => @$_POST['trackable_access'],
				"triggers_access" => @$_POST['triggers_access'], "segments_access" => @$_POST['segments_access'],
				"templates_access" => @$_POST['templates_access'], "digest_access" => @$_POST['digest_access']);
				
			update_option('bftpro_role_settings', serialize($role_settings));	
		} // end config_role
		
		$role_settings = unserialize(get_option('bftpro_role_settings'));
		
		// get the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['bftpro_manage'])) $enabled_roles[] = $key;
		}
		
		require(BFTPRO_PATH."/views/roles.html.php");
	}
	
	// checks the access of the current user
	static function check_access($what, $noexit = false) {
		global $user_ID, $wp_roles;
		
		$role_settings = unserialize(get_option('bftpro_role_settings'));
		$roles = $wp_roles->roles;
		// get all the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['bftpro_manage'])) $enabled_roles[] = $key;
		}
				
		// admin can do everything
		if(current_user_can('administrator')) return 'all';		
		$user = new WP_User( $user_ID );
				
		$has_access = false;
		foreach($user->roles as $role) {
			if(!empty($role_settings[$role])) {				
				// empty is also true because we have to keep the defaults
				if(empty($role_settings[$role][$what]) or $role_settings[$role][$what] == 'all') {
					return 'all';
				}
				elseif($role_settings[$role][$what] == 'own') $has_access = 'own';
				// when none of the above, we just leave $has_access as false			
			}
			elseif(in_array($role, $enabled_roles)) $has_access = 'all'; // role was not specified in fine-tune so we just use the default full access
		}
		
		// if we are here, it means none of his roles had 'all'
		if($has_access) return $has_access;
		
		// when no access, die
		if($noexit) return false;
		else wp_die(__('You are not allowed to do this.', 'bftpro'));
	}
}