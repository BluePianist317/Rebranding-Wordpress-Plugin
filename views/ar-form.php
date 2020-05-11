<h1><?php echo empty($_GET['id'])?__("Add", 'bftpro'):__("Edit", 'bftpro')?> <?php _e("Autoresponder Campaign", 'bftpro')?></h1>

<?php bftpro_display_alerts(); ?>

<div class="postbox wp-admin" style="padding:5px;">
<form method="post" class="bftpro" onsubmit="return validateBFTProAR(this);" id="BFTProARForm">
	<fieldset>
		<legend><?php _e("Autoresponder Marketing Campaign Details", 'bftpro')?></legend>
		<p><label><?php echo __("Campaign Name:", 'bftpro')?></label> <input type="text" name="name" value="<?php echo stripslashes(@$campaign->name)?>" size="40"></p>
		<p><label><?php echo __("Sender Name/Email:", 'bftpro')?></label> <input type="text" name="sender" value="<?php echo stripslashes(empty($campaign->sender)?get_option('bftpro_sender'):$campaign->sender)?>" size="40"></p>
		<p class="help"><?php _e("Please use either only valid email address or name/email combination in the format: <b>Name &lt;email@domain.com&gt;</b>", 'bftpro')?></p>
		<p><label><?php echo __("Optional Description:", 'bftpro')?></label> <textarea name="description" rows="5" cols="40"><?php echo stripslashes(@$campaign->description)?></textarea></p>
	</fieldset>
	
	<fieldset>
		<legend><?php _e("Assign To Mailing Lists", 'bftpro')?></legend>
		<?php foreach($lists as $list):?>
			<p><input type="checkbox" name="list_ids[]" value="<?php echo $list->id?>" <?php if(!empty($list_ids) and in_array($list->id, $list_ids)) echo "checked"?>> <?php echo stripslashes($list->name);?></p>
		<?php endforeach;?>
	</fieldset>	
	
	<p>&nbsp;</p>
	<p><?php if(empty($_GET['id'])):?>
		<input type="submit" name="ok" value="<?php echo __('Add Autoresponder Campaign', 'bftpro');?>">
	<?php else:?>
		<input type="submit" name="ok" value="<?php echo __('Save Campaign', 'bftpro');?>">
		<input type="button" value="<?php echo __('Delete Campaign', 'bftpro');?>" onclick="bftproConfirmDelete(this.form);">
		<input type="hidden" name="del" value="0">
	<?php endif;?>
	<input type="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="window.location='admin.php?page=bftpro_ar_campaigns'"></p>
</form>
</div>	

<script type="text/javascript" >
function validateBFTProAR(frm) {	
	if(frm.name.value=="")
	{
		alert("<?php _e("Please provide a name for the marketing campaign", 'bftpro')?>");
		frm.name.focus();
		return false;
	}
	
	if(frm.sender.value=="")
	{
		alert("<?php _e("Please provide sender email for this email marketing campaign", 'bftpro')?>");
		frm.sender.focus();
		return false;
	}
	
	return true;
}

function bftproConfirmDelete(frm) {
	if(confirm("<?php _e('Are you sure?', 'bftpro')?>")) {
		frm.del.value=1;
		frm.submit();
	}
}
</script>