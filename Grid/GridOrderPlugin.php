<?php

class GridOrderPlugin implements IGridPlugin {

	const ORDER = 'ord';

	private $grid;

	private $order = array();

	public function __construct(Grid $grid) {
		$this->grid = $grid;
	}

	public function preLoadExecute() {
		$req = $this->grid->getRequest();
		$c = $this->grid->getContext();
		$contextOrder = $c->get(self::ORDER);
		$userOrder = $this->grid->getOrderBy();

		// default order
		$this->order = $this->grid->getDefaultOrderBy(); // default order

		if (is_array($userOrder) && !empty($userOrder)) {
			// user order, set with grid->setOrderBy
			$this->order = $userOrder;
		} else if (isset($req[self::ORDER]) && is_array($req[self::ORDER])) {
			// from request
			$this->order = $req[self::ORDER];
		} else if (is_array($contextOrder) && !empty($contextOrder)) {
			// from context (session)
			$this->order = $contextOrder;
		} 

		// cleans bad order params in request
		$this->clean();

		if (is_array($this->order) && !empty($this->order)) {
			// set in grid context
			$c->set(self::ORDER, $this->order);

			$this->grid->setOrderBy($this->order);
		}
	}

	public function postLoadExecute() {
	}

	public function render() {
		$cols = $this->grid->getColumns();
		$html = '
			<tr>';
		foreach($cols as $col) {
			if ($col->getVisible() == false) continue;
			$html .= '
				<th';
			if (isset($this->order[$col->getName()])) {
				$html .= ' class="grid-order-' . ($this->order[$col->getName()] == Grid::ORDER_DESC ? Grid::ORDER_DESC : Grid::ORDER_ASC) . '"';
			}
			$html .= '>';
			if ($col->getSortable()) {
				$html .= '<a href="' . $this->getOrderUrl($col)  . '" title="' . $col->getTitle() . '">' . $col->getTitle() . '</a>';
				if (isset($this->order[$col->getName()])) {
					$html .= '<span class="grid-direction"></span>';
				}
			} else {
				$html .= $col->getTitle();
			}
			$html .= '</th>';
		}
		$html .= '
			</tr>';
		return $html;
	}

	private function clean() {
		if (empty($this->order)) return;
		
		$valid = $this->getSortableColumns();
			
		foreach ($this->order as $col => $ord) {
			if (is_numeric($col)) {
				unset($this->order[$col]); // convert 0 => 'foo' to 'foo' => 'asc'
				if (in_array($ord, $valid)) {
					$this->order[$ord] = Grid::ORDER_ASC;
				}
			} else {
				if (!in_array($col, $valid) || ($ord != Grid::ORDER_ASC && $ord != Grid::ORDER_DESC)) {
					unset($this->order[$col]);
				}
			}
		}
		if (empty($this->order)) $this->order = array();
	}

	private function getOrderUrl(GridColumn $col) {
		if (isset($this->order[$col->getName()]) && $this->order[$col->getName()] == Grid::ORDER_ASC) {
			$ord = Grid::ORDER_DESC;
		} else {
			$ord = Grid::ORDER_ASC;
		}

		return $this->grid->getUrl(array(
			self::ORDER => array($col->getName() => $ord),
			GridPagerPlugin::PAGE => 1	
		));
	}
	
	private function getSortableColumns() {
		$sortableCols = array();
		foreach ($this->grid->getColumns() as $col) {
			if ($col->getSortable()) {
				$sortableCols[] = $col->getName();
			}
		}
		return $sortableCols;
	}
}
