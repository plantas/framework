<?php

class GridContext {

	const SESS_NAME = 'gridSettings';

	private $grid;
	private $context;

	public function __construct(Grid $grid) {
		$this->grid = $grid;

		// read context from session
		$s = Session::get(self::SESS_NAME);
		$this->context = isset($s[$this->grid->getNamespace()]) ? $s[$this->grid->getNamespace()] : array();
	}

	public function set($key, $value) {
		$this->context[$key] = $value;
	}

	public function get($key) {
		if (isset($this->context[$key])) return $this->context[$key];
	}

	public function save() {
		$s = Session::get(self::SESS_NAME);
		$s[$this->grid->getNamespace()] = $this->context;
		Session::set(self::SESS_NAME, $s);
	}
}
