<?php
// send varios test emails etc
class BFTProTests {
   static function test_email($sender, $subject, $message, $mailtype, $list_id, $template_id, $attachments, $preset_id = 0, $receiver_email = '') {
   	global $wpdb;
   	
   	if(empty($receiver_email)) $receiver_email = get_option('bftpro_sender');
		
		// if receiver email contains < break on and get only email
		$receiver_email = bftpro_extract_email($receiver_email);
		
		// add unsubscribe link
		if(!empty($list_id)) $list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $list_id));
		
		if(!empty($preset_id)) $message = ArigatoPROPresets :: match_blocks($message, $preset_id);
		
		$_sender = new BFTProSender();
		
		// nl2br message?
		if($mailtype=='text/html' or $mailtype=='both') $message = $_sender -> maybe_nl2br($message);
		
		// add signature if any
		if(!isset($_sender->signature)) $_sender->signature = stripslashes(get_option('bftpro_signature'));
		if(!empty($_sender->signature)) {
				 if($mailtype=='text/html' or $mailtype=='both') $_sender->signature = $_sender -> maybe_nl2br($_sender->signature);			
			
				if($mailtype=='text/html' or $mailtype=='both') {
		 			 $message.="<p>&nbsp;</p>".$_sender->signature;
		 		}
		 		else $message.="\n\n".$_sender->signature;
		}		
		if(class_exists('BFTISender')) $message = apply_filters('bftpro_template_filter', $message, $template_id);

		$receiver = (object)array("email"=>$receiver_email);
		if(!empty($list_id)) {
			if(strstr($message, '{{unsubscribe-url}}')) $message = $_sender->replace_unsubscribe_link($receiver, $mailtype, @$list, '', 0, $message);
			else $message .= $_sender->add_unsubscribe_link($receiver, $mailtype, @$list, '', 0, null, $message);
		}
		
		$_sender->send($sender, $receiver_email, $subject, $message, $mailtype, $attachments);
	}
}