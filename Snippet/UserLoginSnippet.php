<?php

abstract class UserLoginSnippet extends Snippet {

	const REDIRECT_URL = 'redirectUrl';

	const NAMESP = 'login';

	const SESS_USER_ID = 'loggedUserId';

	const REQ_USERNAME = 'username';
	const REQ_PASSWORD = 'password';
	const REQ_LOGIN = 'login';
	const REQ_LOGOUT = 'logout';

	const ERR_GENERAL = 'errorGeneral';

	private $redirectUrl;

	protected $userId; // identifier - will be stored in session
	protected $username;
	protected $password;

	protected $req;
	protected $form;
	protected $labels = array();
	protected $errors = array();

	// should return user_id or null usually based on $this->username and $this->password 
	abstract protected function auth();

	public static function isLoggedIn() {
		return (bool) Session::get(self::SESS_USER_ID);
	}

	public static function getLoggedUserId() {
		return Session::get(self::SESS_USER_ID);
	}

	public static function setLoggedUserId($id) {
		Session::set(self::SESS_USER_ID, $id);
	}

	public function __construct(Section $section, $params = array()) {
		parent::__construct($section, $params);

		if (isset($params[self::REDIRECT_URL])) {
			$this->redirectUrl = $params[self::REDIRECT_URL];
		} else {
			$this->redirectUrl = $this->getApplication()->getUrl()->getSelf();
		}
	}

	public function run() {
		Lang::addDictionary('login.php');

		$this->req = $this->getRequest();

		$this->username = $this->req[self::REQ_USERNAME];
		$this->password = $this->req[self::REQ_PASSWORD];

		if (isset($this->req[self::REQ_LOGOUT])) {
			$this->logout();
			Http::redirect($this->redirectUrl);
		}

		if (self::isLoggedIn()) {
			return $this->userInfo();
		} else {
			return $this->form();
		}
	}

	protected function userInfo() {
		return '<div id="user-data">' . Lang::get('You are logged in.') . '
			<div id="logout-link">' . $this->getLogoutLink() . '</div>
			</div>';
	}

	protected function getLogoutLink() {
		return '<a href="?'.self::REQ_LOGOUT.'=1">'.Lang::get('Logout').'</a>';
	}

	protected function validate() {
		$ret = array();
		if (empty($this->username)) {
			$ret[self::REQ_USERNAME][] = Lang::get('Enter your username');
		}
		if (empty($this->password)) {
			$ret[self::REQ_PASSWORD][] = Lang::get('Enter your password');
		}
		if (empty($ret)) {
			$this->userId = $this->auth();
			if (empty($this->userId)) {
				$ret[self::ERR_GENERAL][] = Lang::get('Login failed!');
			}
		}
		return $ret;
	}

	protected function logout() {
		Session::set(self::SESS_USER_ID, null);
	}

	protected function login() {
		if (empty($this->userId)) return;
		Session::set(self::SESS_USER_ID, $this->userId);
	}

	protected function form() {
		if (isset($this->req[self::REQ_LOGIN]) && isset($this->req[Form::REQ_FORM_NAME]) && $this->req[Form::REQ_FORM_NAME] == self::NAMESP) {
			if (isset($this->req[self::REQ_LOGIN])) {
				$this->errors = $this->validate();
				if (empty($this->errors)) {
					$this->login();
					Http::redirect($this->redirectUrl);
				}
			}
		}
		return $this->renderForm();
	}

	protected function renderForm() {
		$this->generateForm();
		$e = $this->form->getElements();
		$l = $this->labels;
		$r = $this->errors;

		$generalError = '<div id="login-err">' . Util::validationError($r[self::ERR_GENERAL]) . '</div>';
		
		return '<div id="login-form">' . $this->form->getBegin() . $generalError . '
			<div id="username-label">' . $l[self::REQ_USERNAME] . '</div>
			<div id="username-field">' . $e[self::REQ_USERNAME] . Util::validationError($r[self::REQ_USERNAME]) . '</div>
			<div id="password-label">' . $l[self::REQ_PASSWORD] . '</div>
			<div id="password-field">' . $e[self::REQ_PASSWORD] . Util::validationError($r[self::REQ_PASSWORD]) . '</div>
			<div id="login-button">' . $e[self::REQ_LOGIN] . '</div>
			' . $this->form->getEnd() . '</div>';
	}

	protected function generateForm() {
		$this->form = new Form(array(
			Form::NAME => self::NAMESP
		));

		$e = new TextLine(array(
			FormElement::NAME => self::REQ_USERNAME,
			FormElement::VALUE => $this->username,
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_USERNAME] = new Label(array(
			Label::TEXT => Lang::get('Username'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new Password(array(
			FormElement::NAME => self::REQ_PASSWORD,
			FormElement::VALUE => '',
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_PASSWORD] = new Label(array(
			Label::TEXT => Lang::get('Password'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new Submit(array(
			FormElement::NAME => self::REQ_LOGIN, 
			FormElement::VALUE => Lang::get('Login')
		));
		$this->form->addElement($e);
	}

}
