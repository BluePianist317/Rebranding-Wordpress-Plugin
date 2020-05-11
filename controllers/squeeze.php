<?php
// manage squeeze page settings
class BFTProSqueeze {
	static function main() {
		$multiuser_access = 'all';
		$multiuser_access = BFTProRoles::check_access('squeeze_access');		
		
		if(!empty($_POST['ok'])) {
			update_option('bftpro_squeeze_setting', $_POST['squeeze_setting']); // use squeeze on home, on URL, or none at all
			update_option('bftpro_squeeze_contents', $_POST['squeeze_contents']);
			update_option('bftpro_squeeze_url', $_POST['squeeze_url']);
		}
		
		$squeeze_setting = get_option('bftpro_squeeze_setting');
		
		include(BFTPRO_PATH."/views/manage-squeeze.html.php");
	} // end main()
	
   static function squeeze_template($template) {
   	global $post;
   	if ( is_home() and get_option('bftpro_squeeze_setting') == 'home' ) {
			return BFTPRO_PATH."/views/squeeze.html.php";
		}
		
		if( get_option('bftpro_squeeze_setting') == 'url') {			
			$post_id = url_to_postid(get_option('bftpro_squeeze_url'));			
			if(!empty($post_id) and $post_id == @$post->ID) return BFTPRO_PATH."/views/squeeze.html.php";
		}
	
		return $template;
   } // end temp squeeze
}