<?php

class Request {

	protected $vars;
	private static $instance;

	private function __construct() {
		$this->init();
	}

	public function get($key = null) {
		if (is_null($key)) {
			return $this->vars;
		}
		if (isset($this->vars[$key])) {
			return $this->vars[$key];
		}
		return null;
	}

	protected function init() {
		$this->vars = $_REQUEST;
		if (get_magic_quotes_gpc()) {
			$this->vars = self::stripslashesRecursive($this->vars);
		}
	}

	public static function getInstance() {
		if (self::$instance instanceof Request) {
			return self::$instance;
		}
		self::$instance = new Request();
		return self::$instance;
	}

	public static function stripslashesRecursive($value) {
		$value = is_array($value) ?
			array_map(__CLASS__ . '::' . __FUNCTION__, $value) :
			stripslashes($value);

		return $value;
	}

}


