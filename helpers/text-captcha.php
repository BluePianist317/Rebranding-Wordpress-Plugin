<?php
class BFTProTextCaptcha {
	// verify the captcha
	static function verify($question, $answer) {
		$answer = stripslashes($answer);
		
		$captcha_questions = get_option('bftpro_text_captcha');
		$captcha_questions = explode("\n", $captcha_questions);
		
		$question = base64_decode($question);
		
		foreach($captcha_questions as $captcha_question) {
			list($q, $a) = explode("=", $captcha_question);
			$q = trim($q);		
			$q = stripslashes($q);
			$a = stripslashes($a);
				
			if(strcmp($q, $question) == 0 and strcasecmp(trim($a), trim($answer)) == 0) return true;
		}		
		
		// in any other case return false
		return false;
	}
	
	// generate the captcha
	static function generate() {
		$captcha_questions = get_option('bftpro_text_captcha');
		$captcha_questions = explode("\n", $captcha_questions);
		
		// just get random
		shuffle($captcha_questions);
		$question = $captcha_questions[0];
		@list($q, $a) = @explode("=", $question);
		$q = stripslashes($q);
		
		return '<div class="bftpro-form-group"><label>'.trim($q)."</label> <input type='text' name='bftpro_text_captcha_answer'>\n<input type='hidden' name='bftpro_text_captcha_question' value=\"".base64_encode(trim($q))."\"></div>"; 
	}
}