<?php

class GridFilterAdvancedPlugin extends GridFilterSimplePlugin {

	const COLUMN = 'column';
	const OPERATOR = 'operator';
	const OPERAND = 'operand';

	const OPERATOR_SIMILAR_TO = 'ST';
	const OPERATOR_EQUAL_TO = 'EQ';
	const OPERATOR_NOT_EQUAL_TO = 'NE';
	const OPERATOR_GREATER_THAN = 'GT';
	const OPERATOR_LESS_THAN = 'LT';

	const BUTTON = 'advancedFilter';
	const RESET = 'afReset'; // different simple/advanced reset to keep focus

	// options
	const OPTION_SHOW_READABLE_EXPRESSIONS = 'showReadableExpressions';

	private $expression;

	public function preLoadExecute() {
		$c = $this->grid->getContext();
		$req = $this->grid->getRequest();

		$this->filter = $c->get(self::FILTER);
		if (isset($req[self::FILTER]) && is_array($req[self::FILTER])) {
			$this->filter = $this->cleanFilter($req[self::FILTER]);
			$c->set(self::FILTER, $this->filter);
		}
		if ($req[self::RESET]) {
			$this->filter = null;
			$c->set(self::FILTER, $this->filter);
		}

		$ds = $this->grid->getDataSource();

		if (!is_array($this->filter)) $this->filter = null;

		$expr = null;
		if ($this->filter) {
			// get expression for each column
			foreach ($this->filter as $k => $f) {
				$column = $this->getSearchableColumnByName($f[self::COLUMN]);
				if (!$column instanceof GridColumn) continue;

				$e = $this->getColumnExpression($column, $f);
				if ($e instanceof Expression) {
					$expr = $expr ? $ds->expressionAndFactory($expr, $e) : $e;
				}
			}

		} 

		if ($expr instanceof Expression) {
			if ($existingFilter = $ds->getFilter()) {
				$expr = $ds->expressionAndFactory($expr, $existingFilter);
			}

			$ds->setFilter($expr);
			$this->expression = $expr; //for later human readable output
		}
	}

	private function cleanFilter($filter) {
		if (is_array($filter)) {
			foreach ($filter as $k => $f) {
				$column = $this->getSearchableColumnByName($f[self::COLUMN]);
				// drop non searchable and empty operand cols
				if (!$column instanceof GridColumn || (!is_numeric($f[self::OPERAND][$column->getType()]) && empty($f[self::OPERAND][$column->getType()]))) {
					unset($filter[$k]);
					continue;
				}
			}
			return $filter;
		}
		return null;
	}

	public function render() {
		$filterName = $this->grid->addNamespace(self::FILTER);

		$searchableCols = $this->getSearchableColumns();
		if (empty($searchableCols)) return '';

		if ($this->getOption(self::OPTION_SHOW_READABLE_EXPRESSIONS, false) && $this->expression instanceof Expression) {
			$reader = new ExpressionReader();
			$humanReadableExpression = $reader->readHumanly($this->expression);
		}

		$this->includeJs();

		$html  = '<div class="grid-filter">';
		if (!empty($humanReadableExpression)) {
			$html .= '<div class="grid-expression">' . $humanReadableExpression . '</div>';
		}
		$html .= '<form action="" method="post">';
		$html .= $this->grid->getHiddenFields(array(GridPagerPlugin::PAGE => 1));

		$i = 0;
		// render existing filters
		if (is_array($this->filter)) {
			foreach ($this->filter as $f) {
				$html .= $this->getColumnFilter($filterName . '[' . $i . ']', $f);
				$i++;
			}
		}

		// one empty input at the end
		$html .= $this->getColumnFilter($filterName . '[' . $i . ']');

		$html .= '<div style="margin-top:5px">';
		$html .= '<input type="submit" class="grid-button" name="' . $this->grid->addNamespace(self::BUTTON) . '" value="' . Lang::get('Filter') . '" /> ';
		$html .= '<input type="submit" class="grid-button" name="' . $this->grid->addNamespace(self::RESET) . '" value="' . Lang::get('Reset') . '" />';
		$html .= '</div>';

		$html .= '</form>';
		$html .= '</div>';

		return $html;
	}

	private function getSearchableColumnByName($columnName) {
		$columns = $this->getSearchableColumns();
		foreach ($columns as $c) {
			if ($c->getName() == $columnName) return $c;
		}
	}

