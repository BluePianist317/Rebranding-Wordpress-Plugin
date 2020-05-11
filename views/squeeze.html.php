<?php
$squeeze_contents = trim(stripslashes(get_option('bftpro_squeeze_contents')));
$squeeze_contents = do_shortcode($squeeze_contents); 
echo $squeeze_contents;?>