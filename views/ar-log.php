<h1><?php _e('Autoresponder Campaign Log', 'bftpro')?></h1>

<div class="wrap">
	<h2>"<?php echo stripslashes($mail->subject)?>" <?php _e('Mail In', 'bftpro')?> "<?php echo stripslashes($ar->name)?>"</h2>
	<p><a href="admin.php?page=bftpro_ar_mails&campaign_id=<?php echo $ar->id?>"><?php _e('Back to all email messages in this campaign', 'bftpro')?></a></p>
	
	<?php if(!empty($_GET['from']) and !empty($_GET['to'])):?>
		<p><?php printf(__('Showing logs for the period <b>%s - %s</b>. <a href="%s">Clear this filter.</a>', 'bftpro'),
			date($dateformat, strtotime($_GET['from'])), date($dateformat, strtotime($_GET['to'])), 'admin.php?page=bftpro_mail_log&type=armail&id='.$mail->id."&is_read=".@$_GET['is_read'])?></p>
	<?php endif;?>
	
	<?php if(!empty($_GET['is_read'])):?>
		<p><?php printf(__('Showing log only for the read emails. <a href="%s">Clear this filter.</a>', 'bftpro'), 'admin.php?page=bftpro_mail_log&type=armail&id='.$mail->id."&from=".@$_GET['from'].'&to='.@$_GET['to'])?></p>
	<?php endif;?>
	
	<?php if(!sizeof($sent_mails)):?>
		<p><?php _e('No emails has been sent up to this moment', 'bftpro')?></p></div>
	<?php return false;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('Date', 'bftpro')?></th><th><?php _e('Sent to', 'bftpro')?></th>
		<th><?php _e('Mailing list', 'bftpro')?></th><th><?php _e('Is read?', 'bftpro')?></th></tr>	
		<?php foreach($sent_mails as $sent_mail):
			$class = ("alternate" == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo date(get_option('date_format'), strtotime($sent_mail->date));?></td>
			<td><?php echo empty($sent_mail->email) ? sprintf(__('Deleted user (ID: %d)', 'bftpro'), $sent_mail->user_id) : $sent_mail->email.' '.sprintf(__('(User ID: %d)', 'bftpro'), $sent_mail->user_id)?></td>
			<td><a href="admin.php?page=bftpro_subscribers&id=<?php echo $sent_mail->list_id?>"><?php echo stripslashes($sent_mail->list_name)?></a></td>
			<td><?php echo $sent_mail->is_read ? __('Yes', 'bftpro') : __('No', 'bftpro')?></td></tr>
		<?php endforeach;?>
	</table>
	
	<p align="center">
	<?php if($offset>0):?>
	&nbsp; <a href="admin.php?page=bftpro_mail_log&type=armail&id=<?php echo $mail->id?>&offset=<?php echo ($offset-$limit)?>&from=<?php echo @$_GET['from']?>&to=<?php echo @$_GET['to']?>&is_read=<?php echo @$_GET['is_read']?>"><?php _e("Previous Page", 'bftpro');?></a>
		&nbsp;
	<?php endif;?>
	<?php if($cnt_mails > ($limit + $offset)):?>
	&nbsp; <a href="admin.php?page=bftpro_mail_log&type=armail&id=<?php echo $mail->id?>&offset=<?php echo ($offset+$limit)?>&from=<?php echo @$_GET['from']?>&to=<?php echo @$_GET['to']?>&is_read=<?php echo @$_GET['is_read']?>"><?php _e("Next Page", 'bftpro');?></a> &nbsp;
	<?php endif;?>	
	</p>
</div>