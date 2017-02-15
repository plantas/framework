<?php

class ImagePicker extends FilePicker {

	public function __construct($params) {
		parent::__construct($params);
	}
/*
	protected static function getFileLabelHtml($id, $name, $baseUrl) {
		return '<img src="' . $baseUrl .'/'. $id . '" />';
	}*/
}
