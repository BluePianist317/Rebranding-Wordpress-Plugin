<style type="text/css">
<?php bftpro_resp_table_css(600);?>
</style>

<div class="wrap">

	<h1><?php _e("Your Autoresponder Campaigns", 'bftpro')?></h1>
	
	<?php bftpro_display_alerts(); ?>
	
	<p><a href="admin.php?page=bftpro_ar_campaigns&do=add"><?php _e("Click here to create a marketing campaign", 'bftpro')?></a>
	| <a href="#" onclick="jQuery('#bftproCampaignImport').show();return false;"><?php _e('Import campaign', 'bftpro');?></a></p>
	
	<div id="bftproCampaignImport" style="display:none;">
	  <form method="post" enctype="multipart/form-data">
	     <p><?php _e('Upload .txt file:', 'bftpro')?> <input type="file" name="import_file">
	     <input type="submit" name="import" value="<?php _e('Import', 'bftpro')?>" class="button button-primary">
	     <input type="button" class="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="jQuery('#bftproCampaignImport').hide();">
	     <?php wp_nonce_field('bftpro_import');?>
	  </form>
	  <p><?php _e("You can import a campaign file exported from Arigato PRO. This is internal file format that can't be edited outside.", 'bftpro')?></p>
	</div>
	
	<?php if(!empty($_GET['list_id'])):?>
	<p><strong><?php _e(sprintf("The autoresponder campaigns are currently filtered so only those assigned to the mailing list \"%s\" are shown.", $filter_list->name), 'bftpro')?> <a href="admin.php?page=bftpro_ar_campaigns"><?php _e("Remove filter and show all campaigns", 'bftpro')?></a></strong></p>
	<?php endif;?>
	
	<?php if(sizeof($campaigns)):?>
	<table class="widefat arigato-pro-table">
		<thead>
			<tr><th><?php _e("Name and description", 'bftpro')?></th><th><?php _e("Mailing lists", 'bftpro')?></th>
			<th><?php _e("Email Messages", 'bftpro')?></th><th><?php _e('Reports', 'bftpro')?></th><th><?php _e("Edit", 'bftpro')?></th></tr>
		</thead>	
		
		<tbody>
		<?php foreach($campaigns as $campaign):
			$class = ('alternate' == @$class) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class?>"><td><h3><?php echo stripslashes($campaign->name)?></h3>
			<?php if(!empty($campaign->description)): echo "<p>".stripslashes($campaign->description)."</p>"; endif;?></td>
			<td><?php if(sizeof($campaign->lists)):
				foreach($campaign->lists as $lct=>$list):
				if($lct>0): echo ", "; endif;?>
				<a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>"><?php echo stripslashes($list->name);?></a>			
			<?php endforeach; 
			else:
				 echo __("None yet", 'bftpro'); 
			endif;?></td>
			<td><a href="admin.php?page=bftpro_ar_mails&campaign_id=<?php echo $campaign->id?>"><?php _e("Manage", 'bftpro');?></a>
			<?php if($campaign->num_mails) printf(__('(%d messages)', 'bftpro'), $campaign->num_mails);
			else _e('(No messages yet)', 'bftpro')?></td>
			<td><?php if($campaign->num_mails):?>
				<a href="admin.php?page=bftpro_ar_report&campaign_id=<?php echo $campaign->id?>"><?php _e('View Reports', 'bftpro')?></a> <br>
				<?php printf(__("%d%% opened", 'bftpro'), $campaign->percent_read);?>
			<?php else: _e('N/a', 'bftpro'); endif;?></td>
			<td><a href="admin.php?page=bftpro_ar_campaigns&do=edit&id=<?php echo $campaign->id?>"><?php _e("Edit", 'bftpro');?></a>
			| <a href="admin.php?page=bftpro_ar_campaigns&noheader=1&campaign_id=<?php echo $campaign->id?>&export=1"><?php _e('Export', 'bftpro');?></a></td></tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<?php else:?>
		<p><?php _e("There are no marketing campaigns yet.", 'bftpro')?></p>
	<?php endif;?>
	
	<p><?php _e('<b>Exporting a campaign:</b> this will let you download internal format text file with the campaign settings and messages. The file can be used to import the campaign into the same site or another site running Arigato PRO. Do not edit the file unless you understand PHP serialized data.', 'bftpro');?></p>
</div>
	
<script type="text/javascript">
<?php bftpro_resp_table_js();?>
</script>