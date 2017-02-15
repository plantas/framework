<?php

class TreeNode {

	const ID = 'id';
	const PARENT_ID = 'parentId';
	const DATA = 'data';
	
	protected $id;
	protected $parentId = null;
	protected $children = array();
	protected $depth = 0;
	protected $data = array();
	
	public function __construct($params = null) {
		if (isset($params[self::ID])) {
			$this->id = $params[self::ID];
		} else {
			throw new Exception('ID must be set');
		}
		if (isset($params[self::PARENT_ID])) {
			$this->setParentId($params[self::PARENT_ID]);
		}
		if (isset($params[self::DATA]) && is_array($params[self::DATA])) {
			$this->setData($params[self::DATA]);
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setParentId($parentId) {
		$this->parentId = $parentId;
	}
	
	public function getParentId() {
		return $this->parentId;
	}
	
	public function setData(array $data) {
		$this->data = $data;
	}
	
	public function getData($key = null) {
		return $key ? $this->data[$key] : $this->data;
	}
	


	// for Tree
	public function getDepth() {
		return $this->depth;
	}
	
	public function setDepth($depth) {
		$this->depth = $depth;
	}
	
	public function addChild($nodeId) {
		if (!in_array($nodeId, $this->children)) {
			$this->children[] = $nodeId;
		}
	}
	
	public function removeChild($nodeId) {
		$pos = array_search($nodeId, $this->children);
		if ($pos !== false) {
			unset($this->children[$pos]);
		}
	}

	public function getChildren() {
		return array_values($this->children);
	}
	
}
