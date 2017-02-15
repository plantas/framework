<?php

class FilePicker extends FormElement {

	const FILE_BROWSER_URL = 'fileBrowserUrl';
	const SORTABLE = 'sortable';

	protected $fileBrowserUrl;
	protected $sortable = true;

	public function __construct($params) {
		parent::__construct($params);

		if (isset($params[self::FILE_BROWSER_URL])) {
			$this->fileBrowserUrl = $params[self::FILE_BROWSER_URL];
		} else {
			throw new Exception('FILE_BROWSER_URL is missing');
		}
		if (isset($params[self::SORTABLE])) {
			$this->sortable = (bool) $params[self::SORTABLE];
		}
	}

	public function getHtml() {
		File::includeJs('jquery.js', File::LIB_DIR);
		File::includeJs('jquery-plugins/jqmodal/jqModal.js', File::LIB_DIR);

		$loaderJs = '';
		$value = $this->getValue();
		if (is_array($value) && !empty($value)) {
			// this will do ajax request for all items
			$loaderJs .= '
	$.ajax({
		url: "'.$this->fileBrowserUrl.'?'.FileBrowserSnippet::REQ_FILE_PICKER_NAME.'='.$this->getName().'&'.FileBrowserSnippet::REQ_FILE_ID.'[]='.implode('&'.FileBrowserSnippet::REQ_FILE_ID.'[]=', $value).'", 
		success: function(data){ $("ul#fb-file-list-'.$this->getName().'").append(data); }
	});';
		}

		$sortableJs = '';
		if ($this->sortable) {
			File::includeJs('jquery-ui.js', File::LIB_DIR);
			$sortableJs .= '
	$("ul#fb-file-list-'.$this->getName().'").sortable();';
		}

		HtmlHeadSnippet::addHeadString('
<script type="text/javascript">
$().ready(function() {
	$("div#fb-modal-'.$this->getName().'").jqm({ajax: "@href", trigger: "a#fb-modal-opener-'.$this->getName().'"});
	$("div.fb-remove").live("click", function(event) {
		$(this).parents("li.fb-file-'.$this->getName().'").remove();
	});
	$("a#fb-remove-all-'.$this->getName().'").click(function(event) {
		event.preventDefault();
		$("li.fb-file-'.$this->getName().'").remove();
	});
	'.$sortableJs . $loaderJs.'
})
</script>
');
		$ret = '';
		$ret .= '<div id="fb-add"><a href="' . $this->fileBrowserUrl . '?'.FileBrowserSnippet::REQ_FILE_PICKER_NAME.'='.$this->getName().'" id="fb-modal-opener-'.$this->getName().'">'.Lang::get('Add').'</a></div>';
		$ret .= '<div id="fb-remove-all-box"><a href="#" id="fb-remove-all-'.$this->getName().'">'.Lang::get('Remove all') . '</a></div>';
		$ret .= '<div id="fb-file-list-container">';
		$ret .= '<ul id="fb-file-list-'.$this->getName().'"></ul>';
		$ret .= '</div>';
		$ret .= '<div class="jqmWindow" id="fb-modal-'.$this->getName().'"></div>';

		return $ret;
	}
}
