<?php

abstract class Section implements IRunnable {

	protected $application;

	public function __construct(Application $app) {
		$this->setApplication($app);
	}

	public function setApplication($app) {
		$this->application = $app;
	}

	public function getApplication() {
		return $this->application;
	}

	public function getRequest($var = null) {
		return $this->getApplication()->getRequest()->get($var);
	}

	public function getUrl() {
		return $this->getApplication()->getUrl();
	}

	public static function output($out) {
		ob_clean();
		flush();
		echo $out;
		exit;
	}
}
