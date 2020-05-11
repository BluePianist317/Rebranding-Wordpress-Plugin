<div class="wrap">
	<h1><?php _e('Newsletter Reports', 'bftpro');?></h1>
	
	<p><a href="admin.php?page=bftpro_newsletters"><?php _e('Back to newsletters', 'bftpro')?></a></p>
	
	<form method="post">
		<p><?php _e('Select period:', 'bftpro')?> <?php echo BFTProQuickDDDate('from', $from, NULL, NULL, 2013, date("Y"));?>
			-	<?php echo BFTProQuickDDDate('to', $to, NULL, NULL, 2013, date("Y"));?>
			<input type="submit" value="<?php _e('Refresh report', 'bftpro')?>"></p>
	</form>
	
	<?php if(!empty($_GET['newsletter_id'])):?>
		<p><?php printf(__('Showing reports only for newsletter "%s".'), stripslashes($newsletter->subject))?> <a href="admin.php?page=bftpro_nl_report"><?php _e('Clear this filter and show reports for all newsletters', 'bftpro')?></a></p>
	<?php endif;?>
	
	<?php if(!sizeof($newsletters)):?>
		<p><?php _e('There are no newsletter campaigns.', 'bftpro')?></p></div>
	<?php return false;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('Email subject','bftpro')?></th><th><?php _e('Num. sent', 'bftpro')?></th>
		<th><?php _e('Num. read', 'bftpro')?></th><th><?php _e('Open rate', 'bftpro')?></th>
		<?php do_action('bftrpo_newsletter_report_extra_th');?>	</tr>
		<?php foreach($newsletters as $newsletter):
			$class = ('alternate' == @$class) ? '' : 'alternate';
			if(!empty($_GET['newsletter_id']) and $_GET['newsletter_id']!=$newsletter->id) continue;?>
			<tr class="<?php echo $class;?>"><td><?php echo stripslashes($newsletter->subject)?><br>
			<a href="#" onclick="arigatoProByDomain('<?php echo $from?>', '<?php echo $to?>', '<?php echo $newsletter->id?>');return false;"><?php _e('Stats per email domain', 'bftpro');?></a></td><td><?php
			if($newsletter->num_sent) echo '<a href="admin.php?page=arigatopro_sent_nl_log&id='.$newsletter->id.'&from='.$from.'&to='.$to.'" target="_blank">'; 
			echo $newsletter->num_sent;
			if($newsletter->num_sent)  echo '</a>'; ?></td>
			<td><?php if($newsletter->num_read) echo '<a href="admin.php?page=arigatopro_sent_nl_log&id='.$newsletter->id.'&read=1&from='.$from.'&to='.$to.'" target="_blank">';  
			echo $newsletter->num_read;
			if($newsletter->num_read)  echo '</a>';?></td><td><?php echo $newsletter->open_rate?>%</td>
			<?php do_action('bftrpo_newsletter_report_extra_td', $newsletter, $from, $to);?></tr>
			<tr class="<?php echo $class?>" style="display:none;" id="newsletterByDomain<?php echo $newsletter->id?>"><td colspan="6" id="newsletterByDomainTD<?php echo $newsletter->id?>"><?php _e('Please wait...', 'bftpro');?></td></tr>
		<?php endforeach;?>	
	</table>
</div>

<script type="text/javascript" >
function arigatoProByDomain(from, to, id) {
	data = {'action' : 'arigatopro_nl_by_domain', 'from' : from, 'to' : to, 'id' : id};
	jQuery('#newsletterByDomain' + id).show();
	jQuery.post('<?php echo admin_url("admin-ajax.php");?>', data, function(msg){		
		jQuery('#newsletterByDomainTD' + id).html(msg);		
	});
}
</script>