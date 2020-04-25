<?php

abstract class DataSource {

	abstract function getData($limit = null, $offset = null);

	abstract function orderBy($cols);

	abstract function getRowCount();

	abstract function setDistinct($cols);

	abstract function expressionAndFactory($a, $b, $params = null);
	abstract function expressionOrFactory($a, $b, $params = null);
	abstract function expressionNotFactory($a, $params = null);
	abstract function expressionEqualToFactory($a, $b, $params = null);
	abstract function expressionNotEqualToFactory($a, $b, $params = null);
	abstract function expressionSimilarToFactory($a, $b, $params = null);
	abstract function expressionGreaterThanFactory($a, $b, $params = null);
	abstract function expressionLessThanFactory($a, $b, $params = null);

	abstract function valueTextFactory($value);
	abstract function valueDateFactory($value);
	abstract function valueNumericFactory($value);
	abstract function valueBooleanFactory($value);

	protected $filterExpression;

	public function setFilter(Expression $expr) {
		$this->filterExpression = $expr;
		return $this->filterExpression;
	}
	
	public function getFilter() {
		return $this->filterExpression;
	}

	public function addFilter(Expression $e) {
		$filt = $this->getFilter();
		if (!empty($filt)) {
			$this->setFilter(
				$this->expressionAndFactory(
					$this->getFilter(),
					$e
				)
			);
		} else {
			$this->setFilter($e);
		}
		return $this->filterExpression;
	}

}
