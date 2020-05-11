<?php
class BFTProJetPack {
	static function signup() {
		if(empty($_POST['action']) or $_POST['action'] != 'grunion-contact-form') return false;
		
		// in the latest version Jetpack added some digits to email and name field. We'll have to create new vars
		foreach($_POST as $key => $val) {		
			if(preg_match("/g".@$_POST['contact-form-id']."-(\d+)email(\d+)/", $key)) {				
				$_POST['g' . @$_POST['contact-form-id'] . '-email'] = $val;
			}
			if(preg_match("/g".@$_POST['contact-form-id']."-(\d+)name(\d+)/", $key)) {
				$_POST['g' . @$_POST['contact-form-id'] . '-name'] = $val;
			}
		}
		
		$user = array('email' => "", "name"=>"");	
		$user['email'] = !empty( $_POST['g' . @$_POST['contact-form-id'] . '-email']  ) ? trim( $_POST['g' . @$_POST['contact-form-id'] . '-email'] ) : '';
	   $user['name'] = !empty( $_POST['g' . @$_POST['contact-form-id'] . '-name']  ) ? trim( $_POST['g' . @$_POST['contact-form-id'] . '-name'] ) : '';		
		BFTProIntegrations :: signup($_POST, $user);		
	}
}