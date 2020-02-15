<?php

class GridRendererDataTableDOM extends GridRendererTable {

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

		$html = '
		<div class="grid-container">
			<div class="grid-toolbar">
			' . $this->getToolbar() . '
			</div>
			<div class="grid-data">
				<table border="1" class="grid">
				<thead>
				' . $this->getTableHeader() . '
				</thead>
				<tbody>
				' . 
				$this->getTableBody() . 
				$this->getTableSummary() . '
				</tbody>
				</table>
			</div>
		</div>';
		
		return $html;
	}

	protected function tabsFactory(array $params) {
		return new SimpleTabs($params);
	}

}
