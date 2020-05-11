<?php
// handle attachments
class BFTProAttachmentModel {
	// select attachments of a given mail or newsletter
	function select($type, $id) {
		global $wpdb;
		$field = ($type == 'mail') ? 'mail_id' : 'nl_id';
		
		$attachments = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS."
			WHERE $field=%d ORDER BY id", $id));
			
		return $attachments;	
	}
	
	// lists editable attachments so we can delete them if required
	function list_editable($attachments) {
		if(!is_array($attachments)) return false;
		foreach($attachments as $attachment):?>
			<div><a href="<?php echo $attachment->url?>" target="_blank"><?php echo $attachment->file_name?></a> <input type="checkbox" name="del_attachments[]" value="<?php echo $attachment->id?>"> <?php _e('Mark to delete', 'bftpro')?></div>	
		<?php endforeach;
	}
	
	// deletes attachment along with the local file
	function delete($id) {
		global $wpdb;
		
		$attachment = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_ATTACHMENTS." WHERE id=%d", $id));
		
		@unlink($attachment->file_path);
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_ATTACHMENTS." WHERE id=%d", $id));
	}
	
	// save multiple attachments to mail or newsletter
	function save_attachments($id, $type) {
		global $wpdb;
		$field = ($type == 'mail') ? 'mail_id' : 'nl_id';
		require_once(BFTPRO_PATH."/helpers/filehelper.php");
		
		if(!empty($_FILES['attachments']) and is_array($_FILES['attachments']) and sizeof($_FILES['attachments'])) {
			foreach($_FILES['attachments']['name'] as $cnt=>$name) {	
				if(empty($name)) continue;			
						
				$path = $_FILES['attachments']['tmp_name'][$cnt];
				if(!wp_verify_nonce($_POST['bftpro_attach_nonce'], 'bftpro_attach_nonce')) wp_die("Security check failed");
				
				$upload_dir = wp_upload_dir(); 
				$local_path = $upload_dir['path'];
				$http_path = $upload_dir['url'];
				
				@copy($path, $local_path."/".$name);
				
				$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_ATTACHMENTS." SET
					$field=%d, file_name=%s, file_path=%s, url=%s", 
					$id, $name, $local_path."/".$name, $http_path."/".$name));
			}
		}

		// any old attachments to delete?
		if(!empty($_POST['del_attachments']) and is_array($_POST['del_attachments'])) {
			foreach($_POST['del_attachments'] as $id) $this->delete($id);
		}		
		
		return true;
	}
	
	// delete multiple attachments in email or newsletter
	function delete_attachments($id, $type) {
		$attachments = $this->select($type, $id);		
		foreach($attachments as $attachment) $this->delete($attachment->id);
		
		return true;
	}
}