<?php

/*
 * TODO parametri za max sirinu i visinu slike
 * */

class TextLineImg extends TextLine {

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
		$("#' . $id . '-img").attr("src", url);
		$("#' . $id . '-link").attr("href", url);
	});
	$("#' . $id . '").trigger("change");
})
</script>');
		}
		
		new Lightbox();

		$ret = '';
		$ret .= parent::getHtml();
		$ret .= '
			<div class="textline-img">
				<a href="#" id="' . $id . '-link"><img id="' . $id . '-img" src="" style="max-width:100px;max-height:100px" border="0" alt="" /></a>
			</div>';

		return $ret;
	}
}
