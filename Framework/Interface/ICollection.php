<?php 

interface ICollection extends IteratorAggregate, Countable {
	public function addElement($obj);
	public function removeElement($obj);
	public function clear();
	public function contains($obj);
	
	public function union(ICollection $union);
	public function except(ICollection $except);
	public function distinct();
}
