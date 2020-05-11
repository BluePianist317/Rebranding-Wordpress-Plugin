<?php
class BFTProHelp {
	// display main help page
	static function help_main() {
		require(BFTPRO_PATH."/views/help.php");
	}
	
	static function troubleshooting() {
		include(BFTPRO_PATH."/views/troubleshooting.php");
	}
	
	// pull the cron job log
	static function error_log() {
		global $wpdb;
		$date = empty($_POST['dateyear']) ? date("Y-m-d") : $_POST['dateyear'].'-'.$_POST['datemonth'].'-'.$_POST['dateday'];		
		if(!empty($_POST['cleanup'])) update_option('bftpro_cleanup_cron_log', $_POST['cleanup_days']);		
		
		// select error log
		$log = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LOGS." WHERE date=%s", $date));
		
		$cleanup_cron_log = get_option('bftpro_cleanup_cron_log');
		if(empty($cleanup_cron_log)) $cleanup_cron_log = 30;
		
		require(BFTPRO_PATH."/views/error-log.html.php");
	}
	
	// raw email log
	static function raw_log() {
		global $wpdb;
		$date = empty($_POST['date']) ? date('Y-m-d') : $_POST['date'];
		if(!empty($_POST['cleanup'])) update_option('bftpro_cleanup_raw_log', $_POST['cleanup_days']);
		$receiver_sql = '';
		if(!empty($_POST['receiver'])) $receiver_sql = $wpdb->prepare(" AND receiver=%s ", $_POST['receiver']);
		
		$emails = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".BFTPRO_EMAILLOG." 
			WHERE date=%s $receiver_sql ORDER BY id", $date));
		
		$cleanup_raw_log = get_option('bftpro_cleanup_raw_log');
		if(empty($cleanup_raw_log)) $cleanup_raw_log = 7;
		
		bftpro_enqueue_datepicker();
		require(BFTPRO_PATH."/views/raw-email-log.html.php"); 
	}
}