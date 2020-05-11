<?php
// handles responsive presets. I.e. displays the forms for editing, storing the content in blocks, and preparing it when sending
class ArigatoPROPresets {
	// @param $mail (object) - contains the selected email or newsletter object when editing. When creating new it's null
	// @param $preset_id (int) - contains selected preset ID when creating new email / newsletter. When editing existing it's null
	static function form($mail = null, $preset_id = null) {
		global $wpdb;
		$_sender = new BFTProSender();
		
		if(!empty($mail->id)) {
			$mail_blocks = explode('{{{block}}}', $mail->message);
		}			
		
		if(empty($preset_id)) $preset_id = $_GET['preset_id'];		
		
		// temp hardcode preset 1		
		list($contents, $blocks) = self :: extract_blocks($preset_id);		
		foreach($blocks[0] as $cnt=>$block) {
			$cnt++;
			$rblock = $block;						
			// if editing, we need to use the updated block from the mail contents
			if(!empty($mail->id)) $rblock = stripslashes(@$mail_blocks[$cnt-1]);
			
			$rblock = $_sender->maybe_nl2br($rblock);
			
			$contents = str_replace($block, '<div id="arigatoBlock'.$cnt.'" onmouseover="this.style.cursor=\'pointer\';" onclick="arigatoPROLoadBlock(\'editBlock'.$cnt.'\');" title="'.sprintf(__('Block %d', 'bftpro'), $cnt).'">'.$rblock.'</div>', $contents);
		}
		self :: find();
		wp_enqueue_script('jquery-ui-tooltip');		
		wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	   include(BFTPRO_PATH . "/views/preset-form.html.php");
	}
	
	// prepare the blocks in the contents so they can easily be broken into blocks again
	static function prepare($preset_id) {
		// select the preset
		// temp hardcode the demo one
		list($contents, $blocks) = self :: extract_blocks($preset_id);
		
		$message = '';
		foreach($blocks[0] as $cnt=>$block) {
			$cnt++;
			$message .= $_POST['block_' . $cnt].'{{{block}}}';
		}
		
		return $message;
	}
	
	// small helper to extract the blocks from a preset
	// if no preset is found, return the passed mail contents as is
	static function extract_blocks($preset_id, $contents = '') {
		// select the preset
		// temp hardcode the demo one
		$preset = self::find($preset_id);
		if(empty($preset->id)) return $contents;
				
		// read the file and parse the blocks
		$contents = stripslashes($preset->contents);

		// replace path to presets folder
		$presets_url = ($preset->is_gozaimasu and defined('GOZAIMASU_URL')) ? GOZAIMASU_URL : BFTPRO_URL;
		$contents = str_replace('{{{presets-url}}}', $presets_url . '/presets/', $contents);			
		
		$blocks = array();
		preg_match_all('/<!--arigato-block-start (\d+)-->(.*)<!--arigato-block-end (\d+)-->/sU', $contents, $blocks);
		
		return array($contents, $blocks);
	}
	
	// match contents to preset blocks when sending
	static function match_blocks($mail_contents, $preset_id, $signature = '', $open_tracker = '') {
		global $wpdb;
		$_sender = new BFTProSender();
		
		// extract blocks from the preset
		list($contents, $blocks) = self :: extract_blocks($preset_id, $mail_contents);		
		if(empty($blocks[0]) or !is_array($blocks[0])) return $contents;
		
		// extract blocks from the mail contents
		$mail_blocks = explode('{{{block}}}', $mail_contents);
		
		// foreach block in mail contnents replace the block in  preset
		$num_blocks = count($blocks);
		foreach($blocks[0] as $cnt => $block) {
			$mail_blocks[$cnt] = $_sender->maybe_nl2br($mail_blocks[$cnt]);
			$contents = str_replace($block, stripslashes($mail_blocks[$cnt]), $contents);			
		}
		
		// if there is closing HTML tag add the open tracker before it. If not, add at the end
		// add open tracker
		if(stristr($contents, '</body>')) {
			$contents = str_ireplace('</body>', $open_tracker . "\n</body>", $contents);
		}
		elseif(stristr($contents, '</html>')) {
			$contents = str_ireplace('</html>', $open_tracker . "\n</html>", $contents);
		}
		else $contents .= $open_tracker;
		
		return $contents . $signature;
	}
	
