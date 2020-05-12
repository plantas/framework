<?php

class Checkbox extends FormElement {

	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function getHtml() {
		$ret = '<input type="checkbox" name="' . $this->getName() . '" value="1"';
		if ($this->getValue()) {
			$ret .= ' checked="checked"';
		}
		$id = $this->getId();
		if ($id) {
			$ret .= ' id="' . $id . '"';
		}
		$cssClass = $this->getCssClass();
		if ($cssClass) {
			$ret .= ' class="' . $cssClass . '"';
		}
		if ($this->getReadOnly()) {
			$ret .= ' disabled="disabled"';
		}
		$ret .= ' />';
		// we need hidden to submit readonly checkbox
		if ($this->getReadOnly() && $this->getValue()) {
			$e = new Hidden(array(
				Hidden::NAME => $this->getName(),
				Hidden::VALUE => 1
			));
			$ret .= $e->getHtml();
		}

		return $ret;
	}

}
