<?php

class Log {

	const LOG_FILE = '../log.txt';

	public static function error($msg) {
		self::write('ERROR ' . $msg);
	}

	public static function warning($msg) {
		self::write('WARNING ' . $msg);
	}

	public static function info($msg) {
		self::write('INFO ' . $msg);
	}

	private static function write($msg) {
		$msg = date('d.m.Y. H:i:s') . ' ' . $msg . "\n";
		file_put_contents(self::LOG_FILE, $msg, FILE_APPEND);
	}
}
