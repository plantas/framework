<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

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

	public static function isActive() {
		// check if exporters are available
		if (class_exists(Spreadsheet::class)) {
			return true;
		}
		return false;
	}

	public function render() {
		return '
			<ul class="export">
				<li><a href="' . $this->grid->getApplication()->getUrl()->getSelf() . $this->grid->getUrl(array(self::XLS => 1)) . '" class="export-xsl">XLSX (Excel)</a></li>
				<li><a href="' . $this->grid->getApplication()->getUrl()->getSelf() . $this->grid->getUrl(array(self::CSV => 1)) . '" class="export-csv">CSV</a></li>
			</ul>';
	}

	private function getSpreadsheet() {
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$cols = $this->grid->getColumns();
		$data = $this->grid->getData();
		
		if (!is_array($data)) return false;

		$row = 0; // row index
		$col = 0; // column index

		//header
		foreach ($cols as $c) {
			if ($c instanceof GridColumn && $c->getVisible()) {
				$sheet->setCellValueByColumnAndRow(++$col, $row, $c->getTitle());
			}
		}

		foreach ($data as $d) {
			$col = 0; ++$row;
			foreach ($cols as $c) {
				if ($c instanceof GridColumn && $c->getVisible()) {
					$sheet->setCellValueByColumnAndRow(++$col, $row, $d[$c->getName()]);//, $this->getXlsCellDataType($c->getType()));
				}
			}
		}
		return $spreadsheet;
	}

	private function exportToXls() {
		$writer = new Xlsx($this->getSpreadsheet());
		$this->downloadXlsx($writer);
	}

	private function exportToCsv() {
		$writer = new Csv($this->getSpreadsheet());
		$writer->setDelimiter(';');
		$writer->setEnclosure('');
		$writer->setLineEnding("\r\n");
		$writer->setSheetIndex(0);
		$writer->setUseBOM(true);

		// should be configurable
		\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator(',');
		\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator('.');

		$this->downloadCsv($writer);
	}

	private function downloadXlsx($writer) {
		$ns = $this->grid->getNamespace();

		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment; filename=" . date('Y-m-d') . (empty($ns) ? '' : '-' . $ns) . "-export.xlsx");
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		ob_start();
		$writer->save('php://output');
		$ret = ob_get_clean();
		Section::output($ret);
	}

	private function downloadCsv($writer) {
		$ns = $this->grid->getNamespace();

		ob_start();
		$writer->save('php://output');
		$ret = ob_get_clean();

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($ret));
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=" . date('Y-m-d') . (empty($ns) ? '' : '-' . $ns) . "-export.csv");

		Section::output($ret);
	}

	/*
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
	}*/
	
}
