<h2 class="nav-tab-wrapper">
	<a class='nav-tab' href='admin.php?page=arigatopro_stats'><?php _e('Performance Stats', 'watupro')?></a>
	<a class='nav-tab nav-tab-active'><?php _e('Unsubscribe Stats', 'watupro')?></a>	
</h2>

<div class="wrap">
	<form method="post">		
		<p><?php _e('From:', 'bftpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($from_date))?>" class="bftproDatePicker" id="bftproFromDate">
		<input type="hidden" name="from_date" value="<?php echo $from_date?>" id="alt_bftproFromDate">
		<?php _e('To:', 'bftpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($to_date))?>" class="bftproDatePicker" id="bftproToDate">
		<input type="hidden" name="to_date" value="<?php echo $to_date?>" id="alt_bftproToDate">
		<input type="submit" value="<?php _e('Reload Reports', 'bftpro')?>" class="button button-primary"></p>
	</form>
	
	<h2><?php _e('Most unsubscribed users per newsletter', 'bftpro');?></h2>
	
	<?php if(count($newsletters)):?>
		<table class="widefat">
			<tr><th><?php _e('Subject', 'bftpro');?></th><th><?php _e('Emails sent', 'bftpro');?></th><th><?php _e('Unsubscribed users', 'bftpro');?></th>				
				<th><?php _e('% Unsubscribed', 'bftpro');?></th></tr>
			<?php foreach($newsletters as $newsletter):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><a href="admin.php?page=bftpro_newsletters&do=edit&id=<?php echo $newsletter->id?>"><?php echo stripslashes($newsletter->subject);?></a></td>
					<td><?php echo $newsletter->num_sent?></td>
					<td><?php echo $newsletter->cnt_unsubs?></td>					
					<td><?php echo $newsletter->perc_unsub?></td>				
				</tr>
			<?php endforeach;?>	
		</table>		
	<?php else:?>
		<p><?php _e('There were no unsubscribed users from your newsletters sent in the selected period.', 'bftpro');?></p>
	<?php endif;?>
	
	<p>&nbsp;</p>
	
	<h2><?php _e('Most unsubscribed users per autoresponder campaign', 'bftpro');?></h2>
	
	<?php if(count($campaigns)):?>
		<table class="widefat">
			<tr><th><?php _e('Campaign name', 'bftpro');?></th><th><?php _e('Emails sent', 'bftpro');?></th><th><?php _e('Unsubscribed users', 'bftpro');?></th>				
				<th><?php _e('% Unsubscribed', 'bftpro');?></th></tr>
			<?php foreach($campaigns as $campaign):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><a href="admin.php?page=bftpro_ar_campaigns&do=edit&id=<?php echo $campaign->id?>"><?php echo stripslashes($campaign->name);?></a></td>
					<td><?php echo $campaign->num_sent?></td>
					<td><?php echo $campaign->cnt_unsubs?></td>					
					<td><?php echo $campaign->perc_unsub?></td>				
				</tr>
			<?php endforeach;?>	
		</table>		
	<?php else:?>
		<p><?php _e('There were no unsubscribed users from your autoresponder campaigns sent in the selected period.', 'bftpro');?></p>
	<?php endif;?>
	
	<p>&nbsp;</p>
	
	<h2><?php _e('Most unsubscribed users per individual autoresponder email', 'bftpro');?></h2>
	
	<?php if(count($armails)):?>
		<table class="widefat">
			<tr><th><?php _e('Subject', 'bftpro');?></th><th><?php _e('Emails sent', 'bftpro');?></th><th><?php _e('Unsubscribed users', 'bftpro');?></th>				
				<th><?php _e('% Unsubscribed', 'bftpro');?></th></tr>
			<?php foreach($armails as $armail):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><a href="admin.php?page=bftpro_ar_mails&do=edit&id=<?php echo $armail->id?>&campaign_id=<?php echo $armail->ar_id?>"><?php echo stripslashes($armail->subject);?></a></td>
					<td><?php echo $armail->num_sent?></td>
					<td><?php echo $armail->cnt_unsubs?></td>					
					<td><?php echo $armail->perc_unsub?></td>				
				</tr>
			<?php endforeach;?>	
		</table>		
	<?php else:?>
		<p><?php _e('There were no unsubscribed users from your autoresponder campaigns sent in the selected period.', 'bftpro');?></p>
	<?php endif;?>
	
	<p>&nbsp;</p>
	
	<?php if(!empty($unsubscribe_reasons) and !empty($reasons)):?>
		<table class="widefat">
			<thead>
				<tr><th><?php _e('Reason to unsubscribe', 'bftpro');?></th><th><?php _e('Num. Unsubscribed', 'bftpro');?></th><th><?php _e('% of all', 'bftpro');?></th></tr>
			</thead>
			<tbody>
				<?php foreach($reasons as $reason):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>">
						<td><?php echo stripslashes($reason['reason']);?></td>
						<td><?php echo $reason['num'];?></td>
						<td><?php printf(__('%d%%', 'bftpro'), $reason['percent']);?></td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	<?php endif;?>
	
	<p>&nbsp;</p>
</div>

<script type="text/javascript" >
jQuery(document).ready(function() {
    jQuery('.bftproDatePicker').datepicker({
    		dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',
         altFormat : 'yy-mm-dd'
    });
    
    jQuery(".bftproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
    });
    
    jQuery("span.pie").peity("pie", {
    	'radius' : 200, 
    	fill: function(_, i, all) {
	    var g = parseInt((i / all.length ) * 255)
	    return "rgb(200, " + g + ", 0)"
	  }
    });
});

// do not allow more than 90 days
function validatebftPROReports(frm) {
	var fromDate = new Date(frm.start_date.value);
	var toDate = new Date(frm.end_date.value);
	var oneDay = 24*60*60*1000;
	var diffDays = Math.round(Math.abs((fromDate.getTime() - toDate.getTime())/(oneDay)));
	
	if(diffDays > 90) {
		alert("<?php _e('You can run reports for maximum 90 days.','bftpro')?>");		
		return false;
	}
	
	return true;
}
</script>