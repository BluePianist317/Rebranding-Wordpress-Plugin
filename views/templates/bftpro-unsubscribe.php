<?php
/**
 * The Template for displaying BFTPRO messages
 *
 */
if(empty($in_shortcode)):
get_header(); ?>	

		<div id="container">
			<div id="content" role="main" style="margin:auto;width:50%;" class="bftpro-unsubscribe">
				<h1><?php _e("Unsubscribe from mailing lists", 'bftpro') ?></h1>
<?php endif;?>			
				
				<form method="post" class="bftpro" action="">
					<p><?php _e("Please check all the lists that you wish to unsubscribe from and then submit the form.", 'bftpro')?></p>
					<p><input type="text" name="email" value="<?php echo @$_GET['email']?>"></p>
					
					<div class="bftpro-inside">
						<?php _e('You will be unsubscribed from the following lists:', 'bftpro')?>
						<ul>
							<?php foreach($users as $user):?>
								<li><input type="checkbox" name="list_ids[]" value="<?php echo $user->list_id?>"> <?php echo $user->list_name?></li>
							<?php endforeach;?>
						</ul>
					</div>	
					
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
					
					<p><input type="submit" value="<?php _e('Unsubscribe me', 'bftpro');?>"></p>
					<input type="hidden" name="ok" value="1">
				</form>		
				
				<?php if(empty($in_shortcode)):?>
			</div>
		</div>
				
<?php get_footer();
endif; ?>