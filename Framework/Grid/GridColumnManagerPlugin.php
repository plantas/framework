<?php

class GridColumnManagerPlugin implements IGridPlugin {

	const COLUMNS_TO_HIDE = 'hidden';
	const HIDE_BTN = 'hideCols';
	const SHOW_ALL_BTN = 'showAllCols';

	private $grid;

	private $columnsToHide = array();
	private $visibleColumns = array(); // all cols that are set as visible in a grid setup (including cols that are hidden by column manager)

	public function __construct(Grid $grid) {
		$this->grid = $grid;
	}

	public function preLoadExecute() {
		$c = $this->grid->getContext();
		$req = $this->grid->getRequest();

		if (isset($req[self::SHOW_ALL_BTN])) {
			$this->columnsToHide = array();
			$c->set(self::COLUMNS_TO_HIDE, $this->columnsToHide);
		} else if (is_array($req[self::COLUMNS_TO_HIDE] ?? null)) {
			$toHide = array_keys($req[self::COLUMNS_TO_HIDE]);
			foreach ($this->grid->getColumns() as $k => $col) {
				if (in_array($k, $toHide)) {
					$this->columnsToHide[] = $k;
				}
			}
			$c->set(self::COLUMNS_TO_HIDE, $this->columnsToHide);
		}
		$this->columnsToHide = $c->get(self::COLUMNS_TO_HIDE);

		// update visibilty of columns in grid
		$this->visibleColumns = $this->getVisibleColumns();
		foreach ($this->visibleColumns as $k => $c) {
			if (in_array($k, (array) $this->columnsToHide)) {
				$c->setVisible(false);
			}
		}

	}

	public function postLoadExecute() {
	}

	public function render() {
		$f = new Form();

		foreach ($this->visibleColumns as $k => $c) {
			$e = new Checkbox(array(
				Checkbox::NAME => $this->grid->addNamespace(self::COLUMNS_TO_HIDE) . '[' . $k . ']',
				Checkbox::VALUE => in_array($k, (array) $this->columnsToHide),
			));
			$f->addElement($e);
		}
		$e = new Submit(array(
			Submit::NAME => $this->grid->addNamespace(self::HIDE_BTN),
			Submit::VALUE => Lang::get('Hide selected'),
		));
		$f->addElement($e);
		$e = new Submit(array(
			Submit::NAME => $this->grid->addNamespace(self::SHOW_ALL_BTN),
			Submit::VALUE => Lang::get('Show all'),
		));
		$f->addElement($e);

		$els = $f->getElements();

		$ret = $f->getBegin();
		
		$i = 0;
		$ret .= '<div class="grid-cm-columns">';
		foreach ($this->visibleColumns as $k => $c) {
			$ret .= '<label class="grid-cm-label">';
			$ret .= $els[$this->grid->addNamespace(self::COLUMNS_TO_HIDE) . '[' . $k . ']'];
			$ret .= $c->getTitle();
			$ret .= '</label>';

			if (++$i%15 == 0) $ret .= '<br />';
		}
		$ret .= '</div>';

		$ret .= '<div class="grid-cm-btns">'; 
		$ret .= $els[$this->grid->addNamespace(self::HIDE_BTN)] . '&nbsp;';
		$ret .= $els[$this->grid->addNamespace(self::SHOW_ALL_BTN)];
		$ret .= '</div>';
		$ret .= $f->getEnd();
		
		return $ret;
	}

	// this is used to skip permanently hidden cols
	private function getVisibleColumns() {
		$cols = array();
		foreach ($this->grid->getColumns() as $k => $c) {
			if ($c->getVisible()) {
				$cols[$k] = $c;
			}
		}
		return $cols;
	}
}
