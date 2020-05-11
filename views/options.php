<style type="text/css">
textarea {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;

    width: 100%;
}
</style>

<h1><?php _e("Arigato Autoresponder options", 'bftpro')?></h1>


<?php if(!empty($cron_warning)):?>
	<div class="bftpro-warning">
		<p><?php printf(__('WARNING: You have selected "I will set up a cron job on the server to send my emails" but we have not detected any hits to your cron job URL in the last 24 hours. The program cannot function properly If your cron job does not run at least once per day. Please check the <a href="%s">Help page</a> for more information and contact us if you need assistance with setting up your cron job.', 'bftpro'), 'admin.php?page=bftpro_help')?></p>	
	</div>
<?php endif;?>

<h2 class="nav-tab-wrapper">
		<a class='nav-tab nav-tab-active' href='#' onclick="bftproChangeTab(this, 'generalsettings');return false;"><?php _e('General Settings', 'bftpro')?></a>		
		<a class='nav-tab' href='#' onclick="bftproChangeTab(this, 'unsubsettings');return false;" id="themelnk"><?php _e('Unsubscribe Settings', 'bftpro')?></a>
		<a class='nav-tab' href='#' onclick="bftproChangeTab(this, 'sendingsettings');return false;"><?php _e('Email Sending', 'bftpro')?></a>			
		<a class='nav-tab' href='#' onclick="bftproChangeTab(this, 'captchasettings');return false;"><?php _e('Captcha Settings', 'bftpro')?></a>
		<a class='nav-tab' href='#' onclick="bftproChangeTab(this, 'optinsettings');return false;"><?php _e('Opt-in Email', 'bftpro')?></a>
		<a class='nav-tab' href='#' onclick="bftproChangeTab(this, 'othersettings');return false;"><?php _e('Other Settings', 'bftpro')?></a>
	</h2>

