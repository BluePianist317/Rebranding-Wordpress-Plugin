<div class="wrap">
	<h1><?php printf(__("Import Subscribers In Mailing List '%s'", 'bftpro'), stripslashes($list->name));?></h1>
	
	<?php if(!empty($success)):?>
	<p class="bftpro-alert"><?php echo $success?></p>
	<?php endif;?>
	
		<p><a href="admin.php?page=bftpro_subscribers&id=<?php echo $list->id?>"><?php _e('Back to manage subscribers', 'bftpro')?></a></p>
	
	<form method="post" enctype="multipart/form-data" class="bftpro">
	<div class="postbox wp-admin" style="padding:15px;">
		<p></p>
		
		<div><label><?php _e("Field separator in the CSV file:", 'bftpro')?></label> <input type="text" name="delimiter" value="," size="4"> 
		<?php _e("For tabulator enter <strong>tab</strong>", 'bftpro')?></div>
		<div><label><?php _e("Column numbers of <strong>Email, Name</strong>:", 'bftpro')?></label> <input type="text" name="sequence" value="1,2" size="4">
			<p></p>	
			
			<h3></h3>
		</div>
		
		<p><?php _e("<strong>Additional fields column numbers:</strong><br>", 'bftpro')?></p>
			
		<div><label><?php _e("Column number <br> of <strong>IP Address</strong>:", 'bftpro')?></label> <input type="text" name="ipnum" value="" size="3"> <?php _e('(Optional)', 'bftpro')?></div>			
		<div><label><?php _e("Signup date", 'bftpro')?>:</label> <input type="text" name="date" size="3"> <?php _e('Supported format: YYYY-MM-DD', 'bftpro');?></div>
		
		<?php foreach($fields as $field):?>
			<div><label><?php echo $field->label?>:</label> <input type="text" name="fieldseq_<?php echo $field->id?>" size="3"></div>
		<?php endforeach; ?>
		
		<hr>
		
		<p><i>
		
	</i></p>
		
		<hr>	
		<div><p><input type="checkbox" name="skip_first" value="1"> <?php _e("Skip first line (column titles) when importing", 'bftpro')?></p></div>	
		<div><p><input type="checkbox" name="no_duplicates" value="1"> <?php _e("If subscriber exists update their data (can slow down the import if you are importing large mailing list).", 'bftpro')?></p></div>
		
		<div><label><?php _e("Upload CSV file:", 'bftpro')?></label> <input type="file" name="csv"> <strong></strong></div>
		
		<p><input type="checkbox" name="import_fails" value="1"> <?php _e('Try checking this box if you have any problems imposing and see if it helps', 'bftpro')?></p>
		
		<div><input type="submit" name="import" value="<?php _e('Import Members', 'bftpro')?>"></div>	
	</div>
	<?php wp_nonce_field('bftpro_import');?>
	</form>
</div>	