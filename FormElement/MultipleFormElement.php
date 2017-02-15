<?php 

class MultipleFormElement extends FormElement implements IMultipleElements {

	const SINGLE_ELEMENT_CLASS_NAME = 'single_element_class_name';
	const SINGLE_ELEMENT_PARAMS = 'single_element_params';
	const VALIDATION_ERRORS = 'validation_errors';
	const ORDERING = 'ordering';
	const NUMERATION = 'numeration';
	
	const REQUEST_SINGLE_ELEMENT_CLASS_NAME = 'secn';
	const REQUEST_ELEMENTS = 'elements';
	const REQUEST_ADD = 'add';
	const REQUEST_REMOVE = 'remove';
	const REQUEST_MOVE_UP = 'up';
	const REQUEST_MOVE_DOWN = 'down';
	
	protected $ordering = false;
	protected $numeration = false;
	protected $singleElementParams = array();
	protected $validationErrors = array();
	
	function __construct(array $params=array()) {
		parent::__construct($params);

		if (isset($params[self::SINGLE_ELEMENT_CLASS_NAME])) {
			$this->setSingleElementClassName($params[self::SINGLE_ELEMENT_CLASS_NAME]);
		}
		if (isset($params[self::SINGLE_ELEMENT_PARAMS])) {
			$this->setSingleElementParams($params[self::SINGLE_ELEMENT_PARAMS]);
		}
		if (isset($params[self::VALIDATION_ERRORS])) {
			$this->setValidationErrors($params[self::VALIDATION_ERRORS]);
		}
		if (isset($params[self::ORDERING])) {
			$this->ordering = (bool) $params[self::ORDERING];
		}
		if (isset($params[self::NUMERATION])) {
			$this->numeration = (bool) $params[self::NUMERATION];
		}
	}
	
	public function getHtml() {
		if (!$this->value instanceof MultipleModel) {
			throw new Exception('Invalid multiple model');
		}
		$formElements = array();
		foreach ($this->value->getCollection() as $k => $singleModel) {
			$newElement = $this->singleElementFactory(array_merge($this->getSingleElementParams(), array(
				FormElement::NAME => $this->getName() . '[' . self::REQUEST_ELEMENTS.']'. '['.$k.']',
				FormElement::READ_ONLY => $this->getReadOnly()
			)));
			if (!($newElement instanceof IMultipleElements)) {
				throw new Exception(get_class().'::singleElementFactory did not return an instance of IMultipleElements');
			}
			if (isset($this->validationErrors[$k])) {
				$newElement->setValidationErrors($this->validationErrors[$k]);
			}
			$newElement->setModel($singleModel);
			$formElements[] = $newElement;
		}
		$ret = '
			<table cellspacing="0" cellpadding="0" class="'.$this->getCssClass().'" border="0">';
		$closeTag = '';
		foreach ($formElements as $i=>$el) {
			$ret .= $closeTag;
			$ret .= '
				<tr>';
			if ($this->numeration) {
				$ret .= '<td class="numerator"><span>' . ($i+1) . '.</span></td>';
			}
			$ret .= '<td>';
			$ret .= $el->getHtml();
			$ret .= '</td>';
			$removeElement = new Submit(array(
				FormElement::NAME => $this->getName() . '[' . self::REQUEST_REMOVE. ']['.$i.']',
				FormElement::VALUE => Lang::get('Remove'),
				FormElement::READ_ONLY => $this->getReadOnly(),
			));
			$ret .= '<td class="controls">';
			$ret .= '<span class="remove" title="' . Lang::get('Remove') . '">'.$removeElement->getHtml().'</span>';

			if ($this->ordering) {
				$upElement = new Submit(array(
					FormElement::NAME => $this->getName() . '[' . self::REQUEST_MOVE_UP. ']['.$i.']',
					FormElement::VALUE => Lang::get('Up'),
					FormElement::READ_ONLY => $this->getReadOnly() || ($i == 0),
				));

				$downElement = new Submit(array(
					FormElement::NAME => $this->getName() . '[' . self::REQUEST_MOVE_DOWN. ']['.$i.']',
					FormElement::VALUE => Lang::get('Down'),
					FormElement::READ_ONLY => $this->getReadOnly() || ($i == count($formElements) - 1),
				));
				$ret .= '<span class="moveup" title="' . Lang::get('Up') . '">'.$upElement->getHtml().'</span>';
				$ret .= '<span class="movedown" title="' . Lang::get('Down') . '">'.$downElement->getHtml().'</span>';
			}

			$closeTag = '</td></tr>';
		}
		$addElement = new Submit(array(
			FormElement::NAME => $this->getName() . '[' . self::REQUEST_ADD. ']',
			FormElement::VALUE => Lang::get('Add'),
			FormElement::READ_ONLY => $this->getReadOnly(),
		));
		$ret .= "&nbsp;";
		$ret .= '<span class="add" title="' . Lang::get('Add') . '">'.$addElement->getHtml().'</span>';
		$ret .= $closeTag;
		$ret .= '</table>';
		
		$secnElement = new Hidden(array(
			FormElement::NAME => $this->getName() . '[' . self::REQUEST_SINGLE_ELEMENT_CLASS_NAME . ']',
			FormElement::VALUE => $this->getSingleElementClassName()
		));
		$ret .= $secnElement->getHtml();
		
		return $ret;
	}
	
	
	/**
	 * @param $params
	 * @return IMultipleElements
	 */
	protected function singleElementFactory(array $params) {
		$className = $this->getSingleElementClassName();
		return new $className($params);
	}
	
	
	/**
	 * @param MultipleModel $model
	 * @param array $req
	 */
	public static function populateModelFromRequest($model, $req) {
		if (!$model instanceof MultipleModel) throw new Exception('Invalid model given');
		if (!is_array($req)) {
			$req = array();
		}
		$toRemove = array();
		if (isset($req[self::REQUEST_REMOVE])) {
			if (!is_array($req[self::REQUEST_REMOVE])) {
				throw new Exception('REQUEST_REMOVE is not an array');
			}
			foreach ($req[self::REQUEST_REMOVE] as $elementId => $whatever) {
				$toRemove[] = $elementId;
			}
		}
		if (isset($req[self::REQUEST_ELEMENTS])) {
			$singleElementClassName = $req[self::REQUEST_SINGLE_ELEMENT_CLASS_NAME];
			self::checkSingleElementClassName($singleElementClassName);

			$elements = $req[self::REQUEST_ELEMENTS];
			if (!is_array($elements)) {
				throw new Exception(get_class().'::REQUEST_ELEMENTS is not an array');
			}

			$elements = self::moveElements($elements, $req);

			foreach ($elements as $elementId => $element) {
				if (in_array($elementId, $toRemove)) continue;
				$newElement = $model->factory();
				call_user_func(array($singleElementClassName, 'populateModelFromRequest'), $newElement, $element);
				$model->addElement($newElement);				
			}
		}
		if (isset($req[self::REQUEST_ADD])) {
			$model->addElement($model->factory());
		}
	}

