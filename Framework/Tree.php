<?php

/*
$nodes = array(
	'1' => TreeNode,
	'2' => TreeNode,
	'3' => TreeNode,
	...
);
 */

class Tree {
	
	protected $nodes = array();
	
	public function getNode($id) {
		return $this->nodes[$id];
	}

	public function getNodes() {
		return $this->nodes;
	}
	
	public function insertNode(TreeNode $node) {
		$nodeId = $node->getId();

		if (empty($nodeId)) throw new Exception('Missing TreeNode id');
		if (isset($this->nodes[$nodeId])) throw new Exception('TreeNode with id = ' . $nodeId . ' exists');

		$parentId = $node->getParentId();
		if ($parentId) {
			$parent = $this->getNode($parentId);
			if (!$parent instanceof TreeNode) throw new Exception ('Missing parent TreeNode');

			$parent->addChild($nodeId);
			$node->setDepth($parent->getDepth() + 1);
		} else {
			// root node
			$node->setDepth(0);
		}
		$this->nodes[$nodeId] = $node;
	}
	
	public function removeNode($nodeId) {
		$node = $this->getNode($nodeId);
		if (!$node instanceof TreeNode) return; // does not exist

		$parent = $this->getNode($node->getParentId());
		if ($parent instanceof TreeNode) {
			// removing from parent
			$parent->removeChild($nodeId);
		}
		// remove all children
		$children = $node->getChildren();
		if (is_array($children) && !empty($children)) {
			foreach ($children as $childId) {
				$this->removeNode($childId);	
			}
		}
		unset($this->nodes[$nodeId]);
	}

	public function orderDfs() {
		$roots = $this->getFirstLevelNodes();
		if (is_array($roots)) {
			// get order array with ids
			$order = array();
			foreach ($roots as $r) {
				$order = array_merge($order, $this->getDfsOrder($r));
			}

			// update nodes
			$orderedNodes = array();
			foreach ($order as $o) {
				$orderedNodes[$o] = $this->getNode($o);
			}
			$this->nodes = $orderedNodes;
		}
	}

	public function count() {
		return count($this->nodes);
	}

	public function nodeExists($nodeId) {
		return isset($this->nodes[$nodeId]);
	}

	public function getRoot() {
		$roots = $this->getFirstLevelNodes();
		if (count($roots) == 1) return $this->nodes[$roots[0]];
		return null;
	}

	// returns part of the tree from node identified with $nodeId (including)	
	public function getSubTree($nodeId) {
		$subRoot = $this->getNode($nodeId);
		if (!$subRoot instanceof TreeNode) throw new Exception('TreeNode with id = ' . $nodeId . ' does not exist');

		// unset root's parent
		$subTreeRoot = clone $subRoot;
		$subTreeRoot->setParentId(null);
		$subTreeNodes = $this->getSubTreeNodes($subTreeRoot);
		
		$subTree = new Tree();
		foreach($subTreeNodes as $subTreeNode) {
			$subTree->insertNode(clone $subTreeNode);
		}
		return $subTree;
	}

	public function getPathToRoot($nodeId, $path = array()) {
		$node = $this->getNode($nodeId);
		if (!$node instanceof TreeNode) return $path;

		$path[] = $nodeId;

		$parentId = $node->getParentId();
		$parent = $this->getNode($parentId);
		if ($parent instanceof TreeNode) {
			return $this->getPathToRoot($parentId, $path);
		}
		return $path;
	}
	
	public function getPathFromRoot($nodeId) {
		return array_reverse($this->getPathToRoot($nodeId));
	}

	public function __toString() {
		$ret = '';
		$this->orderDfs();
		foreach ($this->getNodes() as $n) {
			$ret .= str_repeat(' * ', $n->getDepth());
			$ret .= $n->getId() . '(' . $n->getParentId() . ')';
			$ret .= ' ' . var_export($n->getData(), true);
			$ret .= '<hr />';
		}
		return $ret;
	}
	

	protected function getDfsOrder($nodeId, $order = array()) {
		$node = $this->getNode($nodeId);
		if ($node instanceof TreeNode) {
			$order = array_merge($order, array($nodeId));
			$children = $node->getChildren();
			if (is_array($children) && !empty($children)) {
				$childrenOrder = array();
				foreach ($children as $childId) {
					$childrenOrder = $this->getDfsOrder($childId, $childrenOrder);
				}
				$order = array_merge($order, $childrenOrder);
			}
		}
		return $order;
	}

	protected function getSubTreeNodes($subRoot) {
		$nodes = array($subRoot);
		$childrenIds = $subRoot->getChildren();
		if (is_array($childrenIds)) {
			foreach($childrenIds as $childId) {
				$child = $this->getNode($childId);
				$childNodes = $this->getSubTreeNodes($child);
				$nodes = array_merge_recursive($nodes, $childNodes);
			}
		}
		return $nodes;
	}

	protected function getFirstLevelNodes() {
		$root = array();
		if (is_array($this->nodes)) {
			foreach ($this->nodes as $node) {
				if ($node->getDepth() == 0) {
					$root[] = $node->getId();
				}
			}
		}
		return $root;
	}
	
}
