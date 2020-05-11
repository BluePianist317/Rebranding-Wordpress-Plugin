<h2 class="nav-tab-wrapper">
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=main'><?php _e('Help / User Manual', 'bftpro')?></a>
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=troubleshooting'><?php _e('Troubleshooting', 'bftpro')?></a>
	<a class='nav-tab nav-tab-active'><?php _e('Cron Job Log', 'bftpro')?></a>	
	<a class='nav-tab' href='admin.php?page=bftpro_help&tab=raw_log'><?php _e('Raw Email Log', 'bftpro')?></a>
</h2>

<div class="wrap">
	<div class="postbox wp-admin" style="padding:20px;">
		<form method="post">
			<p><?php _e('Select date:', 'bftpro')?> <?php echo BFTProQuickDDDate("date", $date, null, null, 2012, date("Y"))?> <input type="submit" value="<?php _e('View Log', 'bftpro')?>">
			&nbsp;			
			<?php _e('Automatically cleanup old logs after', 'bftpro')?> <input type="text" size="4" name="cleanup_days" value="<?php echo $cleanup_cron_log?>"> <?php _e('days', 'bftpro')?> <input type="submit" name="cleanup" value="<?php _e('Set Cleanup', 'bftpro')?>"> </p>
		</form>
		
		<p>
		<?php if(empty($log->id)):?>
			<?php _e('No logs were found on the selected date', 'bftpro')?>			
		<?php else:
			echo nl2br($log->log);
		endif;?>
		</p>
	</div>
</div>	