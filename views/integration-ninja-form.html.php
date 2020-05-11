<div class="wrap">
	<h1><?php printf(__('Integrate Mailing List "%s" With Ninja Form', 'bftpro'), stripslashes($list->name))?></h1>
	
	<p><?php printf(__('This page is available because you have installed and activated the plugin <a href="%s" target="_blank">Ninja Forms</a>. It lets you connect the form to Arigato PRO mailing list so when the form is submitted the user is also subscribed to the mailing list.', 'bftpro'), 'https://ninjaforms.com/');?></p>
	
	<p><a href="admin.php?page=bftpro_mailing_lists"><?php _e('Back to mailing lists', 'bftpro');?></a></p>
	
	<form method="post" class="bftpro">
		<div class="postbox wp-admin" style="padding:5px;">
			<p><label><?php _e('Select Ninja Form:', 'bftpro');?></label> <select name="form_id" onchange="this.form.submit();">
				<option value=""><?php _e('-Please select-', 'bftpro');?></option>
				<?php foreach($forms as $form):?>
					<option value="<?php echo $form['id']?>" <?php if($selected_form_id == $form['id']) echo 'selected'?>><?php echo stripslashes($form['data']['title'])?></option>
				<?php endforeach;?>
			</select></p>
			
			<h3><?php _e('Mailing list fields','bftpro');?></h3>
			
			<p><?php _e('You need to select which fields from the Ninja form correspond to the fields in the mailing list. The only mailing list field that requires a corresponding form field is email. All other fields can be omitted.', 'bftpro');?></p>
			
			<p><label><?php _e('Email field', 'bftpro');?></label> <select name="field_email">
				<option value=""><?php _e('- Please select -', 'bftpro')?></option>
				<?php foreach($ninja_fields as $ninja): 
					if($ninja['data']['type'] != 'email') continue;?>
					<option value="<?php echo $ninja['id']?>" <?php if(!empty($integration['fields']['email']) and $integration['fields']['email'] == $ninja['id']) echo 'selected'?>><?php echo stripslashes($ninja['data']['label']);?></option>
				<?php endforeach;?>
			</select></p>
			
			<p><label><?php _e('Name field', 'bftpro');?></label> <select name="field_name">
				<option value=""><?php _e('- No matching field -', 'bftpro')?></option>
				<?php foreach($ninja_fields as $ninja): 
					if($ninja['data']['type'] == 'email' or $ninja['type']=='submit' or $ninja['type'] == 'spam' or $ninja['type'] == 'checkbox') continue;?>
					<option value="<?php echo $ninja['id']?>" <?php if(!empty($integration['fields']['name']) and $integration['fields']['name'] == $ninja['id']) echo 'selected'?>><?php echo stripslashes($ninja['data']['label']);?></option>
				<?php endforeach;?>
			</select></p>
			
			<?php foreach($fields as $field):?>
				<p><label><?php echo stripslashes($field->label);?></label> <select name="field_<?php echo $field->id?>">
				<option value=""><?php _e('- No matching field -', 'bftpro')?></option>
				<?php foreach($ninja_fields as $ninja): 
					if($ninja['data']['type'] == 'email' or $ninja['type']=='submit' or $ninja['type'] == 'spam' or $ninja['type'] == 'checkbox') continue;?>
					<option value="<?php echo $ninja['id']?>" <?php if(!empty($integration['fields']['field_' . $field->id]) and $integration['fields']['field_' . $field->id] == $ninja['id']) echo 'selected'?>><?php echo stripslashes($ninja['data']['label']);?></option>
				<?php endforeach;?>
			</select></p>
			<?php endforeach;?>
			
			<p><label><?php _e('Checkbox to confirm', 'bftpro');?></label> <select name="field_checkbox">
				<option value=""><?php _e('- No checkbox required -', 'bftpro')?></option>
				<?php foreach($ninja_fields as $ninja): 
					if($ninja['type'] != 'checkbox') continue;?>
					<option value="<?php echo $ninja['id']?>" <?php if(!empty($integration['fields']['checkbox']) and $integration['fields']['checkbox'] == $ninja['id']) echo 'selected'?>><?php echo stripslashes($ninja['data']['label']);?></option>
				<?php endforeach;?>
			</select> <br>
			<?php _e("You can choose a checkbox from the Ninja form that shoud be selected to subscribe the user in the list. If you don't select such checkbox, everyone who submits the associated Ninja form will be subscribed to the mailing list.", 'bftpro');?></p>
			<p style="color:red;"><b><?php _e('Note: if you change the Ninja form - for example add or remove fields - you may need to go back and save this integration again.', 'bftpro');?></b></p>
			
			<p><input type="submit" name="ok" value="<?php _e('Save integration','bftpro');?>"></p>
		</div>	
	</form>
</div>