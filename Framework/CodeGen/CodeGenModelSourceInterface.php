<?php

interface CodeGenModelSourceInterface {
	
	const COL_NAME = 'colName';
	const COL_TYPE = 'colType';
	const COL_IS_NULLABLE = 'colIsNullable';

	// col types (could match grid types)
	const TYPE_BOOLEAN = 'boolean';
	const TYPE_DATE = 'date';
	const TYPE_NUMERIC = 'numeric';
	const TYPE_TEXT = 'text';


	/*
	 * return array structure:
	 * 
	 * array(
	 * 		array(
	 * 			self::COL_NAME => 'id', // column name
	 * 			self::COL_TYPE => CodeGenModel::TYPE_TEXT,
	 * 			self::COL_IS_NULLABLE => false 
	 * 		),
	 * 		...
	 * )
	 */
	public function getColumnsMeta();	
	
	public function getTableName();	// also used as class name
}
