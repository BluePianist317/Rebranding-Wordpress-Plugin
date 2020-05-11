<div class="wrap">
	<h1><?php echo empty($_GET['user_id'])?__("Add", 'bftpro'):__("Edit", 'bftpro')?> <?php printf(__('Subscriber In Mailing List "%s"', 'bftpro'), stripslashes($list->name));?></h1>
	
	<p><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>"><?php _e('Back to manage subscribers', 'bftpro')?></a></p>
	
	<?php if(!empty($error)):?>
		<div class="error"><p><?php echo $error?></p></div>
	<?php endif;?>
	
	<form method="post" class="bftpro" onsubmit="return validateBFTProUser(this);" enctype="multipart/form-data">
	<div class="postbox wp-admin" style="padding:5px;">
		<div class="inside">
			<p><label><?php echo __("User Name:", 'bftpro')?></label> <input type="text" name="name" value="<?php echo @$user['name']?>"></p>
			<p><label><?php echo __("User Email:", 'bftpro')?></label> <input type="text" name="email" value="<?php echo @$user['email']?>"></p>			
			<p><label><?php echo __("Registration Date:", 'bftpro')?></label> <?php echo BFTProQuickDDDate("date", @$user['date']);?></p>
			<p><label><?php echo __("Status:", 'bftpro')?></label> <select name="status">
				<option value="1" <?php if(empty($user['id']) or $user['status']) echo "selected"?>><?php echo __("Active", 'bftpro');?></option>
				<option value="0" <?php if(!empty($user['id']) and empty($user['status'])) echo "selected"?>><?php echo __("Inactive", 'bftpro');?></option>
			</select></p>	
			<?php $_list->extra_fields($list->id, @$user);?>
			<p><label><?php _e('Tags', 'bftpro');?></label> <input type="text" name="tags" value="<?php echo empty($user['tags']) ? '' : htmlspecialchars($user['tags'])?>">
			<em><?php _e('Optional, separated by comma', 'bftpro');?></em></p>
			<p>&nbsp;</p>
			<p><?php if(empty($_GET['user_id'])):?>
				<input type="submit" class="button button-primary" name="ok" value="<?php echo __('Add User', 'bftpro');?>">
			<?php else:?>
				<input type="submit" class="button button-primary" name="ok" value="<?php echo __('Save User', 'bftpro');?>">
				<input type="button" class="button" value="<?php echo __('Delete User', 'bftpro');?>" onclick="confirmDelete(this.form);">
				<input type="hidden" name="del" value="0">
			<?php endif;?>
			<input type="button" class="button" value="<?php echo __('Cancel', 'bftpro');?>" onclick="window.location='admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>'"></p>
		</div>
	</div>
	</form>
</div>	