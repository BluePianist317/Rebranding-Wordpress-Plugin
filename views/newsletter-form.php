<h1><?php echo empty($_GET['id'])?__("Add", 'bftpro'):__("Edit", 'bftpro')?> <?php _e("Newsletter", 'bftpro')?></h1>

<?php bftpro_display_alerts(); ?>

<div class="postbox wp-admin" style="padding:5px;">
<form method="post" class="bftpro" onsubmit="return validateBFTProNL(this);" id="BFTProNLForm" enctype="multipart/form-data">
	<fieldset>
		<legend><?php _e("Newsletter Message Details", 'bftpro')?></legend>
		<p><label><?php echo __("From Address (Sender):", 'bftpro')?></label> <input type="text" name="sender" value="<?php echo stripslashes(empty($mail->sender)?get_option('bftpro_sender'):$mail->sender)?>" size="40"></p>
		<p id="bftproReplyTo" style='display:<?php echo empty($mail->reply_to) ? 'none' : 'block';?>'><label><?php _e("Reply-To:", 'bftpro')?></label> <input type="text" name="reply_to" value="<?php echo empty($mail->reply_to) ? '' : $mail->reply_to?>" size="40"> </p>
		<p><label><?php echo __("Newsletter Subject:", 'bftpro')?></label> <input type="text" name="subject" value="<?php echo stripslashes(@$mail->subject)?>" size="40"></p>
		<p><label><?php echo __("Newsletter Message:", 'bftpro')?></label>		 
			<?php if(empty($mail->preset_id)): wp_editor(stripslashes(@$mail->message), "arigatopro_message", array('textarea_name' => 'message', 'editor_class'=> 'wp-exclude-emoji')); 
			else: ArigatoPROPresets :: form($mail, @$mail->preset_id); endif;?>
		</p>
		<div class="help">
			
			<div id="customFields">
			<?php if(!empty($mail->list_id) and is_numeric($mail->list_id) and count($custom_fields)):?>
				<p><?php _e("Custom fields", 'bftpro')?></p>
				<ul>
					<?php foreach($custom_fields as $field):?>
						<li><strong>{{<?php echo $field->name?>}}</strong> - <?php echo stripslashes($field->label);?></li>
					<?php endforeach;?>
				</ul>
			<?php endif;?>
			</div>
		</div>		
		
		<p><label><?php _e("Email type:", 'bftpro')?></label> <select name="mailtype">
			<option value="text/html" <?php if(!empty($mail->mailtype) and $mail->mailtype=='text/html') echo 'selected'?>><?php _e("HTML", 'bftpro');?></option>
			<option value="text/plain" <?php if(!empty($mail->mailtype) and $mail->mailtype=='text/plain') echo 'selected'?>><?php _e("Text", 'bftpro');?></option>
			<option value="both" <?php if(!empty($mail->mailtype) and $mail->mailtype=='both') echo 'selected'?>><?php _e("Both", 'bftpro');?></option>
		</select></p>	
		
		<p><input type="checkbox" name="force_wpautop" value="1" <?php if(!empty($mail->force_wpautop)) echo "checked"?>> <?php _e('<b>WP Auto Paragraph</b>', 'bftpro');?></p>
		
		<?php do_action('bftpro-newsletter-form-main')?>	
	</fieldset>
	
	<fieldset>
		<legend><?php _e("Description (will not be emailed, will be shown on newsletter page)", 'bftpro')?></legend>
		<p><textarea rows="5" cols="60" name="description"><?php echo empty($mail->id) ? '' : stripslashes($mail->description);?></textarea></p>
		<p></p>		
	</fieldset>	
	
	<fieldset>
		<legend><?php _e("Attachments (optional)", 'bftpro')?></legend>
		<p><label><?php _e('Upload file(s):', 'bftpro')?></label> <input type="file" name="attachments[]" multiple="multiple"></p>
		<?php wp_nonce_field('bftpro_attach_nonce','bftpro_attach_nonce');?>
		<?php $_att->list_editable(@$attachments);?>
	</fieldset>	
	<?php if(empty($mail->preset_id)) do_action('bftpro-mail-form-designs', @$mail->template_id)?>
	
	<fieldset>
		<legend><?php _e("Select Mailing List", 'bftpro')?></legend>
		<p><label><?php _e('Send to this list:', 'bftpro')?></label> <select name="list_id" id="bftproSelectListId" onchange="bftproChangeList(this.value);">
			<option value=""><?php _e("- Please select -", 'bftpro')?></option>
			<option value="-1"><?php _e("- Global to all lists -", 'bftpro')?></option>
			<?php foreach($lists as $list):?>
				<option value="<?php echo $list->id?>"<?php if(!empty($mail->list_id) and $mail->list_id==$list->id) echo " selected"?>><?php echo stripslashes($list->name);?></option>
			<?php endforeach;?>
		</select></p>
		<p id="globalListInfo" style='display:<?php echo !empty($mail->is_global) ? 'block' : 'none';?>'>
			<?php _e('<b>This is a global newsletter!</b> It will be sent to all your lists in order. If user is subscribed to multiple lists, they will receive the newsletter only once.', 'bftpro');?>
		</p>
			<?php if(!empty($mail->is_global)): echo "<p>".$mail->togo_str."</p>"?>
				<p><?php _e('If you now save or send this newsletter keeping the selection on the current list, it will be converted from global newsletter to a regular newsletter sending ONLY to the selected list.', 'bftpro')?></p>
			<?php endif;?>
			
		<p><input type="checkbox" name="never_duplicate" value="1" <?php if(!empty($mail->never_duplicate)) echo 'checked'?>> <?php _e('Do not send this newsletter to email address that already received it. ', 'bftpro')?></p>
		<?php if(count($lists) > 1 ):?>	
		<p><?php _e("Don't send the newsletter to email addresses which are also in any of the following lists:", 'bftpro');?> 
			<?php foreach($lists as $list):?>
				<span style="white-space: nowrap"><input type="checkbox" name="skip_lists[]" value="<?php echo $list->id?>" <?php if(!empty($skip_lists) and in_array($list->id, $skip_lists)) echo 'checked';?>> <?php echo stripslashes($list->name);?></span>
			<?php endforeach;?>		
		</p>
		<?php endif;?>
	</fieldset>		
	
	<fieldset>
		<legend><?php _e("Test Newsletter", 'bftpro')?></legend>
		<p><input type="checkbox" name="test_newsletter" value="1"> <?php _e('Send only a test email', 'bftpro')?></p>
		<div class="bftpro-help"> <span style="color:red;"><?php _e('Note that unsubscribe link will be added only if you have selected a mailing list and obviously it will not work if the receiver email address does not exist in the list!', 'bftpro');?></span></div>
	</fieldset>
	
	<fieldset>
	  <legend><?php _e('Schedule Newsletter', 'bftpro');?></legend>
	  <p></p>
	  <p><input type="checkbox" name="is_scheduled" value="1" <?php if(!empty($mail->is_scheduled)) echo 'checked'?> onclick="this.checked ? jQuery('#bftproScheduleFor').show() : jQuery('#bftproScheduleFor').hide();"> <?php _e('Schedule this newsletter', 'bftpro');?>
         <span id="bftproScheduleFor" style='display:<?php echo empty($mail->is_scheduled) ? 'none' : 'inline';?>'>
            <?php _e('Start sending on:', 'bftpro');?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime(@$mail->scheduled_for))?>" class="bftproDatePicker" id="bftproScheduleDate">
		<input type="hidden" name="scheduled_for" value="<?php echo @$mail->scheduled_for?>" id="alt_bftproScheduleDate">
         </span>	  
	  </p>
	</fieldset>
	
	<div id="bfproSegments">
	<?php do_action('bftpro-newsletter-form-segments', @$mail, $lists[0]->id);?>
	</div>
		
	<div>&nbsp;</div>
	<div><?php if(empty($_GET['id'])):?>
		<input type="submit" class="button button-primary" value="<?php echo __('Create and Save', 'bftpro');?>">
		<input type="submit" class="button button-primary" style="background-color:green;" name="send" value="<?php echo __('Create And Send', 'bftpro');?>">
	<?php else:?>
		<input type="submit" value="<?php echo __('Save Newsletter', 'bftpro');?>" class="button button-primary">
		<input type="submit" name="send" value="<?php echo __('Save and Send', 'bftpro');?>" class="button button-primary">
		<?php if($mail->status=='in progress'):?>
			<input type="submit" name="cancel" value="<?php echo __('Cancel Sending', 'bftpro');?>" class="button">
		<?php endif;?>
		<input type="button" value="<?php echo __('Delete Newsletter', 'bftpro');?>" onclick="confirmDelete(this.form);"  class="button">
		<input type="hidden" name="del" value="0">
	<?php endif;?>
	<input type="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="window.location='admin.php?page=bftpro_newsletters'"  class="button"></div>

	<?php if(!empty($mail->id) and $mail->status=='in progress'):?>
		<div class="bftpro-warning"><?php _e("Warning: this newsletter is currently in progress. This means that if you click on 'Save Newsletter' button, the changes will affect all the subscribers that have not yet received the newsletter. If you click 'Save and Send' the mailing will be RESTARTED from the beginning with the new content.", 'bftpro')?></div>
	<?php endif;?>	
	
	<input type="hidden" name="ok" value="1">
	<?php wp_nonce_field('bftpro_newsletter');?>