<form method="post" class="bftpro" onsubmit="return BFTPROvalidateOptions(this);">
<div class="postbox wp-admin bftpro-tab-div" id="generalsettings" style="padding:5px;">
	<h3><?php _e('General Settings', 'bftpro')?></h3>

	<p><label><?php _e("Default sender:", 'bftpro')?></label> <input type="text" name="sender" value="<?php echo get_option('bftpro_sender')?>" size="30"></p>
	<p class="help"><?php _e("This will be used to pre-populate your sender email address in newsletters and autoresponder campaigns.<br> You can always change it individually in each form. Please note that this field should contain valid email address or name and email in the format <b>Name &ltemail@domain.com&gt;</b>", 'bftpro')?></p>	
	
	
	<p><label><?php _e("Email signature:", 'bftpro')?></label> <?php echo wp_editor(stripslashes(get_option("bftpro_signature")), "signature", array("textarea_rows"=>4));?></p>
	<div class="help"><?php _e("This field is optional. If you enter some text here, it will be added at the end of all your outgoing emails, right before the unsubscribe link", 'bftpro')?></div>
	
	<p><?php _e('BCC all outgoing emails to this address:', 'bftpro');?> <input type="text" name="bcc" value="<?php echo get_option('bftpro_bcc');?>" size="30"> 
		<?php _e('Leave blank for no BCC', 'bftpro');?></p>	
	
	<p><input type="checkbox" name="sql_debug_mode" value="1" <?php if(get_option('bftpro_sql_debug_mode') == 1) echo 'checked'?>> <?php _e('Enable debug mode to see SQL errors', 'bftpro')?></p>
	
	<p><b><?php _e('Timing settings', 'bftpro');?></b></p>
	<p><input type="checkbox" name="use_wp_time" value="1" <?php if(get_option('bftpro_use_wp_time') == 1) echo 'checked'?>> <?php printf(__('When specifying that emails should be sent after selected time of the day, use the timezone from your <a href="%s" target="_blank">WP Settings</a> page instead of the server time.', 'bftpro'),'options-general.php');?><br>
	<?php _e('When changing this you may need to change the hour settings of emails saved before the change.', 'bftpro');?></p>
	
	<?php if(class_exists('Woocommerce')):?>
	<p><b><?php _e('WooCommerce Integration', 'bftpro');?></b></p>
	<p><input type="checkbox" name="integrate_woocommerce" value="1" <?php if(get_option('bftpro_integrate_woocommerce') == 1) echo 'checked'?>> <?php _e('Enable WooCommerce integration so you can select products purchases that will automatically subscribe users to mailing lists.', 'bftpro');?></p>
	<?php endif;?>
	
	<p><b><?php _e('Site performance', 'bftpro');?></b></p>
	<p><input type="checkbox" name="no_scripts" value="1" <?php if(get_option('bftpro_no_scripts') == 1) echo 'checked'?>> <?php _e('For better performance load Arigato scripts and CSS only on pages that contain signup form shortcodes.', 'bftpro');?></p>
	
	<p><b><?php _e('Passing data from URL', 'bftpro');?></b></p>
	<p><input type="checkbox" name="allow_get" value="1" <?php if(get_option('bftpro_allow_get') == 1) echo 'checked'?>> <?php printf(__('Allow passing data to the subscribe forms through the URL. <a href="%s" target="_blank">Learn how to use this</a>', 'bftpro'), 'https://blog.calendarscripts.info/passing-values-through-url-parameters-in-arigato-pro/');?></p>
	
	<p><b><?php _e('Automated Dashboard Updates', 'bftpro') ?></b></p>
	
		<p><?php _e('If you want to update the plugin automatically through the dashboard you need to enter the email used to purchase it, and the license key. These fields are not required to use the plugin: they are needed only for automated dashboard updates. You can still use the plugin and update it manually even without a license key.', 'bftpro');?></p>
		<p><label><?php _e('Order email address:','bftpro')?></label> <input type="text" name="license_email" value="<?php echo get_option('arigato_license_email');?>"></p>
		<p><label><?php _e('License key:','bftpro')?></label> <input type="text" name="license_key" value="<?php echo get_option('arigato_license_key');?>"></p>	


	<h3 class="hndle"><span><?php _e('Roles', 'bftpro') ?></span></h3>
	<div class="inside">		
	<h4><?php _e('Roles that can manage the autoresponder', 'bftpro')?></h4>
	
	<p><?php _e('By default only Administrator and Super admin can manage the autoresponder. You can enable other roles here.', 'bftpro')?></p>
	<p><?php foreach($roles as $key=>$r):
					if($key=='administrator') continue;
					$role = get_role($key);?>
					<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('bftpro_manage')) echo 'checked';?>> <?php echo $role->name?> &nbsp;
				<?php endforeach;?></p>	
	<p><?php _e('Only administrator or superadmin can change this!', 'bftpro')?></p>
	<?php if(current_user_can('administrator')):?>
		<p><a href="admin.php?page=bftpro_roles" target="_blank"><?php _e('Fine-tune these settings', 'bftpro');?></a></p>
	<?php endif;?>	
	</div>
	<p><input type="submit" class="button button-primary"  value="<?php _e('Save Options', 'bftpro')?>"></p>
</div>

<div class="postbox wp-admin bftpro-tab-div" id="unsubsettings" style="padding:5px;display:none;">
	<h3><?php _e('Unsubscribe Settings', 'bftpro')?></h3>
	
	<p><b><?php _e('When user unsubscribes:', 'bftpro')?></b><br>
	<input type="radio" name="unsubscribe_action" value="deactivate" <?php if(!empty($unsubscribe_action) and $unsubscribe_action == 'deactivate') echo 'checked'?>> <?php _e('Deactivate them but keep them in the mailing list.', 'bftpro')?><br>
	<input type="radio" name="unsubscribe_action" value="delete" <?php if(empty($unsubscribe_action) or $unsubscribe_action == 'delete') echo 'checked'?>> <?php _e('Delete the user.', 'bftpro')?><br></p>
	
	<p><b><?php _e('Unsubscribe page behavior:', 'bftpro')?></b><br>
	<input type="radio" name="unsubscribe_page" value="multiple" <?php if(empty($unsubscribe_page) or $unsubscribe_page == 'multiple') echo 'checked'?>> <?php _e('User sees all lists they are subscribed to and selects which to unsubscribe form.', 'bftpro')?><br>
	<input type="radio" name="unsubscribe_page" value="single" <?php if(!empty($unsubscribe_page) and $unsubscribe_page == 'single') echo 'checked'?>> <?php _e('User confirms unsubscribing from the current mailing list.', 'bftpro')?><br></p>
	
	<p><b><?php _e('Optional unsubscribe poll:', 'bftpro')?></b><br>
		<?php _e('If you want to ask your subscribers for the unsubscribe reasons you can fill possible options below, one per line. They will be turned into radio-buttons on the unsubscribe form.', 'bftpro');?></p>
		
	<p><textarea rows="5" cols="60" name="unsubscribe_reasons"><?php echo stripslashes(get_option('bftpro_unsubscribe_reasons'));?></textarea>
	<input type="checkbox" name="unsubscribe_reasons_other" value="1" <?php if(get_option('bftpro_unsubscribe_reasons_other') == 1) echo "checked";?>> <?php _e('Add "Other" option with a field for free text entry', 'bftpro');?></p>	
	
	<p><b><?php _e('Automatically cleanup unconfirmed subscribers:', 'bftpro')?></b><br>
	
	<p><?php printf(__('Cleanup unconfirmed subscribers older than %s days. If you leave it empty or enter 0 there will be no automated cleanup.', 'bftpro'), '<input type="text" name="cleanup_unconfirmed_emails" value="'.get_option('bftpro_cleanup_unconfirmed_emails').'" size="4">');?><br>
	<?php _e('Note: this will delete subscribers that never confirmed their emails or subscribers deactivated by admin. It will not delete those who unsubscribed. To delete unsubscribed you must select the proper option in the section "When user unsubscribes" above.', 'bftpro');?></p>
	
	<p><input type="submit" class="button button-primary" value="<?php _e('Save Options', 'bftpro')?>"></p>
