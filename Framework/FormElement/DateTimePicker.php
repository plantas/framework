<?php

class DateTimePicker extends DatePicker {

	protected $format = 'd.m.Y. H:i';

	public function __construct($params = array()) {
		parent::__construct($params);

		// force
		$this->config['showsTime'] = 'true';
		$this->config['ifFormat'] = '"%Y-%m-%d %k:%M:00"'; //DBformat
	}


}
