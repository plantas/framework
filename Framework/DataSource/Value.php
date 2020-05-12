<?php

abstract class Value {
	const TYPE_TEXT = 'text';
	const TYPE_BOOLEAN = 'bool';
	const TYPE_NUMERIC = 'numeric';
	const TYPE_DATE = 'date';
	const TYPE_ARRAY = 'array';
	
	protected $value;
	
	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}
	
	function __construct($value) {
		$this->setValue($value);
	}
}

abstract class ValueText extends Value {}
abstract class ValueNumeric extends Value {}
abstract class ValueBoolean extends Value {}
abstract class ValueDate extends Value {}
abstract class ValueArray extends Value {}
