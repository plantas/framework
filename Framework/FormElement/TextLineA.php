<?php

class TextLineA extends TextLine {

	public function getHtml() {
		$id = $this->getId();
		$ro = $this->getReadOnly();
		if (!$ro) {
			File::includeJs('jquery.js', File::LIB_DIR);

			HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {
	$("#' . $id . '").change(function() {
		var url = $("#' . $id . '").val();
		$("#' . $id . '-link a").attr("href", url);
		if (url) $("#' . $id . '-link").show();
		else $("#' . $id . '-link").hide();
	});
	$("#' . $id . '").trigger("change");
})
</script>');
		}
		
		$ret = '';
		$ret .= '
			<div class="textline-a" id="' . $id . '-link">
				<a href="#" target="_blank"></a>
			</div>';
		$ret .= parent::getHtml();

		return $ret;
	}
}
