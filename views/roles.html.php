<div class="wrap">
	<h1><?php _e('Manage role access in Arigato Pro', 'bftpro')?></h1>
	
	<?php if(empty($enabled_roles)):?>
		<p><?php printf(__('To edit this page you need to enable some roles to manage the plugin on the <a href="%s" target=_blank">Arigato PRO Settings page</a>.', 'bftpro'), 'admin.php?page=bftpro_options')?></p>
		</div>
	<?php return false;
	endif;?>
	
	<form method="post">
		<div class="bftpro">
		<p><?php _e('Please select role to configure:', 'bftpro')?> <select name="role_key" onchange="this.form.submit();">
			<option value=""><?php _e('- Please select role -', 'bftpro')?></option>
			<?php foreach($enabled_roles as $role):?>
				<option value="<?php echo $role?>" <?php if(!empty($_POST['role_key']) and $_POST['role_key'] == $role) echo 'selected'?>><?php echo $role?></option>
			<?php endforeach;?>
		</select></p>
		
		<?php if(!empty($_POST['role_key'])):
			$settings = @$role_settings[$_POST['role_key']];?>
			<p><label><?php _e('Manage settings page:', 'bftpro')?></label> <select name="settings_access">
				<option value="all" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'all') echo "selected"?>><?php _e('Manage settings','bftpro')?></option>				
				<option value="no" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'no') echo "selected"?>><?php _e('No access to manage settings','bftpro')?></option>
			</select> </p>			
			
			<p><label><?php _e('Mailing lists access:', 'bftpro')?></label> <select name="lists_access">
				<option value="all" <?php if(!empty($settings['lists_access']) and $settings['lists_access'] == 'all') echo "selected"?>><?php _e('Manage all lists','bftpro')?></option>
				<option value="own" <?php if(!empty($settings['lists_access']) and $settings['lists_access'] == 'own') echo "selected"?>><?php _e('Manage only lists created by the user','bftpro')?></option>				
				<option value="no" <?php if(!empty($settings['lists_access']) and $settings['lists_access'] == 'no') echo "selected"?>><?php _e('No access to manage lists','bftpro')?></option>
			</select> </p>
			
			<p><label><?php _e('Autoresponder campaigns access:', 'bftpro')?></label> <select name="ar_access">
				<option value="all" <?php if(!empty($settings['ar_access']) and $settings['ar_access'] == 'all') echo "selected"?>><?php _e('Manage all campaigns','bftpro')?></option>
				<option value="own" <?php if(!empty($settings['ar_access']) and $settings['ar_access'] == 'own') echo "selected"?>><?php _e('Manage only campaigns created by the user','bftpro')?></option>				
				<option value="no" <?php if(!empty($settings['ar_access']) and $settings['ar_access'] == 'no') echo "selected"?>><?php _e('No access to manage campaigns','bftpro')?></option>
			</select> </p>
			
			<p><label><?php _e('Newsletters access:', 'bftpro')?></label> <select name="nl_access">
				<option value="all" <?php if(!empty($settings['nl_access']) and $settings['nl_access'] == 'all') echo "selected"?>><?php _e('Manage all newsletters','bftpro')?></option>
				<option value="own" <?php if(!empty($settings['nl_access']) and $settings['nl_access'] == 'own') echo "selected"?>><?php _e('Manage only newsletters created by the user','bftpro')?></option>				
				<option value="no" <?php if(!empty($settings['nl_access']) and $settings['nl_access'] == 'no') echo "selected"?>><?php _e('No access to manage newsletters','bftpro')?></option>
			</select> </p>
			
			<p><label><?php _e('Bounce configuration access:', 'bftpro')?></label> <select name="bounce_access">
				<option value="all" <?php if(!empty($settings['bounce_access']) and $settings['bounce_access'] == 'all') echo "selected"?>><?php _e('Manage bounce configuration','bftpro')?></option>
				<option value="no" <?php if(!empty($settings['bounce_access']) and $settings['bounce_access'] == 'no') echo "selected"?>><?php _e('No access to manage bounce configuration','bftpro')?></option>
			</select> </p>
			
			<p><label><?php _e('Subscribe by email configuragion access:', 'bftpro')?></label> <select name="subemail_access">
				<option value="all" <?php if(!empty($settings['subemail_access']) and $settings['subemail_access'] == 'all') echo "selected"?>><?php _e('Manage subscribe by email configuragion','bftpro')?></option>
				<option value="no" <?php if(!empty($settings['subemail_access']) and $settings['subemail_access'] == 'no') echo "selected"?>><?php _e('No access to subscribe by email configuragion','bftpro')?></option>
			</select> </p>		
			
			<p><label><?php _e('Squeeze page management access:', 'bftpro')?></label> <select name="squeeze_access">
				<option value="all" <?php if(!empty($settings['squeeze_access']) and $settings['squeeze_access'] == 'all') echo "selected"?>><?php _e('Manage squeeze page','bftpro')?></option>
				<option value="no" <?php if(!empty($settings['squeeze_access']) and $settings['squeeze_access'] == 'no') echo "selected"?>><?php _e('No access to manage squeeze page','bftpro')?></option>
			</select> </p>
			
			<?php do_action('bftpro-role-settings', $settings);?>
			
			<p><input type="submit" value="<?php _e('Save configuration for this role','bftpro')?>" name="config_role"></p>
		<?php endif;?>
		</div>
	</form>
</div>