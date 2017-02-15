<?php

class CodeGenModelSourceMysql extends CodeGenModelSource {
	
	public function getColumnsMeta() {
		$db = $this->getDb();
		$schema = $this->getSchemaName();
		$table = $this->getTableName();
		
		if (empty($table)) {
			throw new Exception('Table name must be set');
		}
		// check if table exists
		$sql = "SELECT count(*)
			FROM information_schema.tables WHERE table_name = '$table' AND table_schema = '" . Config::get('DB_NAME') . "'";
		$rs = $db->query($sql);
		if ($rs->fetchColumn() != 1) {
			throw new Exception("Table $table does not exist or not exact");
		}
		
		// fetch table cols info
		$sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = '$table' AND table_schema = '" . Config::get('DB_NAME') . "'";

		$rs = $db->query($sql);

		$source = array();
		while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
			$source[] = array(
				CodeGenModelSourceInterface::COL_NAME => $row['COLUMN_NAME'],
				CodeGenModelSourceInterface::COL_TYPE => $this->toStandardType($row['DATA_TYPE']),
				CodeGenModelSourceInterface::COL_IS_NULLABLE => ($row['IS_NULLABLE'] == 'YES'),
			);
		}
		return $source;			
	}
	
	protected function toStandardType($type) {
		switch (strtolower($type)) {		

			case 'date':
			case 'datetime':
			case 'timestamp':
			case 'time':
			case 'year':
				return CodeGenModelSourceInterface::TYPE_DATE;	
				
			case 'boolean':
			case 'bit':
			case 'tinyint':
				return CodeGenModelSourceInterface::TYPE_BOOLEAN;

			case 'int':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
			case 'float':
			case 'double':
			case 'decimal':
			case 'decimal':
				return CodeGenModelSourceInterface::TYPE_NUMERIC;

			default:
				return CodeGenModelSourceInterface::TYPE_TEXT;
		}
	}
}
