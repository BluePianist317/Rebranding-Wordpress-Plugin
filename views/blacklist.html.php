<style type="text/css">
textarea {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;

    width: 100%;
}
</style>
<div class="wrap">
	<h1><?php _e('Blacklist Management', 'bftpro');?></h1>
	
	
	
	<div class="postbox wp-admin" style="padding:5px;">
	<form method="post"  class="bftpro">
		<div style="float:left;width:45%;">
			<h3><?php _e('Blacklisted IP addresses (one per line)', 'bftpro');?></h3>
			<textarea name="ips" rows="10" cols="40"><?php echo get_option('bftpro_blacklisted_ips');?></textarea>
		</div>
		<div style="float:left;width:45%;margin-left:20px;">
			<h3><?php _e('Blacklisted email addresses (one per line)', 'bftpro');?></h3>
			<textarea name="emails" rows="10" cols="40"><?php echo get_option('bftpro_blacklisted_emails');?></textarea>
		</div>
		<div style="width:100%;clear:both;">&nbsp;</div>
		
		<p><?php _e('When blacklisted user tries to register:', 'bftpro');?><br />
			<input type="radio" name="behavior" value="continue" <?php if(empty($settings['behavior'])  or $settings['behavior'] == 'continue') echo 'checked'?>> <?php _e('Gracefully continue with success message like nothing happened.', 'bftpro');?><br />
			<input type="radio" name="behavior" value="exit" <?php if(!empty($settings['behavior']) and $settings['behavior'] == 'exit') echo 'checked'?>> <?php _e('Exit with an error message:.', 'bftpro');?> <textarea name="error_msg" rows="2" cols="60"><?php echo empty($settings['error_msg']) ? '' : stripslashes($settings['error_msg']);?></textarea></p>
			
		<p><input type="submit" value="<?php _e('Save Blacklist Settings', 'bftpro');?>"></p>	
		<input type="hidden" name="ok" value="1">
		<?php wp_nonce_field('arigatopro_blacklist');?>
	</form>
	</div>
</div>