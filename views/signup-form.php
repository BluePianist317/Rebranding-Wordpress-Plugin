<?php if($remote_placement):?>
<script type="text/javascript">
function validateBFTProUser(frm, requireName) {
	requireName = requireName || false;
	
	if(requireName && frm.bftpro_name.value=="") {
		alert("<?php _e('Please provide name', 'bftpro')?>");
		frm.bftpro_name.focus();
		return false;
	}
	
	if(frm.email.value=="" || frm.email.value.indexOf("@")<1 || frm.email.value.indexOf(".")<1) {
		alert("<?php _e('Please provide a valid email address', 'bftpro')?>");
		frm.email.focus();
		return false;
	}
	
	// check custom fields
	var req_cnt = frm.elements["required_fields[]"].length; // there's always at least 1
	if(req_cnt > 1) {
		for(i = 0; i<req_cnt; i++) {
			var fieldName = frm.elements["required_fields[]"][i].value;
			if(fieldName !='') {
				var isFilled = false;
				// ignore radios
				if(frm.elements[fieldName].type == 'radio') continue;
				
				// checkbox
				if(frm.elements[fieldName].type == 'checkbox' && !frm.elements[fieldName].checked) {
					alert("<?php _e('This field is required', 'bftpro')?>");
					frm.elements[fieldName].focus();
					return false;
				}		
				
				// all other fields
				if(frm.elements[fieldName].value=="") {
					alert("<?php _e('This field is required', 'bftpro')?>");
					frm.elements[fieldName].focus();
					return false;
				}
			}
		}
	}
		
	return true;
}
</script>
<?php if(!empty($recaptcha2)):?><script src="https://www.google.com/recaptcha/api.js?hl=<?php echo $recaptcha_lang?>"></script><?php endif;?>
<?php if(!empty($recaptcha3)):?><script src="https://www.google.com/recaptcha/api.js?render=<?php echo $recaptcha_public?>"></script><?php endif;?>
<?php endif;?>

<form method="post" enctype="multipart/form-data" class="bftpro-front-form <?php echo !empty($orientation_class) ? $orientation_class : 'bftpro-vertical'?> <?php echo !empty($label_class) ? $label_class : 'bftpro-inline-label'?>" onsubmit="return validateBFTProUser(this,<?php echo $list->require_name?'true':'false'?>);" <?php if($remote_placement):?>action="<?php echo $form_action;?>"<?php endif;?> style="<?php echo $inline_style?>">
	<?php if(!empty($atts['form_name'])):?><input type="hidden" name="bftpro_form_name" value="<?php echo esc_attr($atts['form_name'])?>"><?php endif;
	if(!empty($atts['passed_design_id'])):?><input type="hidden" name="goz_design_id" value="<?php echo esc_attr($atts['passed_design_id'])?>"><?php endif;
	if(!empty($atts['ab_test_id'])):?><input type="hidden" name="goz_ab_test_id" value="<?php echo esc_attr($atts['ab_test_id'])?>"><?php endif;?>
	<fieldset>
		<?php $this->fields(@$list->id); ?>		
		
		<?php if(empty($list->id)):?>
			<div class="bftpro-form-group"><label><?php _e('Mailing list:', 'bftpro')?></label>
				<?php if(empty($this->attr_mode) or $this->attr_mode == 'dropdown'):?> 
				<select name='list_id'>
					<?php foreach($lists as $l):?>
						<option value="<?php echo $l->id?>"><?php echo $l->name;?></option>
					<?php endforeach;?>
				</select>
				<?php endif;
				if(!empty($this->attr_mode) and $this->attr_mode == 'radio'):
				foreach($lists as $cnt=>$l):?>
					<input type="radio" name="list_id" value="<?php echo $l->id?>" <?php if($cnt==0) echo 'checked'?>> <?php echo $l->name?> <br>
				<?php endforeach;
				endif;
				if(!empty($this->attr_mode) and $this->attr_mode == 'checkbox'):
				foreach($lists as $cnt=>$l):?>
					<input type="checkbox" name="list_ids[]" value="<?php echo $l->id?>" <?php if($cnt==0) echo 'checked'?>> <?php echo $l->name?> <br>
				<?php endforeach;
				endif;?>
			</div>
		<?php endif;?>		

		<?php if(!empty($recaptcha_html)):?><div class="bftpro-form-group recaptcha"><label>&nbsp;</label><?php echo $recaptcha_html?></div><?php endif;?>	
		<?php if(!empty($text_captcha_html)):?><p><?php echo $text_captcha_html?></p><?php endif;?>	
	
	<div class="bftpro-form-group <?php echo (!empty($atts['orientation']) and $atts['orientation'] == 'horizontal') ? 'bftpro-btn-'.$atts['submit_btn_position'] : ''; ?>">
		<?php if(empty($list->signup_graphic)):?>
			<input type="submit" value="<?php echo empty($atts['submit_btn_text']) ? __('Subscribe', 'bftpro') : $atts['submit_btn_text'];?>">
		<?php else:?>
			<input type="image" src="<?php echo $list->signup_graphic?>">
		<?php endif;?>		
	</div>
	</fieldset>
	<input type="hidden" name="bftpro_subscribe" value="1">
	<?php if(!empty($list->id)):?>
		<input type="hidden" name="list_id" value="<?php echo $list->id?>">
	<?php endif;?>	
	<input type="hidden" name="required_fields[]" value="">
	
	<?php if(!empty($atts['magnet_id'])):?><input type="hidden" name="bftpro_magnet_id" value="<?php echo intval($atts['magnet_id']);?>"><?php endif;?>
	<?php if(!empty($atts['redirect_url'])):?><input type="hidden" name="bftpro_redirect_url" value="<?php echo $atts['redirect_url'];?>"><?php endif;?>
</form>