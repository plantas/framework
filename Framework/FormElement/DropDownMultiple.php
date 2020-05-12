<?php

//TODO predati u config sortable...
//http://code.google.com/p/jquery-asmselect/

class DropDownMultiple extends DropDown {

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->disableNullOption = true;
	}

	public function getHtml() {
		$ret = '
			<select multiple="multiple" name="'.$this->getName().'[]"';
		$id = $this->getId();
		$ret .= ' id="' . $id . '"';
		if ($this->getOnChange()) $ret .= ' onchange="'.$this->getOnChange().'"';
		if ($this->getReadOnly()) $ret .= ' disabled="disabled"';
		$title = $this->getTitle();
		if (!empty($title)) $ret .= ' title="'.Util::escape($title).'"';
		$ret .= '>';
		if (!$this->disableNullOption) {
			$ret .= '<option value="">&nbsp;</option>';
		}
		$value = $this->getValue();
		foreach ($this->options as $k => $v) {
			$ret .= '
				<option';
			$ret .=	' value="' . Util::escape($k) . '"';
			if (is_array($value) && in_array($k, $value)) $ret .= ' selected="selected"';
			$ret .= '>' . Util::escape(empty($v) ? $k : $v) . '</option>';
		}
		$ret .= '
			</select>
		';

		if (!$this->getReadOnly()) {
			File::includeJs('jquery.js', File::LIB_DIR);
			File::includeJs('jquery-ui.js', File::LIB_DIR);
			File::includeJs('jquery-plugins/asmselect/jquery.asmselect.js', File::LIB_DIR);
			File::includeCss('jquery-plugins/asmselect/jquery.asmselect.css', File::LIB_DIR);
	//		File::includeCss('jquery-plugins/asmselect/skins/default.css', File::LIB_DIR);

			HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {
	$("select[multiple]#' . $id . '").asmSelect({
		addItemTarget: "bottom",
		animate: true,
		highlight: true,
		sortable: true,
		removeLabel: "' . Lang::get('Remove') . '",
		highlightAddedLabel: "' . Lang::get('Added') . ': ",
		highlightRemovedLabel: "' . Lang::get('Removed') . ': "
	});
})
</script>');

		}

		return $ret;
	}

}
