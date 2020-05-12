<?php

class Submit extends FormElement {

	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function getHtml() {
		$ret = '<input type="submit" name="' . $this->getName() . '" value="' . Util::escape($this->getValue()) . '"';
		$cssClass = $this->getCssClass();
		if ($cssClass) {
			$ret .= ' class="' . $cssClass . '"';
		}
		if ($this->getReadOnly()) {
			$ret .= ' disabled="disabled"';
		}
		$oc = $this->getOnclick();
		if (!empty($oc)) {
			$ret .= ' onclick="' . $oc . '"';
		}
		$id = $this->getId();
		if (!empty($id)) {
			$ret .= ' id="' . $id . '"';
		}

		$ret .= ' />';
		return $ret;
	}
}
