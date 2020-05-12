<?php

class TextLineAC extends TextLine {

	const SOURCE_URL = 'sourceUrl';

	protected $sourceUrl;

	public function __construct($params = array()) {
		parent::__construct($params);
		$this->autocomplete = false; // disable html autocomplete attribute

		if (isset($params[self::SOURCE_URL])) {
			$this->sourceUrl = $params[self::SOURCE_URL];
		} else {
			throw new Exception('SOURCE_URL empty');
		}
	}


	public function getHtml() {
		$id = $this->getId();
		$ro = $this->getReadOnly();
		if (!$ro) {
			File::includeJs('jquery.js', File::LIB_DIR);
			File::includeJs('jquery-ui.js', File::LIB_DIR);
			File::includeCss('jquery-ui/css/smoothness/jquery-ui.custom.css', File::LIB_DIR);

			HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {
	$("#' . $id . '").autocomplete({
		source: "' . $this->sourceUrl . '", 
		width: '.$this->size.',
		search: function() {
			$(this).css("background", "url(\'/lib/icons/throbber16x16.gif\') no-repeat right top");
	       	},
		response: function() {
			$(this).css("background", "");
		}
	});
})
</script>');
		}
		
		$ret = '';

		$ret .= parent::getHtml();
		return $ret;
	}
}
