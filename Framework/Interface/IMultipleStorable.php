<?php

interface IMultipleStorable extends IStorable {

	public function loadMultiple(array $fkeys); //returns MultipleModel

	public function saveMultiple(array $fkeys, MultipleModel $model);
}
