<?php 

abstract class FormElement extends Element {

	protected $name;
	protected $value;
	protected $readOnly;
	protected $required = false;
	protected $onChange;

	const NAME = 'name';
	const VALUE = 'value';
	const READ_ONLY = 'readOnly';
	const REQUIRED = 'required';
	const ON_CHANGE = 'onChange';

	public function __construct($params = array()) {
		parent::__construct($params);
		if (isset($params[self::NAME])) {
			$this->setName($params[self::NAME]);
		}
		if (isset($params[self::VALUE])) {
			$this->setValue($params[self::VALUE]);
		}
		if (isset($params[self::READ_ONLY])) {
			$this->setReadOnly($params[self::READ_ONLY]);
		}
		if (isset($params[self::REQUIRED])) {
			$this->setRequired($params[self::REQUIRED]);
		}
		if (isset($params[self::ON_CHANGE])) {
			$this->setOnChange($params[self::ON_CHANGE]);
		}
	}

	//overriden
	public function getId() {
		if (empty($this->id)) {
			// create id from name attribute but remove illegal chars
			$id = $this->name;
			$id = str_replace('][', '-', $id);
			$id = str_replace('[', '-', $id);
			$id = str_replace(']', '', $id);
			return $id;
		}
		return $this->id;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function setReadOnly($ro) {
		$this->readOnly = (boolean) $ro;
	}

	public function getReadOnly() {
		return $this->readOnly;
	}

	public function setRequired($required) {
		$this->required = (boolean) $required;
	}

	public function getRequired() {
		return $this->required;
	}

	public function setOnChange($onChange) {
		$this->onChange = $onChange;
	}

	public function getOnChange() {
		return $this->onChange;
	}
}
