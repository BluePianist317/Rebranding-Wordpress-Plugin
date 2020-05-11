<?php 
// Arigato CRON Job starter 
// This file is used on hosts that don't allow GET or wget, but have to start command with php-q
//require_once("../arigato-pro.php");
$wp_load_dir = dirname( __FILE__ );
$parts = explode('/', $wp_load_dir);
$parts = array_slice($parts, 0, count($parts) - 3);
$wp_load_dir = implode('/' , $parts);

include_once($wp_load_dir.'/wp-load.php');
include_once('arigato-pro.php');
$_sender = new BFTProSender();
$_sender->start_cron();
//$url = home_url("?bftpro_cron=1");
//file_get_contents($url);