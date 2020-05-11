<table class="widefat">
	<tr><th><?php _e('Domain', 'bftpro');?></th><th><?php _e('Num. sent', 'bftpro');?></th>
		<th><?php _e('Num. read.', 'bftpro');?></th><th><?php _e('Open rate', 'bftpro');?></th></tr>
		<?php foreach($domains as $domain => $stats):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>"><td><?php 
		echo ($domain == 'other') ? __('Other', 'bftpro') : (empty($domain) ? __('Deleted users', 'bftpro'): $domain);?></td><td><?php echo $stats['sent_emails']?></td>
		<td><?php echo $stats['read_emails']?></td><td><?php echo $stats['open_rate']?>%</td></tr>
	<?php endforeach;?>	
</table>