</div>	

<div class="postbox wp-admin bftpro-tab-div" id="sendingsettings" style="padding:5px;display:none;">
	<h3><?php _e('Settings For The Email Sending Process', 'bftpro')?></h3>	
	
	<p><input type="radio" name="cron_mode" value="real" <?php if(empty($cron_mode) or $cron_mode=='real') echo "checked"?> onclick="BFTProChangeCronMode(this, 'real');"> <?php _e('I will set up a', 'bftpro')?> <a href="#" onclick="jQuery('#realCronInfo').toggle();return false;"><?php _e('cron job', 'bftpro')?></a> <?php _e('on the server to send my emails.', 'bftpro')?> <strong><?php _e('(Recommended choice)', 'bftpro')?></strong></p>
	
	<div class="help" style='display:<?php echo (!empty($cron_mode) and $cron_mode == 'real') ? 'block' : 'none'?>;' id='realCronInfo'><?php _e("Cron jobs are scheduled tasks that run on your server. This is the preferred setting but you will need to set up a cron job through your web host control panel. For full details check <a href='admin.php?page=bftpro_help#cron'>Cron Jobs section</a> in the Help page.", 'bftpro')?></div>
	
	<p><input type="radio" name="cron_mode" value="web" <?php if(!empty($cron_mode) and $cron_mode=='web') echo "checked"?> onclick="BFTProChangeCronMode(this, 'web');"> <?php _e('I will rely on my site visitors to initiate the email sending by visiting the site', 'bftpro')?></p>
	
	<div class="help" style='display:<?php echo (!empty($cron_mode) and $cron_mode == 'web') ? 'block' : 'none'?>;' id='webCronInfo'><?php printf(__("If for any reason you can't create real cron jobs, use this setting. It relies on <a href='%s' target='_blank'>WP Cron</a>.", 'bftpro'), 'https://kinsta.com/knowledgebase/wordpress-cron-job/', 'admin.php?page=bftpro_help#cron')?></div>
	
	<div id="webCronOption" style='display:<?php echo (empty($cron_mode) or	 $cron_mode=='real')?'none':'block'?>;'>
		<p><?php _e('Running sequence:','bftpro');?> <select name="cron_schedule">
					<option value="hourly" <?php if(empty($cron_schedule) or $cron_schedule == 'hourly') echo 'selected'?>><?php _e('Hourly', 'bftpro');?></option>
					<option value="daily" <?php if(!empty($cron_schedule) and $cron_schedule == 'daily') echo 'selected'?>><?php _e('Daily', 'bftpro');?></option>
					<option value="daily" <?php if(!empty($cron_schedule) and $cron_schedule == 'twicedaily') echo 'selected'?>><?php _e('Twice aily', 'bftpro');?></option>
				</select>	</p>
		<p><?php _e('In no case allow running the job more of then than each', 'bftpro')?> <input type="text" name="cron_minutes" value="<?php echo get_option('bftpro_cron_minutes');?>" size="4"> <?php _e('minutes.', 'bftpro')?></p>
		<p><?php _e('This setting is for backward compatibility with older versions. Most likely you will not need to change it.', 'bftpro');?></p>		
	</div>	
	
	<p><label><?php _e("Send up to:", 'bftpro')?></label> <input type="text" name="mails_per_run" value="<?php echo get_option('bftpro_mails_per_run')?>"> <?php _e("emails at once", 'bftpro');?></p>
	<div class="help"><?php _e("This setting will be used to define how many emails can be sent at once. This is useful to avoid site crashes, as many hosts will limit your PHP execution time to 30 seconds. Usually you should aim for about 100, up to few hundreds, emails at once on shared hosts.", 'bftpro')?></div>
	
	<p><label><?php _e("Send up to:", 'bftpro')?></label> <input type="text" name="mails_per_day" value="<?php echo get_option('bftpro_mails_per_day')?>"> <?php _e("emails per day", 'bftpro');?></p>
	<div class="help"><p><?php _e("Many hosting companies limit the number of emails you can send per day. If your company imposes such limit, enter it here and the plugin will make sure no more than this number of emails is sent by it. Note that it cannot take into account any emails that do not originate from this autoresponder plugin.", 'bftpro')?></p>
	<p><?php _e('Have in mind also that we will always try to send the immediate emails like welcome messages and double opt-in emails regardless the limits given above.', 'bftpro')?></p></div>
	
	<p><?php _e('Artificial delay between emails:', 'bftpro');?> <input type="text" name="sleep" value="<?php echo get_option('bftpro_sleep');?>" size="4"> <?php _e('In seconds, decimals accepted. Example: 0.1', 'bftpro');?></p>
	<div class="help">
		<p><?php _e('If you are getting bounced emails with warnings that you are sending too fast, your server may be in danger of being blacklisted. In such case try to define some artifical delay. Usually values between 0.1 and 1 second work best.', 'bftpro');?></p>
	</div>
	
	<p><?php _e('Lock file protection valid for:', 'bftpro');?> <input type="text" size="4" name="lock_file_minutes" value="<?php echo get_option('bftpro_lock_file_minutes');?>"> <?php _e('minutes (min 5)', 'bftpro');?>
	<div class="help">
		<?php _e("This is the period that the lock file prevent a long running cron job from being overlapped. In most cases you don't need to change it. Reduce it under 60 minutes only if advised or you are getting many lock file errors in the cron job log.", 'bftpro');?>
	</div></p>
	
	<div class="help"><p style="color:red;"><?php printf(__('Problems sending emails? Please check the <a href="%s" target="_blank">error log</a> and <a href="%s" target="_blank">this page</a>.', 'bftpro'), admin_url('admin.php?page=bftpro_help&tab=error_log'), "http://calendarscripts.info/bft-pro/howto.html")?></p></div>
	
	<p><input type="submit" class="button button-primary" value="<?php _e('Save Options', 'bftpro')?>"></p>
