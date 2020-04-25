<?php

abstract class CodeGenModelSource implements CodeGenModelSourceInterface {
	
	const DB = 'db';
	const SCHEMA = 'schema';
	const TABLE = 'table';
	
	protected $db;
	protected $schema;
	protected $table;

	public static function factory($driver) {
		switch ($driver) {
			case 'pgsql': return new CodeGenModelSourcePgsql();
			case 'mysql': return new CodeGenModelSourceMysql();
			default: throw new Exception('Invalid DB driver');
		}
	}
	
	public function setDb(PDO $db) {
		$this->db = $db;
	}
	
	public function getDb() {
		return $this->db;
	}
	
	public function setSchemaName($schema) {
		$this->schema = $schema;
	}
	
	public function getSchemaName() {
		return $this->schema;
	}
	
	public function setTableName($table) {
		$this->table = $table;
	}
	
	public function getTableName() {
		return $this->table;
	}

}
