<?php

class GridPagerPlugin implements IGridPlugin {

	const PAGE = 'page';
	const RPP = 'rpp';

	// layout
	const PAGER_DROPDOWN = 'pagerDropDown';
	const PAGER_LINKS = 'links';

	// options
	const RPP_OPTIONS = 'rppOptions';
	const RECORDS_COUNT = 'recordsCount';

	private $grid;

	private $pagesCount = 1; // number of total pages

	private $page = 1;
	private $rpp = 0; // infinite
	private $rppOptions = array(20, 50, 100, 0); // 0 for all
	private $recordsCount = true; // show?

	public function __construct(Grid $grid, Array $options = array()) {
		$this->grid = $grid;

		if (isset($options[self::RPP_OPTIONS]) && is_array($options[self::RPP_OPTIONS])) {
			$this->rppOptions = $options[self::RPP_OPTIONS];
		}
		if (isset($options[self::RECORDS_COUNT])) {
			$this->recordsCount = (bool) $options[self::RECORDS_COUNT];
		}
	}

	public function preLoadExecute() {
		$c = $this->grid->getContext();
		$req = $this->grid->getRequest();

		if (isset($req[self::PAGE]) && is_numeric($req[self::PAGE])) {
			$this->page = (int) $req[self::PAGE];
		} else {
			$this->page = $c->get(self::PAGE);
		}
		if (!$this->page) {
			$this->page = 1;
		}

		if (isset($req[self::RPP]) && in_array($req[self::RPP], $this->rppOptions)) {
			$this->rpp = (int) $req[self::RPP];
		} else {
			$this->rpp = $c->get(self::RPP);
		}
		if (is_null($this->rpp) || !in_array($this->rpp, $this->rppOptions)) {
			$this->rpp = $this->rppOptions[0] ?? 0;
		}
		// set in grid context
		$c->set(self::PAGE, $this->page);
		$c->set(self::RPP, $this->rpp);

		$this->grid->setPage($this->page);
		$this->grid->setRecordsPerPage($this->rpp);
	}

	public function postLoadExecute() {
		if ($this->rpp) {
			$this->pagesCount = (int) ceil($this->grid->getDataRowCount() / $this->rpp);
			if ($this->page > $this->pagesCount && $this->pagesCount > 0) {
				// go to the last page
				Http::redirect($this->grid->getApplication()->getUrl()->getSelf() . $this->grid->getUrl(array(self::PAGE => $this->pagesCount, self::RPP => $this->rpp)));
			}
		}
	}

	public function render() {
		$html = '';
		$html .= $this->renderRecordsCount();
		$html .= $this->renderRppPicker();
		$html .= $this->renderPager();
		return $html;
	}

	protected function renderRecordsCount() {
		if (!$this->recordsCount) return '';

		$cnt = $this->grid->getDataRowCount();
		$dispCnt = count($this->grid->getData());
		$html = '';
		if (is_numeric($cnt)) {
			$html = '<div class="grid-pager-rowcount">' . sprintf(Lang::get('Displaying %d of %d records in total'), $dispCnt, $cnt) . '.</div>';
		}
		return $html;
	}

	protected function renderPager($layout = self::PAGER_LINKS) {
		$current = $this->page;
		$total = $this->pagesCount;

		if ($total == 1) return '';

		$html  = '<div class="grid-pager">';

		switch ($layout) {
			case self::PAGER_LINKS:
				if ($current > 1) {
					$html .= '<a href="' . $this->grid->getUrl(array(self::PAGE => 1)) . '"><span>&lt;&lt;</span></a>';
					$html .= '<a href="' . $this->grid->getUrl(array(self::PAGE => ($current - 1))) . '"><span>&lt;</span></a>';
				}
				$start = 1;
				$stop = $total;
				if ($total > 20) {
					$start = $current - 10;
					$stop = $current + 10;
					if ($start < 1) {
						$stop = $stop - $start;
						$start = 1;
					}
					if ($stop > $total) {
						$start = $start - ($stop - $total);
						$stop = $total;
						if ($start < 1) $start = 1;

					}
				}
				if ($start > 1) $html .= '<span>...</span>';
				for ($num = $start; $num <= $stop; $num++) {
					if ($num == $current) {
						$html .= '<span class="current">' . $num . '</span>';
					} else {
						$html .= '<a href="' . $this->grid->getUrl(array(self::PAGE => $num)) . '"><span>' . $num . '</span></a>';
					}
				}
				if ($stop < $total) $html .= '<span>...</span>';

				if ($current < $total) {
					$html .= '<a href="' . $this->grid->getUrl(array(self::PAGE => ($current + 1))) . '"><span>&gt;</span></a>';
					$html .= '<a href="' . $this->grid->getUrl(array(self::PAGE => $total)) . '"><span>&gt;&gt;</span></a>';
				}
				break;

			case self::PAGER_DROPDOWN:
			default:
				$html .= '<form action="" method="post">';
				$html .= $this->grid->getHiddenFields(); // to transfer const params
				$html .= '<select name="' . $this->grid->addNamespace(self::PAGE) . '" onchange="this.form.submit();">';
				for ($num = 1; $num <= $total; $num++) {
					$html .= '<option value="' . $num . '"';
					if ($num == $current) {
						$html .= ' selected';
					}
					$html .= '>' . $num . '</option>';
				}
				$html .= '</select>';
				$html .= '</form>';
		}
		$html .= '</div>';
		return $html;
	}

	protected function renderRppPicker() {
		$html = '';
		if (is_array($this->rppOptions) && !empty($this->rppOptions)) {
			$html .= '
			<div class="grid-pager-records-per-page">
				<form action="' . $this->grid->getUrl(array(self::PAGE => 1)) . '" method="get">';
			$html .= Lang::get('Show') . ' ';
			$html .= $this->grid->getHiddenFields(); // to transfer const params
			$html .= '<select name="' . $this->grid->addNamespace(self::RPP) . '" onchange="this.form.submit();">';
			foreach ($this->rppOptions as $opt) {
				$html .= '<option value="' . $opt . '"';
				if ($this->rpp == $opt) {
					$html .= ' selected';
				}
				$html .= '>' . ($opt == 0 ? Lang::get('all') : $opt) . '</option>';
			}
			$html .= '</select>';
			$html .= ' ' . Lang::get('records per page') . '.';
			$html .= '
				</form>
			</div>';
		}
		return $html;
	}
}
