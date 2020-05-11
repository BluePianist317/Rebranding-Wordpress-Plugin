<h1><?php printf(__("Custom Fields In Mailing List '%s'", 'bftpro'), stripslashes($list->name))?></h1>

<p><a href="admin.php?page=bftpro_fields&list_id=<?php echo $list->id?>&do=add"><?php _e("Click here to add field", 'bftpro')?></a>
| <a href="admin.php?page=bftpro_mailing_lists"><?php _e('Back to manage mailing lists', 'bftpro')?></a></p>

<?php if(count($fields)):?>
	<table class="widefat">
	<tr><th><?php _e("Field Label", 'bftpro')?></th><th><?php _e("Field Name", 'bftpro')?></th><th><?php _e("Field Type", 'bftpro')?></th>
	<th><?php _e("Is Required?", 'bftpro')?></th><th><?php _e("Variable/Mask", 'bftpro')?></th><th><?php _e("Edit/Delete", 'bftpro')?></th></tr>
	<?php foreach($fields as $field):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>"><td><?php echo stripslashes($field->label)?></td><td><?php echo $field->name?></td><td><?php echo $field->ftype?></td>
		<td><?php echo $field->is_required?__('Yes', 'bftpro'):__('No', 'bftpro')?></td>
		<td><input type="text" value="{{<?php echo $field->name?>}}" onclick="this.select();" readonly="readonly"></td>
		<td><a href="admin.php?page=bftpro_fields&list_id=<?php echo $list->id?>&do=edit&id=<?php echo $field->id?>"><?php _e("Edit", 'bftpro')?></a></td></tr>
	<?php endforeach;?>
	</table>
	
	<p><?php _e('The variable/mask is what you can use in your email messages sent to this mailing list. It will be replaced with the value entered by the subscriber.', 'bftpro');?></p>
<?php else:?>
	<p><?php _e("There are no custom fields in this list yet.", 'bftpro')?></p>
<?php endif;?>