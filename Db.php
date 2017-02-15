<?php

class Db {
	
	protected static $db;

	public static function get() {
		if (is_null(self::$db)) {
			self::$db = self::connect();
		}
		return self::$db;
	}

	private static function connect() {
		$driver = Config::get('DB_DRIVER', 'mysql');
		$dbhost = Config::get('DB_HOST', 'localhost');
		$dbname = Config::get('DB_NAME');
		$dbuser = Config::get('DB_USER');
		$dbpass = Config::get('DB_PASS');

		switch ($driver) {
			case 'mysql':
				return new PDO(
					'mysql:host=' . $dbhost . ';dbname=' . $dbname,
					$dbuser,
					$dbpass,
					array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, time_zone = 'CET';")
				); 
			case 'pgsql':
				return new PDO(
					'pgsql:host=' . $dbhost . ';dbname=' . $dbname,
					$dbuser,
					$dbpass,
					null
				); 
			default:
				throw new Exception('DB driver undefined');
		}
	}

}
