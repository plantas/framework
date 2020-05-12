<?php

class CodeGenModelSourcePgsql extends CodeGenModelSource {
		
	public function getColumnsMeta() {
		$db = $this->getDb();
		$schema = $this->getSchemaName() ? $this->getSchemaName() : 'public';
		$table = $this->getTableName();
		
		if (empty($table)) {
			throw new Exception('Table name must be set');
		}
		
		$source = array();
		
		$sql = "SELECT * FROM information_schema.columns WHERE table_name = '$table'";
		if ($schema) {
			$sql .= " AND table_schema = '$schema'";
		}
		$sql .= ' ORDER BY ordinal_position';

		$rs = $db->query($sql);
		while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
			$source[] = array(
				CodeGenModelSourceInterface::COL_NAME => $row['column_name'],
				CodeGenModelSourceInterface::COL_TYPE => $this->toStandardType($row['data_type']),
				CodeGenModelSourceInterface::COL_IS_NULLABLE => ($row['is_nullable'] == 'YES'),
			);
		}
		return $source;			
	}
	
	protected function toStandardType($type) {
		switch ($type) {		
			case 'date':
			case 'timestamp without time zone':
			case 'timestamp with time zone':
			case 'interval':
				return CodeGenModelSourceInterface::TYPE_DATE;	
				
			case 'boolean':
				return CodeGenModelSourceInterface::TYPE_BOOLEAN;

			case 'smallint':	
			case 'bigint':
			case 'bigserial':	
			case 'double precision':
			case 'real':				
			case 'integer':
			case 'serial':
			case 'numeric':
				return CodeGenModelSourceInterface::TYPE_NUMERIC;

			default:
				return CodeGenModelSourceInterface::TYPE_TEXT;
		}
	}
}
