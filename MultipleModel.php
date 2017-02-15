<?php 

abstract class MultipleModel implements ICollection {

	/**
	 * @var Collection
	 */
	private $collection;
	
	abstract public function factory($params = null);

	public function __construct() {
		$this->setCollection(new Collection());
	}
	
	public function setCollection(ICollection $collection) {
		$this->collection = $collection;
	}
	
	/**
	 * @return ICollection
	 */
	public function getCollection() {
		return $this->collection;
	}
	
	public function addElement($obj) { return $this->getCollection()->addElement($obj); }
	public function removeElement($obj) { return $this->getCollection()->removeElement($obj); }
	public function clear() { return $this->getCollection()->clear(); }
	public function contains($obj) { return $this->getCollection()->contains($obj); }
	public function count() { return $this->getCollection()->count(); }
	public function union(ICollection $collection) { $clone=clone $this; $clone->setCollection($this->getCollection()->union($collection)); return $clone; }
	public function except(ICollection $except) { $clone=clone $this; $clone->setCollection($this->getCollection()->except($except)); return $clone; }
	public function distinct() { $clone=clone $this; $clone->setCollection($this->getCollection()->distinct()); return $clone; }
	public function getIterator() { return $this->getCollection()->getIterator(); }
}
