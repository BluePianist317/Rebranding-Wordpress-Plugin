<h1><?php printf(__('Email log for %s', 'bftpro'), sprintf(_x('%s (%s)', 'Subscriber name and email address', 'bftpro'), $user->name, $user->email))?></h1>

<div class="wrap">
		
	<?php if(!sizeof($sent_mails)):?>
		<p><?php _e('No emails has been to this subscriber', 'bftpro')?></p></div>
	<?php return false;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('Date', 'bftpro')?></th><th><?php _e('Subject', 'bftpro')?></th>
		<th><?php _e('Email type', 'bftpro')?></th><th><?php _e('Status', 'bftpro')?></th></tr>	
		<?php foreach($sent_mails as $sent_mail):
			if(empty($sent_mail->ar_subject) and empty($sent_mail->n_subject)) continue;
			$class = ("alternate" == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo date(get_option('date_format'), strtotime($sent_mail->date));?></td>
			<td><?php echo stripslashes($sent_mail->mail_id ? $sent_mail->ar_subject : $sent_mail->n_subject)?></td>
			<td><?php echo $sent_mail->mail_id ? __('Autoresponder', 'bftpro') : __('Newsletter', 'bftpro');?></td>
			<td><?php echo $sent_mail->errors ? __('Not sent (mailing error)', 'bftpro') : __('Sent', 'bfptro');?></td>
			</tr>
		<?php endforeach;?>
	</table>
	
</div>