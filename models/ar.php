<?php
class BFTProARModel {
	// just select all of my autoresponder campaigns order by name
	function select($id = null) {
		global $wpdb, $user_ID;
		$multiuser_access = 'all';
	   $multiuser_access = BFTProRoles::check_access('ar_access');
		
		$id_sql = $id?$wpdb->prepare(" AND id=%d ", $id):"";
		
		$own_sql = '';
		if($multiuser_access == 'own') {
			$own_sql = $wpdb->prepare(' AND editor_id=%d ', $user_ID);
		}	
		
		$campaigns = $wpdb->get_results("SELECT * FROM ".BFTPRO_ARS." WHERE 1 $id_sql $own_sql ORDER BY name");		
			
		if($id) return @$campaigns[0];		
		else return $campaigns;
	}
	
	function add($vars) {
		global $wpdb, $user_ID;
		
		$this->prepare_vars($vars);
		
		// name already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_ARS." WHERE name=%s", $vars['name']));
		if($exists) throw new Exception(__('This campaign name already exists', 'bftpro'));
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_ARS."  SET 
			name=%s, list_ids=%s, description=%s, sender=%s, editor_id=%d", 
				$vars['name'], "|".@implode("|", $vars['list_ids'])."|", 
			$vars['description'], $vars['sender'], $user_ID));
			
		return $wpdb->insert_id;	
	}
	
	function edit($vars, $id) 	{
		global $wpdb;
		$id = intval($id);
		$this->prepare_vars($vars);
		
		// name already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_ARS." WHERE name=%s AND id!=%d", $vars['name'], $id));
		if($exists) throw new Exception(__('This campaign name already exists', 'bftpro'));
		
		$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_ARS." SET
 			name=%s, list_ids=%s, description=%s, sender=%s WHERE id=%d", 
 			$vars['name'], "|".@implode("|", $vars['list_ids'])."|", 
			$vars['description'], $vars['sender'], $id));
			
		return true;		
	}
	
	function delete($id) {
		global $wpdb;
		$id = intval($id);
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_ARS." WHERE id=%d",$id));
		
		return true; 
	} // end delete()
	
	// prepare & sanitize vars
	function prepare_vars(&$vars) {
	   $vars['name'] = sanitize_text_field($vars['name']);
	   $vars['list_ids'] = bftpro_int_array($vars['list_ids']);
	   $vars['description'] = bftpro_strip_tags($vars['description']);
	} // end prepare_vars
}