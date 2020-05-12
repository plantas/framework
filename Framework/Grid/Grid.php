<?php
/*
$options = array(...):
$grid = new Grid($options);
$renderer = new GridRendererTable($grid);
$html = $renderer->getHtml();
 */

class Grid {

	// Grid params
	const APPLICATION = 'application';
	const NAMESP = 'namespace';
	const DATA_SOURCE = 'dataSource';
	const COLUMNS = 'columns';
	const DEFAULT_ORDER_BY = 'defaultOrderBy';
	const CONST_PARAMS = 'constParams';

	// backwards compatibility - disabled
	const RPP_OPTIONS = 'rppOptions';

	// format function args
	const FF_VALUE = 'value';
	const FF_ROW = 'row';
	const FF_GRID = 'grid';
	// summary func args
	const SF_VALUES = 'values';
	const SF_GRID = 'grid';

	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	protected $namespace;

	/**
	 * Application
	 * @var Application
	 */
	protected $application;

	/**
	 * Grid data source
	 * @var DataSource
	 */
	protected $dataSource;

	/**
	 * array of GridColumns
	 * @var Array
	 */
	protected $columns;

	/**
	 * Grid Context object
	 * @var GridContext
	 */
	protected $context;

	/**
	 * Array of constant params
	 * @var Array
	 */
	protected $constParams = array();


	/**
	 * Array of Grid plugins, must implement IGridPlugin interface
	 * @var Array
	 */
	protected $plugins = array();

	private $data = array();
	private $summary = array();
	private $dataRowCount = 0;

	private $page = 1; //current page
	private $recordsPerPage;
	private $orderBy;
	private $defaultOrderBy;

	public $debug = false;

	function __construct($params) {
		if (isset($params[self::NAMESP])) {
			$this->setNamespace($params[self::NAMESP]);
		}
		if (isset($params[self::APPLICATION])) {
			$this->setApplication($params[self::APPLICATION]);
		}
		if (isset($params[self::DATA_SOURCE])) {
			$this->setDataSource($params[self::DATA_SOURCE]);
		}
		if (isset($params[self::COLUMNS])) {
			$this->setColumns($params[self::COLUMNS]);
		}
		if (isset($params[self::DEFAULT_ORDER_BY])) {
			$this->setDefaultOrderBy($params[self::DEFAULT_ORDER_BY]);
		}
		if (isset($params[self::CONST_PARAMS])) {
			$this->setConstParams($params[self::CONST_PARAMS]);
		}
		$this->context = new GridContext($this);
	}

	private function validate() {
		if (empty($this->namespace)) throw new Exception('You must provide Namespace');
		if (!$this->application instanceof Application) throw new Exception('Invalid Application');
		if (!$this->dataSource instanceof DataSource) throw new Exception('Invalid DataSource');
		if (empty($this->columns)) throw new Exception('Empty columns');
	}

	// called in GridRenderer
	public function build() {
		$this->validate(); // validate grid params
		foreach ($this->plugins as $p) {
			$p->preLoadExecute();
		}

		$this->loadDataFromDataSource(); // loads data from DS

		foreach ($this->plugins as $p) {
			$p->postLoadExecute();
		}
		$this->processData(); // applies format and summary functions, ...
		
		$this->context->save();
	}


	// DATA SOURCE FUNCTIONS
	
	private function loadDataFromDataSource() {
		$ds = $this->getDataSource();

		if (is_array($this->orderBy) && !empty($this->orderBy)) {
			$ds->orderBy($this->orderBy);
		}
		$this->dataRowCount = (int) $ds->getRowCount();

		$rpp = $this->getRecordsPerPage();
		$page = $this->getPage();
	
		$this->data = $ds->getData($rpp, ($rpp * ($page - 1)));
	}

