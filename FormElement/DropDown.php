<?php

class DropDown extends FormElement {

	const OPTIONS = 'options';
	const DISABLE_NULL_OPTION = 'disableNullOption';
	
	protected $options = array();
	protected $disableNullOption = false;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::OPTIONS]) && is_array($params[self::OPTIONS])) {
			$this->options = $params[self::OPTIONS];
		}
		if (isset($params[self::DISABLE_NULL_OPTION])) {
			$this->disableNullOption = (boolean) $params[self::DISABLE_NULL_OPTION];
		}
	}

	public function getHtml() {
		$ret = '<select name="'.$this->getName().'"';
		if ($this->getOnChange()) $ret .= ' onchange="'.$this->getOnChange().'"';
		if ($this->getReadOnly()) $ret .= ' disabled="disabled"';
		if ($id = $this->getId()) $ret .= ' id="'.$id.'"';
		$ret .= '>';
		if (!$this->disableNullOption) {
			$ret .= '<option value="">&nbsp;</option>';
		}
		foreach ($this->options as $k => $v) {
			$ret .= '<option';
			$ret .=	' value="' . Util::escape($k) . '"';
			if ($k == $this->getValue()) $ret .= ' selected';
			$ret .= '>' . Util::escape(empty($v) ? $k : $v) . '</option>';
		}
		$ret .= '</select>';
		return $ret;
	}

}
