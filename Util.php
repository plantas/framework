<?php

class Util {

	public static function formatDate($date, $format = 'd.m.Y.') {
		if (!$date || substr($date, 0, 10) == '0000-00-00') return '';
		return date($format, strtotime($date));
	}

	public static function formatDateTime($date, $format = 'd.m.Y. H:i') {
		if (!$date || substr($date, 0, 10) == '0000-00-00') return '';
		return date($format, strtotime($date));
	}

	public static function formatDateRange($startDate, $days = 1) {
		if ($days <= 1) return self::formatDate($startDate);

		$start = strtotime($startDate);
		$sd = date('d', $start);
		$sm = date('m', $start);
		$sy = date('Y', $start);
		$end = strtotime('+' . ($days - 1) . ' day', $start);
		$ed = date('d', $end);
		$em = date('m', $end);
		$ey = date('Y', $end);

		if ($sy != $ey) {
			return self::formatDate($start) . ' - ' . self::formatDate($end);
		}
		if ($sm != $em) {
			return date('d.m.', $start) . ' - ' . date('d.m.Y.', $end);  
		}
		return date('d.', $start) . ' - ' . date('d.m.Y.', $end);
	}

	public static function formatDateTimeRange($begin, $end, $format = 'd.m.Y. H:i') {
		$begin = self::formatDateTime($begin, $format);
		$end = self::formatDateTime($end, $format);
		$p1 = explode(' ', $begin);
		$p2 = explode(' ', $end);
		if ($p1[0] == $p2[0]) return $begin . ' - ' . $p2[1];
		return $begin . ' - ' . $end;
	}

	public static function formatBoolean($bool) {
		if ($bool) return Lang::get('Yes');
		return Lang::get('No');
	}

	public static function validationError($err) {
		if (empty($err)) return '';
		return '<div class="validation-error">' . implode('<br />', Util::escape($err)) . '</div>';
	}


