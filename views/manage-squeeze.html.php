<style type="text/css">
textarea {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;

    width: 100%;
}
</style>

<div class="wrap">
	<h1><?php _e('Squeeze / Landing Page Settings', 'bftpro');?></h1>
	
	<p><?php printf(__('You can use these settings to easily turn your blog, or a specific page of it, into a squeeze/landing page. You just need to design your squeeze page below and place the subscribe form shortcode (or HTML code) right into it. You can find some free squeeze page designs <a href="%s" target="_blank">here</a>.', 'bftpro'), 'https://themewagon.com/free-html-landing-page-templates-responsive-multiverse/');?></p>
	
	<form method="post" class="bftpro-form">
		
			<p><input type="radio" name="squeeze_setting" value="" <?php if(empty($squeeze_setting)) echo 'checked';?> onclick="bftproSqueezeSetting(this.value);"> <?php _e('I am using the subscribe form codes inside my regular blog contents or theme and do not need a squeeze page.', 'bftpro');?> <br>
			<input type="radio" name="squeeze_setting" value="home" <?php if($squeeze_setting == 'home') echo 'checked';?> onclick="bftproSqueezeSetting(this.value);"> <?php _e('Make my blog home the squeeze page. Any other posts or pages I have will still be accessible by their URLs.', 'bftpro');?> <br>
			<input type="radio" name="squeeze_setting" value="url" <?php if($squeeze_setting == 'url') echo 'checked';?> onclick="bftproSqueezeSetting(this.value);"> <?php _e('I want to specify the URL of my squeeze page. (The page should exist - the content on it does not matter.)', 'bftpro');?> </p>
			<div id="squeezeURLField" style="display:<?php echo ($squeeze_setting == 'url') ? 'block' : 'none';?>">
				<label><?php _e('URL of the page:', 'bftpro');?></label> <input type="text" name="squeeze_url" value="<?php echo get_option('bftpro_squeeze_url');?>" size="60">
			</div>
			
			<div id="squeezeContents" style="display:<?php echo empty($squeeze_setting) ? 'none' : 'block';?>">
				<p><b><?php _e('Enter the HTML code of the squeeze page template in the box. Your squeeze page design must be a complete HTML document with header.', 'bftpro');?></b></p>
				<textarea name="squeeze_contents" rows="40" cols="120"><?php echo stripslashes(get_option('bftpro_squeeze_contents'));?></textarea>
				<p><?php _e('Note: if you decide some of the subscribe form shortcodes you need to include the following script in your squeeze page header:', 'bftpro');?><br>
		<textarea rows="10" cols="100" readonly="readonly" onclick="this.select();">&lt;script type="text/javascript"&gt;
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
&lt;/script&gt;</textarea><br>
		<?php _e("You don't need this in case you are using the HTML form code.", 'bftpro');?></p>	
			</div>
	
		
		<p><input type="submit" value="<?php _e('Save Squeeze Page Settings', 'bftpro');?>"></p>
		<input type="hidden" name="ok" value="1">
	</form>
</div>

<script type="text/javascript" >
function bftproSqueezeSetting(val) {
	jQuery('#squeezeURLField').hide();
	jQuery('#squeezeContents').hide();
	
	if(val == 'url') jQuery('#squeezeURLField').show();
	if(val == 'url' || val == 'home') jQuery('#squeezeContents').show();
}
</script>