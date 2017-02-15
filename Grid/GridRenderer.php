<?php

abstract class GridRenderer {
	
	/**
	 * @var Grid
	 */
	protected $grid;

	protected $options = array();

	protected $pagerPlugin;
	protected $orderPlugin;

	protected $filterSimplePlugin;
	protected $filterAdvancedPlugin;
	protected $columnManagerPlugin;
	protected $exportPlugin;

	// system plugin options
	const PAGER_RPP_OPTIONS = 'pagerRppOptions';
	const RECORDS_COUNT = 'recordsCount';

	const FILTER_SIMPLE = 'showFilterSimple';
	const FILTER_SIMPLE_PARAMS = 'filterSimpleParams';
	const FILTER_ADVANCED = 'showFilterAdvanced';
	const FILTER_ADVANCED_PARAMS = 'filterAdvancedParams';
	const COLUMN_MANAGER = 'showColumnManager';
	const EXPORT = 'export';

	const THEME = 'theme';

	public function __construct(Grid $grid, Array $options = array()) {
		$this->setGrid($grid);
		$this->options = array_merge($this->options, $options);

		// system plugins
		$this->pagerPlugin = new GridPagerPlugin($grid, array(GridPagerPlugin::RPP_OPTIONS => $this->getOption(self::PAGER_RPP_OPTIONS), GridPagerPlugin::RECORDS_COUNT => $this->getOption(self::RECORDS_COUNT)));
		$this->grid->registerPlugin($this->pagerPlugin);
		$this->orderPlugin = new GridOrderPlugin($grid);
		$this->grid->registerPlugin($this->orderPlugin);

		if ($this->getOption(self::FILTER_SIMPLE, true)) {
			$this->filterSimplePlugin = new GridFilterSimplePlugin($grid, $this->getOption(self::FILTER_SIMPLE_PARAMS, array()));
			$this->grid->registerPlugin($this->filterSimplePlugin);
		}
		if ($this->getOption(self::FILTER_ADVANCED, true)) {
			$this->filterAdvancedPlugin = new GridFilterAdvancedPlugin($grid, $this->getOption(self::FILTER_ADVANCED_PARAMS, array()));
			$this->grid->registerPlugin($this->filterAdvancedPlugin);
		}
		if ($this->getOption(self::COLUMN_MANAGER, true)) {
			$this->columnManagerPlugin = new GridColumnManagerPlugin($grid);
			$this->grid->registerPlugin($this->columnManagerPlugin);
		}
		if ($this->getOption(self::EXPORT, true)) {
			$this->exportPlugin = new GridExportPlugin($grid);
			$this->grid->registerPlugin($this->exportPlugin);
		}
		
		$this->grid->build();
	}

	abstract public function getHtml();

	public function setGrid(Grid $grid) {
		$this->grid = $grid;
	}

	public function getGrid() {
		return $this->grid;
	}

	public function getOption($var, $default = null) {
		return isset($this->options[$var]) ? $this->options[$var] : $default;
	}

}
