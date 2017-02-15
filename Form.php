<?php

class Form {

	const ID = 'id';
	const METHOD = 'method';
	const ACTION = 'action';
	const NAME = 'name';
	const READ_ONLY = 'readOnly';
	const ENC_TYPE = 'encType';
	const ON_SUBMIT = 'onSubmit';

	// session var name for processed forms
	const PROCESSED_FORMS = 'processedForms';

	const REQ_FORM_ID = 'formid';	// unique for every form instance
	const REQ_FORM_NAME = 'formname';	// same for every instance

	protected $id;
	protected $name;
	protected $method = 'post';
	protected $action;
	protected $elements = array();
	protected $readOnly = false;
	protected $encType;
	protected $onSubmit;

	public function __construct($params = array()) {
		if (isset($params[self::ID])) {
			$this->setId($params[self::ID]);
		} else {
			$this->setId(uniqid());
		}
		if (isset($params[self::METHOD])) {
			$this->setMethod($params[self::METHOD]);
		}
		if (isset($params[self::ACTION])) {
			$this->setAction($params[self::ACTION]);
		}
		if (isset($params[self::NAME])) {
			$this->setName($params[self::NAME]);
		}
		if (isset($params[self::READ_ONLY])) {
			$this->setReadOnly($params[self::READ_ONLY]);
		}
		if (isset($params[self::ENC_TYPE])) {
			$this->setEncType($params[self::ENC_TYPE]);
		}
		if (isset($params[self::ON_SUBMIT])) {
			$this->setOnSubmit($params[self::ON_SUBMIT]);
		}
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function setMethod($method) {
		$this->method = $method;
	}

	public function getMethod() {
		return $this->method;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getAction() {
		return $this->action;
	}

	public function setName($name) {
		$this->name= $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setReadOnly($ro) {
		$this->readOnly = (boolean) $ro;
	}

	public function getReadOnly() {
		return $this->readOnly;
	}

	public function setEncType($type) {
		$this->encType = $type;
	}

	public function getEncType() {
		return $this->encType;
	}

	public function setOnSubmit($e) {
		$this->onSubmit = $e;
	}

	public function getOnSubmit() {
		return $this->onSubmit;
	}
	
	public function addElement(FormElement $e) {
		$this->elements[$e->getName()] = $e;
	}

	public function getBegin() {
		$e = new Hidden(array(
			FormElement::NAME => self::REQ_FORM_ID,
			FormElement::VALUE => $this->getId()
		));
		$this->addElement($e);
		$e = new Hidden(array(
			FormElement::NAME => self::REQ_FORM_NAME,
			FormElement::VALUE => $this->getName()
		));
		$this->addElement($e);
		
		$ret = '<form method="' . $this->getMethod() . '"'; 
		$ret .= ' action="' . $this->getAction() . '"';
		if (!empty($this->encType)) {
			$ret .= ' enctype="' . $this->encType . '"';
		}
		if (!empty($this->onSubmit)) {
			$ret.= ' onsubmit="' . $this->onSubmit . '"';
		}
		$ret .= ' id="' . $this->getId() . '"';
		$ret .= '>';
		
		// insert hidden fields
		foreach ($this->getElements() as $e) {
			if ($e instanceof Hidden) {
				$ret .= "\n" . $e->getHtml();
			}
			if ($this->getReadOnly()) {
				$ro = $e->getReadOnly(); // set only if not defined in element
				if (is_null($ro)) {
					$e->setReadOnly(true);
				}
			}
		}
		return $ret;
	}

	public function getEnd() {
		return '</form>';
	}

	public function getElements() {
		$ret = array();
		if (is_array($this->elements)) {
			foreach ($this->elements as $e) {
				$ret[$e->getName()] = $e;
			}
		}
		return $ret;
	}

	// store in session id of processed forms to avoid multiple form submission
	public static function setProcessed($formId) {
		$pf = Session::get(self::PROCESSED_FORMS);
		if (is_array($pf)) {
			$pf[] = $formId;
		} else {
			$pf = array($formId);
		}
		Session::set(self::PROCESSED_FORMS, $pf);
	}

	public static function isProcessed($formId) {
		$pf = Session::get(self::PROCESSED_FORMS);
		if (!is_array($pf)) return false;
		return in_array($formId, $pf);
	}
}
