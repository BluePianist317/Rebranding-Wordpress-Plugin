<div class="wrap">
	<h1><?php _e('Choose a preset', 'bftpro');?></h1>
	<?php if($_GET['from_page'] == 'ar'):?>
		<h3><?php printf(__('Creating new email message in marketing campaign "%s".'), stripslashes($campaign->name));?></h3>
		
		<p><a href="admin.php?page=bftpro_ar_mails&campaign_id=<?php echo $_GET['campaign_id']?>"><?php _e('Back to campaign', 'bftpro');?></a></p>
	<?php elseif($_GET['from_page'] == 'nl'): // newsletter?>
		<h3><?php _e('Creating a newsletter', 'bftpro');?></h3>
		<p><a href="admin.php?page=bftpro_newsletters"><?php _e('Back to newsletters', 'bftpro');?></a></p>
	<?php else:?>	
		<h3><?php _e('Creating a lead magnet', 'bftpro');?></h3>
		<p><a href="admin.php?page=bfti_magnets"><?php _e('Back to lead magnets', 'bftpro');?></a></p>
	<?php endif;?>
	
	<p><?php _e('Presets are predefined responsive designs which allow you to replace the content of the different blocks with your own content. You cannot delete or move the blocks directly. To do this you must edit the preset intself. Learn more at the bottom of this page.', 'bftpro');?></p>	
	<p><?php _e('Once a message is created from preset, the preset cannot be changed and the message cannot be edited via the standard editor. If you need to do this, you have to create a new message.', 'bftpro');?></p>
	<p><?php printf(__('If you have added or changed any presets in the presets directory, <a href="%s">click here</a> to import the changes in the system.', 'bftpro'), 'admin.php?page=bftpro_choose_preset&from_page='.$_GET['from_page'].'&campaign_id='.@$_GET['campaign_id'].'&fetch_presets=1');?></p>
	
	<div class="wrap" style="width:100%;float:left;">
	<?php foreach($presets as $preset):?>
		<div class="bftpro-choose-preset">
			<h2><?php echo stripslashes($preset->name)?></h2>
			<p><img src="<?php echo ($preset->is_gozaimasu and defined('GOZAIMASU_URL')) ?  GOZAIMASU_URL.'/presets/'.$preset->thumb : BFTPRO_URL.'/presets/'.$preset->thumb?>"></p>
			<p><?php if($_GET['from_page'] == 'ar'):?>
				<a href="admin.php?page=bftpro_ar_mails&do=add&campaign_id=<?php echo $campaign->id?>&preset_id=<?php echo $preset->id?>"><?php _e('Use this preset', 'bftpro');?></a>
			<?php elseif($_GET['from_page'] == 'nl'):?>
				<a href="admin.php?page=bftpro_newsletters&do=add&preset_id=<?php echo $preset->id?>"><?php _e('Use this preset', 'bftpro');?></a>
			<?php else:?>	
				<a href="admin.php?page=bfti_magnets&do=add&preset_id=<?php echo $preset->id?>"><?php _e('Use this preset', 'bftpro');?></a>
			<?php endif;?></p>
		</div>
	<?php endforeach;?>
	</div>
	
	<p>&nbsp;</p>
	<h2><?php _e('Editing Presets / Creating Your Own', 'bftpro');?></h2>
	
	<p><?php printf(__('Our current presets are built using <a href="%s" target="_blank">Foundation for Emails</a>. If you want to create your own presets we strongly recommend using this or similar framework to ensure your emails will be responsive on different devices.', 'bftpro'), 'http://foundation.zurb.com/emails/email-templates.html');?></p>
	
	<p><?php _e('To change the design of a preset you must create a new one with different name and place it in folder "presets" under arigato-pro folder. You should never edit the original files that come with the plugin because if you update to a new version your changes will be lost.', 'bftpro');?><br>
	<b><?php _e('It is your responsibility to keep backups of any custom preset files and thumbnails that you have added in the folder.', 'bftpro');?></b><br> </p>	
	
	<p><?php _e('All presets that are provided with Arigato PRO are expected to be responsive and look good on all devices. However we do not give any guarantees about this.', 'bftpro');?></p>
	
	<h2><?php _e('Important:', 'bftpro');?></h2>
	
	<p><?php _e('When you select to work with a preset, we load its preview on the admin page in your dashboard. Since these presets are complete HTML documents with CSS style definitions they may cause the menu links and the whole design of your admin page to look different while editing. This is normal and is nothing to worry about - once you leave this page your WP administration will look back to normally. These changes will not affect any other pages than the page with the preset editor.', 'bftpro');?></p>
</div>