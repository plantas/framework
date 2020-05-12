<?php

class Tabs extends Element {

	/*
		simple tab functionality

		array('id1' => 'content1', ...);
	*/
	const TABS = 'tabs';
	const ACTIVE_TAB_ID = 'activeTabId';

	const TAB_ID = 'id';
	const TAB_TITLE = 'title';
	const TAB_CONTENT = 'content'; //for static content
	const TAB_URL = 'url'; //for ajax

	protected $tabs = array();
	protected $activeTabId;

	public function __construct($params = array()) {
		parent::__construct($params);
		
		if (empty($this->id)) throw new Exception('ID must be set');

		if (isset($params[self::TABS])) {
			$this->setTabs($params[self::TABS]);
		}

		if (isset($params[self::ACTIVE_TAB_ID])) {
			$this->setActiveTabId($params[self::ACTIVE_TAB_ID]);
		}
	}

	public function setTabs(Array $tabs) {
		$this->tabs = $tabs;
	}

	public function getTabs() {
		return $this->tabs;
	}

	public function setActiveTabId($id) {
		$this->activeTabId = $id;
	}

	private function getActiveTabIndex($activeTabId) {
		if (!$activeTabId) return 0;
		foreach ($this->tabs as $k => $t) {
			if ($t[self::TAB_ID] == $activeTabId) return $k;
		}
		return 0;
	}

	public function getHtml() {
		if (!is_array($this->tabs) || empty($this->tabs)) return '';

		$id = $this->getId();

		File::includeJs('jquery.js', File::LIB_DIR);
		File::includeJs('jquery-ui.js', File::LIB_DIR);
		File::includeCss('jquery-ui/css/smoothness/jquery-ui.custom.css?v=1', File::LIB_DIR);
			   
		HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
	$(document).ready(function() {
		$("#'.$id.'-tabs").tabs();
		'.($this->activeTabId ? '$("#'.$id.'-tabs").tabs("option", "active", '.$this->getActiveTabIndex($this->activeTabId).');' : '').'
	});
</script>
');

		$ret = '
		<div id="'.$id.'-tabs">
			<ul>';
		$i = 1;
		foreach ($this->tabs as $t) {
			if (empty($t[self::TAB_URL])) {
				$src = '#'.$id.'-tab-'.(!empty($t[self::TAB_ID]) ? $t[self::TAB_ID] : $i++);
			} else {
				$src = $t[self::TAB_URL];
			}

			$ret .= '
				<li><a href="'.$src.'">'.Util::escape($t[self::TAB_TITLE]).'</a></li>';
		}
		$ret .= '
			</ul>';

		$i = 1;
		foreach ($this->tabs as $t) {
			if (empty($t[self::TAB_URL])) {
				$ret .= '
				<div id="'.$id.'-tab-'.(!empty($t[self::TAB_ID]) ? $t[self::TAB_ID] : $i++).'">'.$t[self::TAB_CONTENT].' </div>';
			}
		}
		$ret .= '
		</div>';

		return $ret;
	}

}
