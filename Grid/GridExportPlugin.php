<?php

class GridExportPlugin implements IGridPlugin {

	const CSV = 'csv';
	const XLS = 'xls';

	private $grid;

	public function __construct(Grid $grid, Array $options = array()) {
		$this->grid = $grid;
	}

	public function preLoadExecute() {
	}

	public function postLoadExecute() {
		$req = $this->grid->getRequest();

		if (isset($req[self::CSV])) {
			return $this->exportToCsv();
		} else if (isset($req[self::XLS])) {
			return $this->exportToXls();
		}
	}

	public function render() {
		return '
			<ul class="export">
				<li><a href="' . $this->grid->getApplication()->getUrl()->getSelf() . $this->grid->getUrl(array(self::XLS => 1)) . '" class="export-csv">XLS (Excel)</a></li>
			</ul>';
	}

	private function exportToXls() {
		require_once dirname(__FILE__) . '/../external/PHPExcel/PHPExcel.php';

		$xls = new PHPExcel();
		$this->loadDataToXls($xls);

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$xls->setActiveSheetIndex(0);

		$ns = $this->grid->getNamespace();

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		//header('Content-Disposition: attachment;filename="01simple.xls"');
		header("Content-Disposition: attachment; filename=" . date('Y-m-d') . (empty($ns) ? '' : '-' . $ns) . "-export.xls");
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');

		ob_start();
		$objWriter->save('php://output');
		$ret = ob_get_clean();
		Section::output($ret);
	}

	private function loadDataToXls($xls) {
		$cols = $this->grid->getColumns();
		$data = $this->grid->getData();
		
		if (!is_array($data)) return false;

		$i = 1; // row index
		$j = 0; // column index

		//header
		foreach ($cols as $col) {
			if ($col instanceof GridColumn && $col->getVisible()) {
				$xls->setActiveSheetIndex(0)->setCellValueByColumnAndRow($j++, $i, $col->getTitle());
			}
		}

		foreach ($data as $row) {
			$j = 0; $i++;
			foreach ($cols as $col) {
				if ($col instanceof GridColumn && $col->getVisible()) {
					$xls->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow($j++, $i, $row[$col->getName()], $this->getXlsCellDataType($col->getType()));
				}
			}
		}
	}

	private function getXlsCellDataType($type) {
		switch ($type) {
			case Value::TYPE_NUMERIC:
				return PHPExcel_Cell_DataType::TYPE_NUMERIC;
			case Value::TYPE_BOOLEAN:
				return PHPExcel_Cell_DataType::TYPE_BOOL;
			case Value::TYPE_TEXT:
			case Value::TYPE_DATE:
			case Value::TYPE_ARRAY:
			default:
				return PHPExcel_Cell_DataType::TYPE_STRING;
		}
	}
	


/********** CSV *************/
	private function exportToCsv() {
		$cols = $this->grid->getColumns();
		$data = $this->grid->getData();

		$ret = '';
		if (is_array($data)) {
			//header
			$rowData = array();
			foreach ($cols as $col) {
				if ($col instanceof GridColumn && $col->getVisible()) {
					$rowData[] = $col->getTitle();
				}
			}
			$ret .= $this->arrayToCsvRow($rowData);

			foreach ($data as $row) {
				$rowData = array();
				foreach ($cols as $col) {
					if ($col instanceof GridColumn && $col->getVisible()) {
						$rowData[] = $row[$col->getName()];
					}
				}
				$ret .= $this->arrayToCsvRow($rowData);
			}

			$ret = iconv('UTF-8', 'windows-1250', $ret); 
			$ns = $this->grid->getNamespace();
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Length: " . strlen($ret));
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=" . date('Y-m-d') . (empty($ns) ? '' : '-' . $ns) . "-export.csv");
			Section::output($ret);
		}
	}

	private function arrayToCsvRow(array $data) {
		$cells = array();
		foreach ($data as $d) {
			$d = str_replace('"', '""', $d);
			$d = str_replace(array("\r", "\n", "\r\n", "\n\r"), "", $d);
			//$d = (is_numeric($d) ? '=' : '') . '"' . $d . '"'; // 7.5.2014. - oznaka puta 01.02.03 se pretvaral u datum
			$d = '="' . $d . '"';

/*
			$quoted = false;
			if (strpos($d, "\r") !== false || strpos($d, "\n") !== false) {
				$d = str_replace(array("\r", "\n", "\r\n"), "", $d);
				$d = '"' . $d . '"';
				$quoted = true;
			}
			// force ="" for numeric data that will be converted to dates (stupid excel!)
//			if (preg_match('/\d\.\d/', $d)) {
//				$d = $qouted ? '=' . $d : '="' . $d . '"';
//			}
*/
			$cells[] = $d;
		}
		return implode(';', $cells) . "\n";
	}

}
