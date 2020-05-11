<h1><?php echo empty($_GET['user_id'])?__("Add", 'bftpro'):__("Edit", 'bftpro')?> <?php printf(__("Custom Field In Mailing List '%s'", 'bftpro'), stripslashes($list->name));?></h1>

<p><a href="admin.php?page=bftpro_fields&list_id=<?php echo $list->id?>"><?php _e('Back to custom fields in this mailing list', 'bftpro');?></a></p>

<form method="post" class="bftpro" onsubmit="return validateBFTProField(this);">
<div class="postbox wp-admin" style="padding:5px;">
	<p><label><?php _e("Field Name:", 'bftpro')?></label> <input type="text" name="name" value="<?php echo @$field->name?>"></p>
	<p class="help"><?php _e("Only small letters and numbers, no spaces.", 'bftpro')?></p>	
	
	<p><label><?php _e("Field Label:", 'bftpro')?></label> <input type="text" name="label" value="<?php echo @$field->label?>"></p>
	<p class="help"><?php _e("This is what the user will see when they sign-up.", 'bftpro')?></p>
	
	<p><label><?php _e("Field Type:", 'bftpro')?></label> <select name="ftype" onchange="BFTProdisplayValues(this.value);">
		<option value="textfield" <?php if(@$field->ftype=='textfield') echo 'selected'?>><?php _e("Text Field", 'bftpro')?></option>
		<option value="textarea" <?php if(@$field->ftype=='textarea') echo 'selected'?>><?php _e("Rich Text Area", 'bftpro')?></option>
		<option value="simple_textarea" <?php if(@$field->ftype=='simple_textarea') echo 'selected'?>><?php _e("Simple Text Area", 'bftpro')?></option>
		<option value="checkbox" <?php if(@$field->ftype=='checkbox') echo 'selected'?>><?php _e("Checkbox", 'bftpro')?></option>
		<option value="dropdown" <?php if(@$field->ftype=='dropdown') echo 'selected'?>><?php _e("Drop Down", 'bftpro')?></option>
		<option value="radio" <?php if(@$field->ftype=='radio') echo 'selected'?>><?php _e("Radio Group", 'bftpro')?></option>
		<option value="date" <?php if(@$field->ftype=='date') echo 'selected'?>><?php _e("Date Selector", 'bftpro')?></option>
		<option value="file" <?php if(@$field->ftype=='file') echo 'selected'?>><?php _e("File Upload", 'bftpro')?></option>
		<option value="hidden" <?php if(@$field->ftype=='hidden') echo 'selected'?>><?php _e("Hidden", 'bftpro')?></option>
		</select> 
		<span id="dateFormatOption" style='display:<?php echo (!empty($field->id) and $field->ftype == 'date') ? 'inline' : 'none';?>'>
			<?php _e('Format:', 'bftpro')?> <input type="text" name="field_date_format" value="<?php echo empty($field->field_date_format) ? 'YYYY-MM-DD' : $field->field_date_format?>" size="12"> 
				<i><?php _e('The only allowed letters for the field format are Y, M and D. Use dashes between parts.', 'bftpro')?></i>
		</span>
	</p>
	
	<p id="fieldValues" style='display:<?php echo (@$field->ftype=='dropdown' or @$field->ftype=='radio')?"block":"none"?>'>
	<label><?php _e("Possible values:", 'bftpro')?></label>
	<textarea name="fvalues" rows="5" cols="40"><?php echo stripslashes(@$field->fvalues)?></textarea><br />
	<i><?php _e("Enter one value per line.", 'bftpro')?></i></p>
	
	<div id="fileReqs" style='display:<?php echo (!empty($field->id) and $field->ftype == 'file') ? 'block' : 'none';?>'>
		<p><label><?php _e('Max size:', 'bftpro');?></label> <input type="text" name="filesize" value="<?php echo @$filesize?>" size="4"> <?php _e('KB. Leave blank for no limit other than your server upload limit.', 'bftpro')?></p>
		<p><label><?php _e('Accepted extensions:', 'bftpro')?></label> <input type="text" name="filetypes" value="<?php echo $filetypes?>" size="30"> <?php _e('Enter file extensions separated by comma, all small letters. For example: gif, png, jpg, jpeg, doc, xls. Leave blank to not apply any restrictions.', 'bftpro')?></p>
	</div>
	
	<div id="hiddenReqs" style='display:<?php echo (!empty($field->id) and $field->ftype == 'hidden') ? 'block' : 'none';?>'>
	  <p><?php _e('Hidden fields will accept arguments from GET or POST when the argument name matches the field name. They can also have a default value.', 'bftpro');?></p>
	  <p><label><?php _e('Default (optional):', 'bftpro')?></label> <input type="text" name="hidden_default" value="<?php echo @$field->fvalues?>"></p>
	</div>
	
	<p id="isRequired"><label><input type="checkbox" name="is_required" value="1" <?php if(!empty($field->is_required)) echo "checked"?>> <?php _e('This is a required field', 'bftpro')?></label></p>
	
	<p>&nbsp;</p>
	<p><?php if(empty($_GET['id'])):?>
		<input type="submit" name="ok" value="<?php _e('Add Field', 'bftpro');?>">
	<?php else:?>
		<input type="submit" name="ok" value="<?php _e('Save Field', 'bftpro');?>">
		<input type="button" value="<?php _e('Delete Field', 'bftpro');?>" onclick="confirmDelete(this.form);">
		<input type="hidden" name="del" value="0">
	<?php endif;?>
	<input type="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="window.location='admin.php?page=bftpro_fields&list_id=<?php echo $list->id?>'"></p>
</div>
</form>

<script type="text/javascript" >
function validateBFTProField(frm) {
	if(frm.name.value=='') 	{
		alert("<?php _e('Please enter name', 'bftpro')?>");
		frm.name.focus();
		return false;
	}
	
	if(frm.label.value=='') {
		alert("<?php _e('Please enter label', 'bftpro')?>");
		frm.label.focus();
		return false;
	}
	
	return true;
}

function BFTProdisplayValues(val) {
	document.getElementById('fieldValues').style.display='none';
	document.getElementById('dateFormatOption').style.display='none';
	document.getElementById('fileReqs').style.display='none';
	document.getElementById('isRequired').style.display='block';
	document.getElementById('hiddenReqs').style.display='none';
	
	if(val=='dropdown' || val=='radio') {
		document.getElementById('fieldValues').style.display='block';
	}
	
	if(val=='date') {
		document.getElementById('dateFormatOption').style.display='inline';
	}
	
	if(val=='file') {
		document.getElementById('fileReqs').style.display='block';
	}
	
	if(val == 'hidden') {
	   document.getElementById('isRequired').style.display='none';
	   document.getElementById('hiddenReqs').style.display='block';
	}
}
</script>