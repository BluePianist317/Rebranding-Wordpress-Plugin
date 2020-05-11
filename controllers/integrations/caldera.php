<?php
// Caldera forms integration
class BFTProCaldera {
	static function get_form_processors($processors) {		
		$processors['arigatopro_caldera'] 	= array(			
			'name'              =>  'Arigato PRO',
			'description'       =>  __('Subscribe to Arigato PRO Mailing Lists', 'bftpro'),
			'pre_processor'	    =>  array(), // may add validation in the future, for now empty
			'processor' => array(__CLASS__, 'process'),
			'template' => BFTPRO_PATH . '/views/caldera-config.html.php'
		);
		return $processors;
	}
	
	static function process($config, $form, $process_id ) {
	}

	// the UI	
	static function config_fields( ) {
		global $wpdb;
		require_once(BFTPRO_PATH."/models/list.php");
		$_list = new BFTProList();
		
		$form = Caldera_Forms_Forms::get_form( $_GET[ 'edit' ] );
		
		// select arigato mailing lists
		$lists = $_list->select();
		$list_options = $email_field_options = $name_field_options = array();
		foreach($lists as $list) {
			$list_options[$list->id] = $list->name;
		}
		
		foreach($form['fields'] as $field) {
			if($field['type'] == 'email' or $field['type'] == 'hidden') $email_field_options[$field['ID']] = $field['label'];
			if($field['type'] == 'text' or $field['type'] == 'hidden') $name_field_options[$field['ID']] = $field['label'];
			
		} 
		
		return array(
		array(
			'id' => 'list_id',
			'label' => 'List:',
			'type' => 'dropdown',
			'options' => $list_options,
			'required' => true,
			'desc' => 'Select mailing list'
		),
	array(
			'id' => 'email_field',
			'label' => 'Email field:',
			'type' => 'dropdown',
			'options' => $email_field_options,
			'required' => true,
			'desc' => 'Select email field'
		),
		array(
			'id' => 'name_field',
			'label' => 'Name field:',
			'type' => 'dropdown',
			'options' => $name_field_options,
			'required' => true,
			'desc' => 'Select email field'
		),
	);
		
	}
}