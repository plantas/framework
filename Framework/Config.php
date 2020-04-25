<?php

class Config {

	const ENV_ALL = 'ALL';
	const ENV_DEV = 'DEV';
	const ENV_QA = 'QA';
	const ENV_PROD = 'PROD';

	protected static $env;
	protected static $config;

	public function __construct() {
		throw new Exception('Static class');
	}

	protected static function loadConfigFile($configFile) {
		if (is_file($configFile)) {
			include($configFile);
		} else {
			throw new Exception('Missing config file ' . $configFile);
		}
		if (isset($config) && is_array($config)) {
			self::$config = $config;
		} else {
			throw new Exception('$config array empty or not found in config file');
		}
	}

	protected static function loadEnvironment() {
		if (defined('ENVIRONMENT')) {
			self::$env = ENVIRONMENT;
		} else {
			throw new Exception('Missing environment definition in config file');
		}
	}

	public static function load($file) {
		self::loadConfigFile($file);
		self::loadEnvironment();
	}

	public static function get($var, $default = null, $environment = null) {
		$env = (is_null($environment)) ? self::$env : $environment;

		// find value for current environment
		if (isset(self::$config[$var][$env])) {
			return self::$config[$var][$env];
		}
		// try to find value for all environments
		if (isset(self::$config[$var][self::ENV_ALL])) {
			return self::$config[$var][self::ENV_ALL];
		}

		return $default;
	}

	// used to set varible for current environment at runtime
	public static function set($var, $val) {
		self::$config[$var][self::$env] = $val;	
	}
}
