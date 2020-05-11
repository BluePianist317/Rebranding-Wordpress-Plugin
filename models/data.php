<?php
class BFTProData {
	// called directly by a link, downloads file
	static function download() {
		global $wpdb;
		
		// select data
		$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_DATAS." WHERE id=%d", intval($_GET['id'])));
		
		if(empty($data->fileblob)) wp_die(__("There is nothing to download.", 'bftpro'));
	
		// send download headers
		header('Content-Disposition: attachment; filename="'.$data->data.'"');				
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Length: " . strlen($data->fileblob)); 
		
		echo $data->fileblob;
	}
}