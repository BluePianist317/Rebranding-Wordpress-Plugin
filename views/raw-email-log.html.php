<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a class='nav-tab' href='admin.php?page=bftpro_help&tab=main'><?php _e('Help / User Manual', 'bftpro')?></a>
		<a class='nav-tab' href='admin.php?page=bftpro_help&tab=troubleshooting'><?php _e('Troubleshooting', 'bftpro')?></a>
		<a class='nav-tab'  href='admin.php?page=bftpro_help&tab=error_log'><?php _e('Cron Job Log', 'bftpro')?></a>	
		<a class='nav-tab nav-tab-active'><?php _e('Raw Email Log', 'bftpro')?></a>
	</h2>

	<div class="postbox wp-admin" style="padding:20px;">
		<form method="post">
			<p><label><?php _e('Log date:', 'bftpro')?></label> <input type="text" name="date" class="datepicker" value="<?php echo $date?>">
			<?php _e('Filter by receiver:', 'bftpro');?> <input type="text" name="receiver" value="<?php echo empty($_POST['receiver']) ? '' : $_POST['receiver']?>">
			<input type="checkbox" name="show_debug" value="1" <?php if(!empty($_POST['show_debug'])) echo "checked"?>> <?php _e('Show debug columns', 'bftpro');?>
			<input type="submit" value="<?php _e('Show log', 'bftpro')?>">
			<br>
			<?php _e('Automatically cleanup old logs after', 'bftpro')?> <input type="text" size="4" name="cleanup_days" value="<?php echo $cleanup_raw_log?>"> <?php _e('days', 'bftpro')?> <input type="submit" name="cleanup" value="<?php _e('Set Cleanup', 'bftpro')?>"> </p>
		</form>		
		
		<?php if(!sizeof($emails)):?>
			<p><?php _e('No emails have been sent on the selected date.', 'bftpro')?></p>
		<?php else:?>
			<table class="widefat">
				<tr><th><?php _e('Time', 'bftpro')?></th><th><?php _e('Sender', 'bftpro')?></th><th><?php _e('Receiver', 'bftpro')?></th>
<th><?php _e('Subject', 'bftpro')?></th><th><?php _e('Response from the mailing server', 'bftpro')?></th>
				<?php if(!empty($_POST['show_debug'])):?>
					<th><?php _e('Unique ID', 'bftpro');?></th>
					<th><?php _e('Microtime', 'bftpro');?></th>
				<?php endif;?></tr>
				<?php foreach($emails as $email):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td><?php echo date('H:i', strtotime($email->datetime))?></td>
					<td><?php echo stripslashes($email->sender)?></td>
					<td><?php echo stripslashes($email->receiver)?></td>
					<td><?php echo stripslashes($email->subject)?></td>
					<td><?php echo $email->status?></td>
					<?php if(!empty($_POST['show_debug'])):?>
						<th><?php echo $email->unique_id;?></th>
						<th><?php echo $email->micro_time;?></th>
					<?php endif;?></tr>
				<?php endforeach;?>
			</table>
		<?php endif;?>
	</div>
</div>	

<script type="text/javascript" >
jQuery(function(){
	jQuery('.datepicker').datepicker({dateFormat: "yy-m-d"});
});
</script>