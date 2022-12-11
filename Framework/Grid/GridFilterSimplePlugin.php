<?php

class GridFilterSimplePlugin extends GridFilter {

	const BUTTON = 'simpleFilter';
	const RESET = 'sfReset';

	public function preLoadExecute() {
		$c = $this->grid->getContext();
		$req = $this->grid->getRequest();

		$this->filter = $c->get(self::FILTER);
		if (isset($req[self::FILTER])) {
			$this->filter = $req[self::FILTER];

			// set in grid context
			$c->set(self::FILTER, $this->filter);
		}
		// we can set filter from app by setting FILTER_QUERY option
		if ($query = $this->getOption(self::FILTER_QUERY)) {
			$this->filter = $query;
			$c->set(self::FILTER, $this->filter);
		}
		if (isset($req[self::RESET])) {
			$this->filter = null;
			$c->set(self::FILTER, $this->filter);
		}

		// advanced filter will handle array 
		if (is_array($this->filter)) $this->filter = null;

		if ($this->grid->debug) echo 'Simple filter query = ' . Util::escape($this->filter) . '<br />';

		if ($this->filter) {
			$ds = $this->grid->getDataSource();

			foreach ($this->getSearchableColumns() as $column) {
				$e = $ds->expressionSimilarToFactory($column->getName(), $ds->valueTextFactory($this->filter), array('wildcard' => '*', 'autoPrependWildcard' => true, 'autoAppendWildcard' => true));
				$expr = $expr ? $ds->expressionOrFactory($expr, $e) : $e;
			}

			if ($expr instanceof Expression) {
				if ($f = $ds->getFilter()) {
					$expr = $ds->expressionAndFactory($expr, $f);
				}
				$ds->setFilter($expr);
			}
		}
	}

	public function postLoadExecute() {

	}

	public function render() {
		$searchableCols = $this->getSearchableColumns();

		if (empty($searchableCols)) return '';

		$filterName = $this->grid->addNamespace(self::FILTER);

		$html  = '<div class="grid-filter">';
		$html .= '<form action="" method="get">';
		$html .= $this->grid->getHiddenFields(array(GridPagerPlugin::PAGE => 1));
		$html .= /*Lang::get('Filter by') . ':'*/  '<input type="text" class="grid-searchbox" value="' . Util::escape($this->filter) . '" name="' . $filterName . '" id="' . $filterName . '" /> ';
		$html .= '<input type="submit" class="grid-button" name="' . $this->grid->addNamespace(self::BUTTON) . '" value="' . Lang::get('Filter') . '" /> ';
		$html .= '<input type="submit" class="grid-button" name="' . $this->grid->addNamespace(self::RESET) . '" value="' . Lang::get('Reset') . '" />';
		$html .= '</form>';
		$html .= '</div>';

		return $html;
	}

}
