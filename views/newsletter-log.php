<h1><?php _e('Newsletter Progress Details', 'bftpro')?></h1>

<p><?php _e('Showing progress for', 'bftpro')?> <a href="admin.php?page=bftpro_newsletters&do=edit&id=<?php echo $mail->id?>"><?php echo $mail->subject?></a></p>

<p><a href="admin.php?page=bftpro_newsletters"><?php _e('Back to newsletters')?></a></p>

<p><strong><?php printf(__("Sending progress: %d emails have been sent from %d total", 'bftpro'), $num_sent, $total_users)?></strong></p> 

<div class="wp-admin">
	<div class="inside">
		<table class="widefat">
			<tr><th><?php _e('Email')?></th><th><?php _e('Status')?></th></tr>
			<?php foreach($users as $user):
				$class = ("alternate" == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo $user->email?></td><td><?php if($user->id <= $mail->last_user_id or ($mail->status == 'completed' and !$mail->last_user_id)) _e('Sent', 'bftpro');
				else _e('In queue', 'bftpro');?></td></tr>
			<?php endforeach;?>
		</table>
		
		<h2><?php _e('What does "In queue" mean?', 'bftpro')?></h2>
		
		<p><?php _e('When you see "in queue" under the "Status" column this means that the newsletter is not yet sent to the given email address. There might be several reasons for this:', 'bftpro')?></p>
		<ol>
			<li><?php printf(__('The cron job did not reach these mails yet. This depends on the cron job frequency and the limits you have set up in your <a href="%s" target="_blank">autoresponder options</a> page.', 'bftpro'), 'admin.php?page=bftpro_options');?></li>
			<li><?php _e('The cron job is not at all set up. Did you set it up as explained on the same autoresponder options page?', 'bftpro')?></li>
			<li><?php printf(__('Maybe there is an error in your email sending process. Check your <a href="%s" target="_blank">email sending log</a>.', 'bftpro'), 'admin.php?page=bftpro_help&tab=error_log');?></li>
		</ol>
	</div>	
</div>

<p align="center">
	<?php if($offset>0):?>
	&nbsp; <a href="admin.php?page=bftpro_nl_log&offset=<?php echo ($offset-$limit)?>&id=<?php echo $mail->id?>"><?php _e("Previous Page", 'bftpro');?></a>
		&nbsp;
	<?php endif;?>
	<?php if($total_users > ($limit + $offset)):?>
	&nbsp; <a href="admin.php?page=bftpro_nl_log&offset=<?php echo ($offset+$limit)?>&id=<?php echo $mail->id?>"><?php _e("Next Page", 'bftpro');?></a> &nbsp;
	<?php endif;?>	
</p>