<?php

abstract class SingleFormElement extends FormElement implements IMultipleElements {

	const MODEL = 'model';
	const VALIDATION_ERRORS = 'validationErrors';

	protected $model;
	protected $validationErrors = array();

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MODEL])) {
			$this->setModel($params[self::MODEL]);
		}
		if (isset($params[self::VALIDATION_ERRORS])) {
			$this->setValidationErrors($params[self::VALIDATION_ERRORS]);
		}
	}

	public function setModel($model) {
		$this->model = $model;
	}

	public function getModel() {
		return $this->model;
	}

	public function setValidationErrors(array $err) {
		$this->validationErrors = $err;
	}

	public function getValidationErrors() {
		return $this->validationErrors;
	}

}
