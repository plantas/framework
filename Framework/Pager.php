<?php

class Pager {
	
	// postaviti broj stavki i rpp
	const TOTAL_ITEMS = 'totalItems';
	const ITEMS_PER_PAGE = 'itemsPerPage';

	// ili ukupan broj stranica
	const TOTAL_PAGES = 'totalPages';

	const CURRENT_PAGE = 'currentPage';
	const URL_PATTERN = 'urlPattern';
	const MAX_PAGES_TO_DISPLAY = 'maxPagesToDisplay';

	protected $totalItems;
	protected $itemsPerPage = 10;
	protected $urlPattern;
	protected $currentPage = 1;
	protected $maxPagesToDisplay = 10;

	protected $totalPages = 1;

	public function __construct(Array $params) {
		if (isset($params[self::ITEMS_PER_PAGE]) && is_numeric($params[self::ITEMS_PER_PAGE]) && $params[self::ITEMS_PER_PAGE] > 0) {
			$this->itemsPerPage = $params[self::ITEMS_PER_PAGE];
		}
		if (isset($params[self::TOTAL_PAGES]) && is_numeric($params[self::TOTAL_PAGES]) && $params[self::TOTAL_PAGES] > 0) {
			$this->totalPages = (int) $params[self::TOTAL_PAGES];
		} else if (isset($params[self::TOTAL_ITEMS]) && is_numeric($params[self::TOTAL_ITEMS])) {
			$this->totalItems = $params[self::TOTAL_ITEMS];
			$this->totalPages = (int) ceil($this->totalItems / $this->itemsPerPage);
		} else {
			throw new Exception('TOTAL_ITEMS / TOTAL_PAGES param is missing');
		}
		if (isset($params[self::CURRENT_PAGE]) && is_numeric($params[self::CURRENT_PAGE]) && $params[self::CURRENT_PAGE] > 0 && $params[self::CURRENT_PAGE] <= $this->totalPages) {
			$this->currentPage = $params[self::CURRENT_PAGE];
		}
		if (isset($params[self::URL_PATTERN])) {
			$this->urlPattern = $params[self::URL_PATTERN];
		}
		if (isset($params[self::MAX_PAGES_TO_DISPLAY]) && is_numeric($params[self::MAX_PAGES_TO_DISPLAY])) {
			$this->maxPagesToDisplay = $params[self::MAX_PAGES_TO_DISPLAY];
		}
	}
	
	public function getHtml() {
		$start = 1;
		$stop = $this->totalPages;
		if ($this->totalPages > $this->maxPagesToDisplay) {
			$offset = $this->maxPagesToDisplay / 2;
			$start = $this->currentPage - $offset;
			$stop = $this->currentPage + $offset;
			if ($start < 1) {
				$stop = $stop - $start;
				$start = 1;
			}
			if ($stop > $this->totalPages) {
				$start = $start - ($stop - $this->totalPages);
				$stop = $this->totalPages;
				if ($start < 1) $start = 1;

			}
		}

		$html = '<div id="pager">';
		if ($start > 1) $html .= '<span>...</span>';
		for ($num = $start; $num <= $stop; $num++) {
			if ($num == $this->currentPage) {
				$html .= '<span class="current">' . $num . '</span>';
			} else {
				if ($this->urlPattern) $html .= '<a href="' . sprintf($this->urlPattern, $num) . '">';
				$html .= '<span>' . $num . '</span>';
				if ($this->urlPattern) $html .= '</a>';
			}
		}
		if ($stop < $this->totalPages) $html .= '<span>...</span>';
		$html .= '</div>';

		return $html;
	}
}
