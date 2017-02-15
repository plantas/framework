<?php

//TODO params

class Lightbox {

	public function __construct($params = array()) {
		// lightbox
		File::includeJs('jquery.js', File::LIB_DIR);
		File::includeJs('jquery-plugins/lightbox/js/jquery.lightbox-0.5.js', File::LIB_DIR);
		File::includeCss('jquery-plugins/lightbox/css/jquery.lightbox-0.5.css', File::LIB_DIR);

		$iconDir = Config::get('WWW_DIR') . 'lib/jquery-plugins/lightbox/images/';

		HtmlHeadSnippet::addHeadString("
<script type=\"text/javascript\">
	$(function() {
		$('a:has(img)').lightBox({
			overlayBgColor: '#ddd',
			overlayOpacity: 0.6,
			imageLoading: '" . $iconDir . "lightbox-ico-loading.gif',
			imageBtnClose: '" . $iconDir . "btnClose.png',
			imageBtnPrev: '" . $iconDir . "btnPrev.png',
			imageBtnNext: '" . $iconDir . "btnNext.png',
			containerResizeSpeed: 350,
			txtImage: 'Fotografija',
			txtOf: 'od'
		   });
	});
</script>
");

	}
}
