<h1><?php _e('Sent Newsletter Log', 'bftpro')?></h1>

<p><?php _e('Showing sent log for', 'bftpro')?> <a href="admin.php?page=bftpro_newsletters&do=edit&id=<?php echo $mail->id?>"><?php echo stripslashes($newsletter->subject)?></a></p>

<p><a href="admin.php?page=bftpro_newsletters"><?php _e('Back to newsletters')?></a></p>

<div class="wp-admin">
	<div class="inside">
		<table class="widefat">
			<tr><th><?php _e('Email')?></th><th><?php _e('Date sent')?></th><th><?php _e('Opened')?></th></tr>
			<?php foreach($mails as $mail):
				$class = ("alternate" == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo $mail->email ? $mail->email : __('Deleted user', 'bftpro');?></td>
				<td><?php echo date_i18n($dateformat, strtotime($mail->date));?></td>				
				<td><?php if(in_array($mail->user_id, $read_uids)) echo __('Yes', 'bftpro');
				else _e('No', 'bftpro');?></td></tr>
			<?php endforeach;?>
		</table>
		
		<p><?php _e('Note: the "Opened" colum is only approximately correct and may show "No" for some emails that were actually read.', 'bftpro');?></p>
	</div>	
</div>