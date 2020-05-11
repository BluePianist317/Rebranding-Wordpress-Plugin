<?php
// advanced stats & unsubscribe stats 
class ArigatoPROStats {
	static function main() {
		if(empty($_GET['tab']) or $_GET['tab'] == 'advanced') self :: advanced_stats();
		else self :: unsubscribe_stats();
	}	
	
	// main  / performance stats
	static function advanced_stats() {
		global $wpdb;
		
		// from & to dates
		$from_date = empty($_POST['from_date']) ? date("Y-m", current_time('timestamp')) . "-01" : $_POST['from_date'];
		$to_date = empty($_POST['to_date']) ? date("Y-m-d", current_time('timestamp')) : $_POST['to_date'];
		
		// select top 5 email campaigns. With num. sent, % opens, num clicks, % unsubs
		// order by sent
		$campaigns = $wpdb->get_results($wpdb->prepare("SELECT tC.id as id, tC.name as name, COUNT(tS.id) as sentmails
			FROM ".BFTPRO_ARS." tC JOIN ".BFTPRO_MAILS." tM ON tM.ar_id=tC.id
			JOIN ".BFTPRO_SENTMAILS." tS ON tS.mail_id=tM.id
			AND tS.date >= %s AND tS.date <= %s AND tS.errors=''
			GROUP BY tC.id HAVING sentmails > 0 
			ORDER BY sentmails DESC LIMIT 5", $from_date, $to_date));		
			
		if(method_exists('BFTIUrls', 'campaign_clicks')) $clicks_counted = true;	
			
		// now fill % opens, num clicks, % unsubs
		foreach($campaigns as $cnt => $campaign) {
			$num_opens = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tR.id) 
				FROM ".BFTPRO_READMAILS." tR 
				WHERE tR.mail_id IN (SELECT tM.id FROM ".BFTPRO_MAILS." tM WHERE ar_id=%d)
				AND tR.date >= %s AND tR.date <=%s ", $campaign->id, $from_date, $to_date));
				
			$percent_open = round(100 * $num_opens / $campaign->sentmails);
			
			// num & % unsubs
			$num_unsubs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tU.id) 
				FROM ".BFTPRO_UNSUBS." tU 
				WHERE tU.mail_id IN (SELECT tM.id FROM ".BFTPRO_MAILS." tM WHERE ar_id=%d)
				AND tU.mcat='ar'
				AND tU.date >= %s AND tU.date <=%s ", $campaign->id, $from_date, $to_date));
				
			$percent_unsub = round(100 * $num_unsubs / $campaign->sentmails);
			
			$campaigns[$cnt]->num_opens = $num_opens;
			$campaigns[$cnt]->percent_open = $percent_open;
			$campaigns[$cnt]->num_unsubs = $num_unsubs;
			$campaigns[$cnt]->percent_unsub = $percent_unsub;	
			
			// num clicks? - from Intelligence module
			if(!empty($clicks_counted)) {
				$num_clicks = BFTIUrls :: campaign_clicks($campaign->id, $from_date, $to_date);
				$campaigns[$cnt]->num_clicks = $num_clicks;
			}
		}	
		
		// select top 5 newsletters. With num. sent, % opens, num clicks, % unsubs,  sent to (list), sent on date
		// order by sent 
		$newsletters = $wpdb->get_results($wpdb->prepare("SELECT tN.id as id, tN.subject as subject, COUNT(tS.id) as sentmails
			FROM ".BFTPRO_NEWSLETTERS." tN JOIN ".BFTPRO_SENTMAILS." tS ON tS.newsletter_id=tN.id
			AND tS.date >= %s AND tS.date <= %s AND tS.errors=''
			GROUP BY tN.id HAVING sentmails > 0 
			ORDER BY sentmails DESC LIMIT 5", $from_date, $to_date));
			
		foreach($newsletters as $cnt => $newsletter) {
			$num_opens = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tR.id) 
				FROM ".BFTPRO_READNLS." tR 
				WHERE tR.newsletter_id=%d
				AND tR.date >= %s AND tR.date <=%s ", $newsletter->id, $from_date, $to_date));
			$percent_open = round(100 * $num_opens / $newsletter->sentmails);	
			
			// num & % unsubs
			$num_unsubs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tU.id) 
				FROM ".BFTPRO_UNSUBS." tU 
				WHERE tU.mail_id = %d AND tU.mcat='nl'
				AND tU.date >= %s AND tU.date <=%s ", $newsletter->id, $from_date, $to_date));
				
			$percent_unsub = round(100 * $num_unsubs / $newsletter->sentmails);
			
			$newsletters[$cnt]->num_opens = $num_opens;
			$newsletters[$cnt]->percent_open = $percent_open;
			$newsletters[$cnt]->num_unsubs = $num_unsubs;
			$newsletters[$cnt]->percent_unsub = $percent_unsub;	
			
			// num clicks? - from Intelligence module
			if(!empty($clicks_counted)) {
				$num_clicks = BFTIUrls :: nl_clicks($newsletter->id, $from_date, $to_date);
				$newsletters[$cnt]->num_clicks = $num_clicks;
			}
		}	
		
		// select top 5 subscribers with emails received, clciks, opens, subscribed date, list
		// order by clicks
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.id as id, tU.email as email, 
			COUNT(tS.id) as cnt_mails, tL.name as list_name, tU.date as date
			FROM ".BFTPRO_USERS." tU JOIN ".BFTPRO_SENTMAILS." tS ON tS.user_id=tU.id
			JOIN ".BFTPRO_LISTS." tL ON tL.id = tU.list_id
			WHERE tS.date >= %s AND tS.date <= %s AND tS.errors='' GROUP BY tU.id 
			ORDER BY cnt_mails DESC LIMIT 5", $from_date, $to_date));
			
		foreach($users as $cnt=>$user) {
			$opens = 0;
			$nl_opens = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_READNLS."
				WHERE user_id=%d AND date >= %s AND date <= %s", $user->id, $from_date, $to_date));
			$armail_opens = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_READMAILS."
				WHERE user_id=%d AND date >= %s AND date <= %s", $user->id, $from_date, $to_date));	
			$opens = $nl_opens + $armail_opens;	
			$users[$cnt]->opens = $opens;
			
			if(!empty($clicks_counted)) {
				$num_clicks = BFTIUrls :: user_clicks($user->id, $from_date, $to_date);
				$users[$cnt]->num_clicks = $num_clicks;
			}
		}	
				
		// select top 5 clickable links if this feature is enabled 
		// include link title, clicks, email in, num sent
		if(!empty($clicks_counted)) $links = BFTIUrls :: top_links($from_date, $to_date);
		
		// select num subscribers by source, arrange in a pie chart (Peity)
		$sources = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) as cnt, source FROM ".BFTPRO_USERS."
			WHERE date >= %s AND date <= %s AND (status=1 or unsubscribed=1) 
			GROUP BY source ORDER BY cnt DESC", $from_date, $to_date));  	
		$num_sources = count($sources);
		
