<style type="text/css">
<?php bftpro_resp_table_css(900);?>
</style>
<div class="wrap">
	<h1><?php printf(__("Subscribers In Mailing List '%s'", 'bftpro'), stripslashes($list->name));?></h1>
	
	<?php bftpro_display_alerts(); ?>
	
	<p><a href="admin.php?page=bftpro_mailing_lists&do=edit&id=<?php echo $list->id?>"><?php _e('Edit this mailing list', 'bftpro');?></a>
	| <a href="admin.php?page=bftpro_mailing_lists"><?php _e('Manage mailing lists', 'bftpro')?></a>
	| <a href="admin.php?page=bftpro_ar_campaigns"><?php _e('Manage autoresponder campaigns', 'bftpro')?></a></p>	
	
	<div class="postbox wp-admin">
		<p class="inside"><a href="#" onclick="jQuery('#userFilters').toggle();return false;"><?php _e('Show/Hide Search Form', 'bftpro')?></a></p>
		<div class="inside" id="userFilters" style='display:<?php echo $any_filters?'block':'none'?>;'>
			<form class="bftpro" method="get">
			<input type="hidden" name="id" value="<?php echo $list->id?>">
			<input type="hidden" name="page" value="bftpro_subscribers">
				<p><label><?php _e('Filter by email', 'bftpro')?>:</label> <input type="text" name="filter_email" value="<?php echo @$_GET['filter_email']?>"></p>
				<p><label><?php _e('Filter by name', 'bftpro')?>:</label> <input type="text" name="filter_name" value="<?php echo @$_GET['filter_name']?>"></p>
				<div><label><?php _e('Filter by status', 'bftpro')?>:</label> <select name="filter_status">
				<option value="-1" <?php if(!isset($_GET['filter_status']) or $_GET['filter_status']=='-1') echo "selected"?>><?php _e("Any Status", 'bftpro')?></option>
				<option value="1" <?php if(isset($_GET['filter_status']) and $_GET['filter_status']=='1') echo "selected"?>><?php _e("Active", 'bftpro')?></option>	
				<option value="0" <?php if(isset($_GET['filter_status']) and $_GET['filter_status']==='0') echo "selected"?>><?php _e("Inactive", 'bftpro')?></option>
				<option value="-2" <?php if(!empty($unsubscribed_filter)) echo "selected"?>><?php _e("Unsubscribed", 'bftpro')?></option>
				</select></div>
				<p><label><?php _e('Filter by source:', 'bftpro')?></label> <select name="filter_source">
					<option value="" <?php if(empty($_GET['filter_source'])) echo "selected"?>><?php _e("Any source", 'bftpro')?></option>
					<option value="_web" <?php if(@$_GET['filter_source']=='_web') echo "selected"?>><?php _e("Subscribed by web form", 'bftpro')?></option>
					<option value="_admin" <?php if(@$_GET['filter_source']=='_admin') echo "selected"?>><?php _e("Added by admin", 'bftpro')?></option>
					<option value="_import" <?php if(@$_GET['filter_source']=='_import') echo "selected"?>><?php _e("Imported from CSV", 'bftpro')?></option>
					<option value="_auto" <?php if(@$_GET['filter_source']=='_auto') echo "selected"?>><?php _e("Auto-subscribed WP user", 'bftpro')?></option>
					<option value="_email" <?php if(@$_GET['filter_source']=='_email') echo "selected"?>><?php _e("Subscribed by email", 'bftpro')?></option>
					<option value="_woo" <?php if(@$_GET['filter_source']=='_woo') echo "selected"?>><?php _e("Subscribed by WooCommerce", 'bftpro')?></option>
					<option value="_other" <?php if(@$_GET['filter_source']=='_other') echo "selected"?>><?php _e("Other sources", 'bftpro')?></option>
				</select></p>
				<p><label><?php _e('Signed up:', 'bftpro');?></label> <select name="signup_date_cond">
				   <option value=""><?php _e('Any time', 'bftpro');?></option>
					<option value="on" <?php if(!empty($_GET['signup_date_cond']) and $_GET['signup_date_cond']=='on') echo 'selected'?>><?php _e('On', 'bftpro');?></option>
					<option value="after" <?php if(!empty($_GET['signup_date_cond']) and $_GET['signup_date_cond']=='after') echo 'selected'?>><?php _e('After', 'bftpro');?></option>
					<option value="before" <?php if(!empty($_GET['signup_date_cond']) and $_GET['signup_date_cond']=='before') echo 'selected'?>><?php _e('Before', 'bftpro');?></option>
				</select>
				<input type="text" class="bftproDatePicker" name="sdate" id="bftproSignupDate" value="<?php echo empty($_GET['sdate']) ? '' : $_GET['sdate']?>">
				<input type="hidden" name="filter_signup_date" value="<?php echo empty($_GET['filter_signup_date']) ? '' : $_GET['filter_signup_date']?>" id="alt_bftproSignupDate"></p>
				
				<p><label><?php _e('Tagged as:', 'bftpro');?></label>
				<input type="text" name="filter_tags" value="<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"> 
				<select name="filter_tags_mode">
					<option value="any"><?php _e('have any of the tags', 'bftpro');?></option>
					<option value="all" <?php if(!empty($_GET['filter_tags_mode']) and $_GET['filter_tags_mode'] == 'all') echo 'selected';?>><?php _e('have all the tags', 'bftpro');?></option>
				</select>
				<br><em><?php _e('Multiple tags can be separated by comma. In this case select whether you want all or any of them to be matched by the filter.', 'bftpro');?></em><br></p>

				
				<?php foreach($fields as $field):?>
					<p><label><?php printf(__('Filter by %s', 'bftpro'), stripslashes($field->label))?>:</label>
					<?php if($field->ftype == 'checkbox'):?>
						<select name="filter_field_<?php echo $field->id?>">
							<option value=""><?php _e('Any state', 'bftpro');?></option>
							<option value="1" <?php if(@$_GET['filter_field_' . $field->id] == 1) echo 'selected'?>><?php _e('Checked', 'bftpro');?></option>
							<option value="-1" <?php if(@$_GET['filter_field_' . $field->id] == -1) echo 'selected'?>><?php _e('Not checked', 'bftpro');?></option>
						</select>
					<?php else: ?>
					<input type="text" name="filter_field_<?php echo $field->id?>" value="<?php echo esc_attr(@$_GET['filter_field_' . $field->id])?>">
					<?php endif;?>					
					</p>
				<?php endforeach;?>
				<p><label><?php _e('No. mails opened:', 'bftpro')?></label> <input type="text" name="readmails_from" size="4" value="<?php echo @$_GET['readmails_from']?>"> - <input type="text" name="readmails_to" size="4" value="<?php echo @$_GET['readmails_to']?>"> <?php _e('(from - to)', 'bftpro')?>
					<div class="help"><?php _e('Note that "No. mails opened" is not 100% reliable stat. It loads a small graphic in the email to mark which email is opened. But nowadays many email clients do not show the graphics by default, unless the user explicitly selects so.', 'bftpro');?></div>			
				</p>
				<?php do_action('bftpro-subcribers-filter-form');?>
				<p><input type="submit" value="<?php _e('Filter subscribers', 'bftpro');?>" class="button button-primary">
				<input type="button" value="<?php _e('Reset filters', 'bftpro')?>" onclick="window.location='admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>';" class="button"></p>
			</form>
		</div>
	</div>
	
	<p><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>&do=add"><?php _e("Add Subscriber", 'bftpro')?></a> | <a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>&do=import"><?php _e("Import Subscribers", 'bftpro')?></a>
	<?php if($cnt_users):?>
	| <a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>&export=1&noheader=1<?php echo BFTProLinkHelper::subscribers_filters()?>"><?php _e("Export Subscribers", 'bftpro');?></a> &nbsp;<?php printf(__('(The exported file will be <b>semicolon</b> delimited. See <a href="%s" target="_blank">how to open it</a>.)', 'bftpro'), 'http://www.ablebits.com/office-addins-blog/2014/05/01/convert-csv-excel/');?>
		<?php if(!empty($list->fee) and $list->fee > 0 and class_exists('BFTIList')):?>
			| <a href="admin.php?page=bfti_payments&list_id=<?php echo $list->id?>"><?php _e('View payments');?></a>
		<?php endif;?>
	<?php endif;?></p>
	
	<?php if($cnt_users):?>
		<form method="post" id="subscribersForm">
		<table class="widefat arigato-pro-table">
			<thead>
				<tr><td><input type="checkbox" onclick="bftproSelectAll(this);"></td><th><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?><?php echo BFTProLinkHelper::subscribers('name')?>"><?php _e('Name', 'bftpro')?></a></th><th><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?><?php echo BFTProLinkHelper::subscribers('email')?>"><?php _e('Email', 'bftpro')?></a></th>
				<th><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?><?php echo BFTProLinkHelper::subscribers('source')?>"><?php _e('Source', 'bftpro')?></a></th><th><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?><?php echo BFTProLinkHelper::subscribers('status')?>"><?php _e('Status', 'bftpro')?></a></th><th><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?><?php echo BFTProLinkHelper::subscribers('date')?>"><?php _e('Date Signed', 'bftpro')?></a></th>
				<th><?php _e('Emails sent', 'bftpro')?></th>
				<th><?php _e('Emails read', 'bftpro')?></th>
				<th><?php _e('Open rate', 'bftpro')?></th>	
				<?php if(sizeof($datas)):?>
					<th><?php _e('Custom fields', 'bftpro')?></th>
				<?php endif;?>				
				<th><?php _e('Edit/Delete', 'bftpro')?></th></tr>
			</thead>
			<tbody>
			<?php foreach($users as $user):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><input type="checkbox" name="ids[]" value="<?php echo $user['id']?>" class="userid" onclick="showHideDelBtn();"></td>
					<td><?php echo $user['name'];
					if(!empty($user['tags'])) echo '<br><em>'.sprintf(__('Tags: %s', 'bftpro'), $user['tags']).'</em>';?></td>
					<td><?php echo $user['email']?></td>
				<td><?php if(!empty($user['source'])):
						if(preg_match("/^http/i", $user['source'])) {
						$weburl = $user['source'];
						$user['source'] = '_web';						
					}
					switch($user['source']):
						case '_admin': _e('Added by admin', 'bftpro'); break;
						case '_import': _e('Imported from CSV', 'bftpro'); break;
						case '_auto': _e('Auto-subscribed WP user', 'bftpro'); break;
						case '_email': _e('Susbcribed by email', 'bftpro'); break;
						case '_woo': _e('WooCommerce purchase', 'bftpro'); break;
						case '_web': echo '<a href="'.$weburl.'" target="_blank">'.__('web form', 'bftpro').'</a>'; break;
						default: echo $user['source']; break;
					endswitch;
					if(!empty($user['form_name'])) echo '<br>'.stripslashes($user['form_name']);
					if(!empty($user['wp_user'])) echo '<br><a href="user-edit.php?user_id='.$user['wp_user_id'].'" target="_blank">'.$user['wp_user']->user_login.'</a>';
				endif; // end if source is available ?></td>
				<td><?php echo $user['status']?__('Active', 'bftpro'):__('Inactive', 'bftpro');
				if($user['unsubscribed']): echo ' '.__('(Unsubscribed)', 'bftpro');
					if(!empty($user['unsub_info_url'])): printf(__('<br>After reading: %s', 'bftpro'), '<a href="' . $user['unsub_info_url'].'" target="_blank">'.stripslashes($user['unsub_info_subject']).'</a>'); endif;
				endif; // end if unsubscribed
				if(!$user['status'] and !$user['unsubscribed'] and !empty($list->optin)):
					// inactive but not unsubscribed
					echo '<br><a href="admin.php?page=bftpro_subscribers&resend_activation=1&user_id='.$user['id'].'&id='.$list->id.'&offset='.$offset.BFTProLinkHelper::subscribers($orderby, false).'">'.__('Resend activation email', 'bftpro').'</a>';
				endif;
				if(!$user['status'] and !empty($user['status_info'])): echo "<br>".$user['status_info']; endif; ?></td>
				<td><?php echo date(get_option('date_format'), strtotime($user['date'])) ?></td>
				<td><a href="admin.php?page=bftpro_user_log&id=<?php echo $user['id']?>" target="_blank"><?php echo $user['num_sent']?></a></td>
				<td><?php echo $user['num_read']?></td>
				<td><?php echo $user['open_rate']?>%</td>
				<?php if(sizeof($datas)):?>
					<td><?php echo $user['custom_data']?></td>
		  		<?php endif;?>		
				<td><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>&do=edit&user_id=<?php echo $user['id']?>"><?php _e("Edit", 'bftpro')?></a></td></tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<div id="massDeleteBtn" style="display:none;">
			<p align="center">
				<?php if(count($other_lists)):
					_e('Move/Copy to:', 'bftpro');?>
					<select name="move_to">
						<?php foreach($other_lists as $other):?>
							<option value="<?php echo $other->id?>"><?php echo stripslashes($other->name);?></option>
						<?php endforeach;?>
					</select>
					<input type="button" value="<?php _e('Move Selected', 'bftpro')?>" onclick="confirmMassMove(this.form);" class="button button-primary">	
					<input type="button" value="<?php _e('Copy Selected', 'bftpro')?>" onclick="confirmMassCopy(this.form);" class="button button-primary">
				<?php endif;?>			
				<input type="button" value="<?php _e('Delete Selected', 'bftpro')?>" onclick="confirmMassDelete(this.form);" class="button"> 
			</p>	
			<p align="center">
				<?php _e('More actions:', 'bftpro');?>
				<input type="button" value="<?php _e('Activate Selected', 'bftpro')?>" onclick="confirmMassActivate(this.form, true);" class="button button-primary">
				<input type="button" value="<?php _e('Deactivate Selected', 'bftpro')?>" onclick="confirmMassActivate(this.form, false);" class="button button-primary">
			</p>	
		</div>
			<input type="hidden" name="mass_delete" value="0">
			<input type="hidden" name="mass_move" value="0">
			<input type="hidden" name="mass_copy" value="0">
			<input type="hidden" name="mass_activate" value="0">
			<input type="hidden" name="mass_deactivate" value="0">
			<?php wp_nonce_field('bftpro_subscribers');?>			
		</form>
		<p align="center">
		<?php if($offset>0):?>
		&nbsp; <a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>&offset=<?php echo ($offset-$limit)?><?php echo BFTProLinkHelper::subscribers($orderby, false)?>"><?php _e("Previous Page", 'bftpro');?></a>
			&nbsp;
		<?php endif;?>
		<?php if($cnt_users > ($limit + $offset)):?>
		&nbsp; <a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>&offset=<?php echo ($offset+$limit)?><?php echo BFTProLinkHelper::subscribers($orderby, false)?>"><?php _e("Next Page", 'bftpro');?></a> &nbsp;
		<?php endif;?>	
		</p>
	<?php else:?>
	<p><strong><?php _e("There are no subscribers in this list yet or none of them match your filters.", 'bftpro')?></strong></p>
	<?php endif;?>
