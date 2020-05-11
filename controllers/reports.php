<?php
class BFTProReports {
	// run reports for autoresponder campaign
	static function campaign() {
		global $wpdb;
		require_once(BFTPRO_PATH."/models/mail.php");
		$_report = new BFTProReport();
		list($from, $to) = self :: get_period();
		
		$mails = $_report->campaign_report($from, $to);
		$campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_ARS." 
			WHERE id=%d", $_GET['campaign_id']));
		
		require(BFTPRO_PATH."/views/campaign-report.html.php");
	}
	
	// newsletter reports
	static function newsletter() {
		global $wpdb;
		$_report = new BFTProReport();
		list($from, $to) = self :: get_period();
		
		$newsletters = $_report->newsletter_report($from, $to);
		
		if(!empty($_GET['newsletter_id'])) {
			$newsletter = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_NEWSLETTERS." WHERE id=%d", $_GET['newsletter_id']));
		}
		
		require(BFTPRO_PATH."/views/newsletter-report.html.php");
	}
	
	// newsletter reports by email domain. called by Ajax
	static function newsletter_by_domain() {
		global $wpdb;
		$_report = new BFTProReport();
				
		$domains = $_report -> newsletter_by_domain($_POST['from'], $_POST['to'], $_POST['id']);
		
		require(BFTPRO_PATH."/views/newsletter-by-domain.html.php");
		exit;
	}
	
	// armail reports by email domain. called by Ajax, using the same view as newsletter by domain report
	static function armail_by_domain() {
		global $wpdb;
		$_report = new BFTProReport();
				
		$domains = $_report -> armail_by_domain($_POST['from'], $_POST['to'], $_POST['id']);
		
		require(BFTPRO_PATH."/views/newsletter-by-domain.html.php");
		exit;
	}
	
	// defines the from/to period for a report depending on $_POST
	static function get_period() {
		$from = empty($_POST['fromday']) ? date("Y-m")."-01" : $_POST['fromyear'].'-'.$_POST['frommonth'].'-'.$_POST['fromday'];
		$to = empty($_POST['today']) ? date("Y-m-d") : $_POST['toyear'].'-'.$_POST['tomonth'].'-'.$_POST['today'];
		
		return array($from, $to);
	}
	
	// the log of sent newsletter. This is not the same as the "in progress" log from BFTProNLController
	static function sent_nl_log() {
		global $wpdb;
		
		$from = $_GET['from'];
		$to = $_GET['to'];
		
		$newsletter = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_NEWSLETTERS." WHERE id=%d", $_GET['id']));
		
		// select all read mails
		$read_uids = array(0);
		$read_nls = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM ".BFTPRO_READNLS." WHERE newsletter_id=%d", $_GET['id']));	
		foreach($read_nls as $read_nl) $read_uids[] = $read_nl->user_id;
		
		$user_id_sql = '';
		if(!empty($_GET['read'])) {
			$user_id_sql = " AND tS.user_id IN (".implode(',', $read_uids).") ";
		}
		
		// select all sent emails for this newsletter
		$mails = $wpdb->get_results($wpdb->prepare("SELECT tS.*, tU.email as email 
			FROM ".BFTPRO_SENTMAILS." tS LEFT JOIN ".BFTPRO_USERS." tU ON tU.id = tS.user_id
			WHERE tS.newsletter_id=%d $user_id_sql AND tS.date>=%s AND tS.date<=%s AND errors=''
			ORDER BY tS.id DESC", $_GET['id'], $from, $to));	
		
		$dateformat = get_option('date_format');
		require(BFTPRO_PATH."/views/sent-nl-log.html.php");
	}
}