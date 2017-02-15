<?php

class FileUpload extends FormElement {

	const MAX_FILE_SIZE = 'maxFileSize';

	protected $maxFileSize = 100000;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MAX_FILE_SIZE])) {
			$this->setMaxFileSize($params[self::MAX_FILE_SIZE]);
		}
	}

	public function setMaxFileSize($size) {
		$this->maxFileSize = $size;
	}

	public function getMaxFileSize() {
		return $this->maxFileSize;
	}

	public function getHtml() {
		$ret = '';
		$h = new Hidden(array(
			FormElement::NAME => 'MAX_FILE_SIZE',
			FormElement::VALUE => $this->getMaxFileSize()
		));
		$ret .= $h->getHtml();

		$ret .= '<input type="file" name="' . $this->getName() . '"';
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

		$ret .= ' />';

		return $ret;
	}

}