	private function getColumnExpression(GridColumn $column, $filter) {
		$ds = $this->grid->getDataSource();
		$expr = null;

		$type = $column->getType();
		$query = $filter[self::OPERAND][$type];

		// prepare value for DS
		switch ($type) {
			case Value::TYPE_TEXT:
				$val = $ds->valueTextFactory($query); break;
			case Value::TYPE_NUMERIC:
				if (!is_numeric($query)) return null;
				$val = $ds->valueNumericFactory($query); break;	
			case Value::TYPE_BOOLEAN:
				$val = $ds->valueBooleanFactory($query == 't' ? true : false); break;
			case Value::TYPE_DATE:
				//TODO date validation
				$val = $ds->valueDateFactory($query); break;	
		}

		switch ($filter[self::OPERATOR]) {
			case self::OPERATOR_SIMILAR_TO:
				if (in_array($type, array(Value::TYPE_TEXT, Value::TYPE_NUMERIC))) {
					$val = $ds->valueTextFactory($query);
					$e = $ds->expressionSimilarToFactory($column->getName(), $val, array('wildcard' => '*', 'autoPrependWildcard' => true, 'autoAppendWildcard' => true)); 
				}	
				break;
			case self::OPERATOR_EQUAL_TO:
				if (in_array($type, array(Value::TYPE_TEXT, Value::TYPE_NUMERIC, Value::TYPE_DATE, Value::TYPE_BOOLEAN))) {
					if ($type == Value::TYPE_TEXT) {
						// case insensitive
						$e = $ds->expressionSimilarToFactory($column->getName(), $val, array('wildcard' => '*', 'autoPrependWildcard' => false, 'autoAppendWildcard' => false)); 
					} else {
						$e = $ds->expressionEqualToFactory($column->getName(), $val); 
					}
				}
				break;
			case self::OPERATOR_NOT_EQUAL_TO:
				if (in_array($type, array(Value::TYPE_TEXT, Value::TYPE_NUMERIC, Value::TYPE_DATE, Value::TYPE_BOOLEAN))) {
					$e = $ds->expressionNotEqualToFactory($column->getName(), $val); 
				}
				break;
			case self::OPERATOR_GREATER_THAN:
				if (in_array($type, array(Value::TYPE_NUMERIC, Value::TYPE_DATE))) {
					$e = $ds->expressionGreaterThanFactory($column->getName(), $val); 
				}
				break;
			case self::OPERATOR_LESS_THAN:
				if (in_array($type, array(Value::TYPE_NUMERIC, Value::TYPE_DATE))) {
					$e = $ds->expressionLessThanFactory($column->getName(), $val); 
				}
				break;
		}

		if ($e instanceof Expression) {
			$expr = $expr ? $ds->expressionAndFactory($expr, $e) : $e;
		}
		return $expr;
	}

	/*
		filter[0][column] = 'id'
		filter[0][operator] = 'eq'
		//ovisno o tipu kolone cita se drugi operand ali svi se submitaju
		//ovo je iz razloga da se sa jqueryjem moze mijenjati tip unosa text/date/num/boolean
		filter[0][operand][text] = 'asd'
		filter[0][operand][numeric] = '123'
		filter[0][operand][date] = '2010-12-10'
		filter[0][operand][bool] = '1'
	 */
	private function getColumnFilter($elementName, $value = array()) {
		$id = rand();
		$html = '<div>';

		$html .= $this->getColumnPicker($elementName, $value, $id);
		$html .= $this->getOperatorPicker($elementName, $value, $id);
		$html .= $this->getOperandInputFields($elementName, $value, $id);

		$html .= '</div>';
		return $html;
	}

	private function getColumnPicker($elementName, $value, $id) {
		$searchableCols = $this->getSearchableColumns();

		$html = '<select name="' . $elementName . '[' . self::COLUMN . ']" id="col-' . $id . '">';
		foreach ($searchableCols as $col)  {
			$html .= '<option value="' . $col->getName() . '" class="' . $col->getType() . '"' . ($value[self::COLUMN] == $col->getName() ? ' selected="selected"' : '') . '>' . $col->getTitle() . '</option>';
		}
		$html .= '</select>
		';
		return $html;
	}

	private function getOperatorPicker($elementName, $value, $id) {
		$operators = array(
			self::OPERATOR_SIMILAR_TO => Lang::get('similar to'),
			self::OPERATOR_EQUAL_TO => Lang::get('equal'),
			self::OPERATOR_NOT_EQUAL_TO => Lang::get('not equal'),
			self::OPERATOR_GREATER_THAN => Lang::get('greater than'),
			self::OPERATOR_LESS_THAN => Lang::get('less than'),
		);

		$html = '<select name="' . $elementName . '[' . self::OPERATOR . ']" id="op-col-' . $id . '">';
		foreach ($operators as $k => $v) {
			$html .= '
				<option value="' . $k . '"' . ($k == $value[self::OPERATOR] ? ' selected="selected"' : '') . '>' . $v . '</option>';
		}
		$html .= '
			  </select>
		';
		return $html;
	}

