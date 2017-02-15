<?php

class GridDynamicHeader {
	protected $headerFields;
	protected $request;

	protected $defaultSelected;
	protected $selectedFields;
	
	protected $tableHeader;
	
	protected $maxColumns = 0;
	protected $constantColumns = array();
		
	private $hasCookie = false;
	private $hasCancel = false;
	
	protected $sessionName = null;
	
	function __construct(Grid $grid, $params = null) {

		$this->grid = $grid;

		$this->application = $this->grid->getApplication();
		if ($this->application) {
			$this->currentSection = $this->application->getCurrentSectionObject();
		} else {
			if (!isset($params['currentSection'])) {
				throw new Exception('could not get application context from grid object so currentSection param to '.get_class($this).' is necessary. none provided.');
			}
			$this->currentSection = $params['currentSection'];
		}

		if (isset($params['sessionName'])) {
			$this->setSessionName($params['sessionName']); 
		} 

		$this->headerFields = $this->grid->getTableHeader();
		
		$this->request = $this->grid->getRequest();

		if (isset($params['defaultSelected'])) {
			$this->defaultSelected = $params['defaultSelected'];
		} else {
			$this->defaultSelected = array_keys($this->headerFields);
		}
		
		if (isset($params['constantColumns'])) {
			if (is_array($params['constantColumns'])) {
				$this->constantColumns = $params['constantColumns'];
			}
		}
		
		$this->setTableHeader();

		$this->form = $params['form'];
		if (!($this->form instanceof Form)) {
			$formParams = array('watchState'=>false);
			$this->form = new Form($this->currentSection, array_merge($formParams, array(method => 'GET'))); //auto changed params
		}

		if (isset($params['maxColumns'])) {
			$this->maxColumns = is_numeric($params['maxColumns']) ? $params['maxColumns'] : 0;
		}
		if (isset($params['hasCancel'])) {
			$this->hasCancel = $params['hasCancel'] ? true : false;
		}
	}
	
	protected function setSessionName($name) {
		$this->sessionName = $name ? $name : null;
	}
	
	public function getSessionName() {
		return $this->sessionName;
	}

	public function saveToCookie($expire=0, $path=null, $domain=null) {
		$sessionName = $this->getSessionName();
		if (empty($sessionName)) { 
			throw new Exception(get_class($this).'::saveToCookie cannot be called if no session name given! Pass parameter sessionName to constructor.');
		}
		if (!$this->selectedFields) {
			$this->getSelectedFields();
		}
		$name = $this->grid->addNamespace('dynh['.$sessionName.']');
		$value = base64_encode(serialize($this->selectedFields));
		if (is_null($domain)) {
			if (is_null($path)) {
				setcookie($name, $value, $expire);
			} else {
				setcookie($name, $value, $expire, $path);
			}
		} else {
			setcookie($name, $value, $expire, $path, $domain);
		}
	}
	
	public function hasCookie() {
		return $this->hasCookie;
	}
	
	protected function getSelectedFromCookie() {
		$this->hasCookie = true;
		$ret = unserialize(base64_decode(Util::extractValueByName($_COOKIE, $this->grid->addNamespace('dynh['.$this->getSessionName().']'))));
		return is_array($ret) ? $ret : false;
	}
	
	public function getHeaderFields() {
		return $this->headerFields;
	}

	protected function setTableHeader() {
		if (!$this->selectedFields) {
			$this->getSelectedFields();
		}
		if (!is_array($this->selectedFields)) {
			throw new Exception('Empty table');
		}
		$this->tableHeader = array();
		$fieldsToShow = array_merge($this->constantColumns, $this->selectedFields);
//$fieldsToShow = $this->selectedFields;
		foreach ($fieldsToShow as $f) {
			if (isset($this->headerFields[$f])) {
				$this->tableHeader[$f] = $this->headerFields[$f];
			}
		} 
		$this->grid->setTableHeader($this->tableHeader);
		$this->grid->addConstParams(array($this->grid->addNamespace('dynh[sel]') => $this->selectedFields));
		$this->grid->addToOrderByColumnWhiteList(array_keys($this->headerFields));
	}	

