<?php

abstract class ModelProperty implements IValidate {

	const NAME = 'name';
	const TITLE = 'title';
	const VALUE = 'value';
	const NULLABLE = 'nullable';

	protected $name;
	protected $title;
	protected $value;
	protected $nullable = true;

	public function __construct($params = array()) {
		if (isset($params[self::NAME])) {
			$this->setName($params[self::NAME]);
		} else {
			throw new Exception('Name must be set');
		}

		if (isset($params[self::TITLE])) {
			$this->setTitle($params[self::TITLE]);
		} else {
			$this->setTitle($params[self::NAME]);
		}

		if (isset($params[self::NULLABLE])) {
			$this->setNullable($params[self::NULLABLE]);
		}
		// this can be set on model load
		if (isset($params[self::VALUE])) {
			$this->setValue($params[self::VALUE]);
		}
	}

	public function validate() {
		return array();
	}

	public function setName($name) { $this->name = $name; }
	public function getName() { return $this->name; }
	public function setTitle($title) { $this->title = $title; }
	public function getTitle() { return $this->title; }
	public function setValue($value) { $this->value = $value; }
	public function getValue() { return $this->value; }
	public function setNullable($nullable) { $this->nullable = (bool) $nullable; }
	public function getNullable() { return $this->nullable; }
}

class BooleanModelProperty extends ModelProperty {
	public function __construct($params = array()) {
		parent::__construct($params);
		// default
		$this->value = $this->nullable ? null : 0;
	}

	public function setValue($value) {
		if (is_null($value)) {
			$this->value = $this->nullable ? null : 0;
		} else {
			$this->value = ($value) ? 1 : 0;
		}
	}

	public function validate() {
		$ret = array();
		if (!$this->nullable && is_null($this->value)) {
			$ret[] = Lang::get('mandatory field');
		}
		return $ret;
	}
}

class NumericModelProperty extends ModelProperty {

	const LOWER_LIMIT = 'lowerLimit';
	const UPPER_LIMIT = 'upperLimit';

	protected $lowerLimit;
	protected $upperLimit;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::LOWER_LIMIT]) && is_numeric($params[self::LOWER_LIMIT])) {
			$this->lowerLimit = $params[self::LOWER_LIMIT];
		}
		if (isset($params[self::UPPER_LIMIT]) && is_numeric($params[self::UPPER_LIMIT])) {
			$this->upperLimit = $params[self::UPPER_LIMIT];
		}
	}

	public function validate() {
		$ret = array();
		// check if is empty
		$trimmedValue = trim($this->value);
		if (is_null($this->value) || $trimmedValue == '') {
			if (!$this->nullable) {
				$ret[] = Lang::get('mandatory field');
			} else {
				$this->value = null; // to cast ''
			}
		} else {
			if (!is_numeric($this->value)) {
				$ret[] = Lang::get('should be numeric value');
			}
			if (!is_null($this->lowerLimit) && $this->value < $this->lowerLimit) {
				$ret[] = sprintf(Lang::get('number should be greater than %s'), $this->lowerLimit);
			}
			if (!is_null($this->upperLimit) && $this->value > $this->upperLimit) {
				$ret[] = sprintf(Lang::get('number should be less than %s'), $this->upperLimit);
			}
		}

		return $ret;
	}
}

class DateModelProperty extends ModelProperty {
	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function setValue($value) {
		$this->value = empty($value) ? null : $value;
	}

	public function validate() {
		$ret = array();
		if (empty($this->value) && !$this->nullable) {
			$ret[] = Lang::get('mandatory field');
		}
		return $ret;
	}
}

class TextModelProperty extends ModelProperty {

	const MAXLENGTH = 'maxlength';

	protected $maxlength;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MAXLENGTH])) {
			$this->maxlength = (int) $params[self::MAXLENGTH];
		}
	}

	public function validate() {
		$ret = array();
		$trimmedValue = trim($this->value);
		if (!$this->nullable && empty($trimmedValue)) {
			$ret[] = Lang::get('mandatory field');
		}
		if ($this->maxlength > 0 && strlen($this->value) > $this->maxlength) {
			$ret[] = Lang::get('value too long');
		}
		return $ret;
	}
}

class ArrayModelProperty extends ModelProperty {
	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function validate() {
		$ret = array();
		if (!is_array($this->value)) {
			$ret[] = Lang::get('value should be an array');
		}
		return $ret;
	}
}


