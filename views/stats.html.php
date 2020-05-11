<h2 class="nav-tab-wrapper">
	<a class='nav-tab nav-tab-active'><?php _e('Performance Stats', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=arigatopro_stats&tab=unsubscribe'><?php _e('Unsubscribe Stats', 'watupro')?></a>
</h2>

<div class="wrap">
	<form method="post">		
		<p><?php _e('From:', 'bftpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($from_date))?>" class="bftproDatePicker" id="bftproFromDate">
		<input type="hidden" name="from_date" value="<?php echo $from_date?>" id="alt_bftproFromDate">
		<?php _e('To:', 'bftpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($to_date))?>" class="bftproDatePicker" id="bftproToDate">
		<input type="hidden" name="to_date" value="<?php echo $to_date?>" id="alt_bftproToDate">
		<input type="submit" value="<?php _e('Reload Reports', 'bftpro')?>"></p>
	</form>
	
	<h2><?php _e('Most Active Marketing Campaigns / Autoresponders', 'bftpro');?></h2>
	
	<?php if(count($campaigns)):?>
		<table class="widefat">
			<tr><th><?php _e('Campaign name', 'bftpro');?></th><th><?php _e('Emails sent', 'bftpro');?></th><th><?php _e('Emails opened*', 'bftpro');?></th>
				<?php if(!empty($clicks_counted)):?><th><?php _e('Links clicked', 'bftpro');?></th><?php endif;?>
				<th><?php _e('Unsubscribed', 'bftpro');?></th></tr>
			<?php foreach($campaigns as $campaign):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>" id="campaignRow<?php echo $campaign->id?>">
					<td><a href="#" id="campaignLnk<?php echo $campaign->id?>" onclick="arigatoPROCampaignStats(<?php echo $campaign->id?>, '<?php echo $from_date?>', '<?php echo $to_date?>', '<?php echo $class?>');return false;">+</a>
					<a href="admin.php?page=bftpro_ar_campaigns&do=edit&id=<?php echo $campaign->id?>" target="_blank"><?php echo stripslashes($campaign->name);?></a></td>
					<td><?php echo $campaign->sentmails?></td>
					<td><?php printf(__('%d (%d%%)', 'bftpro'), $campaign->num_opens, $campaign->percent_open);?></td>					
					<?php if(!empty($clicks_counted)):?><td><?php echo $campaign->num_clicks;?></td><?php endif;?>
					<td><?php printf(__('%d (%d%%)', 'bftpro'), $campaign->num_unsubs, $campaign->percent_unsub);?></td>
				</tr>
			<?php endforeach;?>	
		</table>
		<p><i><?php _e('* "Emails opened" stats are not reliable and may show lower than the real number of opened mails.', 'bftpro');?></i></p>
	<?php else:?>
		<p><?php _e('No autoresponder messages have been sent out in the selected period.', 'bftpro');?></p>
	<?php endif;?>
	
	<p>&nbsp;</p>
	
	<h2><?php _e('Top Newsletters', 'bftpro');?></h2>
	
	<?php if(count($newsletters)):?>
		<table class="widefat">
			<tr><th><?php _e('Subject', 'bftpro');?></th><th><?php _e('Emails sent', 'bftpro');?></th><th><?php _e('Emails opened*', 'bftpro');?></th>
				<?php if(!empty($clicks_counted)):?><th><?php _e('Links clicked', 'bftpro');?></th><?php endif;?>
				<th><?php _e('Unsubscribed', 'bftpro');?></th></tr>
			<?php foreach($newsletters as $newsletter):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><a href="admin.php?page=bftpro_newsletters&do=edit&id=<?php echo $newsletter->id?>" target="_blank"><?php echo stripslashes($newsletter->subject);?></a></td>
					<td><?php echo $newsletter->sentmails?></td>
					<td><?php printf(__('%d (%d%%)', 'bftpro'), $newsletter->num_opens, $newsletter->percent_open);?></td>					
					<?php if(!empty($clicks_counted)):?><td><?php echo $newsletter->num_clicks;?></td><?php endif;?>
					<td><?php printf(__('%d (%d%%)', 'bftpro'), $newsletter->num_unsubs, $newsletter->percent_unsub);?></td>
				</tr>
			<?php endforeach;?>	
		</table>
		<p><i><?php _e('* "Emails opened" stats are not reliable and may show lower than the real number of opened mails.', 'bftpro');?></i></p>
	<?php else:?>
		<p><?php _e('No newsletters have been sent out in the selected period.', 'bftpro');?></p>
	<?php endif;?>
	
	<p>&nbsp;</p>
	
	<h2><?php _e('Top Subscribers', 'bftpro');?></h2>
	
	<?php if(count($users)):?>
		<table class="widefat">
			<tr><th><?php _e('User Email', 'bftpro')?></th><th><?php _e('Mailing list', 'bftpro');?></th><th><?php _e('Subscribed on', 'bftpro');?></th>
			<th><?php _e('Emails sent', 'bftpro');?></th><th><?php _e('Emails opened*', 'bftpro');?></th>
			<?php if(!empty($clicks_counted)):?><th><?php _e('Links clicked', 'bftpro');?></th><?php endif;?></tr>
			<?php foreach($users as $user):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><?php echo $user->email;?></td>
					<td><?php echo stripslashes($user->list_name);?></td>
					<td><?php echo date_i18n($dateformat, strtotime($user->date));?></td>
					<td><?php echo $user->cnt_mails;?></td>
					<td><?php echo $user->opens;?></td>
					<?php if(!empty($clicks_counted)):?><td><?php echo $user->num_clicks;?></td><?php endif;?>
				</tr>
			<?php endforeach;?>	
		</table>
		<p><i><?php _e('* "Emails opened" stats are not reliable and may show lower than the real number of opened mails.', 'bftpro');?></i></p>
	<?php else:?>
		<p><?php _e('No subscribers have received emails in the selected period.', 'bftpro');?></p>
	<?php endif;?>
	
	<?php if(!empty($clicks_counted)):?>
		<h2><?php _e('Top Trackable Links', 'bftpro');?></h2>
	
		<?php if(count($links)):?>
			<table class="widefat">
				<tr><th><?php _e('Label', 'bftpro')?></th><th><?php _e('Included in', 'bftpro');?></th><th><?php _e('Clicks', 'bftpro');?></th>
				<th><?php _e('Sent', 'bftpro');?></th></tr>
				<?php foreach($links as $link):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>">
						<td><?php echo stripslashes($link->label);?></td>
						<td><?php echo stripslashes($link->in_emails_str);?></td>						
						<td><?php echo $link->clicks;?></td>
						<td><?php printf(__('%d times', 'bftpro'), $link->total_sent);?></td>						
					</tr>
				<?php endforeach;?>	
			</table>			
		<?php else:?>
			<p><?php _e('No trackable links have been clicked in the selected period.', 'bftpro');?></p>
		<?php endif;
	 endif;?>
	 
	 <p>&nbsp;</p>
	 <h2><?php _e('Subscribers by Source', 'bftpro');?></h2>
	 
	 <table cellpadding="20"><tr><td><span class="pie"><?php foreach($sources as $cnt=>$source):
	 	if($cnt) echo ",";
	 	echo $source->cnt;
	 endforeach;?></span></td>
	 <td><?php foreach($sources as $cnt=>$source):
	 $g = round(($cnt / $num_sources ) * 255);
	 $color = "rgb(200, " . $g . ", 0)";?>
	 	<div style="float:left;clear:both;width:100%;">
	 		<div style="float:left;margin:5px;width:30px;height:30px;background:<?php echo $color;?>;"></div>
	 		<div style="margin:5px;float:left;"><?php 
			switch($source->source) {
				case '_admin': $source_txt = __('Added by admin', 'bftpro'); break;
				case '_import': $source_txt = __('Imported from CSV', 'bftpro'); break;
				case '_auto': $source_txt =  __('Auto-subscribed WP user', 'bftpro'); break;
				case '_email': $source_txt = __('Susbcribed by email', 'bftpro'); break;
				case '': $source_txt = __('Other (n/a)', 'bftpro'); break;
				default: $source_txt = $source->source; break;
			}	 		
	 		printf(__('%s (%d subscribers)', 'bftpro'), stripslashes($source_txt), $source->cnt)?></div>
	 	</div>	
	 <?php endforeach;?></td></tr></table>
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

function arigatoPROCampaignStats(id, fromDate, toDate, cls) {
	// figure out status - do we have to remove or load data
	var statusChar = jQuery('#campaignLnk' + id).text();
	if(statusChar == '-') {
		jQuery('#campaignLnk' + id).text('+');
		jQuery('.campaign-sub-row-' + id).hide();
		return true;
	}	
	
	// save ajax requests and DB queries if the user is just clicking +-
	if(jQuery('.campaign-sub-row-' + id).length) {
		jQuery('.campaign-sub-row-' + id).show();
		jQuery('#campaignLnk' + id).text('-');
		return true;	
	}
	
	// ajax call to get the stats
	var url = "<?php echo admin_url('admin-ajax.php')?>";
	data = {'action': 'arigatopro_campaign_stats', 'id' : id, 'from_date': fromDate, 'to_date' : toDate, 'class' : cls};
	
	jQuery.post(url, data, function(msg) {
		jQuery("#campaignRow" + id  ).closest( "tr" ).after(msg);
		jQuery('#campaignLnk' + id).text('-');
	});	
}
</script>