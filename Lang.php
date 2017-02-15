<?php

class Lang {

	protected static $currentLang = 'HR';
	protected static $lang = array();

	public function __construct() {
		throw new Exception('Static class');
	}

	public static function addDictionary($dictionary) {
		$appFile = Config::get('APPLICATION_DIR') . Config::get('LANG_DIR') . $dictionary;
		$frameworkFile = Config::get('ROOT_DIR') . 'framework/lang/' . $dictionary;

		if (is_file($appFile)) {
			include($appFile);
		} else if (is_file($frameworkFile)) {
			include($frameworkFile);
		} else {
			throw new Exception('Missing dictionary file ' . $file);
		}
		self::$lang = array_merge(self::$lang, $lang);
	}

	public static function get($var, $lang = null) {
		if (!is_null($lang) && isset(self::$lang[$var][$lang])) {
			return self::$lang[$var][$lang];
		}
		if (isset(self::$lang[$var][self::$currentLang])) {
			return self::$lang[$var][self::$currentLang];
		}
		return $var;
	}

	public static function setCurrentLang($lang) {
		self::$currentLang = $lang;
	}

	public static function getCurrentLang() {
		return self::$currentLang;
	}
}
