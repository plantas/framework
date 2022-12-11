<?php

class Collection implements ICollection {

	protected $elements = array();

	function getIterator(): Traversable {
		return new ArrayIterator($this->elements);
	}

	function __construct() {
		$args = func_get_args();
		if (!empty($args)) {
			foreach ($args as $arg) {
				$this->addElement($arg);
			}
		}
	}

	protected function setElements(array $elements) {
		$this->elements = $elements;
	}

	/**
	 * @return array
	 */
	public function getArray() {
		return $this->elements;
	}

	public function clear() {
		$this->setElements(array());
	}

	/**
	 * adds an element $el to the collection
	 */
	public function addElement($obj) {
		$idx = count($this->elements);
		$this->elements[$idx] = $obj;
		return $idx;
	}

	/**
	 * removes the first found element
	 * uses binary object comparison if $this does not implement IIdentifiable
	 *
	 * @param $element
	 * @return boolean - if an element has been removed
	 */
	public function removeElement($element) {
		$elements = $this->getArray();
		$i = 0;
		$count=count($elements);
		for ($i=0; $i<$count; $i++) {
			$tmp = array_slice($elements, $i, 1);
			$el = $tmp[0];
			if ($el instanceof IIdentifiable) {
				$remove = $el->identify($element);
			} else {
				$remove = ($el === $element);
			}
			if ($remove) {
				array_splice($elements, $i, 1);
				$this->setElements($elements);
				return true;
			}
		}
		return false;
	}

	public function contains($element) {
		$elements = $this->getArray();
		foreach ($elements as $el) {
			if ($el instanceof IIdentifiable) {
				$equal = $el->identify($element);
			} else {
				$equal = $el === $element;
			}
			if ($equal) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param ICollection $col2
	 * @return ICollection
	 */
	public function union(ICollection $union) {
		$ret = clone $this;
		foreach ($union as $oneElement) {
			$ret->addElement($oneElement);
		}
		return $ret;
	}

	/**
	 *
	 * @return ICollection
	 */
	public function except(ICollection $except) {
		$ret = clone $this;
		$ret->clear();
		foreach ($this as $el) {
			if ($except->contains($el)) {
				continue;
			} else {
				$ret->addElement($el);
			}
		}
		return $ret;
	}

	public function distinct() {
		$ret = clone $this;
		$ret->clear();
		foreach ($this as $element) {
			if ($ret->contains($element)) {
				continue;
			}
			$ret->addElement($element);
		}
		return $ret;
	}

	/* Iterator and Countable implementation */
//	private $idx=0;
//	public function rewind() { $this->idx = 0; }
//	public function current() { return $this->elements[$this->idx]; }
//	public function key() {	return $this->idx; }
//	public function next() { $this->idx++; }
//	public function valid() { return $this->idx < $this->count(); }

	public function count(): int {
		return count($this->elements);
	}

	function __toString() {
		$array = $this->getArray();
		return implode(', ', $array);
	}

	public static function factoryFromArray(array $arr=array()) {
		$c = new Collection();
		foreach ($arr as $a) {
			$c->addElement($a);
		}
		return $c;
	}

}
