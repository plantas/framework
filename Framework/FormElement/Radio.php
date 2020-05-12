<?php

class Radio extends FormElement {

	const OPTIONS = 'options';

	protected $options = array();

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::OPTIONS]) && is_array($params[self::OPTIONS])) {
			$this->options = $params[self::OPTIONS];
		}
	}

	public function getHtml() {
		$ret = '';
		$ro = $this->getReadOnly();
		$cssClass = $this->getCssClass();
		foreach ($this->options as $k => $v) {
			$ret .= "\n" . '<input type="radio" name="'.$this->getName().'"';
			$ret .=	' value="' . Util::escape($k) . '"';
			if ($k == $this->getValue()) $ret .= ' checked="checked"';
			if ($ro) $ret .= ' disabled="disabled"';
			if ($cssClass) $ret .= ' class="'.$cssClass.'"';
			$ret .= ' />' . Util::escape($v);
		}
//		if ($this->getOnChange()) $ret .= ' onchange="'.$this->getOnChange().'"';
		return $ret;
	}


}
