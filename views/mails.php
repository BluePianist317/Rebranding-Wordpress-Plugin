<h1><?php _e(sprintf("Autoresponder Campaign \"%s\" - Manage Email Messages", stripslashes($ar->name)), 'bftpro')?></h1>

<?php bftpro_display_alerts(); ?>

<p><a href="admin.php?page=bftpro_ar_campaigns"><?php _e('Back to autoresponder campaigns', 'bftpro')?></a></p>

<p><a href="admin.php?page=bftpro_ar_mails&do=add&campaign_id=<?php echo $ar->id?>"><?php _e("Create a new email message (standard editor)", 'bftpro')?></a>
| <a href="admin.php?page=bftpro_choose_preset&from_page=ar&campaign_id=<?php echo $ar->id?>"><?php _e("Create a new email message from preset", 'bftpro')?></a></p>

<?php if(sizeof($mails)):?>
<table class="widefat">
	<tr><th><?php _e("Subject", 'bftpro')?></th><th><?php _e("Sending Rule", 'bftpro')?></th>
	<th><?php _e("View Log", 'bftpro')?></th><th><?php _e("Action", 'bftpro')?></th></tr>
	
	<?php foreach($mails as $mail):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>"><td><strong><?php echo $mail->is_paused ? "<span style='color:gray;'>".sprintf(__('%s (paused)', 'bftpro'), stripslashes($mail->subject)).'</span>': stripslashes($mail->subject);?></strong>
		<?php if(!empty($mail->description)) echo wpautop(stripslashes($mail->description));?></td>		
		<td><?php switch($mail->artype):
			case 'date': echo __("Send on ", 'bftpro').date($dateformat, bftpro_datetotime($mail->send_on_date)); break;
			case 'days': _e(sprintf("%d days after registration", $mail->days), 'bftpro'); break;
			case 'event_days': printf (__("%d days %s user's %s", 'bftpro'), $mail->event_days, $mail->event_case, 'custom event'); break;
			case 'every_days': _e(sprintf("Send every %d days", $mail->every), 'bftpro'); break; 
			case 'every_weekday': _e(sprintf("Send every %s", $mail->every), 'bftpro');	break;
		endswitch;?></td>
		<td><a href="admin.php?page=bftpro_mail_log&type=armail&id=<?php echo $mail->id?>"><?php _e('View log')?></a><br>
		<?php printf(__('Sent %d times.', 'bftpro'), $mail->num_sent);
		if($mail->num_unsubscribed) printf("<br>".__('%d user unsubscribed.', 'bftpro'), $mail->num_unsubscribed);?></td>
		<td><a href="admin.php?page=bftpro_ar_mails&do=edit&id=<?php echo $mail->id?>&campaign_id=<?php echo $ar->id?>"><?php _e("Edit", 'bftpro');?></a>
		| <a href="#" onclick="if(confirm('<?php _e('Are you sure?', 'bftpro')?>')) window.location='<?php echo wp_nonce_url('admin.php?page=bftpro_ar_mails&do=copy&id='.$mail->id.'&campaign_id='.$ar->id, 'bftpro_clone_campaign');?>';return false"><?php _e('Copy', 'bftpro');?></a></td></tr>
	<?php endforeach;?>
</table>
<?php else:?>
	<p><?php _e("There are no email messages yet.", 'bftpro')?></p>
<?php endif;?>