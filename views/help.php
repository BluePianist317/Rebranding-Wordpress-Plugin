<h2 class="nav-tab-wrapper">
	<a class='nav-tab nav-tab-active'><?php _e('Help / User Manual', 'bftpro')?></a>
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=troubleshooting'><?php _e('Troubleshooting', 'bftpro')?></a>
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=error_log'><?php _e('Cron Job Log', 'bftpro')?></a>	
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=raw_log'><?php _e('Raw Email Log', 'bftpro')?></a>
</h2>

<div class="wrap">
	<div class="postbox wp-admin" style="padding:20px;">
	
		<h3 style="color:red;"><?php printf(__('If you are just starting please check our <a href="%s" target="_blank">quick getting started guide</a> or <a href="%s" target="_blank">our videos</a>!', 'bftpro'), 'https://calendarscripts.info/bft-pro/guide.html', 'http://calendarscripts.info/bft-pro/videos.html')?></h3></p>
		<p><a href="http://calendarscripts.info/bft-pro/howto.html" target="_blank"><?php _e('Visit also the online F.A.Q.', 'bftpro');?></a></p>
		
		<p><?php _e('Translating the plugin? Its textdomain is <b>bftpro</b>. Check the localization section at the bottom of this page for more information.', 'bftpro');?></p>		
		
		<h2><?php _e('Contents of this page', 'bftpro')?></h2>
		
		<ol>
			<li><a href="#settings"><?php _e('General Settings and Settings Regarding Sending of Emails', 'bftpro')?></a></li>
			<li><a href="#lists"><?php _e('Managing Mailing Lists', 'bftpro')?></a></li>
			<li><a href="#campaigns"><?php _e('Autoresponder Marketing Campaigns', 'bftpro')?></a></li>
			<li><a href="#newsletters"><?php _e('Sending Newsletters', 'bftpro')?></a></li>
			<li><a href="#reports"><?php _e('Reports and logs', 'bftpro')?></a></li>
			<li><a href="#unsub"><?php _e('Unsubscribing', 'bftpro')?></a></li>
			<li><a href="#customize"><?php _e('Customize the Success and Error Message Templates', 'bftpro');?></a></li>
			<li><a href="#help"><?php _e('Help, Support, Upgrades', 'bftpro')?></a></li>
			<li><a href="#localization"><?php _e('Localization', 'bftpro')?></a></li>
			<li><a href="#issues"><?php _e('Known issues', 'bftpro')?></a></li>
	  </ol>
	  
	  <a name="settings"></a>
	  <h2><?php _e('General Settings and Settings Regarding Sending of Emails', 'bftpro')?></h2>
	  <p><?php _e('This section explains the <a href="admin.php?page=bftpro_options">options page</a>. Most of the settings are explained on the screen, so this manual will only elaborate on what is left.', 'bftpro')?></p>
	  
	  <p><?php _e("To save any changes to this page, scroll down to the end and click on \"Save All Options\" button. Any of the changes you have done will be saved.", 'bftpro')?></p>
	  
	  <p><?php _e("The <strong>General Settings</strong> and <strong>Global Double Opt-in Email</strong> will affect everything <strong>unless overridden at a mailing list level</strong>. These settings are prepopulated with default values during the plugin installation.", 'bftpro')?></p>
	  
	  <p><?php _e("Your Recaptcha (image verification to prevent spam) settings are global but showing the image verification code is configurable for every mailing list.", 'bftpro')?></p>
	  
	  <h3><?php _e('Email Sending Limits', 'bftpro')?></h3>
	  
	  <p><?php _e('Because sending many emails at once can overload your server, especially on shared hosts, this autoresponder follows the approach to send all emails on batches. So in this section you should set some reasonable number of emails to be sent at once, and total number of emails that can be sent per day. Make sure to ask your hosting company if they impose any specific limits and stay compliant with them.', 'bftpro')?> </p>
	  
	  <p><?php _e('Have in mind that to avoid unexpected results the autoresponder will ignore these limits when sending confirmation ("double opt-in") emails, instant notifications to admin etc. So always set your limits a little below the absolute limits on your server.', 'bftpro')?></p>
	  
	  <a name="cron"></a>
	  <h2><?php _e('Cron Jobs', 'bftpro')?></h2>
	  
	  <p><?php _e('To send the emails in batches, we store information about what emails should be sent in the database. This means that a script should run once in a while to send the emails - preferably once per hour or even once each few minutes, depending on the number of mails you may want to send per day.', 'bftpro')?></p>
	  
	  <p><?php _e('To handle this we recommend to set up a cron job on your server. Here is a <a href="http://calendarscripts.info/cron-jobs-tutorial.html" target="_blank">quick and easy guide</a> how to do it. The exact command you need to set is:', 'bftpro')?></p>
	  
	  <h3><?php _e('This is the most recommended format and you must stick to it if you have problems with duplicate emails:', 'bftpro');?></h3>
	  
	  <p><input type="text" size="100" value="<?php echo BFTPRO_PATH .'/cron-start.php'?>" readonly="readonly" onclick="this.select()"> <br>
	  <?php _e('Depending on your server configuration you MAY need to add "php" in front of the command like this:','bftpro');?>
	     <input type="text" size="80" value="php <?php echo BFTPRO_PATH .'/cron-start.php'?>" readonly="readonly" onclick="this.select()"></p>
	  
	  <p><?php _e('In case your server does not support cron jobs you can use external cron service to hit the following URL:', 'bftpro');?></p>
	  
	  <p><input type="text" size="80" value="<?php echo BFTPRO_URL .'cron-start.php'?>" readonly="readonly" onclick="this.select()"></p>
	  
	  <p><?php _e('The following two formats are <strong>ALTERNATIVE</strong> and should be used only if you have problems using the above commands.', 'bftpro');?></p>
	  
	  <p><input type="text" size="80" value="curl <?php echo home_url("?bftpro_cron=1");?>" readonly="readonly" onclick="this.select()"></p>
	  
	  <p><?php _e('In case the above does not work on your host please try:', 'bftpro')?></p>
	  
	  <p><input type="text" size="80" value="wget <?php echo home_url("?bftpro_cron=1");?>" readonly="readonly" onclick="this.select()"></p>
	  
	  <p><?php printf(__('You can also run the cron job manually by visiting <a href="%s" target="_blank">the link</a> in your browser. If there are no errors you will see just a blank page with text "Running in cron job mode".', 'bftpro'), home_url("?bftpro_cron=1"))?></p>
	  
	  <p><?php _e("If for any reason you can't or don't want to set up a real cron job, you can select the second option - \"I will rely on my blog visitors to initiate the email sending by visiting my blog\". This means every time when someone visits your blog, the autoresponder will start the procedure to send any scheduled emails that are not yet sent. Don't worry, we will not let this happen more often than once a minute, we will never let two procedure instances to run at the same time, and the autoresponder will not send any duplicate emails. But still this method has two disadvantages:", 'bftpro')?></p>
	  
	  <ol>
	  	<li><?php _e('Your blog needs to be visited regularly. If you have just a few visitors per day, some of your autoresponder emails may remain unsent.', 'bftpro')?></li>
	  	<li><?php _e('Although the autoresponder keeps extra queries to the minimum, each visit to your blog will run a few extra database queries because of the email sending procedure checks. It is better to avoid this', 'bftpro')?></li>
	  </ol>
	  
		<h2><?php _e('Sending Emails Through SMTP', 'bftpro')?></h2>  
		
		<p><?php _e('As this autoresponder works with the <strong>wp_mail</strong> function, any plugin that modifies its behavior will affect the autoresponder too. So it is easy to use SMTP - just enable some of the several free SMTP plugins for Wordpress. <a href="https://wordpress.org/plugins/postman-smtp/" target="_blank">Postman SMTP</a> or <a href="http://wordpress.org/plugins/easy-wp-smtp/" target="_blank">Easy WP SMTP</a> seems to be good choices.', 'bftpro')?></p>
		<p><?php _e('If you need to use an SMTP sending service we recommend Mailgun.','bftpro');?></p>	  
	  	  
	  <a name="lists"></a>
	  <h2><?php _e('Managing Mailing Lists', 'bftpro')?></h2>
	  <p><?php _e('Your mailing lists are different databases with subscribers. You can have as many mailing lists as you wish, and every subscriber can sign-up in one or more mailing lists. Then you can send different newsletters or schedule different autoresponder marketing campaigns to the different subscriber lists.', 'bftpro')?></p>
	  
	  <p><?php _e('On the <a href="admin.php?page=bftpro_mailing_lists">mailing lists</a> page you will be able to manage your lists and to obtain the "Subscribe Form" code so your users can sign-up through the blog. You have the option to use a shortcode inside a page or post, or a template tag to insert a signup form in your theme - for example in the sidebar. If your theme is widgets-enabled, you can use the Arigato Autoresponder widget instead of the template tag.', 'bftpro')?></p>
	  
	  <p><?php _e('This page also lets you manage your subscribers, search and filter them, and import/export them to CSV. You can also create unlimited number of <strong>custom fields</strong> for each mailing list to obtain extra contact data from your subscribers, or data like age, gender, nationality and so on.', 'bftpro')?></p>
	  
	  <p>&nbsp;</p>
	  
	  <a name="campaigns"></a>
	  <h2><?php _e('Autoresponder Marketing Campaigns', 'bftpro')?></h2>
	  
	  <p><?php _e('You can create unlimited marketing campaigns and each campaign can be assigned to one or more of your mailing lists. Assigning a campaign to a mailing list means that the emails scheduled in it will be sent to the subscribers in that list according to each mail settings.', 'bftpro')?></p>
	  
	  <p><?php _e('There are 4 types of emails that can be scheduled in every autoresponder campaign.', 'bftpro')?></p>
	  
	  <ol>
	  	<li><?php _e('<strong>Sequential emails</strong> are sent a number of days after user has registered to your mailing list or confirmed their email if double opt-in is required. To send a "welcome" email just enter "0" for "days after registration" and the email will be send immediately on registration/double opt-in.', 'bftpro')?></li>
	  	<li><?php _e('<strong>Fixed date emails</strong> are simply sent on a choosen date. Use them to schedule announcements, holiday greetings etc.', 'bftpro');?></li>
	  	<li><?php _e("<strong>Every X days mails</strong> will be send indefinitely on the predefined interval. Please be careful with these as they may annoy your subscribers, especially if sent too often.", 'bftpro')?></li>
	  	<li><?php _e('Finally, you can create <strong>weekday mails</strong> that can be sent every Monday, Tuesday, Wednesday, etc.', 'bftpro')?></li>
	  </ol>
	  
	   <p>&nbsp;</p>
	  
	   <a name="newsletters"></a>
	  <h2><?php _e('Sending Newsletters', 'bftpro')?></h2>
	  
	  <p><?php _e('In addition to autoresponder messages you can send one-off email blasts (newsletters). They will be sent on batches accordingly to the settings in the options page. The Status column will show you whether your newsletter is currently in process of sending, completed, or not yet sent at all. You can cancel or restart newsletters in process, or edit them on-the-fly.', 'bftpro')?></p>
	  
	  <p>&nbsp;</p>
	  <a name="reports"></a>  
	  <h2><?php _e('Reports and Logs', 'bftpro')?></h2>
	  
	  <p><?php _e('This plugin provides reports for sent and read newsletters and autoresponder messages plus detailed log of what emails are sent or need to be sent. Please note that the "read" stats (i.e. open rate) are not 100% reliable because many email clients disable images by default. So the number of opened emails shown might be less than the real number.', 'bftpro')?></p>
	  
	   <p>&nbsp;</p>
	  <a name="unsub"></a>  
	  <h2><?php _e('Unsubscribing', 'bftpro')?></h2>
	  
	  <p><?php _e('Unsubscribe link is automatically added to all your outgoing emails. When user unsubscribes their status is updated to "unsubscribed" and the user becomes inactive.', 'bftpro')?></p>
	  
	  <p><?php printf(__('The unsubscribe form is automatically generated using a generic template. This may look odd on some themes. If you have such problem please copy the shortcode %s and place it in the contents of a post or page at your choice. The plugin will automatically recognize this and will redirect the unsubscribes to that page instead of showing the default template.', 'bftpro'), '<input type="text" size="20" readonly="readonly" onclick="this.select();" value="[bftpro-unsubscribe]">')?> </p>
	  
	  <p>&nbsp;</p>
	  <a name="customize"></a>  
	  <h2><?php _e('Customize the Success and Error Message Templates', 'bftpro')?></h2>
	  
	  <p><?php printf(__('The templates that display the success and error messages in Arigato PRO can be overridden by placing copies of the following files under your WP theme folder: <b>%s</b> and <b>%s</b>. Any changes you make to these copies will then be reflected in the system and your versions will be used instead of the default ones.', 'bftpro'), 'views/templates/bftpro-message.php', 'views/templates/bftpro-error.php');?></p>
	  
	  <p>&nbsp;</p>
	  
	   <a name="help"></a>
	  <h2><?php _e('Help, Support, Upgrades', 'bftpro')?></h2>
	  
	  <p><?php _e('For any questions, or if you need help our contact details are <a href="http://calendarscripts.info/contact.html" target="_blank">here</a>. Support and upgrades are free up to one year after your purchase. After that you can renew and upgrate at 50% of the current product price which starts another one year of free support and upgrades.', 'bftpro')?></p>
	   <p><?php _e('<strong>Upgrading is easy</strong>. Once you have obtained the newer version simply deactivate and delete the old one, then install the new file. You will not lose any data unless you explicitly select this on the options page.', 'bftpro')?></p>
	   
	   <p>&nbsp;</p>
	  
	   <a name="localization"></a>
	  <h2><?php _e('Localization', 'bftpro')?></h2>
	  
	  <p><?php _e('If you want to translate the plugin in your language, find the <strong>arigato-pro.pot</strong> file in the root folder, create your locale and place it in <strong>languages/</strong> folder. Note that the plugin textdomain is "bftpro" so your files would be called something like bftpro-fr_FR.po and bftpro-fr_FR.mo. See <a href="http://blog.calendarscripts.info/how-to-translate-a-wordpress-plugin/" target="_blank">this guide</a> for more information.', 'bftpro')?></p>
	  
	     <p>&nbsp;</p>
	  
	  <a name="issues"></a>
	  <h2><?php _e('Known issues', 'bftpro')?></h2>
	  
	  <p><?php _e('When you select "Both" for "Email type" in newlsetters or autoresponder emails, attachments cannot be sent. This is due to ineficciency of wp_mail function which does not recognize custom MIME boundaries. This issue is currently unresolved and we cannot give any estimate of when and if ever it will be fixed. So please if you want to send emails with attachments select either "HTML" or "Text" for email type.', 'bftpro')?></p>
	</div>
</div>	