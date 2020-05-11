<?php
class BFTProWidget extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
	 		'bftpro_widget', // Base ID
			'BroadFast Mailing List Widget', // Name
			array( 'description' => __( 'BroadFast Autoresponder Widget', 'bftpro' ), )
		);
	}
	
	/**
	 * Front-end display of widget.	
	 */
	public function widget( $args, $instance ) {
		require_once(BFTPRO_PATH."/models/list.php");
		require_once(BFTPRO_PATH."/models/field.php");
		extract( $args );
				
		// get widget contents
		$list_id= @$instance['list_id'];
		$_list = new BFTProList();
		$list = $_list->select($list_id);
		
		
		echo $before_widget;
		if ( ! empty( $list->id ) ) {
			echo $before_title . $instance['title'] . $after_title;
			$_list->signup_form($list->id);
		}			
		echo $after_widget;
	}	
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['list_id'] = strip_tags( $new_instance['list_id'] );
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}	
	
	public function form( $instance ) {
		require_once(BFTPRO_PATH."/models/list.php");
		$_list = new BFTProList();
		
		if ( isset( $instance[ 'title' ]) ) $title = $instance[ 'title' ];
		else $title=__("Subscribe By Email", 'bftpro');		
		
		if ( isset( $instance[ 'list_id' ]) ) $list_id = $instance[ 'list_id' ];
		else $list_id=0;		
		// select all mailing lists
		$lists=$_list->select();
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' , 'bftpro'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />	</p>
		
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Mailing List To Display:' , 'bftpro'); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'list_id' ); ?>" name="<?php echo $this->get_field_name( 'list_id' ); ?>">
			<?php foreach($lists as $list):?>
				<option value="<?php echo $list->id?>"<?php if($list->id==$list_id) echo " selected"?>><?php echo $list->name?></option>
			<?php endforeach;?>		
		</select></p>
		<?php 
	}
} // class BFTProWidget