</div>

<div class="postbox wp-admin bftpro-tab-div" id="captchasettings" style="padding:5px;display:none;">
	<h3><?php _e('reCaptcha Settings', 'bftpro')?></h3>
	
	<p><?php _e("You can optionally enable <a href='http://www.google.com/recaptcha' target='_blank'>ReCaptcha</a> to prevent spam bots to register to your mailing lists. You will need a ReCaptcha API key. You can <a href='http://www.google.com/recaptcha/whyrecaptcha' target='_blank'>get one here</a>. It's free.", 'bftpro')?></p>
	
	<p><label><?php _e('Site key:', 'bftpro')?></label> <input type="text" name="recaptcha_public" value="<?php echo get_option('bftpro_recaptcha_public');?>" size='60'></p>	
	
	<p><label><?php _e('Secret key:', 'bftpro')?></label> <input type="text" name="recaptcha_private" value="<?php echo get_option('bftpro_recaptcha_private');?>" size='60'></p>
	
	<p><label><?php _e('reCaptcha Version:', 'bftpro');?></label> <select name="recaptcha_version" onchange="bftproChangeReCaptchaVersion(this.value);">
		<option value="1" <?php if($recaptcha_version == 1) echo 'selected'?>><?php _e('1 (Old reCaptcha)', 'bftpro');?></option>
		<option value="2" <?php if(empty($recaptcha_version) or $recaptcha_version == 2) echo 'selected'?>><?php _e('2 (no Captcha reCaptcha)', 'bftpro');?></option>
		<option value="3" <?php if(!empty($recaptcha_version) and $recaptcha_version == 3) echo 'selected'?>><?php _e('3 (invisible reCaptcha)', 'bftpro');?></option>
	</select></p>	
	
	<p id="reCaptchaSSL" style='display:<?php echo ($recaptcha_version == 1) ? 'block':'none';?>'><input type="checkbox" name="recaptcha_ssl" value="1" <?php if(get_option('bftpro_recaptcha_ssl') == 1) echo 'checked'?>> <?php _e('Load recaptcha libraries through SSL', 'bftpro');?></p>
	
	<div id="reCaptchanoCaptcha" style='display:<?php echo (empty($recaptcha_version) or $recaptcha_version == 2) ? 'block' : 'none'; ?>'>
		<p><?php _e('Size:', 'bftpro');?> <select name="recaptcha_size">
			<option value="normal"><?php _e('Normal', 'bftpro');?></option>
			<option value="compact" <?php if(!empty($recaptcha_size) and $recaptcha_size == 'compact') echo 'selected'?>><?php _e('Compact', 'bftpro');?></option>
		</select></p>
		<p><?php _e('Language code:', 'bftpro');?> <input type="text" name="recaptcha_lang" value="<?php echo $recaptcha_lang ? $recaptcha_lang : 'en'?>" size="4"> <a href="https://developers.google.com/recaptcha/docs/language" target="_blank"><?php _e('See language codes', 'watupro');?></a></p>		
	</div>
	
	<div id="reCaptcha3" style='display:<?php echo ($recaptcha_version == 3) ? 'block' : 'none'; ?>'>
		<p><?php _e('Valid response threshold score:', 'bftpro');?> <select name="recaptcha_score">
			<?php for($i = 1; $i <= 10; $i ++):?>
				<option value="<?php echo $i/10?>" <?php if(!empty($recaptcha_score) and $recaptcha_score == $i/10) echo 'selected'?>><?php echo $i/10?></option>
			<?php endfor;?>
		</select></p>
		<p><?php _e('For more information check reCaptcha v3 docs. If you are unsure just leave the default of 0.5', 'bftpro');?></p>
	</div>
	
	<p class="help"><?php _e('Once you enter both your public and private ReCaptcha keys, a new checkbox will become visible for each mailing list to let you enable recaptcha when users register to it.', 'bftpro');?></p>
	
		<p><?php _e('You need to create keys for your domains and for the different reCaptcha versions. If you want to test the captcha on localhost you have to create a key for "localhost".', 'bftpro');?></p>

	<h3><?php _e('Question based captcha', 'bftpro')?></h3>
	
	<p><?php _e("In addition to ReCaptcha or instead of it, you can use a simple text-based captcha. We have loaded 3 basic questions but you can edit them and load your own. Make sure to enter only one question per line and use = to separate question from answer.", 'bftpro')?></p>
	
	<p><textarea name="text_captcha" rows="10" cols="70"><?php echo stripslashes($text_captcha);?></textarea></p>
	<div class="help"><?php _e('This question-based captcha can be enabled individually by selecting a checkbox in the mailing list settings form. If you do not check the checkbox, the captcha question will not be generated.', 'bftpro');?></div>
	<p><input type="submit" class="button button-primary" value="<?php _e('Save Options', 'bftpro')?>"></p>
