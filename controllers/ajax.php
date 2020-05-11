<?php
// handles various ajax functions
class BFTPROAjax {
	static function dispatch(){
		global $wpdb;
		$type = @$_POST['type'];
		switch($type) {
			case 'list_fields':
				// show custom fields from a mailing list
				$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . BFTPRO_FIELDS. " 
					WHERE list_id=%d ORDER BY name", intval($_POST['list_id'])));					
				?>
				<p><?php _e("Custom fields", 'bftpro')?></p>
				<ul>
					<?php foreach($fields as $field):?>
						<li><strong>{{<?php echo $field->name?>}}</strong> - <?php echo stripslashes($field->label);?></li>
					<?php endforeach;?>
				</ul>
				<?php	
			break;
			
			// set var in session when Gozaimasu design is closed
			case 'goz_close':
				$_SESSION['gozaimasu_closed_'.intval($_POST['design_id'])] = 1;
			break;			
			
			default:
				die("No action");
			break;
		}
		exit;
	} // end dispatch	 
}