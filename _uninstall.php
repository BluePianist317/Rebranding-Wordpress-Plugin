<?php
/*
Plugin URI: http://calendarscripts.info/bft-pro/
Author: Kiboko Labs
*/

global $wpdb;

if(!defined('WP_UNINSTALL_PLUGIN') or !WP_UNINSTALL_PLUGIN) exit;
    
// clenaup all data
if(get_option('bftpro_cleanup_db') === "1")
{
	// delete attachments if any
	$attachments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bftpro_attachments");
	foreach($attachments as $attachment) {
		 @unlink($attachment['file_path']);
	}	
	
	// now drop tables	
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_ars");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_attachments");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_datas");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_fields");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_lists");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_logs");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_mails");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_newsletters");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_readmails");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_readnls");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_sentmails");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_unsubs");
	$wpdb->query("DROP TABLE {$wpdb->prefix}bftpro_users");
	    
	// clean options
	delete_option('bftpro_cleanup_db');
	delete_option('bftpro_version');
	delete_option('bftpro_sender');
	delete_option('bftpro_optin_subject');
	delete_option('bftpro_optin_message');
	delete_option('bftpro_signature');
	delete_option('bftpro_mails_per_run');
	delete_option('bftpro_mails_per_day');
	delete_option('bftpro_cron_mode');
	delete_option('bftpro_cron_minutes');
	delete_option('bftpro_recaptcha_public');
	delete_option('bftpro_recaptcha_private');
	delete_option('bftpro_today_mails');
	delete_option('bftpro_cron_date');
	delete_option('bftpro_last_cron_run');
	delete_option('bftpro_cron_status');
	delete_option('bftpro_signature');
}