<?php

class SimpleTabs extends Element {

	/*
		simple tab functionality

		array('id1' => 'content1', ...);
	*/
	const TABS = 'tabs';
	const ACTIVE_TAB_ID = 'activeTabId';

	const TAB_ID = 'id';
	const TAB_TITLE = 'title';
	const TAB_CONTENT = 'content'; //for static content

	protected static $jsLoaded = false;

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

	private function css() {
return <<<EOF
<style type="text/css">
.widget-tab { overflow: hidden; border: 1px solid #ccc; background-color: #f1f1f1; }
.widget-tab button { background-color: inherit; float: left; border: none; border-right:1px solid #ccc; outline: none; cursor: pointer; padding: 10px 15px; transition: 0.3s; }
.widget-tab button:hover { background-color: #ddd; }
.widget-tab button.active { background-color: #ccc; }
.widget-tab-content { display: none; padding: 6px 12px; border: 1px solid #ccc; border-top: none; }
</style>
EOF;
	
	}

	public function getHtml() {
		if (!is_array($this->tabs) || empty($this->tabs)) return '';

		$id = $this->getId();

		$ret .= '
		<div id="'.$id.'-tabs">
			<div class="widget-tab">';

		$i = 1;
		foreach ($this->tabs as $t) {
			$class = ($t[self::TAB_ID] == $this->activeTabId) ? ' active' : '';
			$ret .= '
				<button class="widget-tab-links'.$class.'" onclick="openTab(event, \''.$id.'-tab-'.(!empty($t[self::TAB_ID]) ? $t[self::TAB_ID] : $i++).'\');return false;">'.Util::escape($t[self::TAB_TITLE]).'</button>';
		}
		$ret .= '
			</div>';

		// tabs content
		$i = 1;
		foreach ($this->tabs as $t) {
			$display = ($t[self::TAB_ID] == $this->activeTabId) ? ' style="display:block"' : '';

			$ret .= '
			<div id="'.$id.'-tab-'.(!empty($t[self::TAB_ID]) ? $t[self::TAB_ID] : $i++).'" class="widget-tab-content"'.$display.'>'.
			$t[self::TAB_CONTENT].' </div>';
		}

		$ret .= '
		</div>';

		$ret .= $this->js();
		$ret .= $this->css();

		return $ret;
	}

	protected function js() {
		if (self::$jsLoaded) return '';

		self::$jsLoaded = true;

		return '
<script type="text/javascript">
function openTab(evt, id) {
	var i, tabcontent, tablinks;
	tabcontent = document.getElementsByClassName("widget-tab-content");
	for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}
	tablinks = document.getElementsByClassName("widget-tab-links");
	for (i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}
	document.getElementById(id).style.display = "block";
	evt.currentTarget.className += " active";
}
</script>
';

	}
}

