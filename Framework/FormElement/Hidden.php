<?php

class Hidden extends FormElement {

	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function getHtml() {
		return '<input type="hidden" name="' . $this->getName() . '" value="' . Util::escape($this->getValue()) . '" />';
	}
}