</div>

<div class="postbox wp-admin bftpro-tab-div" id="optinsettings" style="padding:5px;display:none;">	
	<h3><?php _e('Global Double Opt-In Email', 'bftpro')?></h3>
	
	<p><label><?php _e('Email subject:', 'bftpro')?></label> <input type="text" name="optin_subject" value="<?php echo stripslashes(get_option('bftpro_optin_subject'));?>" size="60"></p>
	<p><label><?php _e('Email message:', 'bftpro')?></label> <?php echo wp_editor(stripslashes(get_option('bftpro_optin_message')), 'optin_message')?></p>
	
	<div class="help"><?php printf(__("This email will be sent to the subscribers to those email lists that have \"Double opt-in\" requirement. You can override the subject and message for any list. <br> Feel free to use the masks {{list-name}}, {{url}} (<a href='%s' target='_blank'>learn more</a>), {{name}}, and {{firstname}} inside the email subject and/or contents.", 'bftpro'), 'http://blog.calendarscripts.info/broadfast-for-wordpress-the-url-variable/')?><br></div>
	<p><input type="submit" class="button button-primary" value="<?php _e('Save Options', 'bftpro')?>"></p>
</div>	
<input type="hidden" name="bftpro_options" value="1">	
<?php echo wp_nonce_field('save_options', 'nonce_options');?>
</form>

