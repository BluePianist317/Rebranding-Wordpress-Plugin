<?php
// main model containing general config and UI functions
class BFTPro {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	if(!$update)  self::init();
   	
   	// subscribers - these do NOT need to be WP users
   	if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_USERS."'") != BFTPRO_USERS) {        
			$sql = "CREATE TABLE " . BFTPRO_USERS . " (
				  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  email VARCHAR(100) NOT NULL DEFAULT '',	
				  name VARCHAR(255)	NOT NULL DEFAULT '',		  
				  status TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  date DATE NOT NULL DEFAULT '2000-01-01',
              ip VARCHAR(100) NOT NULL DEFAULT '',
				  code VARCHAR(10) NOT NULL DEFAULT '',
				  list_id INT UNSIGNED NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // autoresponder mails
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_MAILS."'") != BFTPRO_MAILS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_MAILS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `sender` VARCHAR(255) NOT NULL DEFAULT '',
				  `subject` VARCHAR(255) NOT NULL DEFAULT '',				  
				  `message` TEXT NOT NULL,		
				  `mailtype` VARCHAR(100) NOT NULL DEFAULT '',
				  `artype` VARCHAR(100) NOT NULL DEFAULT '',
				  `ar_id` INT UNSIGNED NOT NULL DEFAULT 0,    		  
				  `days` INT UNSIGNED NOT NULL DEFAULT 0,
              `send_on_date` DATE NOT NULL DEFAULT '2000-01-01',
              `every` VARCHAR(100) NOT NULL DEFAULT '',
              `daytime` INT UNSIGNED NOT NULL DEFAULT 0        
				) DEFAULT CHARSET=utf8mb4;";			
			
			$wpdb->query($sql);
	  }
	  
	  // sent autoresponder mails (to avoid double sending)
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_SENTMAILS."'") != BFTPRO_SENTMAILS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_SENTMAILS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `mail_id` INT UNSIGNED NOT NULL DEFAULT 0,			
				  `newsletter_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,				  
				  `date` DATE NOT NULL DEFAULT '2000-01-01'
				);";
			$wpdb->query($sql);
	  }
	  
	  // newsletters
	 if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_NEWSLETTERS."'") != BFTPRO_NEWSLETTERS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_NEWSLETTERS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `sender` VARCHAR(255) NOT NULL DEFAULT '',
				  `subject` VARCHAR(255) NOT NULL DEFAULT '',				  
				  `message` TEXT NOT NULL,		
				  `date_created` DATE NOT NULL DEFAULT '2000-01-01',
				  `list_id` INT UNSIGNED NOT NULL DEFAULT 0, 
				  `status` VARCHAR(100) NOT NULL DEFAULT 0, /* not sent, in progress or completed */
				  `last_user_id` INT UNSIGNED NOT NULL DEFAULT 0 /* sending to subscribers in the list ordered by ID. This stores the last sent-to ID */				  
				) DEFAULT CHARSET=utf8mb4;";			
			
			$wpdb->query($sql);
	  }
	  	  
	  // autoresponder campaigns
 	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_ARS."'") != BFTPRO_ARS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_ARS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` varchar(255) NOT NULL DEFAULT '',
				  `list_ids` varchar(255) NOT NULL DEFAULT '',				  
				  `description` text NOT NULL,
				  `sender` varchar(255) NOT NULL DEFAULT '' COMMENT 'sender''s name and address'				  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // custom fields	  
 	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_FIELDS."'") != BFTPRO_FIELDS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_FIELDS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` varchar(100) NOT NULL DEFAULT '',
				  `ftype` varchar(100) NOT NULL DEFAULT '',
				  `fvalues` text NOT NULL,
				  `is_required` tinyint(3) unsigned NOT NULL DEFAULT 0,
				  `label` varchar(255) NOT NULL DEFAULT '',
				  `list_id` int(10) unsigned NOT NULL DEFAULT 0	  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // custom fields data 
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_DATAS."'") != BFTPRO_DATAS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_DATAS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `field_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `data` text NOT NULL,
				  `list_id` int(11) NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
			
			$sql = "ALTER TABLE `" . BFTPRO_DATAS . "` ADD UNIQUE (
				`field_id` ,
				`user_id` ,
				`list_id`
				)";
			$wpdb->query($sql);	
	  }
	  
	  // mailing lists
	   if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_LISTS."'") != BFTPRO_LISTS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_LISTS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` varchar(255) NOT NULL DEFAULT '',
				  `description` text NOT NULL,
				  `date` date NOT NULL DEFAULT '2000-01-01',
				  `do_notify` tinyint(3) unsigned NOT NULL DEFAULT 0,
				  `notify_email` varchar(100) NOT NULL DEFAULT '',
				  `redirect_to` varchar(255) NOT NULL DEFAULT '',
				  `redirect_confirm` varchar(255) NOT NULL DEFAULT '',
				  `unsubscribe_notify` tinyint(3) unsigned NOT NULL DEFAULT 0,
				  `confirm_email_subject` varchar(255) NOT NULL DEFAULT '',
				  `confirm_email_content` text NOT NULL,
				  `unsubscribe_text` text NOT NULL,
				  `require_recaptcha` tinyint(3) unsigned NOT NULL DEFAULT 0,
				  `optin` tinyint(3) unsigned NOT NULL DEFAULT 0				  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // read mails
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_READMAILS."'") != BFTPRO_READMAILS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_READMAILS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `mail_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `date` date NOT NULL DEFAULT '2000-01-01',
				  UNIQUE KEY `mail_id` (`mail_id`,`user_id`)
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // read newsletters
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_READNLS."'") != BFTPRO_READNLS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_READNLS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `newsletter_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `date` date NOT NULL DEFAULT '2000-01-01',
				  UNIQUE KEY `mail_id` (`newsletter_id`,`user_id`)
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // unsubscribings
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_UNSUBS."'") != BFTPRO_UNSUBS) {
	  
			$sql = "CREATE TABLE `" . BFTPRO_UNSUBS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `email` varchar(100) NOT NULL DEFAULT '',
				  `list_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `date` date NOT NULL DEFAULT '2000-01-01',
				  `ar_mails` smallint(5) unsigned NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // cron job logs
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_LOGS."'") != BFTPRO_LOGS) {	  			
			$sql = "CREATE TABLE IF NOT EXISTS `".BFTPRO_LOGS."` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `date` date NOT NULL DEFAULT '2000-01-01',
			  `log` text NOT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // this is email log of all the messages sent in the system 
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_EMAILLOG."'") != BFTPRO_EMAILLOG) {	  
			$sql = "CREATE TABLE `" . BFTPRO_EMAILLOG . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `sender` VARCHAR(255) NOT NULL DEFAULT '',
				  `receiver` VARCHAR(255) NOT NULL DEFAULT '',
				  `subject` VARCHAR(255) NOT NULL DEFAULT '',
				  `date` DATE,
				  `datetime` TIMESTAMP,
				  `status` VARCHAR(255) NOT NULL DEFAULT 'OK'				  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
		// attachments table added in 1.7	  
		if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_ATTACHMENTS."'") != BFTPRO_ATTACHMENTS) {	  			
			$sql = "CREATE TABLE IF NOT EXISTS `".BFTPRO_ATTACHMENTS."` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `mail_id` int(10) unsigned NOT NULL DEFAULT 0,
			  `nl_id` int(10) unsigned NOT NULL DEFAULT 0,
			  `file_name` VARCHAR(255) NOT NULL DEFAULT '',
			  `file_path` VARCHAR(255) NOT NULL DEFAULT '',
			  `url` VARCHAR(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_BOUNCES."'") != BFTPRO_BOUNCES) {	  			
			$sql = "CREATE TABLE IF NOT EXISTS `".BFTPRO_BOUNCES."` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `email` VARCHAR(255) NOT NULL DEFAULT '',
			  `x_id` VARCHAR(100) NOT NULL DEFAULT '',
			  `date` DATE,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_PRESETS."'") != BFTPRO_PRESETS) {	  			
			$sql = "CREATE TABLE IF NOT EXISTS `".BFTPRO_PRESETS."` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `file` VARCHAR(255) NOT NULL DEFAULT '',
			  `name` VARCHAR(255) NOT NULL DEFAULT '',
			  `contents` TEXT,
			  `thumb` VARCHAR(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  	  
	  $fields = array( 
	  		array("name"=>"mailtype", "type"=> "VARCHAR(255) NOT NULL NULL DEFAULT ''"),
	  		array("name"=>"date_last_sent", "type"=> "DATE"),
	  		array("name"=>"is_global", "type"=> "TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"lists_to_go", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"),
			array("name"=>"completed_lists", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"),
			array("name"=>"never_duplicate", "type"=> "TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"editor_id", "type"=> "INT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"preset_id", "type"=>"TINYINT NOT NULL DEFAULT 0"), // non-zero if it uses a preset
			array("name"=>"send_test", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), // receivers of the test email if any
			array("name"=>"is_scheduled", "type"=> "TINYINT NOT NULL DEFAULT 0"),
			array("name"=>"scheduled_for", "type"=> "DATE"),
			array("name"=>"date_limit", "type"=> "DATE"), /* Don't send to users registered after this date. Used for AR messages that also have to be sent to older subscribers */
			array("name"=>"has_date_limit", "type"=> "TINYINT NOT NULL DEFAULT 0"),  /* helper field for the above, to make queries easier */
			array("name"=>"from_mail_id", "type"=> "INT NOT NULL DEFAULT 0"), /* When auto-generated for the above reason, save the mail ID here */
			array("name" => "skip_lists", "type" => "VARCHAR(255) NOT NULL DEFAULT ''"), /* don't send if email is in any of these lists (comma separated) */
			array("name" => "force_wpautop", "type" => "TINYINT NOT NULL DEFAULT 0"), /* force wpautop on this message */
			array("name"=>"reply_to", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), // use different reply-to address
			array("name"=>"description", "type"=> "TEXT"), // description for management purposes
	  	);
	  self::add_db_fields($fields, BFTPRO_NEWSLETTERS);	
	  
	  // add more new fields
	  $fields = array(
	  	  array("name"=>"sender", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
	  	  array("name"=>"require_name", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	  	  array("name"=>"auto_subscribe", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* when subscribed in blog, subscribe here */
	  	  array("name"=>"require_text_captcha", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	  	  array("name"=>"subscribe_to_blog", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* when subscribed here, subscribe to blog */
		  array("name"=>"signup_graphic", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* signup button graphic */
		  array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* who created this mailing list */
		  array("name"=>"auto_subscribe_role", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* required role to auto subscribe */
		  array("name"=>"subscribe_to_blog_role", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* target role when auto subscribe to blog */
		  array("name"=>"auto_subscribe_on_signup", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* auto_subscribe fires on registration instead of first login */ 
		  array("name"=>"unsubscribe_text_clickable", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* clickable text to use instead of URL */
		  array("name"=>"unsubscribe_redirect", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
		  array("name"=>"ninja_integration", "type"=>"TEXT"), /* Integration with Ninja Forms */
		  array("name"=>"caldera_integration", "type"=>"TEXT"), /* Integration with Caldera Forms */  
		  array("name"=>"notify_signup_subject", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
		  array("name"=>"notify_signup_message", "type"=>"TEXT"),
		  array("name"=>"redirect_to_prepend_uid", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"redirect_confirm_prepend_uid", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"no_unsubscribe_link", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* Don't add unsubscribe link */
		  array("name"=>"redirect_duplicate", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
		  array("name"=>"redirect_duplicate_prepend_uid", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"woo_products", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* woocommerce product IDs that subscribe to the list. String like |1|2|5|*/
		  array("name"=>"woo_products_unsub", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* woocommerce product IDs that UNsubscribe from the list. String like |1|2|5|*/
		  array("name"=>"reply_to", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), // use different reply-to address
	  );
	  self::add_db_fields($fields, BFTPRO_LISTS);
	  
	  $fields = array(
	  	  array("name"=>"read_nls", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),	  	  
		  array("name"=>"read_armails", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"clicks", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"auto_subscribed", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"unsubscribed", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"source", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* where did this user come from */
		  array("name"=>"magnet_id", "type"=>"INT NOT NULL DEFAULT 0"), /* lead magnet ID - temp field used by the Intelligence module */
		  array("name"=>"wp_user_id", "type"=>"INT NOT NULL DEFAULT 0"), /* when auto-subscribed by WP registration */
		  array("name"=>"datetime", "type"=>"DATETIME"), /* Time of registration or double opt-in */
		  array("name"=>"form_name", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), /* When registered through a form */
		  array("name"=>"ab_test_id", "type"=> "INT UNSIGNED NOT NULL DEFAULT 0"), /* when user is from AB test */
	  	  array("name"=>"design_id", "type"=> "INT UNSIGNED NOT NULL DEFAULT 0"), /* when user is from AB test, which design exactly was used */
	  	  array("name"=>"hash", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), /* hash that will be used by prepend_uid functions to encrypt the ID */
	  	  array("name"=>"tags", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), /* optional tags */
	  );
	  self::add_db_fields($fields, BFTPRO_USERS);
	  
	  // let's also track sent newsletters
	  $fields = array(
	  	  array("name"=>"newsletter_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
	  	  array("name" => "errors", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /*empty status means success*/	  	
	  );
	  self::add_db_fields($fields, BFTPRO_SENTMAILS);
	  
	    // let's also track sent newsletters
	  $fields = array(	  	 
	  	  array("name" => "unique_id", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
	  	  array("name" => "micro_time", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
	  );
	  self::add_db_fields($fields, BFTPRO_EMAILLOG);
	  
	  $fields = array(
	  	  array("name"=>"field_date_format", "type"=>"VARCHAR(20) NOT NULL DEFAULT ''") // for date fields only	  	
	  );
	  self::add_db_fields($fields, BFTPRO_FIELDS);
	  
	  $fields = array(
	  	  array("name"=>"fileblob", "type"=>"LONGBLOB") // for file uploads
	  );
	  self::add_db_fields($fields, BFTPRO_DATAS);
	  
	  $fields = array(
	  	  array("name"=>"is_deleted", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0") // deleted bounced user	  	
	  );
	  self::add_db_fields($fields, BFTPRO_BOUNCES);
	  
	  $fields = array(
	  	  array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0") // who created this campaign
	  );
	  self::add_db_fields($fields, BFTPRO_ARS);
	  
	  $fields = array(
	  	  array("name"=>"mcat", "type"=>"VARCHAR(20) NOT NULL DEFAULT ''"), // the type of email (ar or nl) where the unsub link was clicked
	  	  array("name"=>"mail_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), // the ID of email where the unsub link was clicked
	  	  array("name"=>"reason", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"), // the ID of email where the unsub link was clicked
	  );
	  self::add_db_fields($fields, BFTPRO_UNSUBS);
	  
	  $fields = array(
	  	  array("name"=>"split_testing", "type"=>"TINYINT NOT NULL DEFAULT 0"), // for date fields only
	  	  array("name"=>"preset_id", "type"=>"TINYINT NOT NULL DEFAULT 0"), // non-zero if it uses a preset
	  	  array("name"=>"is_paused", "type"=>"TINYINT NOT NULL DEFAULT 0"), // paused or active
	  	  array("name"=>"send_test", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), // receivers of the test email if any	  		  	
	  	  array("name" => "force_wpautop", "type" => "TINYINT NOT NULL DEFAULT 0"), /* force wpautop on this message */
	  	  array("name"=>"reply_to", "type"=> "VARCHAR(255) NOT NULL DEFAULT ''"), // use different reply-to address
	  	  array("name"=>"mins_after_reg", "type"=> "INT UNSIGNED NOT NULL DEFAULT 0"), // sent at least X minutes after user registration / email confirmation
	  	  array("name"=>"description", "type"=> "TEXT"), // description for management purposes
	  );
	  self::add_db_fields($fields, BFTPRO_MAILS);
	  
	  $fields = array(
	  	  array("name"=>"is_gozaimasu", "type"=>"TINYINT NOT NULL DEFAULT 0"), // presets in Arigato Gozaimasu
	  );
	  self::add_db_fields($fields, BFTPRO_PRESETS);
	  
	  // versions under 2.2 need to update unsubscribed (but make sure it won't run on fresh install)
	  $version = get_option('bftpro_version');
	  if($version < 2.2) {
	  		$wpdb->query("UPDATE ".BFTPRO_USERS." SET unsubscribed=1
	  			WHERE CONCAT(email, '-', list_id) 
	  			IN (SELECT CONCAT(email, '-', list_id) FROM ".BFTPRO_UNSUBS.")"); 
	  }
	  
	  if($version < 2.25) {
	  		$sql = "ALTER TABLE `" . BFTPRO_USERS . "` ADD UNIQUE (				
				`email` ,
				`list_id`
				)";
			$wpdb->query($sql);	
	  }
	  
	  // add indexes on sentmails and readmails columns
	  if($version < 2.3) {
	  		$sql = "ALTER TABLE `".BFTPRO_READMAILS."` ADD INDEX `imail_id` (`mail_id`), ADD INDEX `user_id` (`user_id`)";
	  		$wpdb->query($sql);
	  		
	  		$sql = "ALTER TABLE `".BFTPRO_READNLS."` ADD INDEX `newsletter_id` (`newsletter_id`), ADD INDEX `user_id` (`user_id`)";
	  		$wpdb->query($sql);
	  		
	  		$sql = "ALTER TABLE `".BFTPRO_SENTMAILS."` ADD INDEX `newsletter_id` (`newsletter_id`), 
	  			ADD INDEX `user_id` (`user_id`), ADD INDEX `imail_id` (`mail_id`)";
	  		$wpdb->query($sql);
	  }
	  
	  // convert subject & message columns on newsletters and mails to utf8mb4
	  if($version < 2.78) {
	  	 $sql = "ALTER TABLE ".BFTPRO_NEWSLETTERS." CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	  	 $wpdb->query($sql);
	  	 
	  	 $sql = "ALTER TABLE ".BFTPRO_MAILS." CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	  	 $wpdb->query($sql);
	  }
	  
	  update_option( 'bftpro_version', "2.8");
	  
	  // if default sender is empty, use wp admin email
	  $sender=get_option( 'bftpro_sender' );
	  if(empty($sender))  {
	  		update_option('bftpro_sender', 'WordPress <'.get_option('admin_email').'>');
	  }
	  
	  // no mailing lists yet? create default
	  require_once(BFTPRO_PATH."/models/list.php");
	  $_list=new BFTProList();
	  $lists=$_list->select();
	  
	  if(!count($lists)) {
	  		$_list->add(array("name"=>__("Default", 'bftpro'), "date"=>date("Y-m-d")));
	  }
	  
	  // default optin email subject & message
	  $optin_subject=get_option('bftpro_optin_subject');
	  if(empty($optin_subject))  {
	  		update_option('bftpro_optin_subject', 'Please confirm your email');
	  }
	  
	  $optin_message=get_option('bftpro_optin_message');
	  if(empty($optin_message)) {
	  		update_option('bftpro_optin_message', 'Please confirm your email by clicking on the link below:<br><br><a href="{{url}}">{{url}}</a>');
	  }
	  
	  // run Gozaimasu installation if any
	  if(file_exists(BFTPRO_PATH.'/gozaimasu/models/gozaimasu.php') and class_exists('Gozaimasu')) {
	  	  Gozaimasu :: install($update);
	  }
	  
	  // schedule wp_cron
	  $cron_schedule = get_option('bftpro_cron_schedule');
	  if(!in_array($cron_schedule, array('hourly', 'daily', 'twicedaily'))) $cron_schedule = 'hourly';
	  if (! wp_next_scheduled ( 'bftpro_wp_cron' )) {
        wp_schedule_event( time(), $cron_schedule, 'bftpro_wp_cron' );
     }
	  
	  update_option('bftpro_admin_notice', __('<b>Thank you for activating Arigato PRO Autoresponder!</b> Please check our <a href="http://www.slideshare.net/pimteam/getting-started-with-broadfast-pro-for-wordpress" target="_blank">Quick getting started guide</a> and the <a href="admin.php?page=bftpro_help">Help</a> page to get started!', 'bftpro'));	  
   } // end install()
      
   // main menu
   static function menu() {
   	$bftpro_caps = current_user_can('manage_options') ? 'manage_options' : 'bftpro_manage';
   	
   	add_menu_page(__('Arigato Rebrand', 'bftpro'), __('Arigato Rebrand', 'bftpro'), $bftpro_caps, "bftpro_options", array(__CLASS__, "options"));
   	add_submenu_page("bftpro_options",__('Settings', 'bftpro'), __('Settings', 'bftpro'), $bftpro_caps, "bftpro_options", array(__CLASS__, "options"));
   	$lists_access = BFTProRoles::check_access('lists_access', true);  		 	
  		if($lists_access) add_submenu_page("bftpro_options",__('Mailing Lists', 'bftpro'), __('Mailing Lists', 'bftpro'), $bftpro_caps, "bftpro_mailing_lists", "bftpro_mailing_lists");   		   $ar_access = BFTProRoles::check_access('ar_access', true); 
  		if($ar_access) add_submenu_page("bftpro_options",__('Autoresponder Campaigns', 'bftpro'), __('Autoresponder Campaigns', 'bftpro'), $bftpro_caps, "bftpro_ar_campaigns", array("BFTProARController", "manage"));
  		$nl_access = BFTProRoles::check_access('nl_access', true);
  		if($nl_access) add_submenu_page("bftpro_options",__('Newsletters', 'bftpro'), __('Newsletters', 'bftpro'), $bftpro_caps, "bftpro_newsletters", array("BFTProNLController", "manage"));
  		$bounce_access = BFTProRoles::check_access('bounce_access', true);
  		if($bounce_access) add_submenu_page("bftpro_options",__('Bounce Handling', 'bftpro'), __('Bounce Handling', 'bftpro'), $bftpro_caps, "bftpro_bounce", array("BFTProBounceController", "options"));
  		$subemail_access = BFTProRoles::check_access('subemail_access', true);
  		if($subemail_access) add_submenu_page("bftpro_options",__('Subscribe by Email', 'bftpro'), __('Subscribe by Email', 'bftpro'), $bftpro_caps, "bftpro_subscribe_email", array("BFTProSubscribeEmailController", "options"));		
  		$squeeze_access = BFTProRoles::check_access('squeeze_access', true);	
  		if($squeeze_access) add_submenu_page("bftpro_options",__('Squeeze page', 'bftpro'), __('Squeeze page', 'bftpro'), $bftpro_caps, "bftpro_squeeze", array("BFTProSqueeze", "main"));				
  		add_submenu_page("bftpro_options",__('Advanced Stats', 'bftpro'), __('Advanced Stats', 'bftpro'), $bftpro_caps, "arigatopro_stats", array("ArigatoPROStats", "main"));		
  		add_submenu_page("bftpro_options",__('Blacklist Management', 'bftpro'), __('Blacklist Management', 'bftpro'), $bftpro_caps, "arigatopro_blacklist", array("ArigatoPROBlacklist", "main"));						
  		add_submenu_page("bftpro_options",__('Help', 'bftpro'), __('Help', 'bftpro'), $bftpro_caps, "bftpro_help", array(__CLASS__, "help"));
  		
  		do_action('bftpro_admin_menu');
  		
  		// hidden subpages (i.e. no sidebar menu link to them)
		add_submenu_page("bftpro_mailing_lists",__('Manage Subscribers', 'bftpro'), __('Manage Subscribers', 'bftpro'), $bftpro_caps, "bftpro_subscribers", "bftpro_subscribers");
		add_submenu_page("bftpro_mailing_lists",__('Manage Custom Fields', 'bftpro'), __('Manage Custom Fields', 'bftpro'), $bftpro_caps, "bftpro_fields", "bftpro_fields");  		  		
		add_submenu_page("bftpro_ar_campaigns",__('Manage Email Messages', 'bftpro'), __('Manage Email Messages', 'bftpro'), $bftpro_caps, "bftpro_ar_mails", array("BFTProARController", "mails"));
		
		add_submenu_page(NULL,__('Email Log', 'bftpro'), __('Email Log', 'bftpro'), $bftpro_caps, "bftpro_mail_log", array("BFTProARController", "log"));
		add_submenu_page(NULL,__('Newsletter Progress Log', 'bftpro'), __('Newsletter Log', 'bftpro'), $bftpro_caps, "bftpro_nl_log", array("BFTProNLController", "log"));
		add_submenu_page(NULL,__('User Log', 'bftpro'), __('User Log', 'bftpro'), $bftpro_caps, "bftpro_user_log", array("BFTProUsers", "log"));
		add_submenu_page(NULL,__('Campaign Reports', 'bftpro'), __('Campaign Reports', 'bftpro'), $bftpro_caps, "bftpro_ar_report", array("BFTProReports", "campaign"));
		add_submenu_page(NULL,__('Newsletter Reports', 'bftpro'), __('Newsletter Reports', 'bftpro'), $bftpro_caps, "bftpro_nl_report", array("BFTProReports", "newsletter"));		
		add_submenu_page(NULL,__('Sent Newsletter Log', 'bftpro'), __('Sent Newsletter Log', 'bftpro'), $bftpro_caps, "arigatopro_sent_nl_log", array("BFTProReports", "sent_nl_log"));
		add_submenu_page(NULL,__('Integrate in Contact Form', 'bftpro'), __('Integrate in Contact Form', 'bftpro'), $bftpro_caps, "bftpro_integrate_contact", array("BFTProIntegrations", "contact_form"));
		add_submenu_page(NULL,__('Integrate in Ninja Form', 'bftpro'), __('Integrate in Ninja Form', 'bftpro'), $bftpro_caps, "bftpro_integrate_ninja", array("BFTProIntegrations", "ninja"));
		add_submenu_page(NULL,__('Integrate in Caldera Form', 'bftpro'), __('Integrate in Caldera Form', 'bftpro'), $bftpro_caps, "bftpro_integrate_caldera", array("BFTProIntegrations", "caldera"));
		add_submenu_page(NULL,__('Download File', 'bftpro'), __('Download File', 'bftpro'), $bftpro_caps, "bftpro_download", array("BFTProData", "download"));
		add_submenu_page(NULL,__('Manage Role Access', 'bftpro'), __('Manage Role Access', 'bftpro'), $bftpro_caps, "bftpro_roles", array("BFTProRoles", "manage"));
		add_submenu_page(NULL,__('Choose Preset', 'bftpro'), __('Choose Preset', 'bftpro'), $bftpro_caps, "bftpro_choose_preset", array("ArigatoPROPresets", "choose"));
	}
	
	// CSS and JS
	static function scripts() {
		// CSS
		wp_register_style( 'bftpro-front-css', BFTPRO_URL . 'css/front.css');
	   wp_enqueue_style( 'bftpro-front-css' );
   
   	// Thickbox CSS
      wp_register_style( 'thickbox-css', includes_url('js/thickbox/thickbox.css'));
	   wp_enqueue_style( 'thickbox-css' );
	   
	   // jQuery and Thickbox
	   wp_enqueue_script('jquery');
	   wp_enqueue_script('thickbox');
	   
	   // BFTPro's own Javascript
		wp_register_script(
				'bftpro-common',
				BFTPRO_URL.'js/common.js',
				false,
				'1.0.3',
				false
		);
		wp_enqueue_script("bftpro-common");
		
		$translation_array = array('email_required' => __('Please provide a valid email address', 'bftpro'),
		'name_required' => __('Please provide name', 'bftpro'),
		'required_field' => __('This field is required', 'bftpro'),
		'ajax_url' => admin_url('admin-ajax.php'), 
		'missed_text_captcha' => __('You need to answer the verification question', 'bftpro'));	
		wp_localize_script( 'bftpro-common', 'bftpro_i18n', $translation_array );	
		
		// jQuery Validator
		wp_enqueue_script(
				'jquery-validator',
				'//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js',
				false,
				'1.0.0',
				false
		);
	}
	
	// Is this used at all?
	static function admin_scripts() {
		wp_register_style( 'bftpro-css', BFTPRO_URL . 'css/main.css');
	   wp_enqueue_style( 'bftpro-css' );
		wp_register_script('jquery.peity', BFTPRO_URL."js/jquery.peity.min.js", false, '3.2.0');
		wp_enqueue_script('jquery.peity');
	}
	
	// initialization
	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'bftpro' );
		
		if(get_option('bftpro_sql_debug_mode') == 1) {
			$wpdb->show_errors();
			if(!defined('DIEONDBERROR')) define( 'DIEONDBERROR', true );
		}
		
		// start session only on front-end and WatuPRO admin pages
		if (!session_id() and !is_admin()) {
				@session_start();
		}
		
		define( 'BFTPRO_USERS', $wpdb->prefix. "bftpro_users" );
		define( 'BFTPRO_LISTS', $wpdb->prefix. "bftpro_lists" );
		define( 'BFTPRO_MAILS', $wpdb->prefix. "bftpro_mails" );
		define( 'BFTPRO_SENTMAILS', $wpdb->prefix. "bftpro_sentmails" );
		define( 'BFTPRO_NEWSLETTERS', $wpdb->prefix. "bftpro_newsletters" );
		define( 'BFTPRO_ARS', $wpdb->prefix. "bftpro_ars" ); // autoresponders
		define( 'BFTPRO_FIELDS', $wpdb->prefix. "bftpro_fields" );
		define( 'BFTPRO_DATAS', $wpdb->prefix. "bftpro_datas" );
		define( 'BFTPRO_READMAILS', $wpdb->prefix. "bftpro_readmails" );
		define( 'BFTPRO_READNLS', $wpdb->prefix. "bftpro_readnls" ); // read newsletters
		define( 'BFTPRO_UNSUBS', $wpdb->prefix. "bftpro_unsubs" );
		define( 'BFTPRO_LOGS', $wpdb->prefix. "bftpro_logs" ); // general daily logs  
		define( 'BFTPRO_ATTACHMENTS', $wpdb->prefix. "bftpro_attachments" );
		define( 'BFTPRO_BOUNCES', $wpdb->prefix. "bftpro_bounces" );
		define( 'BFTPRO_EMAILLOG', $wpdb->prefix. "bftpro_emaillog" );
		define( 'BFTPRO_PRESETS', $wpdb->prefix. "bftpro_presets" );
		
		define( 'BFTPRO_VERSION', get_option('bftpro_version'));
		define( 'BFTPRO_BCC_ALL', get_option('bftpro_bcc'));		
		
		
		add_shortcode( 'BFTPRO', array("BFTPro", "shortcode_signup") );		
		add_shortcode( 'bftpro', array("BFTPro", "shortcode_signup") );
		add_shortcode( 'bftpro-form-start', array("BFTProShortcodes", 'form_start'));
		add_shortcode( 'bftpro-field-static', array("BFTProShortcodes", 'static_field'));
		add_shortcode( 'bftpro-field', array("BFTProShortcodes", 'field'));
		add_shortcode( 'bftpro-form-end', array("BFTProShortcodes", 'form_end'));
		add_shortcode( 'bftpro-recaptcha', array("BFTProShortcodes", 'recaptcha'));
		add_shortcode( 'bftpro-text-captcha', array("BFTProShortcodes", 'text_captcha'));
		add_shortcode( 'bftpro-submit-button', array("BFTProShortcodes", 'submit_button'));
		add_shortcode( 'bftpro-int-chk', array("BFTProShortcodes", 'int_chk'));
		add_shortcode( 'bftpro-other-lists', array("BFTProShortcodes", 'other_lists'));
		add_shortcode( 'bftpro-unsubscribe', array("BFTProShortcodes", 'unsubscribe'));
		add_shortcode( 'bftpro-user-field', array("BFTProShortcodes", 'user_field'));
		add_shortcode( 'bftpro-num-subs', array("BFTProShortcodes", 'num_subscribers'));
		
		// hook tracker
		add_action('template_redirect', array('BFTProReport', 'track'));
		
		// add filters for double opt-in and unsubscribe		
		add_filter('query_vars', array("BFTPro", "query_vars"));
		add_action("template_redirect", array("BFTPro", "template_redirect"));
		
		// add_action('wp_loaded', array(__CLASS__, 'wp_loaded')); NO MORE
		
		// contact form 7 integration
		add_filter( 'wpcf7_form_elements', array('BFTProContactForm7', 'shortcode_filter') );
		add_action( 'wpcf7_before_send_mail', array('BFTProContactForm7', 'signup') );
		add_filter( 'wpcf7_mail_components', array('BFTProContactForm7', 'message_filter'), 10, 3 );
		
		// jetpack contact form integration
		add_action('grunion_pre_message_sent', array('BFTProJetPack', 'signup'));
		
		// Ninja forms integration
		add_action( 'ninja_forms_save_sub', array('BFTProIntegrations', 'ninja_signup') );
		
		// Caldera forms integration
		add_filter( 'caldera_forms_get_form_processors', array('BFTProCaldera', 'get_form_processors'));
		add_action( 'caldera_forms_submit_complete', array('BFTProIntegrations', 'caldera_signup'), 55 );
		
		// squeeze template?
		add_filter( 'template_include', array('BFTProSqueeze', 'squeeze_template'), 99 );
		
		// ajax 
		add_action('wp_ajax_bftpro_ajax', array('BFTPROAjax', 'dispatch'));
		add_action('wp_ajax_nopriv_bftpro_ajax', array('BFTPROAjax', 'dispatch'));
		
		// WooCommerce integration
		add_action('woocommerce_order_status_completed', 'bftpro_woo_subscribe');
		
		// cleanup old logs
		$cleanup_cron_log = get_option('bftpro_cleanup_cron_log');
		if(empty($cleanup_cron_log)) $cleanup_cron_log = 30;
		$cleanup_raw_log = get_option('bftpro_cleanup_raw_log');
		if(empty($cleanup_raw_log)) $cleanup_raw_log = 7;
		if($wpdb->get_var("SHOW TABLES LIKE '".BFTPRO_LOGS."'") == BFTPRO_LOGS) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_LOGS." WHERE date < CURDATE() - INTERVAL %d DAY", $cleanup_cron_log));
			$wpdb->query($wpdb->prepare("DELETE FROM ".BFTPRO_EMAILLOG." WHERE date < CURDATE() - INTERVAL %d DAY", $cleanup_raw_log));
		}		
		
		if(BFTPRO_VERSION < 2.8) self :: install(true);
		
		// lock minutes
		$lock_minutes = get_option('bftpro_lock_file_minutes');
		if(empty($lock_minutes)) $lock_minutes = 60;
		if($lock_minutes < 5) $lock_minutes = 5;
		define('BFTPRO_LOCK_FILE_MINUTES', $lock_minutes);
		update_option('bftpro_lock_file_minutes', $lock_minutes);
		
		define('BFTPRO_SLEEP', floatval(get_option('bftpro_sleep')));
		
		add_action('admin_notices', array(__CLASS__, 'admin_notice'));		
		add_action( 'phpmailer_init', array( __CLASS__, 'fix_return_path' ) );
		
		// show the things on the front-end
		if(get_option('bftpro_no_scripts') != 1) add_action( 'wp_enqueue_scripts', array("BFTPro", "scripts"), 9999);
		add_action( 'admin_enqueue_scripts', array("BFTPro", "scripts"), 9999);
		
		// Gozaimasu module
	   if(file_exists(BFTPRO_PATH.'/gozaimasu/models/gozaimasu.php')) {
	   	include_once(BFTPRO_PATH.'/gozaimasu/gozaimasu.php');
	   	// just installed?
	  	   if(get_option('arigato_gozaimasu')== '') {
	  	   	Gozaimasu :: install();
	  	   	update_option('arigato_gozaimasu', 1);
	  	   }
	  	   else Gozaimasu :: init();
	   } // end Gozaimasu

		// check for updates
		$domain = $_SERVER['SERVER_NAME'];
		$license_key = get_option('arigato_license_key');
		$license_email = get_option('arigato_license_email');	
		
		if(!empty($license_key) and !empty($license_email)) {
			$slug = 'arigatopro';
			// echo 'http://calendarscripts.info/cloud/update-plugin.php?plugin='.$slug.'&m='.$license_email.'&k='.md5($license_key)."&action=info&domain=".$domain;
			include BFTPRO_PATH.'/lib/plugin-update-checker/plugin-update-checker.php';	
			$MyUpdateChecker = PucFactory::buildUpdateChecker(
				'http://calendarscripts.info/cloud/update-plugin.php?plugin='.$slug.'&m='.$license_email.'&k='.md5($license_key)."&action=info&domain=".$domain,
			    BFTPRO_PATH.'/arigato-pro.php',
			    $slug
			);
		}	   
	   
	} // end init
	
	static function admin_notice() {
		$notice = get_option('bftpro_admin_notice');
		if(!empty($notice)) {
			echo "<div class='updated'><p>".stripslashes($notice)."</p></div>";
		}
		// once shown, cleanup
		update_option('bftpro_admin_notice', '');
	}
	
	// parse short codes
	static function shortcode_signup($attr) {
		if(get_option('bftpro_no_scripts') == 1) self :: scripts();		
		
		$list_id = intval(@$attr[0]);
		$remote_placement = empty($attr[1]) ? false : true;
		require_once(BFTPRO_PATH."/models/list.php");
		$_list = new BFTProList();
				
		ob_start();
		$_list->attr_mode = @$attr['mode'];
		$_list->signup_form($list_id, $remote_placement, false, $attr);
		$contents = ob_get_contents();
		ob_end_clean();
		
		return $contents;
	}
	
	// handle BFTPro vars in the request
	static function query_vars($vars)
	{
		$new_vars = array('bftpro_subscribe', 'bftpro_confirm', 'bftpro_rmvmmbr', 'bftpro_cron', 'bftpro_track');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 	
		
	// parse BFTPro vars in the request
	static function template_redirect() {		
		global $wp, $wp_query, $wpdb, $post;
		$redirect = false; 		
			
		// subscribing to a list		
	   if( !empty( $wp->query_vars['bftpro_subscribe'] )) {
	   	$redirect = true;
	   	require_once(BFTPRO_PATH."/models/user.php");
	   	$_user = new BFTProUser();
	   		   	
	   	try {	   		
	   		$message="";
	   		$_user->subscribe($_POST, $message);	
	   		$title = __("Thank you!", 'bftpro');
	   		$template = 'bftpro-message.php';	
	   	}
	   	catch(Exception $e) {
	   		$message = $e->getMessage();
	   		$template = 'bftpro-error.php';	   		
	   	}
	   }
	   
	   if( !empty( $wp->query_vars['bftpro_confirm'] )) {
	   	$redirect = true;
	   	require_once(BFTPRO_PATH."/models/user.php");
	   	$_user = new BFTProUser();
	   	
	   	if($message = $_user->confirm()) {
	   		$title = __("Thank you!", 'bftpro');	   		
	   		$template = 'bftpro-message.php';	
	   	}
	   	else {
	   		$message = __("Sorry! The confirmation link is incorrect or expired.", 'bftpro');
	   		$template = 'bftpro-error.php';	   
	   	}
	   }
	   
	   if( !empty( $wp->query_vars['bftpro_rmvmmbr'] ) and !stristr(@$post->post_content,'[bftpro-unsubscribe]')) {
			// when coming here we have  to see if we need to redirect to a post/page
			// which contains the shortcode	  
			// do this only if we are not already in such post
			$post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} 
				WHERE (post_status='publish' or post_status='private')
				AND post_content LIKE '%[bftpro-unsubscribe]%'");
				
			if(!empty($post_id)) {
				$link = get_permalink($post_id);
				// we must remove the @ in the email					
				$link = add_query_arg($_GET, $link);
				bftpro_redirect($link);
				exit;
			}	 
			
			// get the current list from $_GET - used mostly for bftpro-unsubscribe-single.php
			$selected_list = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".BFTPRO_LISTS." WHERE id=%d", $_GET['list_id']));
			   	
   		// this happens only when we don't find post with shortcode
   		$redirect = true;
   		list($template, $users, $error, $message, $title, $unsubscribe_reasons, $unsubscribe_reasons_other) = BFTProUsers :: unsubscribe_form();	   		   	
	   }
	   
	   if($redirect) {	   	
	   	if(@file_exists(get_stylesheet_directory()."/".$template)) include get_stylesheet_directory()."/".$template;		
			else include(BFTPRO_PATH."/views/templates/".$template);
			exit;
	   }	   
	}	
			
	// manage general options
	static function options() {
		global $wpdb, $wp_roles;
		$roles = $wp_roles->roles;		
		
		$multiuser_access = 'all';
		$multiuser_access = BFTProRoles::check_access('settings_access');
		
		if(!empty($_POST['bftpro_options']) and check_admin_referer('save_options', 'nonce_options')) {
			$recaptcha_version = (empty($_POST['recaptcha_version']) or $_POST['recaptcha_version']==2) ? 2 : intval($_POST['recaptcha_version']);
			$recaptcha_ssl = empty($_POST['recaptcha_ssl']) ? 0 : 1;
			$recaptcha_size = sanitize_text_field($_POST['recaptcha_size']);
			$recaptcha_score = floatval($_POST['recaptcha_score']);
			$sql_debug_mode = empty($_POST['sql_debug_mode']) ? 0 : 1;
			$use_wp_time = empty($_POST['use_wp_time']) ? 0 : 1;
			$no_scripts = empty($_POST['no_scripts']) ? 0 : 1;
			$integrate_woocommerce = empty($_POST['integrate_woocommerce']) ? 0 : 1;
			$cron_schedule = sanitize_text_field($_POST['cron_schedule']);
			$unsubscribe_reasons = wp_kses_post($_POST['unsubscribe_reasons']);
			$unsubscribe_reasons_other = empty($_POST['unsubscribe_reasons_other']) ? 0 : 1;
			$allow_get = empty($_POST['allow_get']) ? 0 : 1;
			
			update_option("bftpro_sender", bftpro_strip_tags($_POST['sender']));
			update_option("bftpro_signature", bftpro_strip_tags($_POST['signature']));
			update_option("bftpro_mails_per_run", intval($_POST['mails_per_run']));
			update_option("bftpro_mails_per_day", intval($_POST['mails_per_day']));
			update_option('bftpro_optin_subject', sanitize_text_field($_POST['optin_subject']));
			update_option('bftpro_optin_message', bftpro_strip_tags($_POST['optin_message']));
			update_option('bftpro_cron_mode', sanitize_text_field($_POST['cron_mode']));
			update_option('bftpro_cron_schedule', sanitize_text_field($_POST['cron_schedule'])); // for WP cron: daily, twicedayly, hourly
			update_option('bftpro_cron_minutes', $_POST['cron_minutes'] >= 3 ? intval($_POST['cron_minutes']) : 3);
			update_option('bftpro_recaptcha_public', sanitize_text_field($_POST['recaptcha_public']));
			update_option('bftpro_recaptcha_private', sanitize_text_field($_POST['recaptcha_private']));
			update_option('bftpro_recaptcha_version', $recaptcha_version);
			update_option('bftpro_recaptcha_lang', sanitize_text_field($_POST['recaptcha_lang']));
			update_option('bftpro_recaptcha_ssl', $recaptcha_ssl);
			update_option('bftpro_recaptcha_size', $recaptcha_size);
			update_option('bftpro_recaptcha_score', $recaptcha_score);
			update_option('bftpro_text_captcha', bftpro_strip_tags($_POST['text_captcha']));
			update_option('bftpro_unsubscribe_action', sanitize_text_field($_POST['unsubscribe_action']));
			update_option('bftpro_unsubscribe_page', sanitize_text_field($_POST['unsubscribe_page']));
			update_option('bftpro_sql_debug_mode', $sql_debug_mode);
			update_option('bftpro_bcc', sanitize_email($_POST['bcc']));
			update_option('bftpro_cleanup_unconfirmed_emails', intval($_POST['cleanup_unconfirmed_emails']));
			update_option('bftpro_use_wp_time', $use_wp_time);
			if($_POST['lock_file_minutes'] < 5) $_POST['lock_file_minutes'] = 5;
			update_option('bftpro_lock_file_minutes', intval($_POST['lock_file_minutes']));
			update_option('bftpro_sleep', floatval($_POST['sleep']));
			update_option('arigato_license_key', sanitize_text_field($_POST['license_key']));
			update_option('arigato_license_email', sanitize_email($_POST['license_email']));
			update_option('bftpro_no_scripts', $no_scripts);
			update_option('bftpro_integrate_woocommerce', $integrate_woocommerce);
			update_option('bftpro_unsubscribe_reasons', $unsubscribe_reasons);    
			update_option('bftpro_unsubscribe_reasons_other', $unsubscribe_reasons_other);
			update_option('bftpro_allow_get', $allow_get);
			
			// roles that can manage the autoresponder
			if(current_user_can('manage_options')) {				
				foreach($roles as $key=>$role) {
					$r=get_role($key);
					
					if(@in_array($key, $_POST['manage_roles'])) {					
	    				if(!$r->has_cap('bftpro_manage')) $r->add_cap('bftpro_manage');
					}
					else $r->remove_cap('bftpro_manage');
				}
			}	
			
			// when cron mode is web, make sure to re-run install so we can have it scheduled
			if($_POST['cron_mode'] == 'web') self :: install(false);
		}
		
		if(!empty($_POST['bftpro_uoptions']) and check_admin_referer('save_uoptions', 'nonce_uoptions')) {
			$cleanup_db = empty($_POST['cleanup_db']) ? 0 : 1;
			update_option('bftpro_cleanup_db', $cleanup_db);
		}
		
		// copy data from lite?
		if(!empty($_POST['bftpro_copy_data'])) {
			// create mailing list
			require_once(BFTPRO_PATH."/models/ar.php");
			$_list = new BFTProList();
			$_ar = new BFTProARModel();
			$sender = get_option('bft_sender');			
			
			$lid = $_list->add(array("name"=>__('Imported from BFT Lite', 'bftpro'), "optin"=>get_option('bft_optin'), "sender" => $sender));
			
			// select subscribers
			$users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bft_users ORDER BY id");
			
			// insert them and update their new ID
			foreach($users as $cnt=>$user) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_USERS." SET email=%s, name=%s, status=%d, date=%s, ip=%s, code=%s, list_id=%d", 
					$user->email, $user->name, $user->status, $user->date, $user->ip, $user->code, $lid));
				$new_id = $wpdb->insert_id;
				$users[$cnt]->new_id = $new_id;	
			}
			
			// insert AR mails and update their ID
			$arid = $_ar->add(array("name"=>__('BFT Lite Autoresponder Sequence', 'bftpro'), "list_ids"=>array($lid), "sender"=>$sender));
					
			$mails = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bft_mails ORDER BY id");
			
			foreach($mails as $mail) {
				$artype = empty($mail->send_on_date) ? 'days' : 'date';				
				
				$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_MAILS." SET 
					sender=%s, subject=%s, message=%s, mailtype='text/html', artype=%s, ar_id=%d, days=%d, send_on_date=%s",
					$sender, $mail->subject, $mail->message, $artype, $arid, $mail->days, $mail->date));
				$new_id = $wpdb->insert_id;
				
				// now transfer sent mails
				$sent_mails = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bft_sentmails 
					WHERE mail_id=%d ORDER BY id", $mail->id));	
					
				foreach($sent_mails as $sent_mail) {
					// find out the corresponding user ID
					$user_id = 0;
					foreach($users as $user) {
						if($user->id == $sent_mail->user_id) {
							$user_id = $user->new_id;
							break;
						}
					}					
					
					$wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_SENTMAILS." SET mail_id=%d, user_id=%d, date=%s",
						$new_id, $user_id, $sent_mail->date));
				}	// end foreach sent mail
			} // end foreach $mail
			
			bftpro_redirect("admin.php?page=bftpro_mailing_lists");
		} // end copy from BFT Lite
		
		$cron_mode = get_option('bftpro_cron_mode');
		$text_captcha = get_option('bftpro_text_captcha');
		
		// check the last cron run
		$last_cron_run = get_option('bftpro_last_cron_run');
		// let's give some time without showing warnings to these who just installed 		
		if(empty($last_cron_run)) {
			$last_cron_run = time() - 12*3600;
			update_option('bftpro_last_cron_run', $last_cron_run);
		}
		if( $cron_mode == 'real' and time() > ($last_cron_run + 24*3600) ) $cron_warning = true; 
		
		// load 3 default questions in case nothing is loaded
		if(empty($text_captcha)) {
			$text_captcha = __('What is the color of the snow? = white', 'bftpro').PHP_EOL.__('Is fire hot or cold? = hot', 'bftpro') 
				.PHP_EOL. __('In which continent is France? = Europe', 'bftpro'); 
		}
		
		$unsubscribe_action = get_option('bftpro_unsubscribe_action');
		$unsubscribe_page = get_option('bftpro_unsubscribe_page');
		$recaptcha_version = get_option('bftpro_recaptcha_version');
		$recaptcha_lang = get_option('bftpro_recaptcha_lang');
		$recaptcha_size = get_option('bftpro_recaptcha_size');
		$recaptcha_score = get_option('bftpro_recaptcha_score');
		if(empty($recaptcha_score)) $recaptcha_score = 0.5;
		
		require(BFTPRO_PATH."/views/options.php");
	}	
	
	static function help() {
		$tab = empty($_GET['tab']) ? 'main' : $_GET['tab'];
		
		switch($tab) {
			case 'error_log':
				BFTProHelp :: error_log();
			break;
			case 'raw_log':
				BFTProHelp :: raw_log();
			break;
			case 'troubleshooting':
				BFTProHelp :: troubleshooting();
			break;
			default:
				BFTProHelp :: help_main();				
			break;
		}		
	}	
	
	static function register_widgets() {
		register_widget('BFTProWidget');
	}
	
	// to allow using as template tag
	static function signup_form($list_id) {
		require_once(BFTPRO_PATH."/models/list.php");
		$_list = new BFTProList();
		$_list->signup_form($list_id);
	}
	
	// logs a log
	static function log($logtext) {
		global $wpdb;
		// today's log available?
		$log = $wpdb->get_row("SELECT * FROM ".BFTPRO_LOGS." WHERE date='".date('Y-m-d')."'");
		if(!empty($log->id)) {
				$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_LOGS." SET log=CONCAT(log, '\n', %s) WHERE id=%d",
					$logtext, $log->id));
		}
		else {
			 $wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_LOGS." SET
			 		date='".date('Y-m-d')."', log=%s", $logtext));
		}
		
		// delete logs older than 3 months
		$wpdb->query("DELETE FROM ".BFTPRO_LOGS." WHERE date < CURDATE() - INTERVAL 3 MONTH");
	}
	
	// conditionally adds DB field, if it's not in the DB already
	static function add_db_fields($fields, $table) {
		global $wpdb;
		
		// check fields
		$table_fields = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
		$table_field_names = array();
		foreach($table_fields as $f) $table_field_names[] = $f->Field;		
		$fields_to_add=array();
		
		foreach($fields as $field) {
			 if(!in_array($field['name'], $table_field_names)) {
			 	  $fields_to_add[] = $field;
			 } 
		}
		
		// now if there are fields to add, run the query
		if(!empty($fields_to_add)) {
			 $sql = "ALTER TABLE `$table` ";
			 
			 foreach($fields_to_add as $cnt => $field) {
			 	 if($cnt > 0) $sql .= ", ";
			 	 $sql .= "ADD $field[name] $field[type]";
			 } 
			 
			 $wpdb->query($sql);
		}
	}
	
	// fix phpmailer return path
	// We should check if this is still used because fix_return_path is now in models/sender.php
	static function fix_return_path($phpmailer) {
		$return_path = get_option('bftpro_bounce_email');
		if(empty($return_path)) $return_path = get_option( 'bftpro_sender' );
		
		// extract only email address from the return path. 
		// Otherwie for some damn reason it gives errors on some hosts		
		if(strstr($return_path, '<')) {
			$parts = explode('<', $return_path);
			$return_path = str_replace('>', '', $parts[1]);
		} 
		
		$phpmailer->Sender = $return_path;
		
		return $phpmailer;
	}
	
	// call on wp_loaded
	// CALLING THIS ON wp_load IS NOW DEPRECATED
	// We use wp_cron instead. However this function is still needed for manual runs via the link
	static function wp_loaded() {
	   // run cron from web?	   
	   if(is_admin()) return false;
	   
		if(!empty($_GET['bftpro_cron'])) {
			$_sender = new BFTProSender();
			$_sender->start_cron();
		}
	}
	
	// call the function on wp_cron
	static function wp_cron() {	   
		if(get_option('bftpro_cron_mode') == 'web') {			
			$_sender = new BFTProSender();
			$_sender->start_cron();
		}
	}
}