<?php

abstract class Expression {

	protected $params;

	function toArrayBinary() {
		$ret = array();
		if ($this->left instanceof Expression) {
			$ret['left']['value'] = $this->left->toArray();
		} else if ($this->left instanceof Value) {
			$ret['left']['value'] = $this->left->getValue();
			$ret['left']['type'] = get_class($this->left);
		} else {
			$ret['left']['value'] = $this->left;
		}
		if ($this->right instanceof Expression) {
			$ret['right']['value'] = $this->right->toArray();
		} else if ($this->right instanceof Value) {
			$ret['right']['value'] = $this->right->getValue();
			$ret['right']['type'] = get_class($this->right);
		} else {
			$ret['right']['value'] = $this->right;
		}
		$ret['operator'] = get_class($this);
		if ($this->params) {
			$ret['params'] = $this->params;
		}
		return $ret;
	}

	function toArrayUnary() {
		$ret = array();
		if ($this->operand instanceof Expression) {
			$ret['operand']['value'] = $this->operand->toArray();
		} else if ($this->operand instanceof Value) {
			$ret['operand']['value'] = $this->operand->getValue();
			$ret['operand']['type'] = get_class($this->operand);
		} else {
			$ret['operand']['value'] = $this->operand;
		}
		$ret['operator'] = get_class($this);
		if ($this->params) {
			$ret['params'] = $this->params;
		}
		return $ret;
	}

	function toArray() {
		if (isset($this->operand)) {
			return $this->toArrayUnary();
		} else if (isset($this->left)) {
			return $this->toArrayBinary();
		} else {
			throw new Exception('Cannot transform expression of type ' . get_class($this) . ' toArray()');
		}
	}
	
	static function factoryFromArray($arr) {
		if (!is_array($arr)) {
			return;
		}
		if (!$arr['operator']) {
			throw new Exception('Bad array - no operator');
		}
		if ($arr['left']) { //binary
			if (!is_array($arr['left']['value'])) {
				if ($arr['left']['type']) {
					$left = new $arr['left']['type']($arr['left']['value']);
				} else {
					$left = $arr['left']['value'];
				}
			} else {
				$left = self::factoryFromArray($arr['left']['value']);
			}
			if (!is_array($arr['right']['value'])) {
				if ($arr['right']['type']) {
					$right = new $arr['right']['type']($arr['right']['value']);
				} else {
					$right = $arr['right']['value'];
				}
			} else {
				$right = self::factoryFromArray($arr['right']['value']);
			}
			return new $arr['operator']($left, $right, $arr['params']);

		} else if ($arr['operand']) { //unary
			if (!is_array($arr['operand']['value'])) {
				if ($arr['operand']['type']) {
					$operand = new $arr['operand']['type']($arr['operand']['value']);
				} else {
					$operand = $arr['operand']['value'];
				}
			} else {
				$operand = self::factoryFromArray($arr['operand']['value']);
			}
			return new $arr['operator']($operand, $arr['params']);
		} else {
			throw new Exception('Bad array');
		}
	}

	public function getLeft() {
		if ($this->left) {
			return $this->left;
		}
	}
	
	public function getRight() {
		if ($this->right) {
			return $this->right;
		}
	}
	
	public function getOperand() {
		if ($this->operand) {
			return $this->operand;
		}
	}

}

class ExpressionEqualTo extends Expression {
	protected $left;
	protected $right;
	protected $params;

	function __construct($left, $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
	}
}

class ExpressionNotEqualTo extends Expression {
	protected $left;
	protected $right;
	protected $params;

	function __construct($left, $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
	}
}

class ExpressionSimilarTo extends Expression {
	const WILDCARD = 'wildcard';
	const AUTO_PREPEND_WILDCARD = 'autoPrependWildcard';
	const AUTO_APPEND_WILDCARD = 'autoAppendWildcard';

	protected $left;
	protected $right;
	protected $wildcard;
	protected $autoPrependWildcard = false;
	protected $autoAppendWildcard = true;


	function __construct($left, $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
		$this->wildcard = $params[self::WILDCARD] ? $params[self::WILDCARD] : null;
		if (isset($params[self::AUTO_PREPEND_WILDCARD])) {
			$this->autoPrependWildcard = $params[self::AUTO_PREPEND_WILDCARD] ? true : false;
		}
		if (isset($params[self::AUTO_APPEND_WILDCARD])) {
			$this->autoAppendWildcard = $params[self::AUTO_APPEND_WILDCARD] ? true : false;
		}
	}
}

class ExpressionGreaterThan extends Expression {
	protected $left;
	protected $right;
	protected $params;

	function __construct($left, $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
	}
}

class ExpressionLessThan extends Expression {
	protected $left;
	protected $right;
	protected $params;

	function __construct($left, $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
	}
	
}

class ExpressionIn extends Expression {
	protected $left;
	protected $right;
	protected $params;

	function __construct($left, $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
	}
}

class ExpressionBoolean extends Expression {
}

abstract class ExpressionBooleanUnary extends ExpressionBoolean {
	protected $operand;

