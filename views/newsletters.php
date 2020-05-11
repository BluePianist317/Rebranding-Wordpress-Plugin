<style type="text/css">
<?php bftpro_resp_table_css(600);?>
</style>

<h1><?php _e("Your Newsletters", 'bftpro')?></h1>

<?php bftpro_display_alerts(); ?>

<p><a href="admin.php?page=bftpro_newsletters&do=add"><?php _e("Create a newsletter (standard editor)", 'bftpro')?></a>
| <a href="admin.php?page=bftpro_choose_preset&from_page=nl"><?php _e("Create a newsletter from preset", 'bftpro')?></a></p>

<?php if(sizeof($newsletters)):?>
<form method="post">
<table class="widefat arigato-pro-table">
	<thead>
		<tr><th><input type="checkbox" onclick="bftproSelectNLs(this);"></th>
		<th><?php _e("Subject", 'bftpro')?></th><th><?php _e("Sending To", 'bftpro')?></th><th><?php _e("Cteated on", 'bftpro')?></th><th><?php _e("Status", 'bftpro')?></th>
		<th><?php _e("Last sent on", 'bftpro')?></th>
		<th><?php _e('Reports', 'bftpro')?></th>
		<th><?php _e("Send or Edit", 'bftpro')?></th></tr>
	</thead>	
	
	<tbody>
	<?php foreach($newsletters as $newsletter):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>">
		<td><input type="checkbox" name="nl_ids[]" value="<?php echo $newsletter->id?>" class="bftpro_chk" onclick="bftproShowHideMassButtons();"></td>
		<td><strong><?php echo stripslashes($newsletter->subject);?></strong>
		<?php if($newsletter->status != 'in progress' and $newsletter->from_mail_id == 0):?>
			<br><a href="<?php echo wp_nonce_url('admin.php?page=bftpro_newsletters&clone=1&id='.$newsletter->id, 'bftpro_newsletters');?>" onclick="return confirm('<?php _e('Are you sure?', 'bftpro');?>');"><?php _e('Copy/Clone', 'bftpro');?></a>
		<?php endif;
		if(!empty($newsletter->description)) echo wpautop(stripslashes($newsletter->description));?></td>
		<td><?php echo empty($newsletter->list_name)?__("Not selected", 'bftpro'):"<a href='admin.php?page=bftpro_subscribers&id=".$newsletter->list_id."'>".stripslashes($newsletter->list_name)."</a>"?></td>
		<td><?php echo date($dateformat, strtotime($newsletter->date_created))?></td>
		<td><?php if($newsletter->status == 'in progress'):
		  // in progress, but maybe scheduled
		  if($newsletter->is_scheduled and strtotime($newsletter->scheduled_for) > current_time('timestamp')): 
		    printf(__('Scheduled for %s', 'bftpro'), date_i18n($dateformat, strtotime($newsletter->scheduled_for)));
		  else: echo "<a href='admin.php?page=bftpro_nl_log&id=".$newsletter->id."'>".__('In progress', 'bftpro')."</a>"; endif;		 
		else: echo $newsletter->status; endif;?></td>
		<td><?php echo empty($newsletter->date_last_sent) ? '-' : date($dateformat, strtotime($newsletter->date_last_sent))?></td>
		<td><a href="admin.php?page=bftpro_nl_report&newsletter_id=<?php echo $newsletter->id?>"><?php _e('view reports', 'brtpro')?></a><br>
		<?php printf(__('Sent to %d users.', 'bftpro'), $newsletter->num_sent);
		printf("<br>".__('%d users unsubscribed.', 'bftpro'), $newsletter->num_unsubs); 
		printf("<br>".__("%d%% opened", 'bftpro'), $newsletter->percent_read);?></td>
		<td><a href="admin.php?page=bftpro_newsletters&do=edit&id=<?php echo $newsletter->id?>"><?php _e("Send or Edit", 'bftpro');?></a></td></tr>
		<?php if($newsletter->is_global):?>
			<tr class="<?php echo $class?>"><td colspan="8"><?php _e('This is a global newsletter that will be sent to all your mailing lists.', 'bftpro')?> <?php echo $newsletter->togo_str;?></td></tr>
		<?php endif;
		if($newsletter->from_mail_id and $newsletter->has_date_limit):?>
			<tr class="<?php echo $class?>"><td colspan="8"><?php printf(__('This newsletter was automatically generated from autoresponder email message %1$s from campaign <a href="%2$s">%3$s</a>. It will be sent only to subscribers registered on or before %4$s.', 'bftpro'), stripslashes($newsletter->from_mail_subject), 'admin.php?page=bftpro_ar_mails&campaign_id=' . $newsletter->from_campaign_id, stripslashes($newsletter->from_campaign_name), date_i18n($dateformat, strtotime($newsletter->date_limit)))?> </td></tr>
		<?php endif;?>
	<?php endforeach;?>
	</tbody>
</table>

<p align="center" id="bftproBulkButtons" style="display:none;">
	<input type="button" value="<?php _e('Delete selected newsletters', 'bftpro');?>" onclick="bftproConfirmMassDelete(this.form);" class="button"> 
	<input type="hidden" name="mass_delete" value="0">
</p>
<?php wp_nonce_field('bftpro_newsletters');?>
</form>
<?php else:?>
	<p><?php _e("There are no newsletters yet.", 'bftpro')?></p>
<?php endif;?>	

<script type="text/javascript" >
function bftproSelectNLs(mainChk) {
	jQuery('.bftpro_chk').each(function(i, chk) {
			if(mainChk.checked) jQuery(this).attr('checked', 'checked');
			else jQuery(this).removeAttr('checked');
		});
	bftproShowHideMassButtons();	
}

function bftproShowHideMassButtons() {
	var anyChecked = false;
	jQuery('.bftpro_chk').each(function(i, chk) {
		if(chk.checked) anyChecked = true;
	});
	
	if(anyChecked) jQuery('#bftproBulkButtons').show();
	else jQuery('#bftproBulkButtons').hide();
}

function bftproConfirmMassDelete(frm) {
	if(confirm("<?php _e('Are you sure?', 'bftpro');?>")) {
		frm.mass_delete.value=1;
		frm.submit();
	}
}

<?php bftpro_resp_table_js();?>
</script>