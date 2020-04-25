<?php

class Http {

	const TEMPLATE_404 = '404NotFound.php';

	public static function redirect($url) {
		header('Location: ' . $url);
		exit;	
	}

	public static function response($code) {
		switch ($code) {
			case 404: 
				header("HTTP/1.0 404 Not Found"); 
				if (file_exists(Config::get('TEMPLATE_DIR') . self::TEMPLATE_404)) {
					include Config::get('TEMPLATE_DIR') . self::TEMPLATE_404;
				} else {
					echo '404 Not found'; 
				}
				exit;
		}
	}

}