	// applies format functions
	private function processData() {
		if (!is_array($this->data) || empty($this->data)) return;

		$columns = $this->getColumns();
		foreach ($columns as $colName => $column) {
			if ($column->getVisible() == false) continue;

			$formatFunction = is_callable($column->getFormatFunction()) ? $column->getFormatFunction() : false;

			$sf = $column->getSummaryFunction();
			$summaryFunction = is_callable($sf) ? $sf : null;
			$values = array();

			foreach ($this->data as $key => $row) {
				// collect all column values for summary
				if ($summaryFunction) {
					$values[] = $row[$colName];
				}

				if ($formatFunction) {
					$args = array(
						self::FF_VALUE => $row[$colName],
						self::FF_ROW => $row,
						self::FF_GRID => $this
					);
					$this->data[$key][$colName] = call_user_func($formatFunction, $args);
				} else { 
					$this->data[$key][$colName] = nl2br(Util::escape($row[$colName]));
				}
			}

			if ($summaryFunction) {
				$args = array(
					self::SF_VALUES => $values,
					self::SF_GRID => $this
				);
				$this->summary[$colName] = call_user_func($summaryFunction, $args);
			}
		}
	}

	public function addNamespace($var) {
		return $this->getNamespace() . '[' . $var . ']';
	}

	private function getHidden($name, $value) {
		$e = new Hidden(array(
			FormElement::NAME => $name,
			FormElement::VALUE => $value,
		)); 
		return $e->getHtml();
	}

	public function getHiddenFields(array $vars = array()) {
		$html = '';
		if (is_array($vars)) {
			$params = Util::arrayToQueryParams($vars, $this->getNamespace());
			foreach ($params as $k => $v) {
				if ($v) {
					$html .= $this->getHidden($k, $v);
				}
			}
		}
		foreach ($this->getConstParams() as $k => $v) {
			$html .= $this->getHidden($k, $v);
		}
		return $html;
	}

	public function getUrl(array $vars = array()) {			
		$reqVars = array();
		if (is_array($vars)) {
			$params = Util::arrayToQueryParams($vars, $this->getNamespace());
			foreach ($params as $k => $v) {
				$reqVars[] = $k . '=' . $v;
			}
		}
		foreach ($this->getConstParams() as $k => $v) {
			$reqVars[] = $k . '=' . $v;
		}
		if (!empty($reqVars)) {
			return '?' . implode('&', $reqVars);
		}
		return '';
	}
	
	// GETTERS AND SETTERS

	public function getData() {
		return $this->data;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function getDataRowCount() {
		return $this->dataRowCount;
	}

	// plugins will use this to get namespaced request
	public function getRequest() {
		return $this->application->getRequest()->get($this->getNamespace());
	}


	public function setApplication(Application $app) {
		$this->application = $app;
	}

	public function getApplication() {
		return $this->application;
	}

	public function setNamespace($ns) {
		$this->namespace = $ns;
	}

	public function getNamespace() {
		return $this->namespace . 'grid';
	}

	public function setDataSource(DataSource $ds) {
		$this->dataSource = $ds;
	}

	public function getDataSource() {
		return $this->dataSource;
	}

	public function setColumns(Array $dc) {
		$this->columns = array();
		foreach ($dc as $c) {
			if ($c instanceof GridColumn) {
				$this->addColumn($c);
			}
		}
	}

	public function addColumn(GridColumn $c) {
		$this->columns[$c->getName()] = $c;
	}

	public function getColumns() {
		return $this->columns;
	}

	public function getContext() {
		return $this->context;
	}

	public function setConstParams(array $params = array()) {
		$this->constParams = $params;
	}

	public function getConstParams() {
		return $this->constParams;
	}

	public function registerPlugin(IGridPlugin $plugin) {
		$this->plugins[] = $plugin;
	}

	public function setPage($page) {
		$this->page = $page;
	}

	public function getPage() {
		return $this->page;
	}

	public function setRecordsPerPage($rpp) {
		$this->recordsPerPage = $rpp;
	}

	public function getRecordsPerPage() {
		return $this->recordsPerPage;
	}

	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
	}

	public function getOrderBy() {
		return $this->orderBy;
	}

	public function setDefaultOrderBy($orderBy) {
		$this->defaultOrderBy = $orderBy;
	}

	public function getDefaultOrderBy() {
		return $this->defaultOrderBy;
	}



}