	public static function escape($string) {
		if (!is_string($string)) {
			return $string;
		}
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	public static function filesize($s) {
		foreach (array('','K','M','G') as $i => $k) {
			if ($s < 1024) break;
			$s/=1024;
		}
		return sprintf("%.1f %sB",$s,$k);
	}

	// returns bar for $key = foo from url /asd/foo/bar
	public static function getValueFromUrl($url, $var) {
		$parts = explode('/', $url);
		if (!is_array($parts)) return null;

		// fix value if has leading slash remove it
		if (substr($var, 0, 1) == '/') $var = substr($var, 1);
		$index = array_search($var, $parts);
		if ($index === false) return null;		
		return isset($parts[$index+1]) ? $parts[$index+1] : null;
	}

	public static function arrayToQueryParams($value, $name = '') {
		$ret = array();
		if (is_array($value)) {
			foreach ($value as $k => $v) {
				if ($name) {
					$name2 = $name . '[' . $k . ']';
				} else {
					$name2 = $k;
				}
				if (is_array($v)) {
					$ret = array_merge($ret, self::arrayToQueryParams($v, $name2));
				} else {
					$ret[$name2] = $v;
				}
			}
		} else if ($name) {
			$ret[$name] = $value;
		}
		return $ret;
	}

	protected static $transliterateMap;
	public static function transliterate($in, $encoding='utf8') {
		if (!self::$transliterateMap) {
			self::$transliterateMap = array(
				/* Ă */ pack("n*",0xc482) =>  "A",  /* Â */ pack("n*",0xc382) =>  "A",  /* Ą */ pack("n*",0xc484) =>  "A",  /* Á */ pack("n*",0xc381) =>  "A",  
				/* Ä */ pack("n*",0xc384) =>  "A",  /* À */ pack("n*",0xc380) =>  "A",  /* Ã */ pack("n*",0xc383) =>  "A",  /* Å */ pack("n*",0xc385) =>  "A",  
				/* Æ */ pack("n*",0xc386) => "AE",  /* Ç */ pack("n*",0xc387) =>  "C",  /* Ć */ pack("n*",0xc486) =>  "C",  /* Č */ pack("n*",0xc48c) =>  "C",  
				/* Ď */ pack("n*",0xc48e) =>  "D",  /* Đ */ pack("n*",0xc490) =>  "Dj", /* Ð */ pack("n*",0xc390) => "DH", /* Ê */ pack("n*",0xc38a) =>  "E",  
				/* È */ pack("n*",0xc388) =>  "E",  /* Ę */ pack("n*",0xc498) =>  "E",  /* Ë */ pack("n*",0xc38b) =>  "E",  /* Ě */ pack("n*",0xc49a) =>  "E",  
				/* É */ pack("n*",0xc389) =>  "E",  /* Î */ pack("n*",0xc38e) =>  "I",  /* Ì */ pack("n*",0xc38c) =>  "I",  /* Ï */ pack("n*",0xc38f) =>  "I",  
				/* Í */ pack("n*",0xc38d) =>  "I",  /* Ĺ */ pack("n*",0xc4b9) =>  "L",  /* Ł */ pack("n*",0xc581) =>  "L",  /* Ľ */ pack("n*",0xc4bd) =>  "L",  
				/* Ñ */ pack("n*",0xc391) =>  "N",  /* Ń */ pack("n*",0xc583) =>  "N",  /* Ň */ pack("n*",0xc587) =>  "N",  /* Ó */ pack("n*",0xc393) =>  "O",  
				/* Ô */ pack("n*",0xc394) =>  "O",  /* Õ */ pack("n*",0xc395) =>  "O",  /* Ø */ pack("n*",0xc398) =>  "O",  /* Ö */ pack("n*",0xc396) =>  "O",  
				/* Ő */ pack("n*",0xc590) =>  "O",  /* Ò */ pack("n*",0xc392) =>  "O",  /* Œ */ pack("n*",0xc592) => "OE",  /* Ř */ pack("n*",0xc598) =>  "R",  
				/* Ŕ */ pack("n*",0xc594) =>  "R",  /* Ş */ pack("n*",0xc59e) =>  "S",  /* Ś */ pack("n*",0xc59a) =>  "S",  /* Š */ pack("n*",0xc5a0) =>  "S",  
				/* Ţ */ pack("n*",0xc5a2) =>  "T",  /* Ť */ pack("n*",0xc5a4) =>  "T",  /* Þ */ pack("n*",0xc39e) => "TH",  /* Ù */ pack("n*",0xc399) =>  "U",  
				/* Ú */ pack("n*",0xc39a) =>  "U",  /* Ů */ pack("n*",0xc5ae) =>  "U",  /* Ű */ pack("n*",0xc5b0) =>  "U",  /* Û */ pack("n*",0xc39b) =>  "U",  
				/* Ü */ pack("n*",0xc39c) =>  "U",  /* Ý */ pack("n*",0xc39d) =>  "Y",  /* Ÿ */ pack("n*",0xc5b8) =>  "Y",  /* Ź */ pack("n*",0xc5b9) =>  "Z",  
				/* Ž */ pack("n*",0xc5bd) =>  "Z",  /* Ż */ pack("n*",0xc5bb) =>  "Z",  /* ã */ pack("n*",0xc3a3) =>  "a",  /* à */ pack("n*",0xc3a0) =>  "a",  
				/* á */ pack("n*",0xc3a1) =>  "a",  /* å */ pack("n*",0xc3a5) =>  "a",  /* â */ pack("n*",0xc3a2) =>  "a",  /* ă */ pack("n*",0xc483) =>  "a",  
				/* ä */ pack("n*",0xc3a4) =>  "a",  /* ą */ pack("n*",0xc485) =>  "a",  /* æ */ pack("n*",0xc3a6) => "ae",  /* ç */ pack("n*",0xc3a7) =>  "c",  
				/* č */ pack("n*",0xc48d) =>  "c",  /* ć */ pack("n*",0xc487) =>  "c",  /* đ */ pack("n*",0xc491) => "dj",  /* ď */ pack("n*",0xc48f) =>  "d",  
				/* ð */ pack("n*",0xc3b0) => "dh",  /* ę */ pack("n*",0xc499) =>  "e",  /* ě */ pack("n*",0xc49b) =>  "e",  /* ë */ pack("n*",0xc3ab) =>  "e",  
				/* é */ pack("n*",0xc3a9) =>  "e",  /* ê */ pack("n*",0xc3aa) =>  "e",  /* è */ pack("n*",0xc3a8) =>  "e",  /* î */ pack("n*",0xc3ae) =>  "i",  
				/* ì */ pack("n*",0xc3ac) =>  "i",  /* ï */ pack("n*",0xc3af) =>  "i",  /* í */ pack("n*",0xc3ad) =>  "i",  /* ĺ */ pack("n*",0xc4ba) =>  "l",  
				/* ł */ pack("n*",0xc582) =>  "l",  /* ľ */ pack("n*",0xc4be) =>  "l",  /* ń */ pack("n*",0xc584) =>  "n",  /* ñ */ pack("n*",0xc3b1) =>  "n",  
				/* ň */ pack("n*",0xc588) =>  "n",  /* ö */ pack("n*",0xc3b6) =>  "o",  /* ó */ pack("n*",0xc3b3) =>  "o",  /* ò */ pack("n*",0xc3b2) =>  "o",  
				/* ő */ pack("n*",0xc591) =>  "o",  /* õ */ pack("n*",0xc3b5) =>  "o",  /* ø */ pack("n*",0xc3b8) =>  "o",  /* ô */ pack("n*",0xc3b4) =>  "o",  
				/* œ */ pack("n*",0xc593) => "oe",  /* ŕ */ pack("n*",0xc595) =>  "r",  /* ř */ pack("n*",0xc599) =>  "r",  /* ß */ pack("n*",0xc39f) =>  "s",  
				/* ş */ pack("n*",0xc59f) =>  "s",  /* š */ pack("n*",0xc5a1) =>  "s",  /* ś */ pack("n*",0xc59b) =>  "s",  /* ť */ pack("n*",0xc5a5) =>  "t",  
				/* ţ */ pack("n*",0xc5a3) =>  "t",  /* þ */ pack("n*",0xc3be) => "th",  /* µ */ pack("n*",0xc2b5) =>  "u",  /* ů */ pack("n*",0xc5af) =>  "u",  
				/* ú */ pack("n*",0xc3ba) =>  "u",  /* ű */ pack("n*",0xc5b1) =>  "u",  /* û */ pack("n*",0xc3bb) =>  "u",  /* ü */ pack("n*",0xc3bc) =>  "u",  
				/* ù */ pack("n*",0xc3b9) =>  "u",  /* ÿ */ pack("n*",0xc3bf) =>  "y",  /* ý */ pack("n*",0xc3bd) =>  "y",  /* ź */ pack("n*",0xc5ba) =>  "z",  
				/* ž */ pack("n*",0xc5be) =>  "z",  /* ż */ pack("n*",0xc5bc) =>  "z",  
			); 
		}
		if (strtolower($encoding) != 'utf8') {
			$in = iconv($encoding, 'utf8//ignore');
		}
		return strtr($in, self::$transliterateMap);
	}

	public static function isValidURL($url) {
		return (bool) filter_var($url, FILTER_VALIDATE_URL);
	}

	public static function isValidEmail($email) {
		return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	public static function isValidOib($oib) {
		if ( strlen($oib) == 11 ) {
			if ( is_numeric($oib) ) {

				$a = 10;

				for ($i = 0; $i < 10; $i++) {

					$a = $a + intval(substr($oib, $i, 1), 10);
					$a = $a % 10;

					if ( $a == 0 ) { $a = 10; }

					$a *= 2;
					$a = $a % 11;

				}

				$kontrolni = 11 - $a;
				if ( $kontrolni == 10 ) { $kontrolni = 0; }

				return $kontrolni == intval(substr($oib, 10, 1), 10);
			}
			return false;

		}
		return false;	
	}

	public static function titleSlug($in) {
		$out = self::transliterate($in);
		$out = preg_replace('/[^a-zA-Z0-9\-\s_]*/','', $out);
		$out = trim($out);
		$out = preg_replace('/[\s_]/','-', $out);
		$out = preg_replace('/-+/','-', $out);
		return $out . '.html';
	}	

	// measures password strength
	// returns value between 0 - 100 (higher is stronger)
	// http://www.alixaxel.com/wordpress/2007/06/09/php-password-strength-algorithm
	public static function checkPasswordStrength($password, $username = null) {
		if (!empty($username)) {
			$password = str_replace($username, '', $password);
		}

		$strength = 0;
		$password_length = strlen($password);

		if ($password_length < 4) {
			return $strength;
		} else {
			$strength = $password_length * 4;
		}

		for ($i = 2; $i <= 4; $i++) {
			$temp = str_split($password, $i);
			$strength -= (ceil($password_length / $i) - count(array_unique($temp)));
		}

		preg_match_all('/[0-9]/', $password, $numbers);

		if (!empty($numbers)) {
			$numbers = count($numbers[0]);

			if ($numbers >= 3) {
			    $strength += 5;
			}
		} else {
			$numbers = 0;
		}

		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^¨\\\]/', $password, $symbols);

		if (!empty($symbols)) {
			$symbols = count($symbols[0]);

			if ($symbols >= 2) {
			    $strength += 5;
			}
		} else {
			$symbols = 0;
		}

		preg_match_all('/[a-z]/', $password, $lowercase_characters);
		preg_match_all('/[A-Z]/', $password, $uppercase_characters);

		if (!empty($lowercase_characters)) {
			$lowercase_characters = count($lowercase_characters[0]);
		} else {
			$lowercase_characters = 0;
		}

		if (!empty($uppercase_characters)) {
			$uppercase_characters = count($uppercase_characters[0]);
		} else {
			$uppercase_characters = 0;
		}

		if (($lowercase_characters > 0) && ($uppercase_characters > 0)) {
			$strength += 10;
		}

		$characters = $lowercase_characters + $uppercase_characters;

		if (($numbers > 0) && ($symbols > 0)) {
			$strength += 15;
		}

		if (($numbers > 0) && ($characters > 0)) {
			$strength += 15;
		}

		if (($symbols > 0) && ($characters > 0)) {
			$strength += 15;
		}

		if (($numbers == 0) && ($symbols == 0)) {
			$strength -= 10;
		}

		if (($symbols == 0) && ($characters == 0)) {
			$strength -= 10;
		}

		if ($strength < 0) {
			$strength = 0;
		}

		if ($strength > 100) {
			$strength = 100;
		}

		return $strength;
	} 

	public static function isMobileDevice() {
		require_once dirname(__FILE__) . '/external/MobileDetect/MobileDetect.php';

		$detect = new MobileDetect;

		//$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
		return $detect->isMobile();

		/*
		if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			return true;
		}
		 
		if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) || ((isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])))) {
			return true;
		}    
		 
		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
		$mobile_agents = array(
		    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
		    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
		    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
		    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
		    'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
		    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
		    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
		    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
		    'wapr','webc','winw','winw','xda','xda-');
		 
		if (in_array($mobile_ua, $mobile_agents)) {
			return true;
		}
		 
		if (strpos(strtolower($_SERVER['ALL_HTTP']), 'OperaMini') > 0) {
			return true;
		}
		return false;
		 */
	}
}
