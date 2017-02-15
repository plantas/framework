<?php

class File {
	
	const LIB_DIR = 'lib/';

	protected static $cssFiles = array();
	protected static $jsFiles = array();

	// file - filename or URL
	// dir - directory after /www/
	public static function includeJs($file, $dir = 'js/') {
		if (substr($file, 0, 4) != 'http') {
			// if it's not url prepend base dir to the filename
			$file = $dir . $file;
		}

		if (!in_array($file, self::$jsFiles)) {
			self::$jsFiles[] = $file;
		}
	}

	public static function includeCss($file, $dir = 'css/') {
		if (substr($file, 0, 4) != 'http') {
			$file = $dir . $file;
		}

		if (!in_array($file, self::$cssFiles)) {
			self::$cssFiles[] = $file;
		}	
	}

	public static function getJsFiles() {
		return self::$jsFiles;
	}

	public static function getCssFiles() {
		return self::$cssFiles;
	}
}
