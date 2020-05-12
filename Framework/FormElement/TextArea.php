<?php

class TextArea extends FormElement {

	const COLS = 'cols';
	const ROWS = 'rows';

	protected $cols = 50;
	protected $rows = 8;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::COLS])) {
			$this->setCols($params[self::COLS]);
		}
		if (isset($params[self::ROWS])) {
			$this->setRows($params[self::ROWS]);
		}
	}

	public function setCols($cols) {
		$this->cols = $cols;
	}

	public function getCols() {
		return $this->cols;
	}

	public function setRows($rows) {
		$this->rows = $rows;
	}

	public function getRows() {
		return $this->rows;
	}

	public function getHtml() {
		$ret = '<textarea name="' . $this->getName() . '"';
		$id = $this->getId();
		if ($id) {
			$ret .= ' id="' . $id . '"';
		}
		$cssClass = $this->getCssClass();
		if ($cssClass) {
			$ret .= ' class="' . $cssClass . '"';
		}
		$ro = $this->getReadOnly();
		if ($ro) {
			$ret .= ' readonly="readonly"';
		}
		$ret .= ' cols="' . $this->getCols() . '" rows="' . $this->getRows() . '">';
		$ret .= Util::escape($this->getValue());
		$ret .= '</textarea>';

		return $ret;
	}
}
