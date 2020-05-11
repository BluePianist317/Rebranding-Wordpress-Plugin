[bftpro-form-start <?php echo $list->id?> orientation="vertical" labels="block"]
<?php if(!empty($atts['form_name'])):?><input type="hidden" name="bftpro_form_name" value="<?php echo esc_attr($atts['form_name'])?>"><?php endif;
if(!empty($atts['passed_design_id'])):?><input type="hidden" name="goz_design_id" value="<?php echo esc_attr($atts['passed_design_id'])?>"><?php endif;
if(!empty($atts['ab_test_id'])):?><input type="hidden" name="goz_ab_test_id" value="<?php echo esc_attr($atts['ab_test_id'])?>"><?php endif;?>
<?php if(empty($visual_mode)) echo "<fieldset>\n"; 
$this->fields(@$list->id, null, $visual_mode);?><?php if(!empty($recaptcha_html)):?>[bftpro-recaptcha]
<?php endif;?><?php if(!empty($text_captcha_html)):?>[bftpro-text-captcha]
<?php endif;?>
<?php if(empty($visual_mode)) echo "<div class='bftpro-form-group bftpro-btn-block'>";?>[bftpro-submit-button <?php echo $list->id?> "<?php _e('Subscribe', 'bftpro')?>"]<?php if(empty($visual_mode)) echo "</div>";?>	
<?php if(!empty($atts['magnet_id'])):?>[bftpro-magnet id="<?php echo $atts['magnet_id']?>]<?php endif;?>
<?php if(!empty($atts['redirect_url'])):?>[bftpro-field-static redirect_url value="<?php echo $atts['redirect_url']?>]<?php endif;?>
<?php if(empty($visual_mode)) echo "</fieldset>\n";?>
[bftpro-form-end <?php echo $list->id?>]