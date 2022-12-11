<?php

/*
	JS datetime picker documentation
	http://code.google.com/p/dyndatetime/wiki/Home
*/
class DatePicker extends TextLine {

	const CONFIG = 'config';
	const FORMAT = 'format'; //php style

	protected $config = array();
	protected $format = 'd.m.Y.';

	public function __construct($params = array()) {
		parent::__construct($params);
		if (isset($params[self::FORMAT])) {
			$this->format = $params[self::FORMAT];
		}
		$this->autocomplete = false;

		// config defaults
		//$this->config['electric'] = 'true';

		if (is_array($params[self::CONFIG] ?? null)) {
			$this->config = array_merge($this->config, $params[self::CONFIG]);
		}

		// force
		$this->config['daFormat'] = '"' . $this->convertPhpFormatString($this->format) . '"';
		$this->config['ifFormat'] = '"%Y-%m-%d"'; //DBformat
		$this->config['showsTime'] = 'false';
		$this->config['displayArea'] = '".siblings(\'input#da-'.$this->getId().'\')"';
		$this->config['button'] = '".siblings(\'input#btn-'.$this->getId().'\')"';
	}

	public function getHtml() {
		$ret = '';
		$id = $this->getId();
		if (empty($id)) throw new Exception('Id must be set');

		$ro = $this->getReadOnly();
		if (!$ro) {
			File::includeJs('jquery.js', File::LIB_DIR);
			File::includeJs('jquery-plugins/dyndatetime/jquery.dynDateTime.js', File::LIB_DIR);
			File::includeJs('jquery-plugins/dyndatetime/lang/calendar-'.strtolower(Config::get('LANGUAGE', 'hr')).'.js', File::LIB_DIR);
			File::includeCss('jquery-plugins/dyndatetime/css/calendar-win2k-cold-1.css', File::LIB_DIR);

			$ret .= '
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#' . $id . '").dynDateTime(' . $this->getConfigArray() . ');
		$("a#clear-' . $id . '").click(function() {
			$("input#da-' . $id . '").val("");
			$("input#' . $id . '").val("");
			return false;
		});
	});
</script>
';
		}
		$ret .= '<div class="input-group">';
		$ret .= '<input type="text" id="da-' . $id . '" name="da-' . $this->getName() . '" value="' . Util::formatDate($this->getValue(), $this->format) . '" readonly="readonly" class="form-control '.($this instanceof DateTimePicker ? 'col-5':'col-3').'" />';
		if (!$ro) {
			// hide button if it is readonly
			$ret .= '<input type="button" id="btn-' . $id . '" value="..." class="calendar-picker" />';
			$ret .= ' <a href="#" id="clear-' . $id . '">' . Lang::get('clear') . '</a>';
		}
		// value is stored in this hidden field
		$ret .= '<input type="hidden" name="' . $this->getName() . '" value="' . $this->getValue() . '" id="' . $id . '" />';
		$ret .= '</div>';

		return $ret;
	}

	private function getConfigArray() {
		if (empty($this->config)) return '';
		$params = array();
		foreach ($this->config as $k => $v) {
			$params[] = $k . ' : ' . $v;
		}
		return '{' . implode(', ', $params) . '}';
	}

	/*
Symbol  Meaning
--------------------------
%a 	abbreviated weekday name
%A 	full weekday name
%b 	abbreviated month name
%B 	full month name
%C 	century number
%d 	the day of the month ( 00 .. 31 )
%e 	the day of the month ( 0 .. 31 )
%H 	hour ( 00 .. 23 )
%I 	hour ( 01 .. 12 )
%j 	day of the year ( 000 .. 366 )
%k 	hour ( 0 .. 23 )
%l 	hour ( 1 .. 12 )
%m 	month ( 01 .. 12 )
%M 	minute ( 00 .. 59 )
%n 	a newline character
%p 	`PM'' or `AM''
%P 	`pm'' or `am''
%S 	second ( 00 .. 59 )
%s 	number of seconds since Epoch (since Jan 01 1970 00:00:00 UTC)
%t 	a tab character
%U, %W, %V 	the week number
%u 	the day of the week ( 1 .. 7, 1 = MON )
%w 	the day of the week ( 0 .. 6, 0 = SUN )
%y 	year without the century ( 00 .. 99 )
%Y 	year including the century ( ex. 1979 )
%% 	a literal % character
*/
	private function convertPhpFormatString($phpFormat) {
		$f = preg_replace('/(\w)/', '%$0', $phpFormat);
		return str_replace(array('i'), array('M'), $f);
	}
}
