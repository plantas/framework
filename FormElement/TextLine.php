<?php

class TextLine extends FormElement {

	const MAXLENGTH = 'maxlength';
	const SIZE = 'size';

	protected $maxlength;
	protected $size = 50;
	protected $autocomplete = true; // for extended classes

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MAXLENGTH])) {
			$this->maxlength = $params[self::MAXLENGTH];
		}
		if (isset($params[self::SIZE])) {
			$this->size = $params[self::SIZE];
		}
	}

	public function getCssClass() {
		return $this->cssClass . (empty($this->cssClass) ? '' : ' ') . 'line form-control';
	}

	public function getHtml() {
		$ret = '<input type="text" name="' . $this->getName() . '" value="' . Util::escape($this->getValue()) . '"';
		$id = $this->getId();
		if ($id) {
			$ret .= ' id="' . $id . '"';
		}
		$ret .= ' class="' . $this->getCssClass() . '"';
		if ($this->getReadOnly()) {
			$ret .= ' readonly="readonly"';
		}
		if ($this->maxlength) {
			$ret .= ' maxlength="' . $this->maxlength . '"';
		}
		if ($this->size) {
			$ret .= ' size="' . $this->size . '"';
		}
		if (!$this->autocomplete) {
			$ret .= ' autocomplete="off"';
		}
		$title = $this->getTitle();
		if ($title) {
			$ret .= ' title="'.Util::escape($title).'"';
		}
		$ret .= ' />';

		return $ret;
	}
}