	function __construct(Expression $operand, $params = null) {
		$this->operand = $operand;
		$this->params = $params;
	}
}

abstract class ExpressionBooleanBinary extends ExpressionBoolean {
	protected $left;
	protected $right;

	function __construct(Expression $left, Expression $right, $params = null) {
		$this->left = $left;
		$this->right = $right;
		$this->params = $params;
	}
}

class ExpressionAnd extends ExpressionBooleanBinary {
}

class ExpressionOr extends ExpressionBooleanBinary {
}

class ExpressionNot extends ExpressionBooleanUnary {
}

class ExpressionIsNull extends Expression {
	protected $operand;
	
	function __construct($operand, $params = null) {
		$this->operand = $operand;
		$this->params = $params;
	}
}

class ExpressionNotNull extends Expression {
	protected $operand;
	
	function __construct($operand, $params = null) {
		$this->operand = $operand;
		$this->params = $params;
	}
}

class ExpressionReader {
	protected $expression;
	protected $translateLeftArray;
		
	public function readHumanly(Expression $expr) {
		if ($expr instanceof ExpressionSimilarTo) {
			$left = $expr->getLeft();
			$right = $expr->getRight();
			if ($right instanceof Expression) {
				$right = $this->readHumanly($right);
			} 
			$left = $this->translateLeft($left);
			if ($right instanceof Value) {
				$right = $right->getValue();
				$right = $this->emphasizeLiteral($right);
			}
			return  'the ' . $left . ' matches ' . $right; 
		} else if ($expr instanceof ExpressionEqualTo) {
			$left = $expr->getLeft();
			$right = $expr->getRight();
			if ($right instanceof Expression) {
				$right = $this->readHumanly($right);
			} 
			$left = $this->translateLeft($left);
			if ($right instanceof ValueBoolean) {
				$right = $right->getValue() ? 'true' : 'false';
				return 'the ' . $left . ' is ' . $right;
			} else if ($right instanceof Value) {
				$right = $right->getValue();
				$right = $this->emphasizeLiteral($right);
			}
			return  'the ' . $left . ' is exactly ' . $right; 
		} else if ($expr instanceof ExpressionGreaterThan) {
			$left = $expr->getLeft();
			$right = $expr->getRight();
			if ($right instanceof Expression) {
				$right = $this->readHumanly($right);
			} 
			$left = $this->translateLeft($left);
			if ($right instanceof Value) {
				$right = $right->getValue();
				$right = $this->emphasizeLiteral($right);
			}
			return  'the ' . $left . ' is greater than ' . $right; 
		} else if ($expr instanceof ExpressionLessThan ) {
			$left = $expr->getLeft();
			$right = $expr->getRight();
			if ($right instanceof Expression) {
				$right = $this->readHumanly($right);
			} 
			$left = $this->translateLeft($left);
			if ($right instanceof Value) {
				$right = $right->getValue();
				$right = $this->emphasizeLiteral($right);
			}
			return  'the ' . $left . ' is less than ' . $right; 
		} else if ($expr instanceof ExpressionAnd) {
			$left = $expr->getLeft();
			if ($left instanceof Expression) {
				$left = $this->readHumanly($left);
			} 
			$right = $expr->getRight();
			if ($right instanceof Expression) {
				$right = $this->readHumanly($right);
			} 
			$left = $this->translateLeft($left);
			if ($right instanceof Value) {
				$right = $right->getValue();
				$right = $this->emphasizeLiteral($right);
			}
			return  $left . ' and ' . $right; 
		} else if ($expr instanceof ExpressionOr) {
			$left = $expr->getLeft();
			if ($left instanceof Expression) {
				$left = $this->readHumanly($left);
			} 
			$right = $expr->getRight();
			if ($right instanceof Expression) {
				$right = $this->readHumanly($right);
			} 
			$left = $this->translateLeft($left);
			if ($right instanceof Value) {
				$right = $right->getValue();
				$right = $this->emphasizeLiteral($right);
			}
			return  $left . ' or ' . $right; 
		} else if ($expr instanceof ExpressionNot) {
			$op = $expr->getOperand();
			if ($op instanceof Expression) {
				$op = $this->readHumanly($op);
			} 
			return  'statement (' . $op . ') is not true'; 
		} else if ($expr instanceof ExpressionIsNull) {
			$op = $expr->getOperand();
			$op = $this->translateLeft($op);
			return 'the ' . $op . ' is blank';
		} else if ($expr instanceof ExpressionNotNull) {
			$op = $expr->getOperand();
			$op = $this->translateLeft($op);
			return 'the ' . $op . ' is not blank';
		} else {
			return $expr->__toString();
		}
	}
	

	public function setTranslateLeftArray(Array $tl) {
		$this->translateLeftArray = $tl;
	}
	
	protected function translateLeft($value) {
		if ($this->translateLeftArray[$value]) {
			return $this->translateLeftArray[$value];	
		} else {
			return $value;
		}
	}
	
	protected function emphasizeLiteral($value) {
		return $value;		
	}
}