		$dateformat = get_option('date_format');
		bftpro_enqueue_datepicker();
		include(BFTPRO_PATH . '/views/stats.html.php');
	}
	
	// unsubscribe stats
	static function unsubscribe_stats() {
		global $wpdb;
		
		// from & to dates
		$from_date = empty($_POST['from_date']) ? date("Y-m", current_time('timestamp')) . "-01" : $_POST['from_date'];
		$to_date = empty($_POST['to_date']) ? date("Y-m-d", current_time('timestamp')) : $_POST['to_date'];
		
		// select up to 5 worst performing newsletters in the given period
		$newsletters = $wpdb->get_results($wpdb->prepare("SELECT tN.id as id, tN.subject as subject, COUNT(tU.id) as cnt_unsubs
		FROM ".BFTPRO_NEWSLETTERS." tN JOIN ".BFTPRO_UNSUBS." tU ON tU.mail_id=tN.id AND tU.mcat='nl'
		AND tU.date >= %s AND tU.date <= %s 
		GROUP BY tN.id HAVING cnt_unsubs > 0 
		ORDER BY cnt_unsubs DESC LIMIT 5", $from_date, $to_date));
		
		// foreach newsletter get num sent and % unsusbs	
		foreach($newsletters as $cnt => $newsletter) {
			$num_sent = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_SENTMAILS." tS 
				WHERE newsletter_id = %d AND date >= %s AND date <= %s", $newsletter->id, $from_date, $to_date));
				
			if($num_sent) {
				$perc_unsub = round(100 * $newsletter->cnt_unsubs / $num_sent);
				$perc_unsub = sprintf(__('%d%%', 'bftpro'), $perc_unsub);
			}
			else $perc_unsub = __('n/a', 'bftpro');	
			
			$newsletters[$cnt]->num_sent = $num_sent;
			$newsletters[$cnt]->perc_unsub = $perc_unsub;
		}			
		
		// select up to 5 worst performing email campaigns in the given period
		$campaigns = $wpdb->get_results($wpdb->prepare("SELECT tC.id as id, tC.name as name, COUNT(tU.id) as cnt_unsubs			
			FROM ".BFTPRO_ARS." tC JOIN ".BFTPRO_MAILS." tM ON tM.ar_id=tC.id
			JOIN ".BFTPRO_UNSUBS." tU ON tU.mail_id=tM.id AND tU.mcat='ar'
			AND tU.date >= %s AND tU.date <= %s 
			GROUP BY tC.id HAVING cnt_unsubs > 0 
			ORDER BY cnt_unsubs DESC LIMIT 5", $from_date, $to_date));		
			
		// foreach campaign select num sent and % unsubs
		foreach($campaigns as $cnt => $campaign) {
			$num_sent = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_SENTMAILS." tS 
				WHERE mail_id IN (SELECT tM.id FROM ".BFTPRO_MAILS." tM WHERE tM.ar_id=%d)
				AND date >= %s AND date <= %s", $campaign->id, $from_date, $to_date));
				
			if($num_sent) {
				$perc_unsub = round(100 * $campaign->cnt_unsubs / $num_sent);
				$perc_unsub = sprintf(__('%d%%', 'bftpro'), $perc_unsub);
			}
			else $perc_unsub = __('n/a', 'bftpro');	
			
			$campaigns[$cnt]->num_sent = $num_sent;
			$campaigns[$cnt]->perc_unsub = $perc_unsub;
		}	
		
		// select up to 5 worst performing AR emails in the given period
		$armails = $wpdb->get_results($wpdb->prepare("SELECT tM.id as id, tM.subject as subject, 
		COUNT(tU.id) as cnt_unsubs, tM.ar_id as ar_id
		FROM ".BFTPRO_MAILS." tM JOIN ".BFTPRO_UNSUBS." tU ON tU.mail_id=tM.id AND tU.mcat='ar'
		AND tU.date >= %s AND tU.date <= %s 
		GROUP BY tM.id HAVING cnt_unsubs > 0 
		ORDER BY cnt_unsubs DESC LIMIT 5", $from_date, $to_date));
		
		// foreach newsletter get num sent and % unsusbs	
		foreach($armails as $cnt => $armail) {
			$num_sent = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_SENTMAILS." tS 
				WHERE mail_id = %d AND date >= %s AND date <= %s", $armail->id, $from_date, $to_date));
				
			if($num_sent) {
				$perc_unsub = round(100 * $armail->cnt_unsubs / $num_sent);
				$perc_unsub = sprintf(__('%d%%', 'bftpro'), $perc_unsub);
			}
			else $perc_unsub = __('n/a', 'bftpro');	
			
			$armails[$cnt]->num_sent = $num_sent;
			$armails[$cnt]->perc_unsub = $perc_unsub;
		}			
		
		// exit poll?
		$unsubscribe_reasons = get_option('bftpro_unsubscribe_reasons');
		if(!empty(trim($unsubscribe_reasons))) $unsubscribe_reasons = explode(PHP_EOL, $unsubscribe_reasons); // turn into array
		$unsubscribe_reasons_other = get_option('bftpro_unsubscribe_reasons_other');
		
		if(!empty($unsubscribe_reasons)) {
			// select total number unsubscribed in these dates
			$num_total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_UNSUBS." WHERE date>=%s AND date<=%s", $from_date, $to_date));
			
			if($num_total) {
				// select num and percentage for each reason
				$reasons = array();
				$num_reasoned = 0;
				foreach($unsubscribe_reasons as $reason) {
					
					$num = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".BFTPRO_UNSUBS." 
						WHERE date>=%s AND date<=%s AND reason=%s", $from_date, $to_date, trim($reason)));
					$percent = round(100 * $num / $num_total);
					$num_reasoned += $num;
					
					$reasons[] = array("reason" => $reason, "num" => $num, "percent" => $percent);	
				}
				
				// select num and percentage for other, if enabled
				if(!empty($unsubscribe_reasons_other)) {
					$num = $num_total - $num_reasoned;
					$percent = round(100 * $num / $num_total);
					$reasons[] = array("reason" => __('Other', 'bftpro'), 'num' => $num, 'percent' => $percent);
				}
			}

		}	// end calculating per reason	
		
		$dateformat = get_option('date_format');
		bftpro_enqueue_datepicker();
		include(BFTPRO_PATH . '/views/unsub-stats.html.php');
	} // end unsubscribe_stats()
	
	// detailed stats for a given AR campaign
	// called by Ajax, returns table rows
	static function campaign_stats() {		
		global $wpdb;
		
		$from_date = $_POST['from_date'];
		$to_date = $_POST['to_date'];
		$class = $_POST['class'];
		if(method_exists('BFTIUrls', 'campaign_clicks')) $clicks_counted = true;	
		
		$mails = $wpdb->get_results($wpdb->prepare("SELECT tM.id as id, tM.subject as subject, COUNT(tS.id) as sentmails
			FROM ".BFTPRO_MAILS." tM JOIN ".BFTPRO_SENTMAILS." tS ON tS.mail_id=tM.id
			AND tS.date >= %s AND tS.date <= %s AND tS.errors=''
			WHERE tM.ar_id=%d
			GROUP BY tM.id 
			ORDER BY tM.id", $from_date, $to_date, $_POST['id']));			
			
		$html = '';	
			
		foreach($mails as $cnt => $mail) {
			$class = ('alternate' == $class) ? '' : 'alternate';				
			$html .= "<tr class='campaign-sub-row-".$_POST['id']." $class'><td>&nbsp;-&nbsp;<a href='admin.php?page=bftpro_ar_mails&do=edit&id=".$mail->id."&campaign_id=".$_POST['id']."' target='_blank'>" . stripslashes($mail->subject)."</a></td>";
			
			// avoid division by zero
			if(!$mail->sentmails) {
				if($clicks_counted) $html .= "<td>0</td>";
				$html .= "<td>0</td><td>0</td><td>0</td></tr>";
				continue;
			}			
			
			$num_opens = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tR.id) 
				FROM ".BFTPRO_READMAILS." tR 
				WHERE tR.mail_id=%d
				AND tR.date >= %s AND tR.date <=%s ", $mail->id, $from_date, $to_date));
			$percent_open = round(100 * $num_opens / $mail->sentmails);	
			
			// num & % unsubs
			$num_unsubs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tU.id) 
				FROM ".BFTPRO_UNSUBS." tU 
				WHERE tU.mail_id = %d AND tU.mcat='ar' 
				AND tU.date >= %s AND tU.date <=%s ", $mail->id, $from_date, $to_date));
				
			$percent_unsub = round(100 * $num_unsubs / $mail->sentmails);
			
			$html .= '<td>'.$mail->sentmails.'</td>';
			$html .= '<td>' . sprintf(__('%d (%d%%)', 'bftpro'), $num_opens, $percent_open).'</td>';

			// num clicks? - from Intelligence module
			if(!empty($clicks_counted)) {
				$num_clicks = BFTIUrls :: nl_clicks($mail->id, $from_date, $to_date);		
				$html .= '<td>' . $num_clicks .'</td>';		
			}			
			
			$html .= '<td>' . sprintf(__('%d (%d%%)', 'bftpro'), $num_unsubs, $percent_unsub) . '</td>';
						
			$html .= "</tr>";
		}		
		
		echo $html;
		exit;
	}
}