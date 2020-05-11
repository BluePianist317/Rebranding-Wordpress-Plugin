<?php
/**
 * The Template for displaying BFTPRO messages
 *
 */
if(empty($in_shortcode)):
get_header(); ?>
		<div id="container">
			<div id="content" role="main">		
<?php endif;?>		
				<h1 align="center"><?php _e("An Error Occured", 'bftpro');?></h1>
				<p align="center"><?php echo $message;?></p>
				
<?php if(empty($in_shortcode)):?>				
			</div>
		</div>				
				
<?php get_footer();
endif; ?>