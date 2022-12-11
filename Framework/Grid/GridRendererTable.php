<?php

class GridRendererTable extends GridRenderer {

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
			<div class="grid-pager">' . 
			$this->pagerPlugin->render() . '
			</div>
		</div>';
		
		return $html;
	}

	// HTML PARTS
	
	protected function getToolbar() {
		$tabs = array();

		if ($this->filterSimplePlugin instanceof IGridPlugin) {
			$tabs[] = array(
				Tabs::TAB_ID => 'simplefilter',
				Tabs::TAB_TITLE => Lang::get('Simple filter'),
				Tabs::TAB_CONTENT => $this->filterSimplePlugin->render()		
			);
		}
		if ($this->filterAdvancedPlugin instanceof IGridPlugin) {
			$tabs[] = array(
				Tabs::TAB_ID => 'advancedfilter',
				Tabs::TAB_TITLE => Lang::get('Advanced filter'),
				Tabs::TAB_CONTENT => $this->filterAdvancedPlugin->render()		
			);
		}
		if ($this->columnManagerPlugin instanceof IGridPlugin) {
			$tabs[] = array(
				Tabs::TAB_ID => 'colman',
				Tabs::TAB_TITLE => Lang::get('Column manager'),
				Tabs::TAB_CONTENT => $this->columnManagerPlugin->render()		
			);
		}
		if ($this->exportPlugin instanceof IGridPlugin) {
			$tabs[] = array(
				Tabs::TAB_ID => 'export',
				Tabs::TAB_TITLE => Lang::get('Data export'),
				Tabs::TAB_CONTENT => $this->exportPlugin->render()		
			);
		}

		$tabsCnt = count($tabs);
		if ($tabsCnt == 0) return '';

		if ($tabsCnt == 1) {
			// display in row
			return $tabs[0][Tabs::TAB_CONTENT];
		} else {
			// find active tab
			$req = $this->grid->getRequest();
			$c = $this->grid->getContext();
			$active = 'simplefilter';
			if (isset($req[GridFilterAdvancedPlugin::BUTTON]) || isset($req[GridFilterAdvancedPlugin::RESET]) || is_array($c->get(GridFilterAdvancedPlugin::FILTER))) $active = 'advancedfilter';
			if (isset($req[GridColumnManagerPlugin::SHOW_ALL_BTN]) || isset($req[GridColumnManagerPlugin::HIDE_BTN])) $active = 'colman';

			$t = new SimpleTabs(array(
				Element::ID => 'grid-toolbar-' . uniqid(),
				Tabs::TABS => $tabs,
				Tabs::ACTIVE_TAB_ID => $active
			));
			return $t->getHtml();
		}
	}

	protected function getTableHeader() {
		return $this->orderPlugin->render();
	}

	protected function getTableBody() {
		$data = $this->getGrid()->getData();
		$cols = $this->getGrid()->getColumns();
		$html = '';
		$cnt = 0;
		if (is_array($data)) {
			foreach ($data as $row) {
				$html  .= '
				<tr class="grid-' . ((++$cnt % 2) ? 'odd' : 'even')  . '">';
				foreach ($cols as $col) {
					if ($col instanceof GridColumn && $col->getVisible()) {
						$html .= '<td class="' . $col->getType() . ' ' . $col->getName() . '">' . $row[$col->getName()] . '</td>';
					}
				}
				$html .= '</tr>';
			}
		}
		return $html;
	}

	protected function getTableSummary() {
		$summary = $this->getGrid()->getSummary();
		$cols = $this->getGrid()->getColumns();

		$html = '';

		if (!is_array($summary) || empty($summary)) return $html;

		$html  .= '
			<tr class="grid-summary">';
		foreach ($cols as $col) {
			if ($col instanceof GridColumn && $col->getVisible()) {
				$html .= '
				<td class="' . $col->getName() . '">' . ($summary[$col->getName()] ?? '') . '</td>';
			}
		}
		$html .= '
			</tr>';
		return $html;
	}
}
