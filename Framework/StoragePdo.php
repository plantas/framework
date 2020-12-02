<?php

abstract class StoragePdo implements IStorable {

        protected $statements = array();

        protected function getStatement($stmt) {
                if (!isset($this->statements[$stmt])) {
                        $this->statements[$stmt] = Db::get()->prepare($stmt);
                }
                return $this->statements[$stmt];
        }

	protected function getPdoParams($params = array()) {
		if (is_array($params)) {
			$ret = array();
			foreach ($params as $k => $v) {
				$ret[':' . $k] = $v;
			}
			return $ret;
		}
		return $params;
	}

	protected function getLastInsertId($field = null, $table = null) {
		$driver = Config::get('DB_DRIVER', 'mysql');
		if ($driver == 'pgsql') {
			return Db::get()->lastInsertId($table . '_' . $field . '_seq');
		}
		
		return Db::get()->lastInsertId($field);
	}
}

