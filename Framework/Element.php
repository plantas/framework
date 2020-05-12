<?php

abstract class Element {

	const ID = 'id';
	const CSS_CLASS = 'cssClass';
	const TITLE = 'title';
	const ONCLICK = 'onclick';

	protected $id;
	protected $cssClass;
	protected $title;
	protected $onclick;

	public function __construct($params = array()) {
		if (isset($params[self::ID])) {
			$this->setId($params[self::ID]);
		}
		if (isset($params[self::CSS_CLASS])) {
			$this->setCssClass($params[self::CSS_CLASS]);
		}
		if (isset($params[self::TITLE])) {
			$this->setTitle($params[self::TITLE]);
		}
		if (isset($params[self::ONCLICK])) {
			$this->setOnclick($params[self::ONCLICK]);
		}
	}

	abstract public function getHtml();

	public function __toString() {
		return $this->getHtml();
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function setCssClass($cssClass) {
		$this->cssClass = $cssClass;
	}

	public function getCssClass() {
		return $this->cssClass;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setOnclick($e) {
		$this->onclick = $e;
	}

	public function getOnclick() {
		return $this->onclick;
	}
}
