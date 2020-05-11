<?php
// newsletter model 
class BFTProNLModel {
	function __construct() {
		require_once(BFTPRO_PATH."/models/attachment.php");
		$this->_att = new BFTProAttachmentModel();
	}	
	
	// join to currently selected mailing list
	function select($id = null) {
		global $wpdb, $user_ID;
		$id = intval($id);
		
		$multiuser_access = 'all';
	   $multiuser_access = BFTProRoles::check_access('nl_access');
	   $own_sql = '';
		if($multiuser_access == 'own') {
			$own_sql = $wpdb->prepare(' AND tN.editor_id=%d ', $user_ID);
		}	
		
		if($id) {			
			$mail = $wpdb->get_row($wpdb->prepare("SELECT tN.*, tL.name as list_name FROM ".BFTPRO_NEWSLETTERS." tN
				LEFT JOIN ".BFTPRO_LISTS." tL ON tL.id=tN.list_id
				WHERE tN.id=%d $own_sql", $id));
			return $mail;	
		} 
		else {			
			$mails = $wpdb->get_results("SELECT tN.*, tL.name as list_name, 
				tM.subject as from_mail_subject, tR.name as from_campaign_name, tR.id as from_campaign_id 
				FROM ".BFTPRO_NEWSLETTERS." tN
				LEFT JOIN ".BFTPRO_LISTS." tL ON tL.id=tN.list_id 
				LEFT JOIN ".BFTPRO_MAILS." tM ON tM.id = tN.from_mail_id
				LEFT JOIN ".BFTPRO_ARS." tR ON tR.id = tM.ar_id
				WHERE 1 $own_sql ORDER BY tN.ID DESC");
			return $mails;	
		}
	}
	
	function add($vars) {
		global $wpdb, $user_ID;
		$vars = $this->prepare_vars($vars);
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_NEWSLETTERS." SET
			sender=%s, subject=%s, message=%s, date_created=CURDATE(), list_id=%d, 
			status='not sent', mailtype=%s, is_global=%d, lists_to_go=%s, never_duplicate=%d, editor_id=%d, 
			preset_id=%d, send_test=%s, is_scheduled=%d, scheduled_for=%s,
			date_limit=%s, has_date_limit=%d, from_mail_id=%d, skip_lists=%s, force_wpautop=%d, reply_to=%s, description=%s",
			$vars['sender'], $vars['subject'], $vars['message'], $vars['list_id'], 
			$vars['mailtype'], $vars['is_global'], $vars['lists_to_go'], 
			@$vars['never_duplicate'], $user_ID, intval(@$vars['preset_id']), 
			$vars['send_test'], $vars['is_scheduled'], $vars['scheduled_for'],
			$vars['date_limit'], $vars['has_date_limit'], $vars['from_mail_id'], 
			$vars['skip_lists'], $vars['force_wpautop'], $vars['reply_to'], $vars['description']));
			
		$id = $wpdb->insert_id;	
		$this->_att->save_attachments($id, 'newsletter');	
		do_action('bftpro-nl-save', $vars, $id);				
		return $id;	
	}
	
	function edit($vars, $id) {
		global $wpdb;
		$vars = $this->prepare_vars($vars);
		$id = intval($id);
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET
			sender=%s, subject=%s, message=%s, list_id=%d, mailtype=%s,
			is_global=%d, lists_to_go=%s, never_duplicate=%d, send_test=%s, is_scheduled=%d, scheduled_for=%s,
			date_limit=%s, has_date_limit=%d, from_mail_id=%d, skip_lists=%s, force_wpautop=%d, reply_to=%s, description=%s
			WHERE id=%d",
			$vars['sender'], $vars['subject'], $vars['message'], $vars['list_id'], $vars['mailtype'], 
			$vars['is_global'], $vars['lists_to_go'], @$vars['never_duplicate'], $vars['send_test'], 
			$vars['is_scheduled'], $vars['scheduled_for'], $vars['date_limit'], $vars['has_date_limit'], 
			$vars['from_mail_id'], $vars['skip_lists'], $vars['force_wpautop'], $vars['reply_to'], $vars['description'], $id));
			
		$this->_att->save_attachments($id, 'newsletter');		
		do_action('bftpro-nl-save', $vars, $id);
		return true;	
	}
	
	function delete($id) {
		global $wpdb;
		$id = intval($id);
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_NEWSLETTERS." WHERE id=%d", $id));
		
		$this->_att->delete_attachments($id, 'newsletter');
		
		// delete sent data
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_SENTMAILS." WHERE newsletter_id=%d", $id));
		
		return true;
	}
	
	function send($id) {
		// change status and reset last_user_id
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET status='in progress', 
			last_user_id=0, date_last_sent=CURDATE(), completed_lists = '' 
			WHERE id=%d", $id)); 
	}
	
	function cancel($id) {
		// change status and reset last_user_id
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_NEWSLETTERS." SET status='cancelled', last_user_id=0 WHERE id=%d", $id));
	}
	
	function prepare_vars($vars) {
		global $wpdb;
		
		$vars['is_global'] = 0;
		$vars['lists_to_go'] = '';		
		
		$vars['sender'] = esc_sql($vars['sender']);
		$vars['reply_to'] = esc_sql($vars['reply_to']);
		$vars['subject'] = sanitize_text_field($vars['subject']);
		$vars['message'] = empty($vars['message']) ? '' : bftpro_strip_tags($vars['message']);
		$vars['mailtype'] = sanitize_text_field($vars['mailtype']);
		$vars['is_global'] = empty($vars['is_global']) ? 0 : 1;
		$vars['never_duplicate'] = empty($vars['never_duplicate']) ? 0 : 1;
		$vars['send_test'] = sanitize_text_field($vars['send_test']);   
		$vars['is_scheduled'] = empty($vars['is_scheduled']) ? 0 : 1;
		$vars['scheduled_for'] = sanitize_text_field(@$vars['scheduled_for']);
		$vars['description'] = wp_kses_post($vars['description']);
		
		// global newsletter
		if($vars['list_id'] == -1) {
			// select all list IDs
			$lists = $wpdb->get_results("SELECT id FROM ".BFTPRO_LISTS." ORDER BY id");
			$lids = array();
			foreach($lists as $list) $lids[] = $list->id;
			
			if(sizeof($lids)) {
				$vars['list_id'] = array_shift($lids);
				$vars['lists_to_go'] = implode("|", $lids);
			}
			$vars['is_global'] = 1;
		}
		
		// if using a preset, message becomes the combination of blocks
		if(!empty($vars['preset_id'])) {
			$vars['message'] = ArigatoPROPresets :: prepare($vars['preset_id']);
		}
		
		// these 3 vars come from auto-generating in AR campaings and will be empty when we really create a newsletter
		$vars['date_limit'] = empty($vars['date_limit']) ? '2000-01-01' : sanitize_text_field($vars['date_limit']);
		$vars['has_date_limit'] = empty($vars['has_date_limit']) ? 0 : 1;
		$vars['from_mail_id'] = intval(@$vars['from_mail_id']);
		
		$vars['skip_lists'] = bftpro_int_array(@$vars['skip_lists']);
		if(!empty($vars['skip_lists'][0])) $vars['skip_lists'] = implode(',', $vars['skip_lists']);
		else $vars['skip_lists'] = '';
		
		$vars['force_wpautop'] = empty($vars['force_wpautop']) ? 0 : 1;
		
		return $vars;
	} // end prepare_vars
}