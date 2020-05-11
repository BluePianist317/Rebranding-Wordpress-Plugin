<?php foreach($fields as $field):
	if(!empty($field->is_removed)) continue;
	if(empty($field->ftype)) $field->ftype = 'textfield';
	$extra_classes = '';
	if($field->ftype == 'radio') $extra_classes .= 'bftpro-radio';
	if($field->ftype == 'checkbox') $extra_classes .= 'bftpro-checkbox';
   if($field->ftype == 'hidden') $nolabel = true;
   $placeholder = '';
   if(!empty($field->placeholder_attr)) $placeholder = $field->placeholder_attr; // this is when field shortcode is used
   // the below case is when we come from Gozaimasu designed form
   if(!empty($field->placeholder)) {
   	$placeholder = ' placeholder="'.stripslashes($field->placeholder).'" ';
   }
   
	if(empty($remote_placement)) {
		$div_css = "class=\"bftpro-form-group $extra_classes\"";
		$label_css = $ul_css = '';
	}   
	else {
		$div_css = $label_css = 'style="display: block; margin: 0.5rem auto;"';
		$ul_css = 'style="list-style: none;"';
	}
   
	// $nolabel is set as true by the "visual form" shortcode
	if($field->ftype!='checkbox' and empty($nolabel)) echo "<div $div_css><label $label_css>".(@$field->is_required?'*':'')."{$field->label}:</label> ";
	$field_name = empty($field->special_name) ? 'field_'.$field->id : $field->special_name;
	switch($field->ftype):		
		case 'textfield':	?>
			<input type="text" name="<?php echo $field_name?>" value="<?php echo @$user[$field_name]?>"<?php echo $placeholder.@$onkeyup?>>
		<?php	break;		
		case 'textarea': 
			echo wp_editor(@$user[$field_name], $field_name);
		break;		
		case 'simple_textarea':?> 
			<textarea name="<?php echo $field_name;?>" rows="5" cols="30" <?php echo $placeholder.@$onkeyup?>><?php echo stripslashes(@$user[$field_name])?></textarea>
		<?php break;		
		case 'dropdown':
			$vals=explode("\n",$field->fvalues);	?>
			<select name="<?php echo $field_name;?>">
			<?php foreach($vals as $val):			
				$val=trim($val);
				if($val==@$user[$field_name]) $selected='selected';
				else $selected='';
				echo "<option value=\"$val\" $selected>$val</option>";
			endforeach; ?>
			</select>
		<?php	break;		
		case 'radio':
			$vals = explode("\n",$field->fvalues);		
			echo '<ul '.$ul_css.'>';	
			foreach($vals as $vct=>$val):			
				$val = trim(stripslashes($val));
				if(trim($val)==@$user["field_".$field->id] or (empty($user["field_".$field->id]) and $vct==0)) $checked='checked';
				else $checked='';
				echo "<li><label $label_css><input type='radio' name='$field_name' value=\"$val\" $checked> $val</label></li>";
			endforeach;		
			echo '</ul>';	
		break;	
		case 'date':
			echo BFTProQuickDDDate($field_name, @$user[$field_name], $field->field_date_format);
		break;	
		case 'file':?>
			<input type="file" name="<?php echo $field_name?>">
		<?php break;	
		case 'hidden':
		    // receive from param, with preference to POST
		    $hidden_value = empty($_POST[$field->name]) ? @$_GET[$field->name] : $_POST[$field->name];
		    if(empty($hidden_value)) $hidden_value = $field->fvalues;?>
		    <input type="hidden"  name="<?php echo $field_name?>" value="<?php echo $hidden_value?>">
		<?php break;
		case 'checkbox':?>
			<?php if(empty($nolabel)):?><div <?php echo $div_css?>><?php endif;?>			
				<?php if(empty($nolabel)):?><label <?php echo $label_css?>><?php endif;?><input type="checkbox" name="<?php echo $field_name?>" <?php if(@$user[$field_name]) echo "checked"?> value="1"> <?php if(empty($nolabel)): echo ($field->is_required?'*':'').stripslashes($field->label).'</label></div>'; endif;?>
			
		<?php break;
	endswitch;
	if($field->ftype!='checkbox' and empty($nolabel)) echo "</div>\n";
	
	if($field->ftype == 'file' and !empty($user['field_'.$field->id])) {
		echo "<p>".sprintf(__('Current file: %s', 'bftpro'), "<a href='admin.php?page=bftpro_download&id=".$user['field_'.$field->id.'_dataid']."&noheader=1'>".$user[$field_name]."</a>")."</p>";
		
		// in this case don't add it to required fields
		continue;
	}		
	
	if(!empty($field->is_required) and $field->ftype != 'date') echo "<input type='hidden' name='required_fields[]' value='$field_name'>";
endforeach;?>