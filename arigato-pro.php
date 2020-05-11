<?php
/*
Plugin Name: ArigatoRebrand
Plugin URI: http://calendarscripts.info/bft-pro/
Description: PRO autoresponder / drip marketing plugin for WordPress
Author: Kiboko Labs
Version: 3.1.3.5
Author URI: http://calendarscripts.info/
License: GPLv2 or later
Text Domain: bftpro 
*/

define( 'BFTPRO_PATH', dirname( __FILE__ ) );
define( 'BFTPRO_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'BFTPRO_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
include_once(BFTPRO_PATH."/helpers/linkhelper.php");
include_once(BFTPRO_PATH."/helpers/htmlhelper.php");
include_once(BFTPRO_PATH."/helpers/text-captcha.php");
include_once(BFTPRO_PATH."/models/bftpro.php");
include_once(BFTPRO_PATH."/models/widget.php");
include_once(BFTPRO_PATH."/models/list.php");
include_once(BFTPRO_PATH."/models/report.php");
include_once(BFTPRO_PATH."/models/sender.php");
include_once(BFTPRO_PATH."/models/data.php");
include_once(BFTPRO_PATH."/models/mail.php");
include_once(BFTPRO_PATH."/models/ar.php");	
include_once(BFTPRO_PATH."/controllers/lists.php");
include_once(BFTPRO_PATH."/controllers/campaigns.php");
include_once(BFTPRO_PATH."/controllers/newsletters.php");
include_once(BFTPRO_PATH."/controllers/reports.php");
include_once(BFTPRO_PATH."/controllers/shortcodes.php");
include_once(BFTPRO_PATH."/controllers/help.php");
include_once(BFTPRO_PATH."/controllers/bounce.php");
include_once(BFTPRO_PATH."/controllers/users.php");
include_once(BFTPRO_PATH."/controllers/integrations.php");
include_once(BFTPRO_PATH."/controllers/integrations/contact-form-7.php");
include_once(BFTPRO_PATH."/controllers/integrations/jetpack.php");
include_once(BFTPRO_PATH."/controllers/integrations/caldera.php");
include_once(BFTPRO_PATH."/controllers/subemail.php");
include_once(BFTPRO_PATH."/controllers/tests.php");
include_once(BFTPRO_PATH."/controllers/squeeze.php");
include_once(BFTPRO_PATH."/controllers/roles.php");
include_once(BFTPRO_PATH."/controllers/stats.php");
include_once(BFTPRO_PATH."/controllers/presets.php");
include_once(BFTPRO_PATH."/controllers/blacklist.php");
include_once(BFTPRO_PATH."/controllers/ajax.php");
	

register_activation_hook(__FILE__, array("BFTPro", "install"));
add_action('admin_menu', array("BFTPro", "menu"));
add_action('admin_enqueue_scripts', array("BFTPro", "scripts"));
add_action('admin_enqueue_scripts', array("BFTPro", "admin_scripts"));

// widgets
add_action( 'widgets_init', array("BFTPro", "register_widgets") );

// other actions
add_action('init', array("BFTPro", "init"));
add_action('user_register', array('BFTProList', 'register_subscribe'));
add_action('wp_login', array('BFTProList', 'auto_subscribe'), 10, 2);

// ajax requests
add_action('wp_ajax_arigatopro_campaign_stats', array('ArigatoPROStats', 'campaign_stats'));		
add_action('wp_ajax_arigatopro_nl_by_domain', array('BFTProReports', 'newsletter_by_domain'));
add_action('wp_ajax_arigatopro_armail_by_domain', array('BFTProReports', 'armail_by_domain'));

// let other plugins request email sending too
add_action('bftpro_send_immediate_emails', array('BFTProSender', 'immediate_mails'));

// WP Cron
add_action('bftpro_wp_cron', array('BFTPro', 'wp_cron'));
add_action('wp_loaded', array('BFTPro', 'wp_loaded')); // the old version of calling it as "?bftpro_cron=1"