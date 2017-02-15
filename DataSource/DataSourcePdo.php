<?php

include_once('Expression.php');
include_once('Value.php');

class DataSourcePdo extends DataSource implements IDataSourceDistinctable {

	private $pdo;
	private $query;
	private $queryRewritten;
	private $queryFilter;
	private $queryFilterOrderLimitOffset;
	private $orderBy;
	private $limit;
	private $offset;
	private $statement;
	private $distinct;

	function __construct(PDO $pdo, $query) {
		$this->pdo = $pdo;
		$this->query = $query;
	}


	public function getData($limit = null, $offset = null) {
		if (is_numeric($limit)) {
			$this->limit = $limit;
		}
		if (is_numeric($offset)) {
			$this->offset = $offset;
		}
		$s = $this->executeStatement();
		$data = $s->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}


	public function orderBy($cols) {
		//array('col_identifier'=>'asc|desc')
		//array('col_identifier1', 'col_identifier2')
		$this->orderBy = $cols;
	}


	public function getRowCount() {
		$this->prepareSql();
		$countQuery = preg_replace('/^SELECT \* FROM (.*)$/ims', 'SELECT COUNT(*) FROM $1', $this->queryFilter);
		$res = $this->pdo->query($countQuery);
		if (!$res) {
			trigger_error('Query failed [' . $countQuery . ']', E_USER_ERROR);
		}
		$count = $res->fetchColumn();
		
		return $count;
	}


	public function setDistinct($cols) {
		if (! is_array($cols)) {
			throw new Exception('List of distinct columns must be in an array.');
		}
		$this->distinct = $cols;
	}


	private function getOrderByString() {
		if (!is_array($this->orderBy) || empty($this->orderBy)) return '';
		$a = array();
		foreach ($this->orderBy as $column => $direction) {
			if (is_numeric($column)) {
				// backwards compatibility array('id', 'name') will be 'order by id asc, name asc'
				$a[] = $direction . ' asc';
			} else {
				$a[] = $column . ' ' . $direction;
			}
		}
		return ' ORDER BY ' . implode(', ', $a);
	}


	private function getLimitOffsetString() {
		$limitOffset = '';
		if ($this->limit) {
			$limitOffset .= ' LIMIT ' . $this->limit;
		}
		if ($this->offset) {
			$limitOffset .= ' OFFSET ' . $this->offset;
		}
		return $limitOffset;
	}


	private function getDistinctString() {
		if (empty($this->distinct)) return '';
		return ' DISTINCT ON (' . implode(', ', $this->distinct) . ')';
	}


	private function prepareSql() {
		$this->queryRewritten = 'SELECT * FROM (' . $this->query . ') AS foo ';
		$this->queryFilter = $this->queryRewritten . ($this->filterExpression ? 'WHERE ' . $this->filterExpression : '');
		if ($this->distinct) {
			$this->queryFilter = 'SELECT * FROM (SELECT ' . $this->getDistinctString() . ' * FROM (' . $this->queryFilter . ') AS bar ) AS foobar';
		}
	}

	private function prepareStatement() {
		$this->prepareSql();
		$this->queryFilterOrderLimitOffset = $this->queryFilter . $this->getOrderByString()  . $this->getLimitOffsetString();

		$this->statement = $this->pdo->prepare($this->queryFilterOrderLimitOffset);
		return $this->statement;
	}


	public function getDistinctValues($column) {
		$this->prepareSql();
		$query = 'select distinct ' . $column . ' from (' . $this->queryFilter . ') as distinct_values order by 1 asc';
		return $this->pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);
	}
	
	private function executeStatement() {
		$s = $this->prepareStatement();
		$s->execute();
		return $s;
	}

	function expressionAndFactory($a, $b, $params = null) {
		return new ExpressionSqlAnd($a, $b, $params);
	}
	function expressionOrFactory($a, $b, $params = null) {
		return new ExpressionSqlOr($a, $b, $params);
	}
	function expressionNotFactory($a, $params = null) {
		return new ExpressionSqlNot($a, $params);
	}
	function expressionEqualToFactory($a, $b, $params = null) {
		return new ExpressionSqlEqualTo($a, $b, $params);
	}
	function expressionNotEqualToFactory($a, $b, $params = null) {
		return new ExpressionSqlNotEqualTo($a, $b, $params);
	}
	function expressionSimilarToFactory($a, $b, $params = null) {
		return new ExpressionSqlSimilarTo($a, $b, $params);
	}
	function expressionGreaterThanFactory($a, $b, $params = null) {
		return new ExpressionSqlGreaterThan($a, $b, $params);
	}
	function expressionLessThanFactory($a, $b, $params = null) {
		return new ExpressionSqlLessThan($a, $b, $params);
	}
	function expressionInFactory($a, $b, $params = null) {
		return new ExpressionSqlIn($a, $b, $params);
	}
	function expressionIsNullFactory($a, $params = null) {
		return new ExpressionSqlIsNull($a, $params);
	}
	function expressionNotNullFactory($a, $params = null) {
		return new ExpressionSqlNotNull($a, $params);
	}

	public function valueTextFactory($value) {
		return new SqlValueText($value);
	}
	public function valueDateFactory($value) {
		return new SqlValueDate($value);
	}
	public function valueNumericFactory($value) {
		return new SqlValueNumeric($value);
	}
	public function valueBooleanFactory($value) {
		return new SqlValueBoolean($value);
	}
	public function valueArrayFactory($value) {
		return new SqlValueArray($value);
	}
}

