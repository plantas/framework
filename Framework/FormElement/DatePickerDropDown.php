<?php

class DatePickerDropDown extends TextLine {

	public function __construct($params = array()) {
		parent::__construct($params);
	}

	public function getHtml() {
		$ret = '';
		$id = $this->getId();
		if (empty($id)) throw new Exception('Id must be set');
		
		$ro = $this->getReadOnly();
		if (!$ro) {
			File::includeJs('jquery.js', File::LIB_DIR);

			$ret .= '
<script type="text/javascript">
	jQuery(document).ready(function() {
		$("select[name=\'' . $this->getName() . '_d\'], select[name=\'' . $this->getName() . '_m\'], select[name=\'' . $this->getName() . '_y\']").change(
			function() {
				var value = $("select[name=\'' . $this->getName() . '_y\']").val() + "-" + $("select[name=\'' . $this->getName() . '_m\']").val() + "-" + $("select[name=\'' . $this->getName() . '_d\']").val();
				$("input#' . $this->getName() . '").val(value);
			} 
		);
	});
</script>
';		
		}
	
		list($y, $m, $d) = explode('-', $this->getValue());

		// day, month, year picker
		$day = new DropDown(array(
			DropDown::NAME => $this->getName() . '_d',
			DropDown::VALUE => $d,
			DropDown::OPTIONS => array_combine(range(1,31), range(1,31)),
			DropDown::READ_ONLY => $ro,
		));
		$month = new DropDown(array(
			DropDown::NAME => $this->getName() . '_m',
			DropDown::VALUE => $m,
			DropDown::OPTIONS => array_combine(range(1,12), range(1,12)),
			DropDown::READ_ONLY => $ro,
		));

		$year = new DropDown(array(
			DropDown::NAME => $this->getName() . '_y',
			DropDown::VALUE => $y,
			DropDown::OPTIONS => array_combine(range(date('Y'), 1900), range(date('Y'), 1900)),
			DropDown::READ_ONLY => $ro,
		));
		$ret .= $day->getHtml() . ' ' . $month->getHtml() . ' ' .  $year->getHtml();
		$ret .= '<input type="hidden" id="' . $this->getName() . '" name="' . $this->getName() . '" value="' . $this->getValue() . '" />';

		return $ret;
	}
}
