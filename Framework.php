<?php

require(dirname(__FILE__) . '/vendor/autoload.php');

class Framework {

	protected static function getClassDirs() {
		return array(
			'/',
			'/Interface/',	
			'/Section/',	
			'/Snippet/',	
			'/FormElement/',	
			'/Element/',	
			'/Grid/',	
			'/CodeGen/',	
			'/DataSource/'	
		);
	}		

	// framework autoload
	public static function autoload($class) {
		$dirs = self::getClassDirs();
		foreach ($dirs as $d) {
			$f = dirname(__FILE__) . '/Framework' . $d . $class . '.php';
			if (is_file($f)) {
				return include($f);
			}
		}
	}
}

spl_autoload_register('Framework::autoload');
