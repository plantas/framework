<?php

abstract class ACDataSourceSection extends Section {

	const REQ_QUERY = 'term';
	const REQ_ID = 'id';

	public function run() {
		$req = $this->getRequest();
		if (isset($req[self::REQ_QUERY])) {
			echo $this->getData($req[self::REQ_QUERY]);
		} elseif (isset($req[self::REQ_ID])) {
			echo $this->getItem($req[self::REQ_ID]);
		}
	}


	/*
	 * Returns data source filtered by "term" from request
	 */
	abstract protected function getData($term);

	/*
	 * Returns data source filtered by "term" from request
	 */
	abstract protected function getItem($id);

}
