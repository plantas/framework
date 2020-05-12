<?php

abstract class UserChangePasswordSnippet extends Snippet {
	
	// get params
	const REQ_USER_ID = 'uid';
	const REQ_HASH = 'hash';

	const REQ_PASSWORD1 = 'password1';
	const REQ_PASSWORD2 = 'password2';
	const REQ_SEND = 'send';

	const NAMESP = 'reset';

	// config params
	const USER_MODEL = 'usermodel';
	const PASSWORD_STRENGTH = 'passwdstrength';

	protected $req;

	protected $form;
	protected $labels = array();
	protected $errors = array();

	protected $user;
	protected $passwordStrength = 50;

	abstract protected function saveUserPassword(IUserManagable $user, $password);
	abstract protected function getUserById($id);

	public function __construct(Section $section, $params = array()) {
		parent::__construct($section, $params);

		if (isset($params[self::USER_MODEL]) && $params[self::USER_MODEL] instanceof IUserManagable) {
			$this->user = $params[self::USER_MODEL];
		} 
		if (isset($params[self::PASSWORD_STRENGTH]) && is_numeric($params[self::PASSWORD_STRENGTH])) {
			$this->passwordStrength = $params[self::PASSWORD_STRENGTH];
		}
	}


	public function run() {
		Lang::addDictionary('login.php');

		$ret = '';
		
		$this->req = $this->getRequest();

		if ($this->user->getId()) {
			// logged in
			$ret .= $this->form();
		} else {
			// check url params
			if (isset($this->req[self::REQ_USER_ID]) && isset($this->req[self::REQ_HASH])) {
				$this->user = $this->getUserById($this->req[self::REQ_USER_ID]);

				if ($this->user) {
					if (UserForgotPasswordSnippet::getHash($this->user) == $this->req[self::REQ_HASH]) {
						$ret .= $this->form();
					} else {
						// invalid hash
						$ret .= Util::validationError(array(Lang::get('Invalid link')));
					}
				} else {
					// invalid user
					$ret .= Util::validationError(array(Lang::get('Invalid link')));
				}
			} else {
				// params missing
				Http::redirect($this->getUrl()->getBase(true));
			}
		}
		return $ret;
	}

	protected function validate() {
		$ret = array();
		$p1 = $this->req[self::REQ_PASSWORD1];
		$p2 = $this->req[self::REQ_PASSWORD2];
		if (empty($p1)) {
			$ret[self::REQ_PASSWORD1][] = Lang::get('Enter new password');
		}
		if (empty($p2)) {
			$ret[self::REQ_PASSWORD2][] = Lang::get('Confirm new password');
		}

		if ($p1 && $p2) {
			if ($p1 == $p2) {
				// check password complexity
				if (Util::checkPasswordStrength($p1) < $this->passwordStrength) {
					$ret[self::REQ_PASSWORD2][] = Lang::get('Password too weak');
				}
			} else {
				// passwd missmatch
				$ret[self::REQ_PASSWORD2][] = Lang::get('Passwords do not match');
			}
		}
		return $ret;
	}

	protected function form() {
		if (isset($this->req[self::REQ_SEND]) && isset($this->req[Form::REQ_FORM_NAME]) && $this->req[Form::REQ_FORM_NAME] == self::NAMESP) {
			$this->errors = $this->validate();
			if (empty($this->errors)) {

				$this->saveUserPassword($this->user, $this->req[self::REQ_PASSWORD1]);

				MessageSnippet::setMessage(Lang::get('Password successfully changed'));
				Http::redirect($this->getApplication()->getUrl()->getBase());
			}
		}
		return $this->renderForm();
	}

	protected function renderForm() {
		$this->generateForm();
		$e = $this->form->getElements();
		$l = $this->labels;
		$r = $this->errors;

		return '<div id="change-password-form">' . $this->form->getBegin() . '
			<div>' . $l[self::REQ_PASSWORD1] . '</div>
			<div>' . $e[self::REQ_PASSWORD1] . Util::validationError($r[self::REQ_PASSWORD1]) . '</div>
			<div>' . $l[self::REQ_PASSWORD2] . '</div>
			<div>' . $e[self::REQ_PASSWORD2] . Util::validationError($r[self::REQ_PASSWORD2]) . '</div>
			<div id="send-button">' . $e[self::REQ_SEND] . '</div>
			' . $this->form->getEnd() . '</div>';
	}

	protected function generateForm() {
		$this->form = new Form(array(
			Form::NAME => self::NAMESP
		));

		$e = new Hidden(array(
			FormElement::NAME => self::REQ_USER_ID,
			FormElement::VALUE => $this->req[self::REQ_USER_ID],
		));
		$this->form->addElement($e);

		$e = new Hidden(array(
			FormElement::NAME => self::REQ_HASH,
			FormElement::VALUE => $this->req[self::REQ_HASH],
		));
		$this->form->addElement($e);

		$e = new Password(array(
			FormElement::NAME => self::REQ_PASSWORD1,
			FormElement::VALUE => null,
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_PASSWORD1] = new Label(array(
			Label::TEXT => Lang::get('Password'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new Password(array(
			FormElement::NAME => self::REQ_PASSWORD2,
			FormElement::VALUE => null,
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_PASSWORD2] = new Label(array(
			Label::TEXT => Lang::get('Confirm password'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new Submit(array(
			FormElement::NAME => self::REQ_SEND, 
			FormElement::VALUE => Lang::get('Send')
		));
		$this->form->addElement($e);
	}


}
