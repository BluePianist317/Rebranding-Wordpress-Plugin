<style type="text/css">
.ui-tooltip {
  max-width: 150px;
}
</style>

<div class="arigatoPreset" style="float:left;width:100%;">
	<div style="float:left;width:70%;">
		<?php echo $contents;?>
	</div>
	<div style="float:right;width:25%;margin:10px;">
		<?php foreach($blocks[0] as $cnt=>$block):
			$cnt++;
			if(!empty($mail->id)) $block = stripslashes(@$mail_blocks[$cnt-1]);?>			
			<h3><a href="#" onclick="arigatoPROLoadBlock('editBlock<?php echo $cnt;?>');return false;"><?php printf(__('Edit block %d', 'bftpro'), $cnt);?></a></h3>
			<div style="display:none;" class="arigato-preset-editor" id="editBlock<?php echo $cnt;?>"><?php wp_editor($block, 'block_'.$cnt);?>
			<p align="center"><input type="button" value="<?php _e('Preview Changes', 'bftpro');?>" onclick="arigatoPreviewContents('<?php echo 'block_'.$cnt?>', <?php echo $cnt?>)">
			<input type="button" value="<?php _e('Hide Editor', 'bftpro');?>" onclick="jQuery('#editBlock<?php echo $cnt;?>').hide();"></p></div>
		<?php endforeach;?>
	</div>
	<input type="hidden" name="preset_id" value="<?php echo $preset_id?>">
</div>

<script type="text/javascript" >
function arigatoGetEditorContent(id) {
    var content;
    var inputid = id;
    var editor = tinyMCE.get(inputid);
    var textArea = jQuery('textarea#' + inputid);    
    if (textArea.length>0 && textArea.is(':visible')) {
        content = textArea.val();        
    } else {
        content = editor.getContent();
    }    
    return content;
}

function arigatoPreviewContents(id, cnt) {
	var contents = arigatoGetEditorContent(id);
	jQuery('#arigatoBlock' + cnt).html(contents);
}

jQuery(function() {
 jQuery( document ).tooltip();
});

function arigatoPROLoadBlock(id) {
	jQuery('.arigato-preset-editor').hide();
	jQuery('#' + id).show();
}
</script>