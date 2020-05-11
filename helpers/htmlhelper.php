<?php 
// contains little procedural functions to output various HTML strings

// Adapted code from the MIT licensed QuickDD class
// created also by me
function BFTProQuickDDDate($name, $date=NULL, $format=NULL, $markup=NULL, $start_year=1900, $end_year=2100) {
   // normalize params
   if(empty($date) or !preg_match("/\d\d\d\d\-\d\d-\d\d/",$date)) $date=date("Y-m-d");
    if(empty($format)) $format="YYYY-MM-DD";
    if(empty($markup)) $markup=array();

    $parts=explode("-",$date);
    $html="";

    // read the format
    $format_parts=explode("-",$format);

    $errors=array();
        
    // let's output
    foreach($format_parts as $cnt=>$f) {
        if(preg_match("/[^YMD]/",$f)) { 
            $errors[]="Unrecognized format part: '$f'. Skipped.";
            continue;
        }

        // year
        if(strstr($f,"Y")) {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."year\"".$extra_html.">\n";

            for($i=$start_year;$i<=$end_year;$i++) {
                $selected="";
                if(!empty($parts[0]) and $parts[0]==$i) $selected=" selected";
                
                $val=$i;
                // in case only two digits are passed we have to strip $val for displaying
                // it's either 4 or 2, everything else is ignored
                if(strlen($f)<=2) $val=substr($val,2);        
                
                $html.="<option value='$i'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }

        // month
        if(strstr($f,"M")) {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."month\"".$extra_html.">\n";

            for($i=1;$i<=12;$i++)
            {
                $selected="";
                if(!empty($parts[1]) and intval($parts[1])==$i) $selected=" selected";
                
                $val=sprintf("%02d",$i);
                    
                $html.="<option value='$val'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }

        // day - we simply display 1-31 here, no extra intelligence depending on month
        if(strstr($f,"D")) {
            $extra_html="";
            if(isset($markup[$cnt]) and !empty($markup[$cnt])) $extra_html=" ".$markup[$cnt];
            $html.=" <select name=\"".$name."day\"".$extra_html.">\n";

            for($i=1;$i<=31;$i++)
            {
                $selected="";
                if(!empty($parts[2]) and intval($parts[2])==$i) $selected=" selected";
                
                if(strlen($f)>1) $val=sprintf("%02d",$i);
                else $val=$i;
                    
                $html.="<option value='$val'".$selected.">$val</option>\n";
            }

            $html.="</select>";    
        }
    }
    
    if(!empty($errors)) {
    	 foreach($errors as $error) $html .= $error."<br>";
    }
	
    // that's it, return dropdowns:
    return $html;
}

// safe redirect
function bftpro_redirect($url, $args = "") {
	// prepend some variable(s) to the URL
	if(!empty($args) and is_array($args)) $url = add_query_arg($args, $url);
	
	echo "<meta http-equiv='refresh' content='0;url=$url' />"; 
	exit;
}

// new line for CSV
function bftpro_define_newline() 
{
	// credit to http://yoast.com/wordpress/users-to-csv/
	$unewline = "\r\n";
	if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win')) {
	   $unewline = "\r\n";
	} else if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac')) {
	   $unewline = "\r";
	} else {
	   $unewline = "\n";
	}
	return $unewline;
}


function bftpro_get_mime_type() {
	// credit to http://yoast.com/wordpress/users-to-csv/
	$USER_BROWSER_AGENT="";

			if (preg_match('/OPERA(\/| )([0-9].[0-9]{1,2})/', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='OPERA';
			} else if (preg_match('/MSIE ([0-9].[0-9]{1,2})/',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='IE';
			} else if (preg_match('/OMNIWEB\/([0-9].[0-9]{1,2})/', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='OMNIWEB';
			} else if (preg_match('/MOZILLA\/([0-9].[0-9]{1,2})/', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='MOZILLA';
			} else if (preg_match('/KONQUEROR\/([0-9].[0-9]{1,2})/', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
		    	$USER_BROWSER_AGENT='KONQUEROR';
			} else {
		    	$USER_BROWSER_AGENT='OTHER';
			}

	$mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
				? 'application/octetstream'
				: 'application/octet-stream';
	return $mime_type;
}

// displays session flash, errors etc, and clears them if required
function bftpro_display_alerts()
{
	global $error, $success;
	
	if(!empty($_SESSION['flash']))
	{
		echo "<div class='bftpro-alert'><p>".$_SESSION['flash']."</p></div>";
		unset($_SESSION['flash']);
	}
	
	if(!empty($error)){
		echo '<div class="bftpro-error"><p>'.$error.'</p></div>';
	}
	
	if(!empty($success)){
		echo '<div class="bftpro-success"><p>'.$success.'</p></div>';
	}
}

function bftpro_datetotime($date) {
	list($year, $month, $day) = explode("-",$date);
	return mktime(1, 0, 0, $month, $day, $year);
}

/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * @author Tristan Jahier
 * thanks to http://tristan-jahier.fr/blog/2013/08/convertir-un-format-de-date-php-en-format-de-date-jqueryui-datepicker
 */
if(!function_exists('dateformat_PHP_to_jQueryUI')) { 
	function dateformat_PHP_to_jQueryUI($php_format) {
	    $SYMBOLS_MATCHING = array(
	        // Day
	        'd' => 'dd',
	        'D' => 'D',
	        'j' => 'd',
	        'l' => 'DD',
	        'N' => '',
	        'S' => '',
	        'w' => '',
	        'z' => 'o',
	        // Week
	        'W' => '',
	        // Month
	        'F' => 'MM',
	        'm' => 'mm',
	        'M' => 'M',
	        'n' => 'm',
	        't' => '',
	        // Year
	        'L' => '',
	        'o' => '',
	        'Y' => 'yy',
	        'y' => 'y',
	        // Time
	        'a' => '',
	        'A' => '',
	        'B' => '',
	        'g' => '',
	        'G' => '',
	        'h' => '',
	        'H' => '',
	        'i' => '',
	        's' => '',
	        'u' => ''
	    );
	    $jqueryui_format = "";
	    $escaping = false;
	    for($i = 0; $i < strlen($php_format); $i++)
	    {
	        $char = $php_format[$i];
	        if($char === '\\') // PHP date format escaping character
	        {
	            $i++;
	            if($escaping) $jqueryui_format .= $php_format[$i];
	            else $jqueryui_format .= '\'' . $php_format[$i];
	            $escaping = true;
	        }
	        else
	        {
	            if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
	            if(isset($SYMBOLS_MATCHING[$char]))
	                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
	            else
	                $jqueryui_format .= $char;
	        }
	    }
	    return $jqueryui_format;
	}
}

// enqueue the localized and themed datepicker
function bftpro_enqueue_datepicker() {
	$locale_url = get_option('bftpro_locale_url');	
	wp_enqueue_script('jquery-ui-datepicker');	
	if(!empty($locale_url)) {
		// extract the locale
		$parts = explode("datepicker-", $locale_url);
		$sparts = explode(".js", $parts[1]);
		$locale = $sparts[0];
		wp_enqueue_script('jquery-ui-i18n-'.$locale, $locale_url, array('jquery-ui-datepicker'));
	}
	$css_url = get_option('bftpro_datepicker_css');
	if(empty($css_url)) $css_url = '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css';
	wp_enqueue_style('jquery-style', $css_url);
}

// in case email address is in format Name <email@dot.com> extracts only the email address
function bftpro_extract_email($email) {
	if(strstr($email, "<")) {
			$parts = explode("<", $email);
			$email = str_replace(">", '', $parts[1]);
		}		
		
	return $email;	
}

// strip tags when user is not allowed to use unfiltered HTML
// keep some safe tags on
function bftpro_strip_tags($content) {
   if(!current_user_can('unfiltered_html')) {
		$content = strip_tags($content, '<b><i><em><u><a><p><br><div><span><hr><font><img>');
	}
	
	return $content;
}

// makes sure all values in array are ints. Typically used to sanitize POST data from multiple checkboxes
function bftpro_int_array($value) {
   if(empty($value) or !is_array($value)) return array();
   $value = array_filter($value, 'intval');
   return $value;
}

// convert sender from Name, email@dot.com to Name <email@dot.com>
function bftpro_convert_sender($sender) {
	if(strstr($sender, ',') and !strstr($sender, '<')) {
		$parts = explode(',', $sender);
		$sender = trim($parts[0]).' <'.$parts[1].'>';
	}
	
	return $sender;
}

// output responsive table CSS in admin pages (and not only)
function bftpro_resp_table_css($screen_width = 600) {
	?>
/* Credits:
 This bit of code: Exis | exisweb.net/responsive-tables-in-wordpress
 Original idea: Dudley Storey | codepen.io/dudleystorey/pen/Geprd */
  
@media screen and (max-width: <?php echo $screen_width?>px) {
    table.arigato-pro-table {width:100%;}
    table.arigato-pro-table thead {display: none;}
    table.arigato-pro-table tr:nth-of-type(2n) {background-color: inherit;}
    table.arigato-pro-table tr td:first-child {background: #f0f0f0; font-weight:bold;font-size:1.3em;}
    table.arigato-pro-table tbody td {display: block;  text-align:center;}
    table.arigato-pro-table tbody td:before { 
        content: attr(data-th); 
        display: block;
        text-align:center;  
    }
}
	<?php
} // end bftpro_resp_table_css()

function bftpro_resp_table_js() {
	?>
/* Credits:
This bit of code: Exis | exisweb.net/responsive-tables-in-wordpress
Original idea: Dudley Storey | codepen.io/dudleystorey/pen/Geprd */
  
var headertext = [];
var headers = document.querySelectorAll("thead");
var tablebody = document.querySelectorAll("tbody");

for (var i = 0; i < headers.length; i++) {
	headertext[i]=[];
	for (var j = 0, headrow; headrow = headers[i].rows[0].cells[j]; j++) {
	  var current = headrow;
	  headertext[i].push(current.textContent);
	  }
} 

for (var h = 0, tbody; tbody = tablebody[h]; h++) {
	for (var i = 0, row; row = tbody.rows[i]; i++) {
	  for (var j = 0, col; col = row.cells[j]; j++) {
	    col.setAttribute("data-th", headertext[h][j]);
	  } 
	}
}
<?php
} // end bftpro_resp_table_js