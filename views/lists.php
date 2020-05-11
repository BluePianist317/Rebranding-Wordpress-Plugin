<style type="text/css">
<?php bftpro_resp_table_css(900);?>

.bftpro-tab {
	padding: 10px;
	background-color: #EEE;
}

.bftpro-active {
	font-weight:bold;
	background-color: #DDD;
}
</style>

<div class="wrap">

<h1><?php _e("Your Mailing Lists", 'bftpro')?></h1>
	
	<?php bftpro_display_alerts(); ?>	
	<p><a href="admin.php?page=bftpro_mailing_lists&do=add"><?php _e("Click here to add a new mailing list", 'bftpro')?></a></p>
	<form method="post">
	<table class="widefat arigato-pro-table">
		<thead>
		<tr><th><input type="checkbox" onclick="bftproSelectLists(this);"></th>
		<th><?php _e('ID', 'bftpro');?></th>	
		<th><?php _e("Name and description", 'bftpro')?></th><th><?php _e("Autoresponders", 'bftpro')?></th>
		<th><?php _e("Subscribers", 'bftpro')?></th><th><?php _e("Custom Fields", 'bftpro')?></th>
		<th><?php _e("Subscribe Form", 'bftpro')?></th><th><?php _e("Action", 'bftpro')?></th></tr>
		</thead>
		<tbody>
		<?php foreach($lists as $list):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>">
			<td><input type="checkbox" name="list_ids[]" value="<?php echo $list->id?>" class="bftpro_chk" onclick="bftproShowHideMassButtons();"></td>
			<td><?php echo $list->id;?></td>
			<td><h3><?php echo stripslashes($list->name)?></h3>
			<?php if(!empty($list->description)): echo wpautop(stripslashes($list->description)); endif;
			if(!empty($list->auto_subscribe)):
				echo $list->auto_subscribe_role ? sprintf(__('Automatically subscribes users who register to your site with role "%s".', 'bftpro'), $list->auto_subscribe_role) : __('Automatically subscribes everyone who registers to your site.','bftpro');
			endif;
			if(!empty($list->woo_msg)) echo '<p>'.$list->woo_msg.'</p>';
			if(!empty($list->woo_msg_unsub)) echo '<p>'.$list->woo_msg_unsub.'</p>';?></td>
			<td><?php if($list->responders):?>
				<a href="admin.php?page=bftpro_ar_campaigns&list_id=<?php echo $list->id?>"><?php echo $list->responders;?></a>
			<?php else:
				 _e("None yet", 'bftpro'); 
			endif;?></td>
			<td><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>"><?php _e("Manage", 'bftpro')?> (<?php echo $list->subscribers?>)</a><br>
			<?php printf(__('%d active subscribers', 'bftpro'), $list->active_subscribers); 
			printf("<br>" . __('%d%% open rate', 'bftpro'), $list->open_rate);
			if($list->num_unsubscribed) printf("<br>" . __('%d unsubscribed (%d%%)', 'bftpro'), $list->num_unsubscribed, $list->percent_unsubscribed);?></td>
			<td><a href="admin.php?page=bftpro_fields&list_id=<?php echo $list->id?>"><?php _e("Manage", 'bftpro');?></a></td>
			<td>
				<p><?php _e('Shortcode:', 'bftpro')?> <input type="text" value="[bftpro <?php echo $list->id?>]" onclick="this.select();" readonly="readonly"></p>	
				
				<?php do_action('bftpro-list-shortcodes', $list);?>	
				
				<p><a href="#" onclick="jQuery('#visualCode<?php echo $list->id?>').toggle();return false;"><?php _e('WordPress-friendly form code (for the rich text editor)','bftpro')?></a></p>		
				
				<div id="visualCode<?php echo $list->id?>" style="display:none;padding:5px;">
					<h3><?php _e('How to use the code below:', 'bftpro')?></h3>
					<ol>
						<li><?php _e('Copy the code by clicking in the box below.','bftpro');?></li>
						<li><?php _e('Create a post or page in your blog or edit an existing post or page.', 'bftpro')?></li>
						<li><?php _e('Paste the code in the text or visual mode and feel free to edit it any way you wish without changing the contents of the shortcodes.', 'bftpro')?></li>
						
					</ol>			
					<?php $_list->visual = true;?>
						<p><a href="#" id="list<?php echo $list->id?>_textModeLink" onclick="jQuery('#list<?php echo $list->id?>_textMode').show();jQuery('#list<?php echo $list->id?>_visualMode').hide();jQuery(this).addClass('bftpro-active');jQuery('#list<?php echo $list->id?>_visualModeLink').removeClass('bftpro-active');return false;" class="bftpro-tab bftpro-active"><?php _e('For Text mode (Recommended!)', 'bftpro');?></a>  <a href="#" id="list<?php echo $list->id?>_visualModeLink" onclick="jQuery('#list<?php echo $list->id?>_textMode').hide();jQuery('#list<?php echo $list->id?>_visualMode').show();jQuery(this).addClass('bftpro-active');jQuery('#list<?php echo $list->id?>_textModeLink').removeClass('bftpro-active');return false;" class="bftpro-tab"><?php _e('For Visual mode', 'bftpro')?></a></p>
						<div id="list<?php echo $list->id?>_textMode"><textarea rows="15" cols="80" readonly="true" onclick="this.select();"><?php $_list->signup_form($list->id, true, false)?></textarea></div>
						<div id="list<?php echo $list->id?>_visualMode" style="display:none;"><textarea rows="15" cols="80" readonly="true" onclick="this.select();"><?php $_list->signup_form($list->id, true, true)?></textarea></div>
					<?php $_list->visual = false;?>
					<p><?php printf(__('You can also include the shortcode %s to display checkboxes for subscribing to your other mailing lists.<br> Custom fields and redirects will be ignored on them. If used, this shortcode must be placed before the submit button shortcode.', 'bftpro'), '<input type="text" value="[bftpro-other-lists list_id='.$list->id.']" onclick="this.select();" readonly="readonly" size="20">');?></p>
					<p><?php printf(__('Text fields and simple text areas also accept placeholder attribute to generate a HTML placeholder in the field.<br> Example: %s', 'bftpro'), "<input type='text' value='[bftpro-field _1 placeholder=\"Your name:\"]' onclick='this.select();' readonly='readonly' size='30'>");?></p>
					<p><?php printf(__('To specify a redirect URL which is different than the mailing list redirect URL (if any), you can add a static field like this: %s', 'bftpro'),
						"<input type='text' value='[bftpro-field-static redirect_url value=\"http://url-to-redirect.com\"]' onclick='this.select();' readonly='readonly' size='30'>");?></p>
				</div>
				
				<p><a href="#" onclick="jQuery('#formCode<?php echo $list->id?>').toggle();return false;"><?php _e('Form code for using outside WordPress (HTML)', 'bftpro')?></a></p>
				<div id="formCode<?php echo $list->id?>" style="display:none;padding:5px;"><div style="border:1px solid black;padding:5px;"><pre><?php echo htmlentities(BFTPro::shortcode_signup(array($list->id, true)))?></pre></div></div>
				
				<p><a href="admin.php?page=bftpro_integrate_contact&id=<?php echo $list->id?>"><?php _e('Integrate in contact form', 'bftpro')?></a></p>
				<?php if(function_exists('ninja_forms_get_all_forms')):?>
					<p><a href="admin.php?page=bftpro_integrate_ninja&id=<?php echo $list->id?>"><?php _e('Integrate in Ninja form', 'bftpro')?></a></p>
				<?php endif;?>
				<?php if(class_exists('Caldera_Forms')):?>
				<p><a href="admin.php?page=bftpro_integrate_caldera&id=<?php echo $list->id?>"><?php _e('Integrate in Caldera form', 'bftpro')?></a></p>
				<?php endif;?>
			</td>
			<td><a href="admin.php?page=bftpro_mailing_lists&do=edit&id=<?php echo $list->id?>"><?php _e("Edit", 'bftpro');?></a>
			| <a href="<?php echo wp_nonce_url('admin.php?page=bftpro_mailing_lists&duplicate=1&do=duplicate&id='.$list->id, 'bftpro_lists');?>" onclick="return confirm('<?php _e('This will make a copy of the list with all the custom fields. The list will be associated to the same autoresponders. Subscribers will not be included in the duplicated list. Do you want to continue?', 'bftpro');?>');"><?php _e("Duplicate", 'bftpro');?></a></td></tr>
		<?php endforeach;?>
		</tbody>
	</table>
	
	<p align="center" id="bftproBulkButtons" style="display:none;">
		<input type="button" value="<?php _e('Delete selected lists', 'bftpro');?>" onclick="bftproConfirmMassDelete(this.form);" class="button"> 
		<input type="hidden" name="mass_delete" value="0">
	</p>
	<?php wp_nonce_field('bftpro_lists');?>
	</form>
	
	<p class="note">* <?php _e("You can embed every mailing list code right in any page or post. You can also enable a <a href='widgets.php'>widget</a> for the mailing list.", 'bftpro');?></p>
	
	<h2><?php _e('Shortcode Parameters for the Simple Shortcode', 'bftpro')?></h2>
	
	<p><?php printf(__('<strong>Form orientation:</strong> The  parameter "%s" defines whether form fields are aligned vertical or horizontally in two column (horizontal is possible only when the form is wide enough. The design is responsive. Possible values: "horizontal" and "vertical". Default: horizontal. Example: %s', 'bftpro'),
		'orienation', '[bftpro 1 orientation="vertical"]');?></p>
	<p><?php printf(__('<strong>Labels style:</strong> The  parameter "%s" defines the position of the labels - next to fields or above them. Possible values: "inline" and "block". Default: inline. Example: %s', 'bftpro'),
		'labels', '[bftpro 1 labels="block"]');?></p>	
	<p><?php printf(__('<strong>Form width:</strong> The  parameter "%s" defines the maximum width of the form in pixels. It does not fix the width because the form is responsive and will shrink on small screens. Example: %s', 'bftpro'),
		'form_max_width', '[bftpro 1 form_max_width="500"]');?></p>		
	<p><?php printf(__('<strong>Submit button text:</strong> The  parameter "%s" lets you change the text of the submit button. If you enter valid image URL it will replace the button with an image. Example: %s', 'bftpro'),
		'submit_btn_text', '[bftpro 1 submit_btn_text="Let me in!"]');?></p>			
	<p><?php printf(__('<strong>Redirect:</strong> The parameter "%s" overrides the redirect URL for the mailing list. Use it like this: %s', 'bftpro'),
		'redirect_url', '[bftpro 1 redirect_url="http://your-url.com"]');?></p>
	
	<p><?php printf(__('<strong>Form name:</strong> The  parameter "%s" can be used to give a name to the form. It will be shown in the users list under the "Source" column, thus helping you to track where exactly the subscriber came from in case you are using multiple forms on different parts of your site. Use it like this: %s', 'bftpro'),
		'form_name', '[bftpro 1 form_name="free giveaway"]');?></p>	
	
	<h2><?php _e('Generic Shortcodes', 'bftpro')?></h2>
	
	<p><?php _e('This code will let the user choose which mailing list they want to sign up to by a drop-down selector (default). Please note that custom fields will not appear for such form and captcha will not be enabled. Required custom fields will be automatically filled with "1".', 'bftpro');?></p>
	
	<p><?php _e('Generic shortcode:', 'bftpro')?> <input type="text" value="[bftpro]" readonly="readonly" onclick="this.select();"></p>
	
	<p><?php _e('You can also specify a different mailing list selector:', 'bftpro')?> </p>
	
	<p><input type="text" value='[bftpro mode="radio"]' readonly="readonly" onclick="this.select();" size="24"> <?php _e('will display radio buttons insead of drop-down.', 'bftpro')?><br>
	<input type="text" value='[bftpro mode="checkbox"]' readonly="readonly" onclick="this.select();" size="24"> <?php _e('will display checboxes and will allow the user to subscribe to several mailing lists at once. The last selected list message and redirection settings will be used.', 'bftpro')?>
	</p>
	
	<h2><?php _e('Other Shortcodes', 'bftpro')?></h2>
	
	<p><?php printf(__('The shortcode %s will display the number of active subscribers in any mailing list. Replace X with the actual list ID which you can see in the table above.', 'bftpro'), '<input type="text" value="[bftpro-num-subs list_id=X]" onclick="this.select();" readonly="readonly">');?></p>
</div>	

<script type="text/javascript" >
function bftproSelectLists(mainChk) {
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