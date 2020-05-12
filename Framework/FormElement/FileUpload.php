<?php

class FileUpload extends FormElement {

	const MAX_FILE_SIZE = 'maxFileSize'; // do not use
	const MULTIPLE = 'multiple';

	protected $maxFileSize = 100000;
	protected $multiple = false;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MAX_FILE_SIZE])) {
			$this->setMaxFileSize($params[self::MAX_FILE_SIZE]);
		}
		if (isset($params[self::MULTIPLE])) {
			$this->setMultiple($params[self::MULTIPLE]);
		}
	}

	public function setMaxFileSize($size) {
		$this->maxFileSize = $size;
	}

	public function getMaxFileSize() {
		return $this->maxFileSize;
	}

	public function setMultiple($multiple) {
		$this->multiple = (bool) $multiple;
	}

	public function getMultiple() {
		return $this->multiple;
	}

	public function getHtml() {
		$ret = '';
		/*
		$h = new Hidden(array(
			FormElement::NAME => 'MAX_FILE_SIZE',
			FormElement::VALUE => $this->getMaxFileSize()
		));
		$ret .= $h->getHtml();
		 */
		$ret .= '<input type="file" name="' . $this->getName() . ($this->multiple ? '[]' : '') . '"';
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
		if ($this->getMultiple()) {
			$ret .= ' multiple';
		}

		$ret .= ' />';

		return $ret;
	}

}
