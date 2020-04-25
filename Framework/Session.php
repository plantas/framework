<?php

class Session {

	private static $vars;
	private static $inited = false;

	private static function init() {
		if (!self::$inited) {
			@session_start();
			self::$vars =& $_SESSION;
			self::$inited = true;
		}
	}

	public static function set($key, $value) {
		self::init();
		self::$vars[$key] = $value;
	}

	public static function get($key = null) {
		self::init();
		if (is_null($key)) {
			return self::$vars;
		}
		if (isset(self::$vars[$key])) {
			return self::$vars[$key];
		}
		return null;
	}
}
