<?php

interface IStorable {
	public function load($id); //returns instance of model
	public function save(Model $model);
	public function delete(Model $model);
}
