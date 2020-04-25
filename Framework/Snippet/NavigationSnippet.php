<?php

class NavigationSnippet extends Snippet {

	const HORIZONTAL = 'horizontal';
	const ITEMS = 'items';
	const TITLE = 'title';

	const ITEM_TITLE = 'itemTitle';
	const ITEM_URL = 'itemUrl';
	const ITEM_ACTIVE = 'itemActive';

	protected $horizontal = true;
	protected $items = array();
	protected $title = '';

	public function __construct(Section $section, $params = array()) {
		parent::__construct($section, $params);

		if (isset($params[self::HORIZONTAL])) {
			$this->horizontal = (boolean) $params[self::HORIZONTAL];
		}
		if (isset($params[self::ITEMS])) {
			$this->items = $params[self::ITEMS];
		}
		if (isset($params[self::TITLE])) {
			$this->title = $params[self::TITLE];
		}
	}

	public function run() {
		if (empty($this->items)) return '';

		return ($this->horizontal) ? $this->getHorizontalNavigation() : $this->getVerticalNavigation();
	}

	protected function getHorizontalNavigation() {
		$ret = '<div class="nav-horizontal">
			<ul class="inline">';
		$first = true;
		foreach ($this->items as $i) {
			$class = $first ? ' class="first"' : '';
			$ret .= '<li' . $class . '>';
			$ret .= '<a' . ($i[self::ITEM_ACTIVE] ? ' class="active"' : '') . ' href="' . $i[self::ITEM_URL] . '">' . Util::escape($i[self::ITEM_TITLE]) . '</a>';
			$ret .= '</li>';
			$first = false;
		}
		$ret .='</ul>
			</div>';

		return $ret;
	}

	protected function getVerticalNavigation() {
		$ret = '<div class="nav-vertical">';
		if (!empty($this->title)) {
			$ret .= '<div class="title">' . Util::escape($this->title) . '</div>';
		}
		$ret .= '<ul>';
		foreach ($this->items as $i) {
			$ret .= '<li>';
			$ret .= '<a' . ($i[self::ITEM_ACTIVE] ? ' class="active"' : '') . ' href="' . $i[self::ITEM_URL] . '">' . Util::escape($i[self::ITEM_TITLE]) . '</a>';
			$ret .= '</li>';
		}
		$ret .='</ul>
			</div>';

		return $ret;
	}
}
