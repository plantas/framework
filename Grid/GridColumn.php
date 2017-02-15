<?php

class GridColumn {

	const NAME = 'name';
	const TITLE = 'title';
	const TYPE = 'type';
	const FORMAT_FUNCTION = 'formatFunction';
	const SUMMARY_FUNCTION = 'summaryFunction';
	const SORTABLE = 'sortable';
	const SEARCHABLE = 'searchable';
	const VISIBLE = 'visible';

	protected $name;
	protected $title;
	protected $type = Value::TYPE_TEXT;
	protected $formatFunction;
	protected $summaryFunction;
	protected $sortable = true;
	protected $searchable = true;
	protected $visible = true;

	public function __construct($params = array()) {
		if (isset($params[self::NAME])) {
			$this->setName($params[self::NAME]);
		}
		if (isset($params[self::TITLE])) {
			$this->setTitle($params[self::TITLE]);
		}
		if (isset($params[self::TYPE])) {
			$this->setType($params[self::TYPE]);
		}
		if (isset($params[self::FORMAT_FUNCTION])) {
			$this->setFormatFunction($params[self::FORMAT_FUNCTION]);
		}
		if (isset($params[self::SUMMARY_FUNCTION])) {
			$this->setSummaryFunction($params[self::SUMMARY_FUNCTION]);
		}
		if (isset($params[self::SORTABLE])) {
			$this->setSortable($params[self::SORTABLE]);
		}
		if (isset($params[self::SEARCHABLE])) {
			$this->setSearchable($params[self::SEARCHABLE]);
		}
		if (isset($params[self::VISIBLE])) {
			$this->setVisible($params[self::VISIBLE]);
		}
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getType() {
		return $this->type;
	}

	public function setFormatFunction($func) {
		$this->formatFunction = $func;
	}

	public function getFormatFunction() {
		return $this->formatFunction;
	}

	public function setSummaryFunction($func) {
		$this->summaryFunction = $func;
	}

	public function getSummaryFunction() {
		return $this->summaryFunction;
	}

	public function setSortable($bool) {
		$this->sortable = $bool ? true : false;
	}

	public function getSortable() {
		return $this->sortable;
	}

	public function setSearchable($bool) {
		$this->searchable = $bool ? true : false;
	}

	public function getSearchable() {
		return $this->searchable;
	}

	public function setVisible($bool) {
		$this->visible = $bool ? true : false;
	}

	public function getVisible() {
		return $this->visible;
	}

//	public function setFormatFunctionArgs($args) {
//		$this->formatFunctionArgs = $args;
//	}
//
//	public function getFormatFunctionArgs() {
//		return $this->formatFunctionArgs;
//	}

	public static function order($cols, array $order = array()) {
		$ret = array();
		foreach ($order as $c) {
			if (array_key_exists($c, $cols)) $ret[$c] = $cols[$c];
		}
		return $ret;
	}
}
