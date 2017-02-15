<?php

//TODO implement some caching engine

class Cache {

	private static $debug = false;
	private static $c = array();

	public static function get($key) {
		if (isset(self::$c[$key])) {
			if (self::$debug) echo "Cache hit [$key]\n";
			if (self::isSerialized(self::$c[$key])) {
				return unserialize(self::$c[$key]);
			}
			return self::$c[$key];
		}
		if (self::$debug) echo "Cache miss [$key]\n";
		return null;
	}

	public static function set($key, $value) {
		if (self::$debug) echo 'Cache store ['.$key.']' . "\n";
		if (is_object($value)) $value = serialize($value);
		self::$c[$key] = $value;
	}

	private static function isSerialized($val){
		if (!is_string($val)) { return false; }
		if (trim($val) == "") { return false; }
		if (preg_match("/^(i|s|a|o|d):(.*);/si",$val) !== false) { return true; }
		return false;
	} 
}
