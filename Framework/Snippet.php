<?php

abstract class Snippet implements IRunnable {

	protected $section;
	protected $params = array();

	public function __construct(Section $section, $params = array()) {
		$this->section = $section;
		$this->params = $params;
	}

	public function getSection() {
		return $this->section;
	}

	public function getParams() {
		return $this->params;
	}

	public function getParam($var) {
		return $this->params[$var];
	}

	public function getApplication() {
		return $this->getSection()->getApplication();
	}

	public function getRequest($var = null) {
		return $this->getApplication()->getRequest()->get($var);
	}

	public function getUrl() {
		return $this->getApplication()->getUrl();
	}
}
