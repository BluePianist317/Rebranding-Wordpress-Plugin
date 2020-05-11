<?php
/**
 * The template for unsubscribing from a single mailing list
 *
 */
if(empty($in_shortcode)):
get_header(); ?>	

		<div id="container">
			<div id="content" role="main" style="margin:auto;width:50%;" class="bftpro-unsubscribe">
				<h1><?php printf(__('Unsubscribe from mailing list %s', 'bftpro'), stripslashes($selected_list->name)); ?></h1>
<?php endif;?>			
				
				<form method="post" class="bftpro" action="">
					<p><?php _e("Are you sure you want to unsubscribe? Please enter your email and confirm.", 'bftpro')?></p>
					<p><input type="text" name="email" value="<?php echo @$_GET['email']?>"></p>
					
					<input type="hidden" name="list_ids[]" value="<?php echo $selected_list->id?>">
					
					<?php if(!empty($unsubscribe_reasons)):?>
					<div class="bftpro-inside">
						<?php _e('Reason to unsubscribe:', 'bftpro');?>
						<ul>
							<?php foreach($unsubscribe_reasons as $reason):?>
								<li><input type="radio" name="reason" value="<?php echo htmlentities(stripslashes($reason));?>"> <?php echo stripslashes($reason);?></li>
							<?php endforeach;?>
							<?php if(!empty($unsubscribe_reasons_other)):?>
								<li><input type="radio" name="reason" value="other"> <?php _e('Other (specify):', 'bftpro');?> <input type="text" name="other_reason"></li>
							<?php endif;?>
						</ul>
					</div>
					<?php endif;?>				
							
					<p><input type="submit" value="<?php _e('Unsubscribe me', 'bftpro');?>"> <input type="button" value="<?php _e('Cancel', 'bftpro');?>" onclick="window.location='<?php echo home_url();?>'"></p>
					<input type="hidden" name="ok" value="1">
				</form>		
				
				<?php if(empty($in_shortcode)):	?>
			</div>
		</div>
				
<?php get_footer();
endif; ?>