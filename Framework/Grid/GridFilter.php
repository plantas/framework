<?php

abstract class GridFilter implements IGridPlugin {

	protected $grid;

	protected $options = array();

	protected $filter; // query

	const FILTER = 'f'; // request var

	const FILTER_QUERY = 'filterQuery'; // option to set query from app

	public function __construct(Grid $grid, Array $options = array()) {
		$this->grid = $grid;
		$this->options = $options;
	}

	public function getGrid() {
		return $this->grid;
	}

	public function getOption($var, $default = null) {
		return isset($this->options[$var]) ? $this->options[$var] : $default;
	}

	protected function getSearchableColumns() {
		$searchable = array();
		foreach ($this->getGrid()->getColumns() as $col) {
			if ($col->getSearchable()) { // && $col->getVisible()
				$searchable[] = $col;
			}
		}
		return $searchable;
	}

}
