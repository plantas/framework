<?php

//DOCS http://www.uploadify.com/documentation/

//TODO const params 
class FileUploadAdvanced extends FileUpload {

	const CONFIG = 'config';

	protected $config = array(
		'uploader' => '"/www/lib/jquery-plugins/uploadify/uploadify.swf"',
		'script' => '"/upload/foo.php"',
		'cancelImg' => '"/www/lib/jquery-plugins/uploadify/cancel.png"',
		'folder' => '"/upload/file"',
		'auto' => 'true',
		'multi' => 'true'
	);

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::CONFIG]) && is_array($params[self::CONFIG])) {
			$this->config = array_merge($this->config, $params[self::CONFIG]);
		}
	}

	public function getHtml() {
		$ro = $this->getReadOnly();
		if (!$ro) {
			File::includeJs('jquery.js', File::LIB_DIR);
			File::includeJs('jquery-plugins/uploadify/swfobject.js', File::LIB_DIR);
			File::includeJs('jquery-plugins/uploadify/jquery.uploadify.v2.1.0.min.js', File::LIB_DIR);
			File::includeCss('jquery-plugins/uploadify/uploadify.css', File::LIB_DIR);
		
			HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {

	$("input#' . $this->getId() . '").uploadify({' .
	       $this->getConfig() . '
	});

})
</script>');
		}

		return parent::getHtml();
	}

	private function getConfig() {
		$params = array();
		if (is_array($this->config)) {
			foreach ($this->config as $k => $v) {
				$params[] = "'$k' : $v"; 
			}
		}
		return implode(",\n", $params);
	}

}
