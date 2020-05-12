<?php 

interface IMultipleElements {

	public static function populateModelFromRequest($model, $req);
	
	public function setValidationErrors(array $err);

}
