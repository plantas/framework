<?php

include_once('ModelProperty.php');

abstract class Model implements ArrayAccess, IEquatable {

	protected $properties = array();

	/**
	 * @return ModelProperty
	 */
	public function getProperty($name) {
		if (isset($this->properties[$name]) && ($this->properties[$name] instanceof ModelProperty)) {
			return $this->properties[$name];
		} else {
			throw new Exception('Invalid ModelProperty name ' . $name);
		}
	}

	/**
	 *  populate properties with given values
	 *  @param array key-property name, value-new value to set
	 */
	public function setValues($values = array()) {
		if (is_array($values)) {
			foreach ($values as $k => $v) {
				if (isset($this->properties[$k]) && $this->properties[$k] instanceof ModelProperty) {
					$this->properties[$k]->setValue($v);
				}
			}
		}	
	}

	/*
	 * Returns associative array of selected property values or all if nothing selected
	 * @param $select array of property names to return
	 */
	public function getValues($select = array()) {
		$ret = array();

		if (is_array($select) && !empty($select)) {
			$properties = array();
			foreach ($select as $name) {
				if (isset($this->properties[$name]) && $this->properties[$name] instanceof ModelProperty) {
					$properties[$name] = $this->properties[$name];
				}
			}
		} else {
			// return all properties
			$properties = $this->properties;
		}

		foreach ($properties as $k => $v) {
			$ret[$k] = $v->getValue();
		}
		return $ret;
	}

	public function validate() {
		Lang::addDictionary('validation.php');
		$ret = array();
		foreach ($this->properties as $k => $v) {
			$errors = $v->validate();
			if (!empty($errors)) {
				$ret[$k] = $errors;
			}
		}
		return $ret;
	}

	public function equals($m2) {
		if ($m2 instanceof self) {
			$p1 = $this->getValues();
			$p2 = $m2->getValues();
			// all property-value pairs should match
			return ($p1 == array_intersect_assoc($p1, $p2));
		}
		return false;
	}

	public function getGridColumns() {
		$cols = array();
		foreach ($this->properties as $k => $v) {
			$cols[$k] = new GridColumn(array(
				GridColumn::NAME => $v->getName(),
				GridColumn::TITLE => $v->getTitle(),
				GridColumn::TYPE => $this->getGridType($v)
			));
		}
		return $cols;
	}

	private function getGridType(ModelProperty $p) {
		if ($p instanceof NumericModelProperty) return Value::TYPE_NUMERIC;
		if ($p instanceof TextModelProperty) return Value::TYPE_TEXT;
		if ($p instanceof BooleanModelProperty) return Value::TYPE_BOOLEAN;
		if ($p instanceof DateModelProperty) return Value::TYPE_DATE;
		if ($p instanceof ArrayModelProperty) return Value::TYPE_ARRAY;
		throw new Exception('Invalid ModelProperty type');
	}

	// initial setup of properties
	protected function initProperties($properties = array()) {
		$this->properties = $properties;
	}

	// array access interface
	public function offsetSet($offset, $value): void {
		$this->properties[$offset]->setValue($value);
	}

	public function offsetExists($offset): bool {
		return ($this->properties[$offset] instanceof ModelProperty);
	}

	public function offsetUnset($offset): void {
	}

	public function offsetGet($offset): mixed {
		return ($this->properties[$offset] instanceof ModelProperty) ? $this->properties[$offset]->getValue() : null;
	}
}