	protected function getSelectedFields() {
		if (isset($this->request['dynh']['reset'])) {
			$this->selectedFields = $this->defaultSelected;
		} else if (isset($this->request['dynh']['submit'])) {
			if (isset($this->request['dynh']['sel'])) {
				if (empty($this->request['dynh']['sel'])) {
					$this->selectedFields =  (!empty($this->constantColumns)) ? array() : $this->defaultSelected;
				} else {
					$this->selectedFields =  $this->request['dynh']['sel'];
				}
			} 
		} else if (isset($this->request['dynh']['cancel'])) { 
				$this->selectedFields = $this->request['dynh']['sel_old'];
		} else {
			if ($this->getSessionName()) {
				$cookieSelectedFields = $this->getSelectedFromCookie();
				if ($cookieSelectedFields === false) {
					$this->selectedFields =  $this->defaultSelected;
				} else if (empty($cookieSelectedFields)) {
					$this->selectedFields =  (!empty($this->constantColumns)) ? array() : $this->defaultSelected;
				} else {
					$this->selectedFields = $cookieSelectedFields;
				}
			} else {
				$this->selectedFields = $this->defaultSelected;
			}
		}
		if (!is_array($this->selectedFields)) {
			$this->selectedFields = array();
		}
		return $this->selectedFields;
	}
	
	protected function getRequestParams() {
		$requestParams = $this->grid->getRequestParams();
		unset($requestParams[$this->grid->addNamespace('dynh[sel]')]); //because SelectMultipleDrowpDown already exists
		//unset possible section/action request params [it will be provided from the form object!]
		if (isset($requestParams[$this->grid->addNamespace('section')])) {
			unset($requestParams[$this->grid->addNamespace('section')]);
		}
		if (isset($requestParams[$this->grid->addNamespace('action')])) {
			unset($requestParams[$this->grid->addNamespace('action')]);
		}
//var_dump($requestParams);
		return $requestParams;
	}

	protected function getHeaderOptions() {
		$ret = array();
		foreach ($this->headerFields as $colname => $opts) {
			if (in_array($colname, $this->constantColumns)) continue;
			$ret[] = array('id' => $colname, 'name'=>($opts['title'] ? $opts['title'] : $colname));
		}
		return $ret;
	}
	
	public function getHtml() {
		//build requestParams as hidden form elements
		$els = FormElement::createHiddenFormElements($this->getRequestParams(), '', array('noNamespace'=>true));
		foreach ($els as $k=>$v) {
			$this->form->addElement($v);
		}
		if (!$this->selectedFields) {
			$this->getSelectedFields();
		}
		$params = array('elementName'=>'dynh[sel]', 'options'=>$this->getHeaderOptions(), 'value'=>$this->selectedFields);
		if ($this->maxColumns) {
			$params['maxSelectedItems'] = $this->maxColumns;
		}
		$sel = new SelectMultipleDualList($params);
		$sel->setWidth('15em');
		$sel->setSize('12');
		$sel->setCaptions(array(
				  'left' => 'Hide column(s)'
				, 'right' => 'Show column(s)'
				, 'up' => 'Show before'
				, 'down' => 'Show after'
			)
		);
		$sel->setLabels(array(
				  'left' => 'Left'
				, 'right' => 'Right'
				, 'up' => 'Up'
				, 'down' => 'Down'
			)
		);
		$sel->setSelectLabels(array(
				  'left' => 'Hidden columns'
				, 'right' => 'Columns to show'.($this->maxColumns ? ' (max. '.$this->maxColumns.' columns)' : '')
			)
		);
		$this->form->addElement($sel);

		if ($this->hasCancel) {
			//build old_sel as hidden form elements
			$els = FormElement::createHiddenFormElements($this->selectedFields, 'dynh[sel_old]');
			foreach ($els as $k=>$v) {
				$this->form->addElement($v);
			}
			
			$e = new Submit(array('elementName'=>'dynh[cancel]', 'value'=>'Cancel'));
			$this->form->addElement($e);
		}
		$e = new Submit(array('elementName'=>'dynh[submit]', 'value'=>'Set columns'));
		$this->form->addElement($e);
		$e = new Submit(array('elementName'=>'dynh[reset]', 'value'=>'Use default'));
		$this->form->addElement($e);

		$fields = $this->form->getFields();

//if (!empty($this->constantColumns)) {
//	$ret .= '<br /><br />Note: some fields will always be shown<br />';
//}
		$ret .= $fields['begin'];
		$ret .= $fields['dynh']['sel'];
		$ret .= $fields['dynh']['submit'];
		$ret .= '&nbsp;&nbsp;';
		if ($fields['dynh']['cancel']) {
			$ret .= $fields['dynh']['cancel'];
			$ret .= '&nbsp;&nbsp;';
		}
		$ret .= $fields['dynh']['reset'];
		$ret .= $fields['end'];
//$this->form->echoHtml($ret);
		return $ret;
	}
	
}


?>