	private static function moveElements($elements, $req) {
		if (is_array($req[self::REQUEST_MOVE_UP])) {
			$idx = array_keys($req[self::REQUEST_MOVE_UP]);
			$mvIdx = $idx[0];
			if (is_numeric($mvIdx)) {
				$tmp = $elements[$mvIdx - 1];
				$elements[$mvIdx - 1] = $elements[$mvIdx];
				$elements[$mvIdx] = $tmp;
			}

		}
		if (is_array($req[self::REQUEST_MOVE_DOWN])) {
			$idx = array_keys($req[self::REQUEST_MOVE_DOWN]);
			$mvIdx = $idx[0];
			if (is_numeric($mvIdx)) {
				$tmp = $elements[$mvIdx + 1];
				$elements[$mvIdx + 1] = $elements[$mvIdx];
				$elements[$mvIdx] = $tmp;
			}

		}
		return $elements;
	}
	
	public function setSingleElementClassName($singleElementClassName) {
		self::checkSingleElementClassName($singleElementClassName);
		$this->singleElementClassName = $singleElementClassName;
	}
	
	public function getSingleElementClassName() {
		return $this->singleElementClassName;
	}

	public static function checkSingleElementClassName($singleElementClassName) {
		if (empty($singleElementClassName)) {
			throw new Exception('No REQUEST_SINGLE_ELEMENT_CLASS_NAME!');
		}
		if (!class_exists($singleElementClassName, true)) {
			throw new Exception('Class '.$singleElementClassName.' does not exist or is not accessible with autoload');
		}
		$r = new ReflectionClass($singleElementClassName);
		if (!$r->implementsInterface('IMultipleElements')) {
			throw new Exception('Single Element Class '.$singleElementClassName.' does not implement IMultipleElements');
		}
	}

	public function setValidationErrors(array $err) {
		$this->validationErrors = $err;
	}

	public function getValidationErrors() {
		return $this->validationErrors;
	}

	// overriden to check instance
	public function setValue($value = null) {
		if (!$value instanceof MultipleModel) throw new Exception('Value must be instance of MultipleModel');
		$this->value = $value;
	}

	public function setSingleElementParams(array $singleElementParams) {
		$this->singleElementParams = $singleElementParams;
	}

	public function getSingleElementParams() {
		return (is_array($this->singleElementParams) ? $this->singleElementParams : array()); 
	}


}
