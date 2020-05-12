<?php

interface IUserManagable {

	public function getId();
	public function getEmail();
	public function getUsername();
	public function getPasswordHash();
}
