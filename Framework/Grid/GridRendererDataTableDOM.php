<?php

class GridRendererDataTableDOM extends GridRendererTable {

	protected $options = [
		self::PAGER_RPP_OPTIONS => 100000,
	];

	protected $tableDomId;

	public function getHtml() {
		File::includeCss('grid/table.css', File::LIB_DIR);
		$theme = $this->getOption(self::THEME);
		if (empty($theme)) {
			$theme = Config::get('GRID_THEME');
		}
		if (!empty($theme)) {
			File::includeCss('grid/theme/' . $theme . '.css', File::LIB_DIR);
		}
		Lang::addDictionary('grid.php');

		$this->tableDomId = 'grid-table-' . uniqid();

		$html = '
		<div class="grid-container">
			<div class="grid-toolbar">
			' . $this->getToolbar() . '
			</div>
			<div class="grid-data">
				<table border="1" class="grid" id="' . $this->tableDomId . '">
				<thead>
				' . $this->getTableHeader() . '
				</thead>
				<tbody>
				' . 
				$this->getTableBody() . '
				</tbody>
				</table>
			</div>
		</div>';
		
		return $html;
	}

	protected function getTableHeader() {
		$html = '<tr>';
		$cols = $this->getGrid()->getColumns();
		foreach ($cols as $col) {
			if ($col instanceof GridColumn && $col->getVisible()) {
				$html .= '<th class="' . $col->getType() . ' ' . $col->getName() . '">' . $col->getTitle() . '</th>';
			}
		}
		$html .= '</tr>';
		return $html;
	}

	public function getTableDomId() {
		return $this->tableDomId;
	}

	protected function tabsFactory(array $params) {
		return new SimpleTabs($params);
	}

}