	// find all preset or just a given preset
	static function find($preset_id = null) {		
		global $wpdb;
	
		// select from DB
		$id_sql = empty($preset_id) ? "" : $wpdb->prepare("WHERE id=%d", $preset_id);
		$presets = $wpdb->get_results("SELECT * FROM ".BFTPRO_PRESETS." $id_sql ORDER BY name");
		if(!empty($preset_id) and !empty($presets[0])) return $presets[0];
		
		return $presets; // when returning them all
	} // end find
	
	// fetch the presets from the files and update them in the database
	static function fetch_presets() {
		global $wpdb;
		$presets = array();
		
		if ($handle = opendir(BFTPRO_PATH . '/presets')) {
		    /* This is the correct way to loop over the directory. */
		    while (false !== ($entry = readdir($handle))) {
		        if(preg_match("/(.*)\.html/", $entry)) {
		        	   // name, path and ID
		        	   $thumb = str_replace(".html", ".png", $entry);		        	   		        	 
		        	   $contents = file_get_contents(BFTPRO_PATH."/presets/".$entry);
		        	   $nameparts = explode('|ARIGATOPRONAME|', $contents);
		        	   $name = $nameparts[1];
		        	   
						$preset = array("file" => "presets/".$entry,  "thumb" => $thumb, "name" => $name, "contents"=>$contents, "is_gozaimasu"=>0);
						$presets[] = $preset;
		        }
		    }
		    closedir($handle);
		}
		
		if(class_exists('Gozaimasu')) {
			if ($handle = opendir(GOZAIMASU_PATH . '/presets')) {
			    /* This is the correct way to loop over the directory. */
			    while (false !== ($entry = readdir($handle))) {
			        if(preg_match("/(.*)\.html/", $entry)) {
			        	   // name, path and ID
			        	   $thumb = str_replace(".html", ".png", $entry);		        	   		        	 
			        	   $contents = file_get_contents(GOZAIMASU_PATH."/presets/".$entry);
			        	   $nameparts = explode('|ARIGATOPRONAME|', $contents);
			        	   $name = $nameparts[1];
			        	   
							$preset = array("file" => "presets/".$entry,  "thumb" => $thumb, "name" => $name, "contents"=>$contents, "is_gozaimasu"=>1);
							$presets[] = $preset;
			        }
			    }
			    closedir($handle);
			}
		}  // gozaimasu presets
		
		// now update or insert in the DB
		foreach($presets as $preset) {
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".BFTPRO_PRESETS." WHERE file=%s", $preset['file']));
						
			if($exists) {
				$wpdb->query($wpdb->prepare("UPDATE ".BFTPRO_PRESETS." SET contents=%s, name=%s WHERE id=%d", $preset['contents'], $preset['name'], $exists));	
			}
			else {
				 $wpdb->query($wpdb->prepare("INSERT INTO ".BFTPRO_PRESETS." SET
					file=%s, name=%s, contents=%s, thumb=%s, is_gozaimasu=%d", 
					$preset['file'], $preset['name'], $preset['contents'], $preset['thumb'], $preset['is_gozaimasu']));
			}
		} // end foreach preset	
	}
	
	// displays a page to select preset and then continue to the new newsletter or new email page
	static function choose() {
      global $wpdb;
		$presets = self ::  find();		
		
		// visiting for the first time or coming from a link to fetch
		if(empty($presets) or !empty($_GET['fetch_presets'])) {
			self :: fetch_presets();
			$presets = self ::  find();			
		}
		
		// if it's autoresponder, select the campaign
		if($_GET['from_page'] == 'ar') {
			$campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . BFTPRO_ARS . " WHERE id=%d", $_GET['campaign_id']));
		}		
		
		include(BFTPRO_PATH."/views/choose-preset.html.php");
	}
}