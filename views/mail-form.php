<div class="wrap">

	<h1><?php echo empty($_GET['id'])?__("Add", 'bftpro'):__("Edit", 'bftpro')?> <?php _e("Email Message in", 'bftpro')?> "<?php echo stripslashes($ar->name);?>"</h1>
	
	<?php bftpro_display_alerts(); ?>
	
	<p><a href="admin.php?page=bftpro_ar_mails&campaign_id=<?php echo $_GET['campaign_id']?>"><?php _e('Back to the autoresponder campaign', 'bftpro')?></a></p>
	
	<div class="postbox wp-admin" style="padding:5px;">
	<form method="post" class="bftpro" onsubmit="return validateBFTProMail(this);" id="BFTProMailForm" enctype="multipart/form-data">
		<fieldset>
			<legend><?php _e("Email Message Details", 'bftpro')?></legend>
			<div><label><?php echo __("From Address (Sender):", 'bftpro')?></label> <input type="text" name="sender" value="<?php echo stripslashes(empty($mail->sender)?$ar->sender:$mail->sender)?>" size="40"> <?php printf(__('(Used also as reply-to address. <a href="#" onclick="%s">Set different reply-to</a>.)', 'bftpro'), "jQuery('#bftproReplyTo').toggle();return false;");?></div>
			<div id="bftproReplyTo" style='display:<?php echo empty($mail->reply_to) ? 'none' : 'block';?>'><label><?php _e("Reply-To:", 'bftpro')?></label> <input type="text" name="reply_to" value="<?php echo empty($mail->reply_to) ? '' : $mail->reply_to?>" size="40"> <?php printf(__('Leave empty to use the sender address (<a href="%s" target="_blank">learn more</a>).', 'bftpro'), 'https://calendarscripts.info/bft-pro/howto.html?q=16#q16');?></div>
			<div><label><?php echo __("Email Subject:", 'bftpro')?></label> <input type="text" name="subject" value="<?php echo stripslashes(@$mail->subject)?>" size="40"></div>
			<div><label><?php echo __("Email Message:", 'bftpro')?></label> 
			<?php if(empty($mail->preset_id)): wp_editor(stripslashes(@$mail->message), "bftMessage", array('textarea_name'=>'message')); 
			else: ArigatoPROPresets :: form($mail, $mail->preset_id); endif;?></div>
			<div class="help"><p><?php _e("You can use the following short codes inside the email content. They will be replaced with dynamic data from your mailing lists.", 'bftpro')?></p>
				<p><?php _e("Basic fields", 'bftpro')?></p>
				<ul>
					<li><strong>{{email}}</strong> - <?php _e("subscriber email", 'bftpro')?></li>
					<li><strong>{{name}}</strong> - <?php _e("subscriber name", 'bftpro')?></li>
					<li><strong>{{firstname}}</strong> - <?php _e("subscriber first name", 'bftpro')?></li>
					<li><strong>{{list-name}}</strong> - <?php _e("the name of the user's mailing list", 'bftpro')?></li>
					<li><strong>{{date}}</strong> - <?php _e("subscriber registration date", 'bftpro')?></li>
				</ul>			
				
				<?php if(!empty($fields) and sizeof($fields)):?>
				<p><?php _e("Custom fields", 'bftpro')?></p>
				<ul>
					<?php foreach($fields as $field):?>
						<li><strong>{{<?php echo $field->name?>}}</strong> - <?php _e(sprintf("From mailing list '%s'", stripslashes($field->list_name)), 'bftpro')?></li>
					<?php endforeach;?>
				</ul>
				<?php endif;?>
			</div>		
			
			<div><label><?php _e("Email type:", 'bftpro')?></label> <select name="mailtype">
				<option value="text/html" <?php if(!empty($mail->mailtype) and $mail->mailtype=='text/html') echo 'selected'?>><?php _e("HTML", 'bftpro');?></option>
				<option value="text/plain" <?php if(!empty($mail->mailtype) and $mail->mailtype=='text/plain') echo 'selected'?>><?php _e("Text", 'bftpro');?></option>
				<option value="both" <?php if(!empty($mail->mailtype) and $mail->mailtype=='both') echo 'selected'?>><?php _e("Both", 'bftpro');?></option>
			</select></div>	
			
			<?php do_action('bftpro-mail-form-main')?>		
		</fieldset>
		
		<fieldset>
			<legend><?php _e("Attachments (optional)", 'bftpro')?></legend>
			<div><label><?php _e('Upload file(s):', 'bftpro')?></label> <input type="file" name="attachments[]" multiple="multiple"></div>
			<?php wp_nonce_field('bftpro_attach_nonce','bftpro_attach_nonce');?>
			<?php $_att->list_editable(@$attachments);?>
		</fieldset>	
		
		<?php if(empty($mail->preset_id)) do_action('bftpro-mail-form-designs', @$mail->template_id)?>
		
		<fieldset>
		<legend><?php _e("Description (optional, for management purposes)", 'bftpro')?></legend>
		<p><textarea rows="5" cols="60" name="description"><?php echo empty($mail->id) ? '' : stripslashes($mail->description);?></textarea></p>
		<p><?php _e('If provided, the description will be shown on the page with campaign messages.', 'bftpro');?></p>		
	</fieldset>	
		
		<fieldset>
			<legend><?php _e("Email Sending Options", 'bftpro')?></legend>
			<div><input type="radio" name="artype" value="days" <?php if(empty($mail->id) or $mail->artype=='days') echo "checked"?> onclick="bftproSelectMailScheduleType(this.value);"> <?php _e("Send", 'bftpro')?>
				<input type="text" id="daysAfterReg" name="days" size="4" value="<?php if(@$mail->artype=='days') echo @$mail->days?>" onkeyup="if(this.value == 0 || this.value == '') {jQuery('#minsAfterReg').show()} else {jQuery('#minsAfterReg').hide()};"> <?php _e('days after user registration.', 'bftpro');?>
				<span id="sendAsNewsletter" style='display:<?php echo (empty($mail->artype) or $mail->artype == 'days') ? 'inline' : 'none';?>'><input type="checkbox" name="send_to_old" value="1"> <?php _e('Send as newsletter to these who will never get it because they are registered earlier than this.', 'bftpro');?></span>	
			</div>
				<div><input type="radio" name="artype" value="date" <?php if(!empty($mail->id) and $mail->artype=='date') echo "checked"?> onclick="bftproSelectMailScheduleType(this.value);"> <?php _e("Send on", 'bftpro')?>
				<?php echo BFTProQuickDDDate("send_on_date", empty($mail->send_on_date)?date("Y-m-d"):$mail->send_on_date, null, null, 2012);?></div>
				
				<div><input type="radio" name="artype" value="every_days" <?php if(!empty($mail->id) and $mail->artype=='every_days') echo "checked"?> onclick="bftproSelectMailScheduleType(this.value);"> <?php _e("Send every", 'bftpro')?>
				<input type="text" name="every_days" size="4" value="<?php if(@$mail->artype=='every_days') echo @$mail->every?>"> <?php _e('days. (accordintly to user registration date)', 'bftpro');?></div>
				
				<div><input type="radio" name="artype" value="every_weekday" <?php if(!empty($mail->id) and $mail->artype=='every_weekday') echo "checked"?> onclick="bftproSelectMailScheduleType(this.value);"> <?php _e("Send every", 'bftpro')?>
				<select name="every_weekday">
					<option value="monday" <?php if(@$mail->every=='monday') echo "selected"?>><?php _e("Monday", 'bftpro');?></option>
					<option value="tuesday" <?php if(@$mail->every=='tuesday') echo "selected"?>><?php _e("Tuesday", 'bftpro');?></option>
					<option value="wednesday" <?php if(@$mail->every=='wednesday') echo "selected"?>><?php _e("Wednesday", 'bftpro');?></option>
					<option value="thursday" <?php if(@$mail->every=='thursday') echo "selected"?>><?php _e("Thursday", 'bftpro');?></option>
					<option value="friday" <?php if(@$mail->every=='friday') echo "selected"?>><?php _e("Friday", 'bftpro');?></option>
					<option value="saturday" <?php if(@$mail->every=='saturday') echo "selected"?>><?php _e("Saturday", 'bftpro');?></option>
					<option value="sunday" <?php if(@$mail->every=='sunday') echo "selected"?>><?php _e("Sunday", 'bftpro');?></option>
				</select></div>
				<?php do_action('bftpro-mail-form-sending-options', $ar, @$mail)?>
				
				<p><input type="checkbox" name="is_paused" value="1" <?php if(!empty($mail->is_paused)) echo "checked"?>> <?php _e('Pause this message. (When paused it will be inactive and will not be sent on the target date.)', 'bftpro');?></p>			
				
				<p><input type="checkbox" name="force_wpautop" value="1" <?php if(!empty($mail->force_wpautop)) echo "checked"?>> <?php _e('Use <b>wp auto paragraphs</b> on this message. Select this only if the new lines are not properly added when you receive this message by email.', 'bftpro');?></p>					
		</fieldset>
		
		<fieldset>
		<legend><?php _e("Time of the day", 'bftpro')?></legend>
		<div>
			<p><input type="radio" name="time_of_sending" value='any' <?php if(empty($mail->daytime)) echo "checked"?>> <?php _e('Send any time of the day', 'bftpro')?></p>   
			 <p><input type="radio" name="time_of_sending" value="specified" <?php if(!empty($mail->daytime)) echo "checked"?>> <?php _e('Send after', 'bftpro')?> 
	    	<select name="daytime">
	    	<?php for($i=0; $i<24; $i++):?>    		
	    		<option value="<?php echo $i?>"<?php if(!empty($mail->daytime) and $mail->daytime==$i):?> selected<?php endif;?>><?php echo sprintf("%02d",$i)?>:00</option> 
	    	<?php endfor;?>
	    	</select> <?php _e("o'clock", 'bftpro')?></p>
	    	<p><?php if($use_wp_time):
   	    	   printf(__('This setting uses your WP timezone. Accordingly to it your current time is <b>%s</b>. You can change it at the <a href="%s" target="_blank">WP Settings</a> page.', 'bftpro'), $server_time, 'options-general.php');
	    	   else:
	    	      printf(__('This setting uses your MySQL server time. Your current server time is <b>%s</b>. You can switch to using your WP timezone from the <a href="%s" target="_blank">Arigato Settings</a> page.', 'bftpro'), $server_time, 'admin.php?page=bftpro_options');
	    	   endif;?></p>
	    	   
	    	<p id="minsAfterReg" style='display:<?php echo (empty($mail->id) or ($mail->artype == 'days' and $mail->days == 0)) ? 'block' : 'none' ?>'>
	    		<?php printf(__('Send this message at least %s minutes after subscriber activation. This setting is available only for "0 days" emails to ensure some interval before sending. The exact interval in minutes is guaranteed as minimum, but the maximum interval depends on the frequency of your cron job.', 'bftpro'), '<input type="text" size="4" name="mins_after_reg" value="'.(@$mail->mins_after_reg).'">');?>
	    	</p>   
		</div>				
		</fieldset>	
		
		<fieldset>
			<legend><?php _e("Test Email", 'bftpro')?></legend>
			<p><input type="checkbox" name="test_email" value="1"> <?php _e('When saving send test email', 'bftpro')?></p>
			<div class="bftpro-help"><?php printf(__('When this is selected click on "Send" button and the newsletter will be delivered to %s. It will not be sent or resent to the mailing list and will not disturb in any way any sending in progress.', 'bftpro'), '<input type="text" name="send_test" value="'.(empty($mail->send_test) ? bftpro_extract_email(get_option('bftpro_sender')) : $mail->send_test).'">')?></div>
		</fieldset>
		
		<?php do_action('bftpro-mail-form-segments', $ar, @$mail)?>
		
		<div>&nbsp;</div>
		<div><?php if(empty($_GET['id'])):?>
			<input type="submit" name="ok" value="<?php echo __('Create Email Message', 'bftpro');?>">
		<?php else:?>
			<input type="submit" name="ok" value="<?php echo __('Save Message', 'bftpro');?>">
			<input type="button" value="<?php echo __('Delete Message', 'bftpro');?>" onclick="confirmDelete(this.form);">
			<input type="hidden" name="del" value="0">
		<?php endif;?>
		<input type="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="window.location='admin.php?page=bftpro_ar_mails&campaign_id=<?php echo $ar->id?>'"></div>
	</form>
	</div>	
</div>

<script type="text/javascript" >
function validateBFTProMail(frm) {
	if(frm.sender.value=="") {
		alert("<?php _e("Please provide sender email for this email message", 'bftpro')?>");
		frm.sender.focus();
		return false;
	}
	
	if(frm.subject.value=="") {
		alert("<?php _e("Please provide subject", 'bftpro')?>");
		frm.subject.focus();
		return false;
	}
	
	return true;
}

function bftproSelectMailScheduleType(val) {
	if(val == 'days') {
		jQuery('#sendAsNewsletter').show();
		if(jQuery('#daysAfterReg').val() == 0 || jQuery('#daysAfterReg').val() == '') jQuery('#minsAfterReg').show();
		else jQuery('#minsAfterReg').hide();
	}
	else {
		jQuery('#sendAsNewsletter').hide();
		jQuery('#minsAfterReg').hide();
	}
}
</script>