<?php

class DbAuditTrail extends Db {

	public static function setUserContext($userId) {
		$db = Db::get();
		if (!$db instanceof PDO) return;

		if (is_numeric($userId)) {
			$res = $db->query("SELECT * FROM util.temp_table_exists('_currentuser')");
			$exists = $res->fetchColumn();
			if (!$exists) {
				$db->exec('CREATE TEMP TABLE _currentuser(id integer)');
			} else {
				$db->exec('DELETE FROM _currentuser');
			}
			$db->exec('INSERT INTO _currentuser(id) VALUES (' . $userId . ')');
		}
	}

}
