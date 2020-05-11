<?php
class BFTProMailModel {
	function __construct() {
		require_once(BFTPRO_PATH."/models/attachment.php");
		$this->_att = new BFTProAttachmentModel();
	}	
	
	function select($campaign_id, $id = null) {
		global $wpdb;
		
		if($id) {
			$mail = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_MAILS." 
				WHERE ar_id=%d AND id=%d", $campaign_id, $id));
			return $mail;	
		} 
		else {			
			$mails = $wpdb->get_results($wpdb->prepare("SELECT tM.*, 
				(SELECT COUNT(tUN.id) FROM ".BFTPRO_UNSUBS." tUN WHERE tUN.mcat='ar' AND tUN.mail_id=tM.id) as num_unsubscribed,
				(SELECT COUNT(tS.id) FROM ".BFTPRO_SENTMAILS." tS WHERE tS.mail_id=tM.id) as num_sent
				FROM ".BFTPRO_MAILS." tM
				WHERE tM.ar_id=%d ORDER BY tM.days, tM.send_on_date, tM.every, tM.subject, tM.id", $campaign_id));
			return $mails;	
		}
	}
	
	function add($vars) {
		global $wpdb;
		
		$this->prepare_vars($vars);
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_MAILS." SET
			sender=%s, subject=%s, message=%s, artype=%s, ar_id=%d, 
			days=%d, send_on_date=%s, every=%s, mailtype=%s, daytime=%d, split_testing=%d, preset_id=%d, 
			is_paused=%d, send_test=%s, force_wpautop=%d, reply_to=%s, mins_after_reg=%d, description=%s",
			$vars['sender'], $vars['subject'], $vars['message'], $vars['artype'], $vars['ar_id'], 
			$vars['days'], @$vars['send_on_date'], @$vars['every'], $vars['mailtype'], 
			$vars['daytime'], @$vars['split_testing'], intval(@$_GET['preset_id']), @$vars['is_paused'], 
			$vars['send_test'], $vars['force_wpautop'], $vars['reply_to'], $vars['mins_after_reg'], $vars['description']));
		$id = $wpdb->insert_id;		
		
		$this->_att->save_attachments($id, 'mail');		
		if($vars['artype'] == 'days' and !empty($vars['send_to_old'])) $this->generate_newsletter($id, $vars);	
		
		do_action('bftpro-armail-save', $vars, $id);
			
		return $id;	
	}
	
	function edit($vars, $id) {
		global $wpdb;
		
		$this->prepare_vars($vars);		
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_MAILS." SET
			sender=%s, subject=%s, message=%s, artype=%s, ar_id=%d, days=%d, send_on_date=%s, 
			every=%s, mailtype=%s, daytime=%d, split_testing=%d, is_paused=%d, send_test=%s, force_wpautop=%d, 
			reply_to=%s, mins_after_reg=%d, description=%s
			WHERE id=%d",
			$vars['sender'], $vars['subject'], $vars['message'], $vars['artype'], $vars['ar_id'], 
			$vars['days'], $vars['send_on_date'], $vars['every'], $vars['mailtype'], 
			$vars['daytime'], $vars['split_testing'], $vars['is_paused'], $vars['send_test'], $vars['force_wpautop'], 
			$vars['reply_to'], $vars['mins_after_reg'],$vars['description'], $id));
			
		$this->_att->save_attachments($id, 'mail');
		if($vars['artype'] == 'days' and !empty($vars['send_to_old'])) $this->generate_newsletter($id, $vars);
		
		do_action('bftpro-armail-save', $vars, $id);		
			
		return true;	
	}
	
	function delete($id) {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_MAILS." WHERE id=%d", $id));
		
		$this->_att->delete_attachments($id, 'mail');
		
		// delete sent data
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_SENTMAILS." WHERE mail_id=%d", $id));
		
		return true;
	}
	
	// make some changes to $vars for inserting in the DB
	private function prepare_vars(&$vars) {
		if($vars['artype']=='every_days') $vars['every'] = $vars['every_days'];
		if($vars['artype']=='every_weekday') $vars['every'] = $vars['every_weekday'];
		
		if($vars['artype']=='date') $vars['send_on_date']=$vars['send_on_dateyear']."-".$vars['send_on_datemonth'].'-'.$vars['send_on_dateday'];
		
		if(empty($vars['send_on_date'])) $vars['send_on_date'] = '2000-01-01';
		
		$vars['daytime'] = (!empty($vars['time_of_sending']) and $vars['time_of_sending']=='specified') ? $vars['daytime'] : 0;
		if($vars['artype'] == 'event_days') $vars['split_testing'] = 0;
		
		// if using a preset, message becomes the combination of blocks
		$vars['message'] = bftpro_strip_tags($vars['message']);
		if(!empty($vars['preset_id'])) {
			$vars['message'] = ArigatoPROPresets :: prepare($vars['preset_id']);
		}
		
		$vars['force_wpautop'] = empty($vars['force_wpautop']) ? 0 : 1;
		
		$vars['sender'] = esc_sql($vars['sender']);		
		$vars['reply_to'] = esc_sql($vars['reply_to']);		
		$vars['artype'] = sanitize_text_field($vars['artype']);
		$vars['ar_id'] = intval($vars['ar_id']);
		$vars['days'] = intval($vars['days']);
		$vars['send_on_date'] = sanitize_text_field($vars['send_on_date']);
		$vars['every'] = sanitize_text_field(@$vars['every']);
		$vars['mailtype'] = sanitize_text_field(@$vars['mailtype']);
		$vars['is_paused'] = empty($vars['is_paused']) ? 0 : 1;
		$vars['daytime'] = intval($vars['daytime']);
		$vars['split_testing'] = intval(@$vars['split_testing']);
		$vars['send_test'] = sanitize_text_field($vars['send_test']);
		$vars['description'] = wp_kses_post($vars['description']);
		
		$vars['mins_after_reg'] = intval(@$vars['mins_after_reg']);
		if($vars['artype'] != 'days' or $vars['days'] != 0) $vars['mins_after_reg'] = 0;
	}
	
	// generates a newsletter that will be sent to subscribers who will not get the message because they are registered
	// for example: if the mail is to be sent 7 days after sign-up, subscribers registered 8 or more days ago will never get it
	function generate_newsletter($mail_id, $vars) {
		global $wpdb;
		include_once(BFTPRO_PATH . '/models/newsletter.php');

		// this has to be done once for each mailing list this AR is assigned to
		$list_ids = $wpdb->get_var($wpdb->prepare("SELECT tR.list_ids FROM ".BFTPRO_ARS." tR
			JOIN ".BFTPRO_MAILS." tM ON tR.id = tM.ar_id
			WHERE tM.id=%d", $mail_id));
		$list_ids = explode('|', $list_ids);
		$list_ids = array_filter($list_ids);
		
		$vars['has_date_limit'] = 1;
		$vars['from_mail_id'] = $mail_id;
		
		// calculate date
		$vars['date_limit'] = date('Y-m-d', strtotime("- ".$vars['days']." days"));
		$_nl = new BFTProNLModel();
		
		foreach($list_ids as $list_id) {
			$vars['list_id'] = $list_id;
			$nid = $_nl->add($vars);
			$_nl->send($nid);
		}
		
	} // end generate_newsletter
}