</form>
</div>	

<script type="text/javascript" >
function validateBFTProNL(frm) {
	if(frm.sender.value=="") 	{
		alert("<?php _e("Please provide sender email for this newsletter", 'bftpro')?>");
		frm.sender.focus();
		return false;
	}
	
	if(frm.subject.value=="") 	{
		alert("<?php _e("Please provide subject", 'bftpro')?>");
		frm.subject.focus();
		return false;
	}
	
	if(frm.list_id.value=="") {
		alert("<?php _e('Please select mailing lsit', 'bftpro');?>");
		frm.list_id.focus();
		return false;
	}
	
	return true;
}

function bftproChangeList(id) {	
	if(id == '-1') jQuery('#globalListInfo').show();
	else jQuery('#globalListInfo').hide();
	
	// call ajax to load custom fields
	var url = "<?php echo admin_url('admin-ajax.php');?>";
	var data = {'action' : 'bftpro_ajax', 'type' : 'list_fields', 'list_id' : id}
	jQuery.post(url, data, function(msg){
		jQuery('#customFields').html(msg);
	});
}

jQuery(document).ready(function() {
    jQuery('.bftproDatePicker').datepicker({
    		dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',
         altFormat : 'yy-mm-dd'
    });
    
    jQuery(".bftproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
    });
});
</script>

<?php do_action('bftpro-newsletter-form-js', @$mail);?>