<?php

//fixme: //todo: simple filter does not work - does not keep context!
//idea: Grid::setSearchMode Grid::SEARCHMODE_FILTER, Grid::SEARCHMODE_SEARCH 

	class GridGenericSearch {

		const OPERATOR_CONTAINS = 'contains';
		const OPERATOR_CONTAINS_NOT = 'containsnot';

		const OPERATOR_EQUAL_NOT = 'notequal';		

		const OPERATOR_LESS = 'less';
		const OPERATOR_LESS_EQUAL = 'lessequal';
		const OPERATOR_EQUAL = 'equal';
		const OPERATOR_GREATER_EQUAL = 'greaterequal';
		const OPERATOR_GREATER = 'greater';

		const OPERATOR_BLANK = 'blank';
		const OPERATOR_BLANK_NOT = 'notblank';

		const OPERATOR_TRUE = 'true';
		const OPERATOR_FALSE = 'false';

		const OPERATOR_BETWEEN = 'between';
		
		/**
		 * @var Grid
		 */
		protected $grid;
		protected $tableHeader;
		protected $application;
		protected $currentSection;
		protected $form;
		protected $request;
		protected $requestParams;

		protected $oldExpression;
		protected $newExpression;
		protected $expression;
		
		protected $textFields = array();
		protected $numericFields = array();
		protected $dateFields = array();
		protected $booleanFields = array();

		private $searchMode;		
		
		protected $expressionReader;

		function __construct(Grid $g, $params = null) {
			$this->grid = $g;

			if (is_array($params['tableHeader'])) {
				$this->tableHeader = $params['tableHeader'];
			} else {
				$this->tableHeader = $this->grid->getTableHeader();
			}
			foreach ($this->tableHeader as $k=>$v) {
				if (!isset($v['type'])) { 
					continue;			
				}
				$v['column_name'] = $k;
				switch ($v['type']) {
					case GridValue::TYPE_TEXT :
						$this->textFields[$k] = $v; 
						break;
					case GridValue::TYPE_NUMERIC : 
						$this->numericFields[$k] = $v; 
						break;
					case GridValue::TYPE_DATE : 
						$this->dateFields[$k] = $v; 
						break;
					case GridValue::TYPE_BOOLEAN : 
						$this->booleanFields[$k] = $v; 
						break;
					default :
				} 	
			}
			$this->application = $this->grid->getApplication();
			if ($this->application) {
				$this->currentSection = $this->application->getCurrentSectionObject();
			} else {
				if (!isset($params['currentSection'])) {
					throw new Exception('could not get application context from grid object so currentSection param to '.get_class($this).' is necessary. none provided.');
				}
				$this->currentSection = $params['currentSection'];
			}
			if (!($this->currentSection instanceof ApplicationSection)) {
					throw new exception ('Grid must provide application with active current section, or you must provide one as a param "currentSection"');
			}
			$this->form = $params['form'];
			if (!($this->form instanceof Form)) {
				$formParams = array('watchState'=>false);
				$this->form = new Form($this->currentSection, array_merge($formParams, array(method => 'POST'))); //auto changed params
			}
			if (isset($params['request'])) {
				$this->request = $params['request'];
			} else {
				$this->request = $this->grid->getRequest();
			}
			
			$this->ds = $this->grid->getDataSource();

			$reqexpr = $this->getExpressionFromRequest();
			if ($reqexpr) {
				$this->grid->addConstParams(array($this->grid->addNamespace('generic_search') => $reqexpr->toArray()));
				$this->ds->addFilter($reqexpr);
			}

			if (isset($params['searchMode'])) {
				$this->searchMode = $params['searchMode'] ? true : false;
			}
			
			if (!$this->hasExpression() && $this->searchMode) {
				//force no results
				$this->ds->addFilter($this->ds->expressionEqualToFactory($this->ds->valueBooleanFactory(true), $this->ds->valueBooleanFactory(false)));
			}

		}


		protected function getRequestParams() {
			$requestParams = $this->grid->getRequestParams();
			//unset possible section/action request params [it will be provided from the form object!]
			if (isset($requestParams[$this->grid->addNamespace('section')])) {
				unset($requestParams[$this->grid->addNamespace('section')]);
			}
			if (isset($requestParams[$this->grid->addNamespace('action')])) {
				unset($requestParams[$this->grid->addNamespace('action')]);
			}
			return $requestParams;
		}


		protected function getNewExpressionFromRequest($req = null) {
			//get req from parameter or from instance's request
			if (!$req) {
				$req = & $this->request;
			}
			$expr = null;
			$exprArray = array();
			if (isset($req['gridsearch'])) {
				if (isset($req['gridsearch']['text'])) {
					if ($req['gridsearch']['text']['column'] && $req['gridsearch']['text']['operator']) {
						if ($req['gridsearch']['text']['value']) {
							switch ($req['gridsearch']['text']['operator']) {
								case self::OPERATOR_CONTAINS :
									$exprArray[] = $this->ds->expressionSimilarToFactory($req['gridsearch']['text']['column'], $this->ds->valueTextFactory($req['gridsearch']['text']['value']), array('wildcard'=>'*', 'autoPrependWildcard'=>true));
									break;
								case self::OPERATOR_CONTAINS_NOT :
									$exprArray[] = $this->ds->expressionNotFactory($this->ds->expressionSimilarToFactory($req['gridsearch']['text']['column'], $this->ds->valueTextFactory($req['gridsearch']['text']['value'])));
									break;
								case self::OPERATOR_EQUAL :
									$exprArray[] = $this->ds->expressionEqualToFactory($req['gridsearch']['text']['column'], $this->ds->valueTextFactory($req['gridsearch']['text']['value']));
									break;
								case self::OPERATOR_EQUAL_NOT :
									$exprArray[] = $this->ds->expressionNotFactory($this->ds->expressionEqualToFactory($req['gridsearch']['text']['column'], $this->ds->valueTextFactory($req['gridsearch']['text']['value'])));
									break;
							}
						} else {
							switch ($req['gridsearch']['text']['operator']) {
								case self::OPERATOR_BLANK :
									$exprArray[] = $this->ds->expressionIsNullFactory($req['gridsearch']['text']['column']);
									break;
								case self::OPERATOR_BLANK_NOT :
									$exprArray[] = $this->ds->expressionNotNullFactory($req['gridsearch']['text']['column']);
									break;
							}
						}
					}
				}
				if (isset($req['gridsearch']['numeric'])) {
					if ($req['gridsearch']['numeric']['column'] && $req['gridsearch']['numeric']['operator']) {
						if ($req['gridsearch']['numeric']['value']) {
							switch ($req['gridsearch']['numeric']['operator']) {
								case self::OPERATOR_LESS :
									$exprArray[] = $this->ds->expressionLessThanFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value']));
									break;
								case self::OPERATOR_LESS_EQUAL :
									$exprArray[] =
										$this->ds->expressionOrFactory(
											$this->ds->expressionEqualToFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value'])),
											$this->ds->expressionLessThanFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value']))
										);
									break;
								case self::OPERATOR_EQUAL :
									$exprArray[] = $this->ds->expressionEqualToFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value']));
									break;
								case self::OPERATOR_GREATER_EQUAL :
									$exprArray[] =
										$this->ds->expressionOrFactory(
											$this->ds->expressionEqualToFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value'])),
											$this->ds->expressionGreaterThanFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value']))
										);
									break;
								case self::OPERATOR_GREATER :
									$exprArray[] = $this->ds->expressionGreaterThanFactory($req['gridsearch']['numeric']['column'], $this->ds->valueNumericFactory($req['gridsearch']['numeric']['value']));
									break;
							}
						} else {
							switch ($req['gridsearch']['numeric']['operator']) {
								case self::OPERATOR_BLANK :
									$exprArray[] = $this->ds->expressionIsNullFactory($req['gridsearch']['numeric']['column']);
									break;
								case self::OPERATOR_BLANK_NOT :
									$exprArray[] = $this->ds->expressionNotNullFactory($req['gridsearch']['numeric']['column']);
									break;
							}
						}
					}
				}
				if (isset($req['gridsearch']['date'])) {
					if ($req['gridsearch']['date']['column'] && $req['gridsearch']['date']['operator']) {
						if ($req['gridsearch']['date']['value']) {
							switch ($req['gridsearch']['date']['operator']) {
								case self::OPERATOR_LESS :
									$exprArray[] = $this->ds->expressionLessThanFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value']));
									break;
								case self::OPERATOR_LESS_EQUAL :
									$exprArray[] =
										$this->ds->expressionOrFactory(
											$this->ds->expressionEqualToFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value'])),
											$this->ds->expressionLessThanFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value']))
										);
									break;
								case self::OPERATOR_EQUAL :
									$exprArray[] = $this->ds->expressionEqualToFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value']));
									break;
								case self::OPERATOR_GREATER_EQUAL :
									$exprArray[] =
										$this->ds->expressionOrFactory(
											$this->ds->expressionEqualToFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value'])),
											$this->ds->expressionGreaterThanFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value']))
										);
									break;
								case self::OPERATOR_GREATER :
									$exprArray[] = $this->ds->expressionGreaterThanFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value']));
									break;

							}
							if ($req['gridsearch']['date']['value2'] &&
									$req['gridsearch']['date']['operator'] == self::OPERATOR_BETWEEN) {
									$exprArray[] =
										$this->ds->expressionAndFactory(
											$this->ds->expressionOrFactory(
												$this->ds->expressionEqualToFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value'])),
												$this->ds->expressionGreaterThanFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value']))
											),
											$this->ds->expressionOrFactory(
												$this->ds->expressionEqualToFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value2'])),
												$this->ds->expressionLessThanFactory($req['gridsearch']['date']['column'], $this->ds->valuedateFactory($req['gridsearch']['date']['value2']))
											)
										);
							}
						} else {
							switch ($req['gridsearch']['date']['operator']) {
								case self::OPERATOR_BLANK :
									$exprArray[] = $this->ds->expressionIsNullFactory($req['gridsearch']['date']['column']);
									break;
								case self::OPERATOR_BLANK_NOT :
									$exprArray[] = $this->ds->expressionNotNullFactory($req['gridsearch']['date']['column']);
									break;
							}
						}
					}
				}
				if (isset($req['gridsearch']['boolean'])) {
					if ($req['gridsearch']['boolean']['column'] && $req['gridsearch']['boolean']['operator'] /*&& $req['gridsearch']['boolean']['value']*/) {
						$value = ($req['gridsearch']['boolean']['operator'] == self::OPERATOR_TRUE) ? true : false;
						$exprArray[] = $this->ds->expressionEqualToFactory($req['gridsearch']['boolean']['column'], $this->ds->valueBooleanFactory($value));
					}
				}
				foreach ($exprArray as $e) {
					if ($expr) {
						$expr = $this->ds->expressionAndFactory($expr, $e);
					} else {
						$expr = $e;
					}
				}
			}
			$this->newExpression = $expr;
			return $expr;
		}
		

		protected function getOldExpressionFromRequest($req = null) {
			//get req from parameter or from instance's request
			if (!$req) {
				$req = & $this->request;
			}
			$expr = null;
			if (isset($req['generic_search']) && !empty($req['generic_search'])) {
				$expr = GridExpression::factoryFromArray($req['generic_search']);
			}
			$this->oldExpression = $expr;
			return $expr;
		}


		protected function getExpressionFromRequest($req = null) {
			//get req from parameter or from instance's request
			if (!$req) {
				$req = & $this->request;
			}

			if ($req['gridsearch']['clear']) {
				$this->grid->resetPaging();
				return null;
			}

			$old = $this->getOldExpressionFromRequest($req);
			$new = $this->getNewExpressionFromRequest($req);

			$expr = null;
			if ($old instanceof GridExpression) {
				if ($new instanceof GridExpression) {
					$this->grid->resetPaging();
					$expr = $this->ds->expressionAndFactory($old, $new);
				} else {
					$expr = $old;
				}
			} else if ($new instanceof GridExpression) {
				$this->grid->resetPaging();
				$expr = $new;
			}
			$this->expression = $expr;
			return $expr;
		}


		function getHtml() {
			$ret = $this->getForm();
			return $ret;

		}

		public function hasExpression() {
			return $this->expression instanceof GridExpression;
		}

		protected function echoHtml($html) {
			echo '<textarea cols=120 rows=20>';
			echo htmlentities($html);
			echo '</textarea>';
		}

	
		protected function getForm() {
			//build requestParams as hidden form elements
			$els = FormElement::createHiddenFormElements($this->getRequestParams(), '', array('noNamespace'=>true));
			foreach ($els as $k=>$v) {
				$this->form->addElement($v);
			}
			$uniqueId = uniqid();
			$ret .= "
				<script>
					function operatorChange(operator, type) {
						var id1 = type + '_val1_' + '" . $uniqueId . "';
						var obj1 = document.getElementById(id1);
						if (operator == '" . self::OPERATOR_BLANK . "' || operator == '" . self::OPERATOR_BLANK_NOT . "') {
							obj1.style.display = 'none';
						} else {
							obj1.style.display = 'block';
						}

						if (type == 'date') {
							var id2 = type + '_val2_' + '" . $uniqueId . "';
							var obj2 = document.getElementById(id2);
							var id3 = type + '_val3_' + '" . $uniqueId . "';
							var obj3 = document.getElementById(id3);
							if (operator != '" . self::OPERATOR_BETWEEN . "') {
								obj2.style.display = 'none';
								obj3.style.display = 'none';
							} else {
								obj2.style.display = 'block';
								obj3.style.display = 'block';
							}
						}
					}
				</script>
			";
			
			if (!empty($this->textFields)) {
				$options = array();
				foreach ($this->textFields as $k => $v) {
					$opt = array (
						  'id' => $k
						, 'name' => $v['title']
					);
					$options[] = $opt;
				}
				$e = new SelectSingleDropDown(array(
					'elementName' => 'gridsearch[text][column]',
					'options' => $options,
					'nullText' => '',
				));
				$this->form->addElement($e);
			
				$options = array(
					array(
						'id' => GridGenericSearch::OPERATOR_CONTAINS,
						'name' => 'contains' 
					),
					array(
						'id' => GridGenericSearch::OPERATOR_CONTAINS_NOT,
						'name' => 'does not contain' 
					),
					array(
						'id' => GridGenericSearch::OPERATOR_EQUAL,
						'name' => 'is exactly' 
					),
					array(
						'id' => GridGenericSearch::OPERATOR_EQUAL_NOT,
						'name' => 'is not' 
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BLANK,
						'name' => 'is blank'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BLANK_NOT,
						'name' => 'is not blank'
					),
				);
				$e = new SelectSingleDropDown(array(
					'elementName' => 'gridsearch[text][operator]',
					'options' => $options,
					'disableNullOption' => true,
					'value' => GridGenericSearch::OPERATOR_CONTAINS,
					'onChange' => 'operatorChange(this.value, \'text\')',
				));
				$this->form->addElement($e);
	
				$e = new TextLine(array('elementName'=>'gridsearch[text][value]', 'value'=>''));
				$this->form->addElement($e);
			}

			if (!empty($this->numericFields)) {
				$options = array();
				foreach ($this->numericFields as $k => $v) {
					$opt = array (
						  'id' => $k
						, 'name' => $v['title']
					);
					$options[] = $opt;
				}
				$e = new SelectSingleDropDown(array(
					'elementName' => 'gridsearch[numeric][column]',
					'options' => $options,
					'nullText' => ''
				));
				$this->form->addElement($e);
	
				$options = array(
					array(
						'id' => GridGenericSearch::OPERATOR_LESS,
						'name' => 'less than'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_LESS_EQUAL,
						'name' => 'less than or equal to'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_EQUAL,
						'name' => 'equal to'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_GREATER_EQUAL,
						'name' => 'greater than or equal to'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_GREATER,
						'name' => 'greater than'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BLANK,
						'name' => 'is blank'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BLANK_NOT,
						'name' => 'is not blank'
					),
				);
				$e = new SelectSingleDropDown(array(
					'elementName' => 'gridsearch[numeric][operator]',
					'options' => $options,
					'disableNullOption' => true,
					'value' => GridGenericSearch::OPERATOR_EQUAL,
					'onChange' => 'operatorChange(this.value, \'numeric\')',
				));
				$this->form->addElement($e);
	
				$e = new TextLine(array('elementName'=>'gridsearch[numeric][value]', 'value'=>''));
				$this->form->addElement($e);
			}

			if (!empty($this->dateFields)) {
				$options = array();
				foreach ($this->dateFields as $k => $v) {
					$opt = array (
						  'id' => $k
						, 'name' => $v['title']
					);
					$options[] = $opt;
				}
				$e = new SelectSingleDropDown(array(
					'elementName' => 'gridsearch[date][column]',
					'options' => $options,
					'nullText' => ''));
				$this->form->addElement($e);
	
				$options = array(
					array(
						'id' => GridGenericSearch::OPERATOR_LESS,
						'name' => 'before'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_LESS_EQUAL,
						'name' => 'before or on'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_EQUAL,
						'name' => 'on'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_GREATER_EQUAL,
						'name' => 'after or on'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_GREATER,
						'name' => 'after than'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BLANK,
						'name' => 'is blank'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BLANK_NOT,
						'name' => 'is not blank'
					),
					array(
						'id' => GridGenericSearch::OPERATOR_BETWEEN,
						'name' => 'between'
					),
				);
				$e = new SelectSingleDropDown(array(
					'elementName' => 'gridsearch[date][operator]',
					'options' => $options,
					'disableNullOption' => true,
					'value' => GridGenericSearch::OPERATOR_EQUAL,
					'onChange' => 'operatorChange(this.value, \'date\')',
				));
				$this->form->addElement($e);
	
				$e = new DateTime(array('elementName'=>'gridsearch[date][value]', 'value'=>''));
				$this->form->addElement($e);

				$e = new DateTime(array('elementName'=>'gridsearch[date][value2]', 'value'=>''));
				$this->form->addElement($e);
			}

			if (!empty($this->booleanFields)) {
				$options = array();
				foreach ($this->booleanFields as $k => $v) {
					$opt = array (
						  'id' => $k
						, 'name' => $v['title']
					);
					$options[] = $opt;
				}
				$e = new SelectSingleDropDown(array('elementName'=>'gridsearch[boolean][column]', 'options'=>$options, 'nullText'=>''));
				$this->form->addElement($e);
			
				$options = array(
					array(
						'id' => GridGenericSearch::OPERATOR_TRUE,
						'name' => 'is true' 
					),
					array(
						'id' => GridGenericSearch::OPERATOR_FALSE,
						'name' => 'is false' 
					),
				);
				$e = new SelectSingleDropDown(array('elementName'=>'gridsearch[boolean][operator]', 'options'=>$options, 'disableNullOption'=>true, 'value' => GridGenericSearch::OPERATOR_EQUAL, 'value' => GridGenericSearch::OPERATOR_EQUAL));
				$this->form->addElement($e);
	
				$e = new TextLine(array('elementName'=>'gridsearch[boolean][value]', 'value'=>''));
				$this->form->addElement($e);
			}

			if (($this->searchMode) && (!$this->hasExpression())) {
				$cap = 'Search';
			} else {
				$cap = 'Add Filter';
			}
			$e = new Submit(array('elementName'=>'gridsearch[submit]', 'value'=>$cap));
			$this->form->addElement($e);

			if ($this->hasExpression()) {
				$e = new Submit(array('elementName'=>'gridsearch[clear]', 'value'=>'Clear Filter'));
				$this->form->addElement($e);
			}

			$fields = $this->form->getFields();

			$visible = array();

			$opText = $fields['gridsearch']['text']['operator'];
			$opNumeric = $fields['gridsearch']['numeric']['operator'];
			$opDate = $fields['gridsearch']['date']['operator'];
			$opBoolean = $fields['gridsearch']['boolean']['operator'];
			
			$visibleText = ($opText == self::OPERATOR_BLANK || $opText == self::OPERATOR_BLANK_NOT) ? 'none' : 'block';
			$visibleNumeric = ($opNumeric == self::OPERATOR_BLANK || $opNumeric == self::OPERATOR_BLANK_NOT) ? 'none' : 'block';
			$visibleBoolean = ($opBoolean == self::OPERATOR_BLANK || $opBoolean == self::OPERATOR_BLANK_NOT) ? 'none' : 'block';
			$visibleDate1 = ($opDate == self::OPERATOR_BLANK || $opDate == self::OPERATOR_BLANK_NOT) ? 'none' : 'block';
			$visibleDate2 = ($opDate != self::OPERATOR_BETWEEN) ? 'none' : 'block';

			$ret .= $fields['begin'];
			$ret .= '<table>';
			$ret .= '<tr>';
				$ret .= '<td>' . $fields['gridsearch']['text']['column'] . '</td>';
				$ret .= '<td>' . $fields['gridsearch']['text']['operator'] . '</td>';
				$ret .= '<td style="display: ' . $visibleText . '" colspan="3" id="text_val1_' . $uniqueId . '">';
					$ret .= $fields['gridsearch']['text']['value'];
				$ret .= '</td>';
			$ret .= '</tr>';
			$ret .= '<tr>';
				$ret .= '<td>' . $fields['gridsearch']['numeric']['column'] . '</td>';
				$ret .= '<td>' . $fields['gridsearch']['numeric']['operator'] . '</td>';
				$ret .= '<td colspan="3">';
					$ret .= '<div id="numeric_val1_' . $uniqueId . '" style="display: ' . $visibleNumeric . '">';
					$ret .= $fields['gridsearch']['numeric']['value'];
					$ret .= '</div>';
				$ret .= '</td>';
			$ret .= '</tr>';
			$ret .= '<tr>';
				$ret .= '<td>' . $fields['gridsearch']['date']['column'] . '</td>';
				$ret .= '<td>' . $fields['gridsearch']['date']['operator'] . '</td>';
				$ret .= '<td>';
					$ret .= '<div id="date_val1_' . $uniqueId . '" style="display: ' . $visibleDate1 . '">';
					$ret .= $fields['gridsearch']['date']['value'];
					$ret .= '</div>';
				$ret .= '</td>';
				$ret .= '<td>';
					$ret .= '<div id="date_val2_' . $uniqueId . '" style="display: ' . $visibleDate2 . '">';
					$ret .= '&nbsp;and&nbsp;';
					$ret .= '</div>';
				$ret .= '</td>';
				$ret .= '<td>';
					$ret .= '<div id="date_val3_' . $uniqueId . '" style="display: ' . $visibleDate2 . '">';
					$ret .= $fields['gridsearch']['date']['value2'];
					$ret .= '</div>';
				$ret .= '</td>';
			$ret .= '</tr>';

$ret .= '<tr>';
	$ret .= '<td>' . $fields['gridsearch']['boolean']['column'] .  '</td>';
	$ret .= '<td colspan="4">' . $fields['gridsearch']['boolean']['operator'] . '</td>';
	//$ret .= '<td>' . $fields['gridsearch']['boolean']['value'] . '</td>';
$ret .= '</tr>';

			$ret .= '</table>';
			$ret .= $fields['gridsearch']['submit'];
			if ($fields['gridsearch']['clear']) {
				$ret .= '&nbsp;';
				$ret .= $fields['gridsearch']['clear'];

				if (!$this->expressionReader instanceof GridExpressionReader) {
					$this->expressionReader = new GridExpressionReader();
				}
				$this->applyLeftTranslationToExpression($this->expressionReader);
				$ret .= '<br /><br /> Showing results matching the following statement: <br /><br />' . $this->expressionReader->readHumanly($this->expression) . '<br />';
			}
			$ret .= $fields['end'];
			return $ret;
		}

		public function setExpressionReader(GridExpressionReader $e) {
			$this->expressionReader = $e;
		}

		protected function applyLeftTranslationToExpression(GridExpressionReader $expressionReader) {
			$trArr = array();
			if (!is_array($this->tableHeader)) {
				return;
			}
			foreach ($this->tableHeader as $k=>$v) {
				if ($v['title']) {
					$trArr[$k] = $v['title'];
				}
			}
			$expressionReader->setTranslateLeftArray($trArr);
		}
	}


?>