<div class="postbox wp-admin bftpro-tab-div" id="othersettings" style="padding:5px;display:none;">	

	<?php do_action('bftpro-options-page');?>
	
	<?php if(get_option('bft_db_version')):?>
		<form method="post">
		<div class="postbox wp-admin" style="padding:5px;">	
			<h3><?php _e('Copy Data from Arigato Lite', 'bftpro')?></h3>
			
			<p><?php _e('By clicking the button below you can copy all your data from Arigato Lite plugin. A new mailing list and new autoresponder will be created, and your current sequence will be kept as is and can continue from where you left it.', 'bftpro')?></p>
			
			<p><input class="button button-primary" type="submit" value="<?php _e('Copy Data', 'bftpro')?>" name="bftpro_copy_data"></p>
			
			<p><b><?php _e('Important! Once you are satisfied with the copied data make sure that Arigato Lite is deactivated. Otherwise you may have emails sent twice.', 'bftpro')?></b></p>
		</div>
		<?php echo wp_nonce_field('save_uoptions', 'nonce_uoptions');?>
		</form>
	<?php endif;?>
	
	<form method="post">
	<div class="postbox wp-admin" style="padding:5px;">	
		<h3><?php _e('Uninstall Options', 'bftpro')?></h3>
		
		<p><input type="checkbox" name="cleanup_db" value="1" <?php if(get_option('bftpro_cleanup_db')==1) echo "checked"?>> <?php _e('Delete all data and attachments when uninstalling the plugin. Think twice - if you check this you will lose ALL YOUR DATA when deleting the plugin. Do not check this if you are just upgrading to a newer version.', 'bftpro')?></p>
		
		<p><input type="submit" class="button button-primary" value="<?php _e('Save Uninstall Options', 'bftpro')?>" name="bftpro_uoptions"></p>
	</div>
	<?php echo wp_nonce_field('save_uoptions', 'nonce_uoptions');?>
	</form>

</div>


<script type="text/javascript">
function BFTProChangeCronMode(chk, mode) {
	if(mode=='web') {
		jQuery('#webCronInfo').show();
		jQuery('#webCronOption').show();
		jQuery('#realCronInfo').hide();
	}
	else {
		jQuery('#webCronInfo').hide();
		jQuery('#webCronOption').hide();
		jQuery('#realCronInfo').show();
	}	
}

function BFTPROvalidateOptions(frm) {
	// ensure valid sender address
	var sender = frm.sender.value;
	
	if(sender.indexOf('.') < 1 || sender.indexOf('@') < 1) {
		alert("<?php _e('Please enter valid email address or email / name combination as explained under the field.', 'bftpro')?>");
		frm.sender.focus();
		return false;
	}
	
	// now let's make sure if sender contains name there is space between the name and <
	if(sender.indexOf('<') > -1 && sender.indexOf(' <') < 0) {
		alert("<?php _e('There must be space between the name and the triangular brace in the sender address.', 'bftpro')?>");
		frm.sender.focus();
		return false;
	}
	
	return true;
}

function bftproChangeReCaptchaVersion(val) {
	if(val == 1) {
		jQuery('#reCaptchaSSL').show();
		jQuery('#reCaptchanoCaptcha').hide();
		jQuery('#reCaptcha3').hide();
	}
	if(val == 2) {
		jQuery('#reCaptchaSSL').hide();
		jQuery('#reCaptchanoCaptcha').show();
		jQuery('#reCaptcha3').hide();
	}
	if(val == 3) {
		jQuery('#reCaptchaSSL').hide();
		jQuery('#reCaptchanoCaptcha').hide();
		jQuery('#reCaptcha3').show();
	}
}

function bftproChangeTab(lnk, tab) {
	jQuery('.bftpro-tab-div').hide();
	jQuery('#' + tab).show();
	
	jQuery('.nav-tab-active').addClass('nav-tab').removeClass('nav-tab-active');
	jQuery(lnk).addClass('nav-tab-active');
}
</script>