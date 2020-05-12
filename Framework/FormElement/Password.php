<?php

class Password extends FormElement {

	const MAXLENGTH = 'maxlength';
	const SIZE = 'size';

	protected $maxlength;
	protected $size = 50;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MAXLENGTH])) {
			$this->maxlength = $params[self::MAXLENGTH];
		}
		if (isset($params[self::SIZE])) {
			$this->size = $params[self::SIZE];
		}
	}

	public function getHtml() {
		$ret = '<input type="password" name="' . $this->getName() . '" value="' . Util::escape($this->getValue()) . '"';
		$id = $this->getId();
		if ($id) {
			$ret .= ' id="' . $id . '"';
		}
		$cssClass = $this->getCssClass();
		$cssClass = ($cssClass) ? $cssClass . ' line' : 'line';
		$ret .= ' class="' . $cssClass . '"';

		if ($this->maxlength) {
			$ret .= ' maxlength="' . $this->maxlength . '"';
		}
		if ($this->size) {
			$ret .= ' size="' . $this->size . '"';
		}
		$ret .= ' />';

		return $ret;
	}
}
