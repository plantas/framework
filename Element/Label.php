<?php

class Label extends Element {
	
	const TEXT = 'text';
	const TITLE = 'title';
	const FOR_ELEMENT = 'for';

	protected $text;
	protected $title = '';
	protected $for;

	public function __construct($params = array()) {
		if (isset($params[self::TEXT])) {
			$this->setText($params[self::TEXT]);
		}
		if (isset($params[self::TITLE])) {
			$this->setTitle($params[self::TITLE]);
		}
		if (isset($params[self::FOR_ELEMENT])) {
			$this->setFor($params[self::FOR_ELEMENT]);
		}
	}

	public function getHtml() {
		$ret = '<label';
		$ret .= $this->generateFor();
		if (!empty($this->title)) {
			$ret .= ' title="' . Util::escape($this->title) . '"';
		}
		$ret .= '>' . $this->text . '</label>';

		return $ret;
	}

	private function generateFor() {
		$for = $this->getFor();
		if (!$for) return '';
		
		if ($for instanceof FormElement) {
			$ret = '';
			if ($for->getRequired()) {
				$ret .= ' class="required"';
			}
			$ret .= ' for="' . Util::escape($for->getId()) . '"';
			return $ret;
		}
		return ' for="' . Util::escape($for) . '"';
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function getText() {
		return $this->text;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setFor($for) {
		$this->for = $for;
	}

	public function getFor() {
		return $this->for;
	}
}
