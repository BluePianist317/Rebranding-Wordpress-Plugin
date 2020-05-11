<h1><?php echo empty($_GET['id'])?__("Add", 'bftpro'):__("Edit", 'bftpro')?> <?php _e("Mailing List", 'bftpro')?></h1>

<?php bftpro_display_alerts(); ?>

<div class="postbox wp-admin" style="padding:5px;">
<form method="post" class="bftpro" onsubmit="return validateBFTProList(this);" id="BFTProListForm">
	<fieldset>
		<legend><?php _e("List Details", 'bftpro')?></legend>
		<p><label><?php echo __("List Name:", 'bftpro')?></label> <input type="text" name="name" value="<?php echo stripslashes(@$list->name)?>"></p>
		<p><label><?php echo __("Default Sender Email:", 'bftpro')?></label> <input type="text" name="sender" value="<?php echo stripslashes(@$list->sender?$list->sender:get_option('bftpro_sender'));?>" size="30"></p>
		<p class="help"><?php _e('Used for double opt-in and other automated messages.')?></p>
		<p><label><?php echo __("Optional Description:", 'bftpro')?></label> <textarea name="description" rows="5" cols="40"><?php echo stripslashes(@$list->description)?></textarea></p>
		<p style='display:<?php echo (get_option('bftpro_recaptcha_public') and get_option('bftpro_recaptcha_private'))?'block':'none';?>'>
			<input type="checkbox" name="require_recaptcha" value="1" <?php if(!empty($list->require_recaptcha)) echo 'checked'?>> <?php _e('Enable ReCaptcha for any signup forms of this mailing list.', 'bftpro')?>	<b><?php _e('Note that reCaptcha can be included only once in one web page.', 'bftpro')?></b>	
		</p>
		
		<input type="checkbox" name="require_text_captcha" value="1" <?php if(!empty($list->require_text_captcha)) echo 'checked'?>> <?php _e('Enable Question based "captcha" for any signup forms of this mailing list.', 'bftpro')?>	
	</fieldset>
	
	<fieldset>
		<legend><?php _e("Registration Settings", 'bftpro')?></legend>
		<p><input type="checkbox" name="do_notify" value="1" <?php if(!empty($list->do_notify)) echo "checked"?> onclick="BFTPRO.changeNotify();"> <?php _e("Notify me when a new subscriber registers", 'bftpro')?></p>	
		
		<div id="doNotifyConfig" style='display:<?php echo empty($list->do_notify) ? 'none' : 'block';?>'>
			<p><label><?php _e('Notification subject:', 'bftpro');?></label> <input type="text" size="60" name="notify_signup_subject" value="<?php echo empty($list->notify_signup_subject) ? __("New member signed up", 'bftpro') : stripslashes($list->notify_signup_subject)?>"></p>
			<p><label><?php _e('Notification message:', 'bftpro');?></label> <textarea rows="10" cols="60" name="notify_signup_message"><?php echo empty($list->notify_signup_message) ? $default_signup_message : stripslashes($list->notify_signup_message)?></textarea></p>
			<p><b><?php _e('You can use the following variables:', 'bftpro');?></b> {{{list-name}}} <?php _e('- both in subject and message, and the others only in the message:', 'bftpro');?> {{{email}}}, {{{name}}}, {{{ip}}}, {{{custom-fields}}}</p>
		</div>
		
		<p><input type="checkbox" name="unsubscribe_notify" value="1" <?php if(!empty($list->unsubscribe_notify)) echo "checked"?> onclick="BFTPRO.changeNotify();"> <?php _e("Notify me when a someone unsubscribes", 'bftpro')?></p>	
		
		<p><input type="checkbox" name="auto_subscribe" value="1" <?php if(!empty($list->auto_subscribe)) echo "checked"?> onclick="this.checked ? jQuery('#autoSubscribeOptions').show() : jQuery('#autoSubscribeOptions').hide()"> <?php _e("Automatically subscribe in this list everyone who registers in my blog (By default to avoid spam this will happen after they log-in for the first time!)", 'bftpro')?>
			<div id="autoSubscribeOptions" style='display:<?php echo empty($list->auto_subscribe) ? 'none' : 'block';?>;margin-left:25px;'>				
				<?php _e('Do this when user role is:', 'bftpro');?> <select name="auto_subscribe_role">
					<option value=""><?php _e('- Any role -', 'bftpro');?></option>
					<?php foreach($roles as $key=>$role):
						$selected = (@$list->auto_subscribe_role == $key) ? ' selected' : '';?>
						<option value="<?php echo $key?>"<?php echo $selected?>><?php echo $key?></option>
					<?php endforeach;?>
				</select>
				<br>
				<input type="checkbox" name="auto_subscribe_on_signup" value="1" <?php if(!empty($list->auto_subscribe_on_signup)) echo "checked"?>> <?php _e('Subscribe immediately when user registers - don\'t wait for first login. This will respect the mailing list double opt-in configuration.', 'bftpro');?>
			</div>		
		</p>	
		
		<p><input type="checkbox" name="subscribe_to_blog" value="1" <?php if(!empty($list->subscribe_to_blog)) echo "checked"?> onclick="this.checked ? jQuery('#subToBlogOptions').show() : jQuery('#subToBlogOptions').hide();"> <?php _e("When user subscribes to this mailing lists, register them as subscriber for my site too. (Happens when the subscription is activated.)", 'bftpro')?>
			<div id="subToBlogOptions" style='display:<?php echo empty($list->subscribe_to_blog) ? 'none' : 'block';?>;margin-left:25px;'>
				<?php _e('Assign role:', 'bftpro');?> <select name="subscribe_to_blog_role">
					<option value=""><?php _e('- Default new user role -', 'bftpro');?></option>
					<?php foreach($roles as $key=>$role):
						$selected = (@$list->subscribe_to_blog_role == $key) ? ' selected' : '';?>
						<option value="<?php echo $key?>"<?php echo $selected?>><?php echo $key?></option>
					<?php endforeach;?>
				</select>
			</div>			
		</p>	
		
		<p><label><?php _e("Notifications email(s):", 'bftpro')?></label> <input type="text" name="notify_email" value="<?php echo empty($list->notify_email)?$notify_email:$list->notify_email?>" <?php if(empty($list->do_notify) and empty($list->unsubscribe_notify)) echo "disabled='true'";?> size="40"> <?php _e('(You can enter multiple notification emails separated by comma)', 'bftpro')?></p>
		
		<p><label><?php _e("After registration,<br> go to (Enter full URL):", 'bftpro')?></label> <input type="text" name="redirect_to" value="<?php echo @$list->redirect_to?>" size="60"> <input type="checkbox" name="redirect_to_prepend_uid" value="1" <?php if(!empty($list->redirect_to_prepend_uid)) echo "checked"?>> <?php printf(__('Prepend user id (<a href="%s" target="_blank">what is this</a>)', 'bftpro'), 'http://blog.calendarscripts.info/user-field-shortcodes-and-dynamic-thank-you-pages-from-arigato-pro-2-7-4/#prepend');?></p>
		<br>
		<p><label><?php _e("If the email address is already active in the list,<br> redirect to (Enter full URL):", 'bftpro')?></label> &nbsp;<input type="text" name="redirect_duplicate" value="<?php echo @$list->redirect_duplicate?>" size="60"> </p>
		<br>
		<p><input type="checkbox" name="require_name" value="1" <?php if(!empty($list->require_name)) echo "checked"?>> <?php _e("Make 'name' a required field (email is always required)", 'bftpro')?></p>	
	</fieldset>	
	
	<fieldset>
		<legend><?php _e("Registration Form Settings", 'bftpro')?></legend>
		<p><?php printf(__('Optionally you can use a graphic for the "Submit" button on the registration form. There are multiple codes that can be used to display this registration form on your site - you can get them from your <a href="%s">mailing lists</a> page.', 'bftpro'), 'admin.php?page=bftpro_mailing_lists');?></p>
		
		<p><label><?php _e('Graphic URL:', 'bftpro')?></label> <input type="text" name="signup_graphic" value="<?php echo @$list->signup_graphic?>" size="50"> <?php _e('(Leave blank to use the default signup button)', 'bftpro')?></p>
		
		<p><?php printf(__('If you want to upload graphic you can do it in your <a href="%s" target="_blank">media library</a>. Then you can copy the file URL as shown <a href="%s" target="_blank">here</a>.', 'bftpro'), 'upload.php', 'http://www.wpuniversity.com/wordpress-tips/find-media-library-file-url/')?></p>
	</fieldset>	
	
	<fieldset>
		<legend><?php _e("Email Confirmation Settings", 'bftpro')?></legend>	
		<p><input type="checkbox" name="optin" value="1" <?php if(!empty($list->optin)) echo "checked"?> onclick="BFTPRO.changeOptin(this);"> <?php _e("Require email confirmation ('Double opt-in') when someone subscribes for this mailing list.", 'bftpro')?></p>	
		
		<p class="bftpro-optin" <?php if(empty($list->optin)):?>style="display:none;"<?php endif;?>><label><?php _e("After double opt-in confirmation, go to (Enter full URL):", 'bftpro')?></label> <input type="text" name="redirect_confirm" value="<?php echo @$list->redirect_confirm?>" size="60"> <input type="checkbox" name="redirect_confirm_prepend_uid" value="1" <?php if(!empty($list->redirect_confirm_prepend_uid)) echo "checked"?>> <?php printf(__('Prepend user id (<a href="%s" target="_blank">what is this</a>)', 'bftpro'), 'http://blog.calendarscripts.info/user-field-shortcodes-and-dynamic-thank-you-pages-from-arigato-pro-2-7-4/#prepend');?></p>
		
		<div class="bftpro-optin" <?php if(empty($list->optin)):?>style="display:none;"<?php endif;?>><p><?php printf(__("Confirmation subject and message are optional. If you omit them, I'll use the ones saved in your <a href='admin.php?page=bftpro_options'>Arigato PRO Settings</a> page. Feel free to use the masks {{list-name}} and {{url}} (<a href='%s' target='_blank'>learn more</a>) inside the email subject and/or contents. <strong>Using HTML Code is allowed.</strong> If you don't enter anything here, default content will be used.", 'bftpro'), 'http://blog.calendarscripts.info/broadfast-for-wordpress-the-url-variable/')?></p></div>
	
		<p class="bftpro-optin" <?php if(empty($list->optin)):?>style="display:none;"<?php endif;?>><label><?php echo __("Confirmation Subject:", 'bftpro')?></label> <input type="text" name="confirm_email_subject" value="<?php echo stripslashes(@$list->confirm_email_subject)?>" size="60"></p>
		<div class="bftpro-optin" <?php if(empty($list->optin)):?>style="display:none;"<?php endif;?>><p><label><?php echo __("Confirmation message:", 'bftpro')?></label> <textarea name="confirm_email_content" rows="10" cols="80"><?php echo stripslashes(@$list->confirm_email_content)?></textarea></p>
		<p class="bftpro-help"><?php printf(__('You can use the variables %s and %s to customize this message.', 'bftpro'), '{{name}}', '{{firstname}}');?></p>		
		</div>		
	</fieldset>
	
	<fieldset>
		<legend><?php _e("Unsubscribe Settings", 'bftpro')?></legend>
		<p><?php _e("Text before the unsubscribe link. If you leave this empty, default text will be used. The unsubscribe link will be added after your text. As an alternative to using this, include the variable {{{unsubscribe-url}}} directly in your message. In such case neither the unsubscribe text here nor the default one will be added.", 'bftpro')?></p>
		<div><textarea name="unsubscribe_text" rows="5" cols="60"><?php echo stripslashes(@$list->unsubscribe_text)?></textarea></div>
		
		<p><?php _e('Clickable text:', 'bftpro');?> <input type="text" name="unsubscribe_text_clickable" value="<?php echo @$list->unsubscribe_text_clickable?>" size="50" onkeyup="this.value ? jQuery('#unsubClickableWarning').show() : jQuery('#unsubClickableWarning').hide();"> </p>
		
		<p id="unsubClickableWarning" style='display:<?php echo empty($list->unsubscribe_text_clickable) ? 'none' : 'block';?>'>
			<?php _e("By default the clickable unsubscribe text is the URL itself. You can change it here. Do this at your own risk. If the clickable link does not work in someone's email client they may send a spam complaint.", 'bftpro');?>
		</p>
		
		<p><label><?php _e('Redirect URL:', 'bftpro');?></label> <input type="text" size="40" value="<?php echo @$list->unsubscribe_redirect?>" name="unsubscribe_redirect"> <br>
		<?php _e('This is optional URL where to redirect the user after they unsubscribe. If you leave it empty they will be given a thank-you message on the same page.', 'bftpro');?></p>
		
		<p><input type="checkbox" name="no_unsubscribe_link" value="1" <?php if(!empty($list->no_unsubscribe_link)) echo 'checked'?> onclick="this.checked ? jQuery('#noUnsubLinkHelp').show() : jQuery('#noUnsubLinkHelp').hide();"> <?php _e("Don't automatically add unsubscribe link to outgoing messages.", 'bftpro');?>
			<div class="bftpro-help" id="noUnsubLinkHelp" style='display:<?php echo empty($list->no_unsubscribe_link) ? 'none' : 'block';?>'>
				<strong><?php _e('Do this at your own responsibility and only if you offer alternative unsubscribe method! We take no liability if you get into trouble for spamming.', 'bftpro');?></strong>
			</div>		
		</p>
	</fieldset>		
	
	<?php if(class_exists('Woocommerce') and count($woo_products)):?>
	<fieldset>
		<legend><?php _e("WooCommerce Settings", 'bftpro')?></legend>
		<?php if($integrate_woocommerce == 1):
			if(count($woo_products)):?>
			<p><?php _e('Automatically subscribe to this list customers who purchase any of the following products:', 'bftpro');?></p>
			<p>
				<?php foreach($woo_products as $product):?>
					<span style="white-space:nowrap;"><input type="checkbox" name="woo_products[]" value="<?php echo $product->get_id()?>" <?php if(!empty($list->woo_products) and strstr($list->woo_products, '|'.$product->get_id().'|')) echo 'checked'?>> <?php echo stripslashes($product->get_name());?></span>
				<?php endforeach;?>
			</p>
			
			<p><?php _e('Automatically UN-subscribe from this list customers who purchase any of the following products:', 'bftpro');?></p>
			<p>
				<?php foreach($woo_products as $product):?>
					<span style="white-space:nowrap;"><input type="checkbox" name="woo_products_unsub[]" value="<?php echo $product->get_id()?>" <?php if(!empty($list->woo_products_unsub) and strstr($list->woo_products_unsub, '|'.$product->get_id().'|')) echo 'checked'?>> <?php echo stripslashes($product->get_name());?></span>
				<?php endforeach;?>
			</p>
			
			<p><?php _e("Make sure you don't select the same products in both sections: it does not make sense to subscribe and unsubscribe the user to/from the same list at the same time.", 'bftpro');?></p>
			<?php else:?>
			<p><?php _e('You have not added any products to your WooCommerce store yet.', 'bftpro');?></p>
			<?php endif;
		else:?>
		<p><?php printf(__('You can enable WooCommerce integration on the <a href="%s" target="_blank">Arigato Pro Settings</a> page. It will allow you to automatically subscribe users to lists when they purchase products.', 'bftpro'), 'admin.php?page=bftpro_options');?></p>
		<?php endif;?>
	</fieldset>		
	<?php endif; // end woocommerce integration ?>
	
	<?php if(!empty($list->editor_id) and $multiuser_access == 'all'):?>
		<fieldset>
			<legend><?php _e('Owner settings', 'bftpro');?></legend>
			<p><?php _e('This setting is useful if you have defined user roles that can edit only their own mailing lists.', 'bftpro');?></p>
			<p><label><?php _e('List Owner:', 'bftpro');?></label> <select name="editor_id">
			<?php foreach($editors as $editor):?>
				<option value="<?php echo $editor->ID?>" <?php if($editor->ID == $list->editor_id) echo 'selected'?>><?php echo $editor->display_name;?></option>
			<?php endforeach;?>
			</select></p>
		</fieldset>
	<?php endif;?>
	
	<?php do_action('bftpro-list-form-end', @$list);?>
	
	<div>&nbsp;</div>
	<div><?php if(empty($_GET['id'])):?>
		<input type="submit" name="ok" value="<?php echo __('Add Mailing List', 'bftpro');?>">
	<?php else:?>
		<input type="submit" name="ok" value="<?php echo __('Save List', 'bftpro');?>">
		<input type="button" value="<?php echo __('Delete List', 'bftpro');?>" onclick="confirmDelete(this.form);">
		<input type="hidden" name="del" value="0">
	<?php endif;?>
	<input type="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="window.location='admin.php?page=bftpro_mailing_lists'"></div>
	<?php wp_nonce_field('bftpro_list');?>
