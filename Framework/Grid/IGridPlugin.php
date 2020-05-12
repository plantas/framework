<?php

interface IGridPlugin {

	public function preLoadExecute();
	public function postLoadExecute();
	public function render();
}
