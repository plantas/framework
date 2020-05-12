<?php

class Application {

	protected $request;
	protected $url;

	protected static $singleton;

	public function getSectionClass() {
		$map = Config::get('URL_MAP');
		return $map[$this->getUrl()->getApp()];
	}

	public function getRequest() {
		return $this->request;
	}

	public function getUrl() {
		return $this->url;
	}

	protected function init() {
		// can be implemented in extendsed class 
	}

	public function main() {
		error_reporting(Config::get('ERROR_REPORTING', 0));
		ini_set('display_errors', Config::get('DISPLAY_ERRORS', 0));

		$this->request = Request::getInstance();
		$this->url = new Url($this->getRequest());
		$this->initLang();
		$this->init();

		$sectionClass = $this->getSectionClass();
		if (!class_exists($sectionClass)) {
			Http::response(404);
		}
		$sectionObj = new $sectionClass($this);
		$sectionObj->run();
	}

	public static function factory($appName) {
		if (self::$singleton instanceof Application) {
			return self::$singleton;
		}
		self::$singleton = new $appName;

		// specific app autoload 
		$autoloadFunctionName = $appName . '::autoload';
		if (is_callable($autoloadFunctionName)) {
			spl_autoload_register($autoloadFunctionName);
		}

		return self::$singleton;
	}

	protected function initLang() {
		Lang::setCurrentLang(Config::get('LANGUAGE', 'EN'));

		$dicts = Config::get('DEFAULT_DICTIONARIES');
		if (is_array($dicts)) {
			foreach ($dicts as $d) {
				Lang::addDictionary($d);
			}
		}
	}
}