</form>
</div>

<script type="text/javascript" >
function validateBFTProList(frm) {
	
	if(frm.name.value=="") {
		alert("<?php _e("Please provide at least a name", 'bftpro')?>");
		frm.name.focus();
		return false;
	}
	
	// make sure the notification email is not in Name <email@dot.com> format
	if(frm.notify_email.value.indexOf("<") > -1) {
		alert("<?php _e('Please enter only email addresses here, not email/name combinations', 'bftpro');?>");
		frm.notify_email.focus();
		return false;
	}
	
	return true;
}

jQuery(function(){
	BFTPRO.changeNotify = function() {
		if(jQuery('#BFTProListForm input[name=do_notify]').is(":checked") 
			|| jQuery('#BFTProListForm input[name=unsubscribe_notify]').is(":checked")) {
			jQuery('#BFTProListForm input[name=notify_email]').removeAttr('disabled');
			
			if(jQuery('#BFTProListForm input[name=do_notify]').is(":checked")) jQuery('#doNotifyConfig').show();
			else jQuery('#doNotifyConfig').hide();
		}
		else {
			jQuery('#BFTProListForm input[name=notify_email]').attr('disabled','true');
			
			if(!jQuery('#BFTProListForm input[name=do_notify]').is(":checked")) jQuery('#doNotifyConfig').hide();
			else jQuery('#doNotifyConfig').show();
		}
	}
	
	BFTPRO.changeOptin = function(elt)
	{
		if(elt.checked) {
			jQuery(".bftpro-optin").show();
		}
		else {
			jQuery(".bftpro-optin").hide();
		}
	}
});
</script>