<?php foreach($fields as $field):
	if(empty($field->ftype)) $field->ftype = 'textfield';
	$extra_classes = '';
	if($field->ftype == 'radio') $extra_classes .= 'bftpro-radio';
	if($field->ftype == 'checkbox') $extra_classes .= 'bftpro-checkbox';
	if(empty($visual_mode)) echo "<div class='bftpro-form-group $extra_classes'>";
	if($field->ftype!='checkbox') {
		if(empty($visual_mode)) echo '<label>';
		echo ($field->is_required?'*':'').stripslashes($field->label);
		if(empty($visual_mode)) echo '</label>';
		else echo "\n";
	} 
	switch($field->ftype):
		case 'textfield':	
		case 'textarea':
		case 'simple_textarea':  
		case 'dropdown':	
		case 'radio':
		case 'date':
		case 'file':
		?>[bftpro-field <?php echo $field->id?>]<?php 		
		break;		
		case 'checkbox':
			if(empty($visual_mode)) echo '<label>';?>[bftpro-field <?php echo $field->id?>] <?php echo ($field->is_required?'*':'').$field->label;
			if(empty($visual_mode)) echo '</label>';
		break;
	endswitch;	
	if(empty($visual_mode)) echo "</div>";
	echo "\n";	
endforeach;?>