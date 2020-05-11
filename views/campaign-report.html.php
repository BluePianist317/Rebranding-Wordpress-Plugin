<div class="wrap">
	<h1><?php printf(__('Viewing reports for campaign "%s"', 'bftpro'), stripslashes($campaign->name));?></h1>
	
	<p><a href="admin.php?page=bftpro_ar_campaigns"><?php _e('Back to campaigns', 'bftpro')?></a></p>
	
	<form method="post">
		<p><?php _e('Select period:', 'bftpro')?> <?php echo BFTProQuickDDDate('from', $from, NULL, NULL, 2013, date("Y"));?>
			-	<?php echo BFTProQuickDDDate('to', $to, NULL, NULL, 2013, date("Y"));?>
			<input type="submit" value="<?php _e('Refresh report', 'bftpro')?>"></p>
	</form>
	
	<?php if(!sizeof($mails)):?>
		<p><?php _e('There are no email messages in this campaign yet.', 'bftpro')?></p></div>
	<?php return false;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('Email subject','bftpro')?></th><th><?php _e('Num. sent', 'bftpro')?></th>
		<th><?php _e('Num. read', 'bftpro')?></th><th><?php _e('Open rate', 'bftpro')?></th>
		<?php do_action('bftrpo_campaign_report_extra_th');?>		
		</tr>
		<?php foreach($mails as $mail):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo stripslashes($mail->subject)?><br>
			<a href="#" onclick="arigatoProByDomain('<?php echo $from?>', '<?php echo $to?>', '<?php echo $mail->id?>');return false;"><?php _e('Stats per email domain', 'bftpro');?></a></td><td><a href="admin.php?page=bftpro_mail_log&type=armail&id=<?php echo $mail->id?>&from=<?php echo $from?>&to=<?php echo $to?>" target="_blank"><?php echo $mail->num_sent?></a></td>
			<td><a href="admin.php?page=bftpro_mail_log&type=armail&id=<?php echo $mail->id?>&from=<?php echo $from?>&to=<?php echo $to?>&is_read=1" target="_blank"><?php echo $mail->num_read?></a></td><td><?php echo $mail->open_rate?>%</td>
			<?php do_action('bftrpo_campaign_report_extra_td', $mail, $from, $to);?></tr>
			<tr class="<?php echo $class?>" style="display:none;" id="armailByDomain<?php echo $mail->id?>"><td colspan="6" id="armailByDomainTD<?php echo $mail->id?>"><?php _e('Please wait...', 'bftpro');?></td></tr>
		<?php endforeach;?>	
	</table>
</div>

<script type="text/javascript" >
function arigatoProByDomain(from, to, id) {
	data = {'action' : 'arigatopro_armail_by_domain', 'from' : from, 'to' : to, 'id' : id};
	jQuery('#armailByDomain' + id).show();
	jQuery.post('<?php echo admin_url("admin-ajax.php");?>', data, function(msg){		
		jQuery('#armailByDomainTD' + id).html(msg);		
	});
}
</script>