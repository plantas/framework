<?php

class DropDownAC extends DropDown {

	const SOURCE_URL = 'sourceUrl';
	const SIZE = 'size';

	protected $sourceUrl;
	protected $size = 50;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::SOURCE_URL])) {
			$this->sourceUrl = $params[self::SOURCE_URL];
		} else {
			throw new Exception('SOURCE_URL empty');
		}
		if (isset($params[self::SIZE])) {
			$this->size = $params[self::SIZE];
		}
	}

	public function getHtml() {
		$id = $this->getId();
		File::includeJs('jquery.js', File::LIB_DIR);
		File::includeJs('jquery-ui.js', File::LIB_DIR);
		File::includeCss('jquery-ui/css/smoothness/jquery-ui.custom.css', File::LIB_DIR);

		$bgCss = "url('".Config::get('WWW_DIR')."lib/icons/throbber16x16.gif') no-repeat right top";

		HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {
	var da = $("input#' . $id . '-da");
	var id = $("input#' . $id . '");

	da.autocomplete({ 
		source: "' . $this->sourceUrl . '",
		mustMatch: true,
		minChars: 2,
		width: ' . $this->size . ',
		select: function( event, ui ) { 
			if (ui.item.id)	id.val(ui.item.id); 
		},
		change: function( event, ui ) { 
			if (!da.val()) id.val(""); 
		},'.(!$this->getReadOnly() ? '
		search: function() {
			$(this).css("background", "'.$bgCss.'");
		},' : '').'
		open: function() {
			$(this).css("background", "");
		}
	})'.($this->getReadOnly() ? '.keypress(function(e) { e.preventDefault(); })' : '').';
	if (id.val()) {
		$.post("' . $this->sourceUrl . '", {id: id.val()}, function(data) {
			if(data[0]) {
				da.val(data[0].value);
				da.css("background", "");
			}	
		}, "json");
	}
});
</script>');
		
		$ret = '<input type="hidden" value="' . Util::escape($this->getValue()) . '" name="' . $this->getName() . '" id="' . $id . '" />';
		$ret .= '<input type="text" id="' . $id . '-da" value=""';
		if ($this->value) {
			$ret .= ' style="background:'.$bgCss.'"';
		}

		$cssClass = $this->getCssClass();
		$cssClass = ($cssClass) ? $cssClass . ' line' : 'line';
		$ret .= ' class="' . $cssClass . '"';
		if ($this->getReadOnly()) {
			$ret .= ' readonly="readonly"';
		}
		if ($this->maxlength) {
			$ret .= ' maxlength="' . $this->maxlength . '"';
		}
		if ($this->size) {
			$ret .= ' size="' . $this->size . '"';
		}
		$ret .= ' autocomplete="off" />';

		return $ret;
	}
}
