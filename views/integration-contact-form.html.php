<div class="wrap">
	<h1><?php _e('Mailing List to Contact Form Integration', 'bftpro')?></h1>
		
	<p><a href="admin.php?page=bftpro_mailing_lists"><?php _e('Back to the mailing lists', 'bftpro')?></a></p>
	
	<p><?php printf(__('Using the shortcode below will display a checkbox for subscribing in the mailing list inside your contact form. We currently support integration with <a href="%s" target="_blank">Contact Form 7</a> and the <a href="%s" target="_blank">JetPack Contact form</a>.', 'bftpro'),'http://wordpress.org/plugins/contact-form-7/', 'http://wordpress.org/plugins/jetpack/');?> </p>
	
	<table>
		<tr><td width="50%" valign="top">
			<div class="postbox wp-admin" style="padding:10px;">
				<form method="post" class="bftpro">
					<div><label><?php _e('Select mailing list:', 'bftpro')?></label> <select name="list_id" onchange="this.form.submit();">
						<?php foreach($lists as $l):
							$selected = ($l->id == $list_id) ? ' selected' : '';?>
							<option value="<?php echo $l->id?>"<?php echo $selected?>><?php echo stripslashes($l->name)?></option>
						<?php endforeach;?>
					</select></div>
					<p><input type="checkbox" name="checked_by_default" value="1" <?php if(!empty($_POST['checked_by_default'])) echo 'checked'?>> <?php _e('Checked by default', 'bftpro')?></p>
					
					<p><input type="checkbox" name="is_hidden" value="1" <?php if(!empty($_POST['is_hidden'])) echo 'checked'?>> <?php _e('Hidden. Do this at your own responsibility. You may be accused of spam for not asking users to explicitly confirm their willingness to subscribe.', 'bftpro')?></p>
				
					<p><label><?php _e('CSS classes (optional):', 'bftpro')?></label> <input type="text" name="classes" value="<?php echo @$_POST['classes']?>"></p>
					<p><label><?php _e('HTML ID (optional):', 'bftpro')?></label> <input type="text" name="html_id" value="<?php echo @$_POST['html_id']?>"></p>
					<p><input type="submit" value="<?php _e('Refresh Shortcode', 'bftpro')?>"></p>
					
					<p><?php _e('Shortcode to use in contact form:', 'bftpro')?> 
					<textarea readonly="readonly" onclick="this.select()" rows="2" cols="40">[bftpro-int-chk list_id="<?php echo $list_id?>"<?php echo $shortcode_atts?>] <?php 
					if(empty($_POST['is_hidden'])): printf(__('Subscribe for %s', 'bftpro'), stripslashes($list->name)); endif;?></textarea></p>
					
					<h3><?php _e('Contact Form 7 Integration', 'bftpro')?></h3>
					<p><b><?php _e('Place this shortcode inside your Contact Form 7 contact form - right where you want the checkbox to appear.', 'bftpro')?></b> <?php printf(__('For more information check <a href="%s" target="_blank">this blog post</a>.', 'bftpro'), 'http://blog.calendarscripts.info/how-to-integrate-broadfast-for-wordpress-with-contact-form-7/')?></p>
					<p><?php _e('<b>IMPORTANT:</b> By default Contact Form 7 creates shortcodes with parameters "your-name" for the name field, and "your-email" for the email field. If you have changed this you must reflect this in the boxes below otherwise the integration will not work.', 'bftpro')?></p>
					
					<p><label><?php _e('Name field name:', 'bftpro')?></label> <input type="text" name="cf7_name_field" value="<?php echo $name_name?>"></p>
					<p><label><?php _e('email field name:', 'bftpro')?></label> <input type="text" name="cf7_email_field" value="<?php echo $email_name?>"></p>
					
					<p><b><?php _e('These field names are the same for all your mailing lists.', 'bftpro')?></b></p>
					<p><input type="submit" name="change_defaults" value="<?php _e('Change field names', 'bftpro')?>"></p>
					
					<h3><?php _e('JetPack Contact Form Integration', 'bftpro')?></h3>
					<p><b><?php _e('Place this shortcode inside the published shortcode of your contact form - somewhere before the closing "[/contact-form]" shortcode.', 'bftpro')?></b></p>
				</form> 
			</div>
		</td><td width="50%" valign="top">
			<?php if(sizeof($fields)):?>
			<div class="postbox wp-admin" style="padding:10px;">
				<p><?php _e('The selected mailing list has some custom fields. Using the shortcodes below you can have these fields also included in your contact form.', 'bftpro')?></p>
				<p><?php _e('The information from these fields will not be included in the contact form message, but will be stored along with your subscribed user data in the mailing list.', 'bftpro')?></p>
				
				<table class="widefat">
					<tr><th><?php _e('Field', 'bftpro')?></th>
					<th><?php _e('Code', 'bftpro')?></th></tr>
					<?php foreach($fields as $field):
						$class = ('alternate' == @$class) ? '' : 'alternate';?>
						<tr class="<?php echo $class?>"><td><?php echo stripslashes($field->label)?></td><td><textarea rows="2" cols="30" readonly="readonly" onclick="this.select();"><p><?php echo $field->label?><br />
[bftpro-field <?php echo $field->id?>]</p></textarea></td></tr>
					<?php endforeach;?>
				</table>
				
				<p><b><?php _e('Place any of these codes inside your Contact Form 7 or JetPack contact form - right where you want the custom field to appear. JetPack contact form users must do this in the post or page where the form shortcodes are already placed.', 'bftpro')?></b></p>
			</div>		
			<?php endif;?>
		</td></tr>	
	</table>	 
</div>