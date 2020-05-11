<h2 class="nav-tab-wrapper">
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=main'><?php _e('Help / User Manual', 'bftpro')?></a>
	<a class='nav-tab nav-tab-active'><?php _e('Troubleshooting', 'bftpro')?></a>
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=error_log'><?php _e('Cron Job Log', 'bftpro')?></a>	
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=raw_log'><?php _e('Raw Email Log', 'bftpro')?></a>
</h2>

<div class="wrap">
	<div class="postbox wp-admin" style="padding:20px;">
		<p><?php _e('This page answers the most common problems and their solutions.', 'bftpro')?></p>
		
		<h2><?php _e('It does not send emails', 'bftpro')?></h2>
		
		<p><?php _e('First figure out which emails are not sent - all of them or only newsletters and subsequent auto responder emails? If welcome emails, confirmations and notifications are sent, but newsletters and subsequent auto responder emails are not sent, this is sure sign that your cron job is not working. Please check the section "Cron job does not work" later below.', 'bftpro')?></p>
		
		<p><b><?php _e('If all emails are not sent:', 'bftpro')?></b></p>
		
		<ol>
			<li><?php _e('Have a quick look over the "Cron Job Log" and "Raw Email Log" tabs on this page. They may give you some hints. For example the reason for not sent emails might be invalid sender or receiver email addresses.', 'bftpro')?></li>
			<li><?php printf(__('Please install <a href="%s" target="_blank">WP Mail SMTP</a>, set the sender name and email and select "PHP Mail" as sending mode. Try to send a test mail to yourself. If this email is sent properly and the autoresponder emails still do not go, please contact us to have a look. If this email is not sent or goes to junk box, the problem is in your installation. You have the following options:', 'bftpro'), 'http://wordpress.org/plugins/wp-mail-smtp/');?><br>
			<?php _e('- Contact your hosting support for help', 'bftpro')?><br>
			<?php _e('- Switch to SMTP email sending and enter SMTP details. If emails start going out, you are all set - the autoresponder will also work. If even SMTP test email does not get sent you absolutely should talk to your hosting support. Please do not contact us in this case until your mailing issue is resolved.', 'bftpro')?></li>
		</ol>    
		
		<h2><?php _e('Cron job does not work', 'bftpro')?></h2>
		
		<p><?php _e('If you are getting the "welcome" emails and/or various automated notifications but you are not getting newsletter emails or subsequent auto-responder emails your cron job is most probably not working propely. Please go through these steps.', 'bftpro')?></p>
		
		<ol>
			<li><?php printf(__('Check if the cron job URL works correctly by visiting <a href="%s" target="_blank">Cron Job Check</a>. If you see the text "Running in cron job mode" then everything is fine. If you see "Starting too soon - we have a limit of 1 minute" just wait for a couple of minutes and try again. If you see something else, please contact us.', 'bftpro'), home_url("?bftpro_cron=1"));?></li>
			<li><?php printf(__('Ensure that you have set up your cron job as shown in the <a href="%s" target="_blank">Help page</a>. There should be nothing added to the cron job command - just copy it as shown and place it in your hosting control panel. Do not add "php -q" or any similar commands to it. If you do not know how to do this feel free to contact us for help.', 'bftpro'), 'admin.php?page=bftpro_help#settings')?></li>
			<li><?php _e('If you have set your cron job properly but it still does not work please try the second command shown on the Help page.', 'bftpro')?></li>		
			<li><?php _e('If it still does not work it is best to contact your hosting support. You can contact us to have a look if everything is set up correctly. However if there is a problem with cron jobs on your server this can only be solved by your hosting support.', 'bftpro')?></li>	
		</ol>
		
		<p>&nbsp;</p>
		<p><strong><?php printf(__('If you need to contact us feel free to email <a href="mailto:%s">%s</a>', 'bftpro'), 'info@calendarscripts.info', 'info@calendarscripts.info')?></strong></p>
	</div>
</div>	