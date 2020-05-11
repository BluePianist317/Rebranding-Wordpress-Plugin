<div class="wrap">
	<h1><?php _e('Handle Bounced Emails', 'bftpro')?></h1>
	
	
	
	<?php if(!empty($_POST['test_connection'])):?>
		<h3><?php _e('Result of the test:', 'bftpro');?></h3>
		<?php if(empty($error_msg)): 
			echo "<p style='color:green;'>".__('Connection successful!', 'bftpro')."</p>";
			else: echo "<p style='color:red;'>".$error_msg."</p>";
			endif;
	   endif;?>
	
	<form method="post" class="bftpro">
		<div class="postbox wp-admin" style="padding:5px;">
			<div><label><?php _e('Email address to receive bounces:', 'bftpro')?></label>
				<input type="text" name="bounce_email" value="<?php echo $bounce_email?>"></div>
			
			<p><input type="checkbox" name="handle_bounces" value="1" <?php if($handle_bounces) echo 'checked'?> onclick="this.checked ? jQuery('#bounceHandling').show():jQuery('#bounceHandling').hide();"> <?php _e('I want to use automated bounces handling', 'bftpro')?></p>
			
			<fieldset id="bounceHandling" style="display:<?php echo $handle_bounces ? 'block' : 'none';?>">
				<legend><?php _e("Configure authomated bounce handling", 'bftpro')?></legend>
			
				
				<p><?php _e('Delete email from all mailing lists after it bounces', 'bftpro')?> <input type="text" name="bounce_limit" value="<?php echo $bounce_limit?>" size="4"> <?php _e('times.', 'bftpro')?></p>
				
				<p><label><?php _e('Email Server Host:', 'bftpro')?></label> <input type="text" name="bounce_host" value="<?php echo $bounce_host?>"></p>
				<p><label><?php _e('Email Server Port:', 'bftpro')?></label> <input type="text" name="bounce_port" value="<?php echo $bounce_port?>" size="5"> <?php _e('(Not IMAP!)', 'bftpro');?></p>
				<p><label><?php _e('Use SSL/TLS:', 'bftpro')?></label> <select name="bounce_ssl">
					<option value="0"><?php _e('No', 'bftpro');?></option>
					<option value="1" <?php if(!empty($bounce_ssl)) echo 'selected'?>><?php _e('Yes', 'bftpro');?></option>
				</select></p>
				<p><label><?php _e('Account Login:', 'bftpro')?></label> <input type="text" name="bounce_login" value="<?php echo $bounce_login?>"></p>
				<p><label><?php _e('Account Password:', 'bftpro')?></label> <input type="password" name="bounce_pass" value="<?php echo stripslashes($bounce_pass)?>"></p>				
				
				<p><b><?php _e('If you have any problems with the POP3 connection you should contact your hosting support and not the plugin developers.', 'bftpro');?></b></p>
				
			
			</fieldset>			
			
			<p><input type="submit" value="<?php _e('Save Options', 'bftpro')?>" name="ok"></p>	
		<?php wp_nonce_field('bftpro_bounces');?>
		</div>	
	</form>
</div>