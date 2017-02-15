<?php

function pre_var_dump($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}

class Framework {

	public static function getClassDirs() {
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
			$f = dirname(__FILE__) . $d . $class . '.php';
			if (is_file($f)) {
				return include($f);
			}
		}
	}
}

spl_autoload_register('Framework::autoload');