class ExpressionSqlAnd extends ExpressionAnd {
	public function __toString() {
		return '(' . $this->left->__toString() . ' AND ' . $this->right->__toString() . ')';
	}
}

class ExpressionSqlOr extends ExpressionOr {
	public function __toString() {
		return '(' . $this->left->__toString() . ' OR ' . $this->right->__toString() . ')';
	}
}

class ExpressionSqlNot extends ExpressionNot {
	public function __toString() {
		return '( NOT ' . $this->operand->__toString() . ')';
	}
}

class ExpressionSqlGreaterThan extends ExpressionGreaterThan {
	public function __toString() {
		return '(' . $this->left . ' > ' . $this->right . ')';
	}
}

class ExpressionSqlLessThan extends ExpressionLessThan {
	public function __toString() {
		return '(' . $this->left . ' < ' . $this->right . ')';
	}
}

class ExpressionSqlEqualTo extends ExpressionEqualTo {
	public function __toString() {
		return '(' . $this->left . ' = ' . $this->right . ')';
	}
}

class ExpressionSqlNotEqualTo extends ExpressionNotEqualTo {
	public function __toString() {
		return '(' . $this->left . ' != ' . $this->right . ')';
	}
}

class ExpressionSqlIsNull extends ExpressionIsNull {
	public function __toString() {
		return '(' . $this->operand . ' IS NULL)';
	}
}

class ExpressionSqlNotNull extends ExpressionNotNull {
	public function __toString() {
		return '(' . $this->operand . ' IS NOT NULL)';
	}
}

class ExpressionSqlSimilarTo extends ExpressionSimilarTo {
	/* So we use standard SQL LIKE + upper(), and escape a much smaller range of special characters (just "%" and "_"). */
	public function __toString() {
		// escape wildcards
		$right = strtr($this->right, array(
			'%' => "\\%",
			'_' => "\\_",
		));

		if ($this->wildcard) {
			$right = 'REPLACE(' . $right . ', \'' . $this->wildcard . '\', \'%\')';
		}

		if ($this->right instanceof Value) {
			switch (Config::get('DB_DRIVER')) {
				case 'mysql':
					$right = ' CONCAT(' .
						($this->autoPrependWildcard ? '\'%\', ' : '') .
						$right .
						($this->autoAppendWildcard ? ', \'%\'' : '') .
					') ';
					return '(UPPER(' . $this->left . ') LIKE UPPER(' . $right . '))';
				case 'pgsql':
					$right = ' (' .
						($this->autoPrependWildcard ? '\'%\' || ' : '') .
						$right .
						($this->autoAppendWildcard ? ' || \'%\'' : '') .
					') ';
					return '(UPPER(' . $this->left . '::text) LIKE UPPER(' . $right . '))';
				default: throw new Exception('Invalid DB driver');
			}
		}
	}

}

class ExpressionSqlIn extends ExpressionIn {
	public function __toString() {
		return '(' . $this->left . ' IN ' . $this->right . ')';
	}
}


class SqlValueText extends ValueText {
	public function __toString() {
		return "'" . addslashes($this->value) . "'";
	}
}

class SqlValueBoolean extends ValueBoolean {
	public function __toString() {
		switch (Config::get('DB_DRIVER')) {
			case 'mysql':
				return ($this->value === true) ? '1' : '0';
			case 'pgsql':
				return ($this->value === true) ? 'true' : 'false';
		}
	}
}

class SqlValueDate extends ValueDate {
	public function __toString() {
		return "'" . addslashes($this->value) . "'";
	}
}

class SqlValueNumeric extends ValueNumeric {
	public function __toString() {
		return (string) $this->value;
	}
}

class SqlValueArray extends ValueArray {
	private $_quoted = false;

	public function __toString() {
		if (!$this->_quoted) {
			for ($i = 0; $i < count($this->value); $i++) {
				$this->value[$i] = "'" . addslashes($this->value[$i]) . "'";
			}
			$this->_quoted = true;
		}
		return "(" . implode(', ', $this->value) . ")";
	}
}