	private function getOperandInputFields($elementName, $value, $id) {
		// create operand form fields for every type
		$te = new TextLine(array(
			TextLine::NAME => $elementName . '[' . self::OPERAND . '][' . Value::TYPE_TEXT . ']',
			TextLine::VALUE => $value[self::OPERAND][Value::TYPE_TEXT],
			TextLine::SIZE => 30
		));
		$ne = new TextLine(array(
			TextLine::NAME => $elementName . '[' . self::OPERAND . '][' . Value::TYPE_NUMERIC . ']',
			TextLine::VALUE => $value[self::OPERAND][Value::TYPE_NUMERIC],
			TextLine::SIZE => 10
		));
		$de = new DatePicker(array(
			DatePicker::NAME => $elementName . '[' . self::OPERAND . '][' . Value::TYPE_DATE . ']',
			DatePicker::ID => $id . '-' . self::OPERAND . '-' . Value::TYPE_DATE,
			DatePicker::VALUE => $value[self::OPERAND][Value::TYPE_DATE],	
		));
		$be = new DropDown(array(
			DropDown::NAME => $elementName . '[' . self::OPERAND . '][' . Value::TYPE_BOOLEAN . ']',
			DropDown::VALUE => $value[self::OPERAND][Value::TYPE_BOOLEAN],
			DropDown::OPTIONS => array('t' => Lang::get('yes'), 'f' => Lang::get('no'))
		));

		$html = '
			<span id="x-col-' . $id . '-' . Value::TYPE_TEXT . '" class="' . Value::TYPE_TEXT . '">
				' . $te->getHtml() . '
			</span>
			<span id="x-col-' . $id . '-' . Value::TYPE_NUMERIC . '" class="' . Value::TYPE_NUMERIC . '">
				' . $ne->getHtml() . '
			</span>
			<span id="x-col-' . $id . '-' . Value::TYPE_DATE . '" class="' . Value::TYPE_DATE . '">
				' . $de->getHtml() . '
			</span>
			<span id="x-col-' . $id . '-' . Value::TYPE_BOOLEAN . '" class="' . Value::TYPE_BOOLEAN . '">
				' . $be->getHtml() . '
			</span>
		';
		return $html;
	}

	private function includeJs() {
		// for dynamic dropdown
		File::includeJs('jquery.js', File::LIB_DIR);

		HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
	function filterDropDowns(dd) {
		var c = $(dd).find("option:selected");
		var op = $("#op-"+$(dd).attr("id"));

		$("option", op).each(function() {$(this).removeAttr("disabled")});
		$("[id^=\'x-"+$(dd).attr("id")+"-\']").each(function () {$(this).hide()});

		switch(c.attr("class")) {
			case "'.Value::TYPE_TEXT.'": 
				op.find("option[value=\''.self::OPERATOR_GREATER_THAN.'\']").attr("disabled", true);
				op.find("option[value=\''.self::OPERATOR_LESS_THAN.'\']").attr("disabled", true);
				$("#x-"+$(dd).attr("id")+"-'.Value::TYPE_TEXT.'").show();
				
			break;
			case "'.Value::TYPE_DATE.'": 
				op.find("option[value=\''.self::OPERATOR_SIMILAR_TO.'\']").attr("disabled", true);
				$("#x-"+$(dd).attr("id")+"-'.Value::TYPE_DATE.'").show();
			break;
			case "'.Value::TYPE_NUMERIC.'": 
				op.find("option[value=\''.self::OPERATOR_SIMILAR_TO.'\']").attr("disabled", true);
				$("#x-"+$(dd).attr("id")+"-'.Value::TYPE_NUMERIC.'").show();
			break;
			case "'.Value::TYPE_BOOLEAN.'":
				op.find("option[value=\''.self::OPERATOR_SIMILAR_TO.'\']").attr("disabled", true);
				op.find("option[value=\''.self::OPERATOR_GREATER_THAN.'\']").attr("disabled", true);
				op.find("option[value=\''.self::OPERATOR_LESS_THAN.'\']").attr("disabled", true);
				$("#x-"+$(dd).attr("id")+"-'.Value::TYPE_BOOLEAN.'").show();
			break;
		}
		if (op.find("option:selected").attr("disabled")) {
			op.find("option:not(:disabled)").first().attr("selected", true);
		}
	}
	$(document).ready(function() {
		$("select[id^=\'col-\']").change(function() {filterDropDowns(this)});
		$("select[id^=\'col-\']").each(function() {filterDropDowns(this)});
	});

</script>
');
	}

}
