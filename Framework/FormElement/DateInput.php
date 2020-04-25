<?php

class DateInput extends FormElement {

	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function getHtml() {
		$ret = '<input type="date" name="' . $this->getName() . '" value="' . Util::escape($this->getValue()) . '"';
		$id = $this->getId();
		if ($id) {
			$ret .= ' id="' . $id . '"';
		}
		$cssClass = $this->getCssClass();
		if ($cssClass) {
			$ret .= ' class="' . $cssClass . '"';
		}
		if ($this->getReadOnly()) {
			$ret .= ' readonly="readonly"';
		}
		$title = $this->getTitle();
		if ($title) {
			$ret .= ' title="'.Util::escape($title).'"';
		}
		$ret .= ' />';

		return $ret;
	}

}