</div>	

<script type="text/javascript">
function bftproSelectAll(chk) {
	if(chk.checked) {
		jQuery('#massDeleteBtn').show();
		jQuery('.userid').attr('checked', 'true');
	} else {
		jQuery('.userid').removeAttr('checked');
		jQuery('#massDeleteBtn').hide();
	}
}

function confirmMassDelete(frm) {
	if(confirm("<?php _e('Are you sure?', 'bftpro')?>")) {
		frm.mass_delete.value=1;
		frm.submit();
	}
}

function confirmMassMove(frm) {
	if(confirm("<?php _e('Are you sure? If these subscribers have any data in custom fields, it will be REMOVED! If user with the same email address already exists in the target list, this user will not be moved and their data will not be deleted.', 'bftpro')?>")) {
		frm.mass_move.value=1;
		frm.submit();
	}
}

function confirmMassCopy(frm) {
	if(confirm("<?php _e('Are you sure? If user with the same email address already exists in the target list, this user will not be copied.', 'bftpro')?>")) {
		frm.mass_copy.value=1;
		frm.submit();
	}
}

function confirmMassActivate(frm, activate) {
	if(confirm("<?php _e('Are you sure?', 'bftpro')?>")) {
		if(activate) frm.mass_activate.value=1;
		if(!activate) frm.mass_deactivate.value=1;
		frm.submit();
	}
}

// show or hide the delete button
function showHideDelBtn() {
	var anyChecked = false;
	jQuery('.userid').each(function(){
		if(jQuery(this).attr('checked')) anyChecked = true;	
	});
	
	if(anyChecked) jQuery('#massDeleteBtn').show();
	else jQuery('#massDeleteBtn').hide();
}

jQuery(document).ready(function() {
    jQuery('.bftproDatePicker').datepicker({
    		dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',
         altFormat : 'yy-mm-dd'
    });
    
    jQuery(".bftproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
    });
});
<?php bftpro_resp_table_js();?>
</script>