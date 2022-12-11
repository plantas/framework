<?php

abstract class LoginSnippet extends Snippet {

	const HAS_REMEMBER = 'hasRemeber';
	const COOKIE_LIFETIME = 'cookieLifetime';
	const REDIRECT_URL = 'redirectUrl';

	private $hasRemember = true;
	private $cookieLifetime = 8640000; //100 dana
	private $redirectUrl;


	const NAMESP = 'login';

	const SESS_USER_ID = 'loggedUserId';
	const COOKIE_USERNAME = 'cookieUsername';
	const COOKIE_PASSWORD = 'cookiePassword';

	const REQ_USERNAME = 'username';
	const REQ_PASSWORD = 'password';
	const REQ_REMEMBER = 'remember';
	const REQ_LOGIN = 'login';
	const REQ_LOGOUT = 'logout';

	const ERR_GENERAL = 'errorGeneral';

	protected $req;
	protected $nsReq;

	protected $userId; // identifier - will be stored in session
	protected $username;
	protected $password;
	protected $passwordHash;
	protected $remember = false;

	protected $form;
	protected $labels = array();
	protected $errors = array();

	// should return user_id or null if failed
	abstract protected function getUserIdentifier();

	public function __construct(Section $section, $params = array()) {
		parent::__construct($section, $params);

		if (isset($params[self::HAS_REMEMBER])) {
			$this->hasRemember = (bool) $params[self::HAS_REMEMBER];
		}
		if (isset($params[self::COOKIE_LIFETIME])) {
			$this->cookieLifetime = (int) $params[self::COOKIE_LIFETIME];
		}
		if (isset($params[self::REDIRECT_URL])) {
			$this->redirectUrl = $params[self::REDIRECT_URL];
		} else {
			$this->redirectUrl = $this->getApplication()->getUrl()->getSelf();
		}
	}

	protected static function addNamespace($var) {
		return self::NAMESP . '[' . $var . ']';
	}

	public function run() {
		Lang::addDictionary('login.php');

		//Session::set(self::SESS_USER_ID, null);exit;
		$this->req = $this->getRequest();
		$this->nsReq = $this->getRequest(self::NAMESP);

		$this->username = $this->nsReq[self::REQ_USERNAME] ?? '';
		$this->password = $this->nsReq[self::REQ_PASSWORD] ?? '';
		$this->passwordHash = $this->getPasswordHash($this->password);
		$this->remember = isset($this->nsReq[self::REQ_REMEMBER]);

		if (isset($this->nsReq[self::REQ_LOGOUT])) {
			$this->logout();
			Http::redirect($this->redirectUrl);
		}

		if ($this->isLoggedIn()) {
			return $this->userInfo();
		} else {
			$this->cookieLogin(); // check the cookies
			return $this->form();
		}
	}

	protected function getPasswordHash($password) {
		return md5($password);
	}

	protected function userInfo() {
		return '<div id="user-data">' . Lang::get('You are logged in.') . '
			<div id="logout-link">' . $this->getLogoutLink() . '</div>
			</div>';
	}

	protected function getLogoutLink() {
		return '<a href="?'.self::addNamespace(self::REQ_LOGOUT).'=1">'.Lang::get('Logout').'</a>';
	}

	protected function isLoggedIn() {
		return (bool) Session::get(self::SESS_USER_ID);
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
			$this->userId = $this->getUserIdentifier();
			if (empty($this->userId)) {
				$ret[self::ERR_GENERAL][] = Lang::get('Login failed!');
			}
		}
		return $ret;
	}

	protected function logout() {
		Session::set(self::SESS_USER_ID, null);
		if (isset($_COOKIE[self::COOKIE_USERNAME])) {
			setcookie(self::COOKIE_USERNAME, '', time() - $this->cookieLifetime, "/", $_SERVER['HTTP_HOST']);
			unset($_COOKIE[self::COOKIE_USERNAME]);
		}
		if (isset($_COOKIE[self::COOKIE_PASSWORD])) {
			setcookie(self::COOKIE_PASSWORD, '', time() - $this->cookieLifetime, "/", $_SERVER['HTTP_HOST']);
			unset($_COOKIE[self::COOKIE_PASSWORD]);
		}
	}

	protected function login() {
		if (empty($this->userId)) return;

		Session::set(self::SESS_USER_ID, $this->userId);
		if ($this->hasRemember && $this->remember) {
			setcookie(self::COOKIE_USERNAME, $this->username, time() + $this->cookieLifetime, '/', $_SERVER['HTTP_HOST']);
			setcookie(self::COOKIE_PASSWORD, $this->passwordHash, time() + $this->cookieLifetime, '/', $_SERVER['HTTP_HOST']);
		}
	}

	protected function cookieLogin() {
		// cookie login
		if (isset($_COOKIE[self::COOKIE_USERNAME]) && isset($_COOKIE[self::COOKIE_PASSWORD])) {
			$this->username = $_COOKIE[self::COOKIE_USERNAME];
			$this->passwordHash = $_COOKIE[self::COOKIE_PASSWORD];
			$this->userId = $this->getUserIdentifier();

			if ($this->userId) {
				$this->login();
			} else {
				$this->logout();
			}
		}
	}

	protected function form() {
		if (isset($this->nsReq[self::REQ_LOGIN]) && isset($this->req[Form::REQ_FORM_NAME]) && $this->req[Form::REQ_FORM_NAME] == self::NAMESP) {
			if (isset($this->nsReq[self::REQ_LOGIN])) {
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
		
		$remember = $this->hasRemember ? '<div id="remember-label">' . $l[self::REQ_REMEMBER] . '</div><div id="remember-field">' . $e[self::addNamespace(self::REQ_REMEMBER)] . '</div>' : '';

		return '<div id="login-form">' . $this->form->getBegin() . $generalError . '
			<div id="username-label">' . $l[self::REQ_USERNAME] . '</div>
			<div id="username-field">' . $e[self::addNamespace(self::REQ_USERNAME)] . Util::validationError($r[self::REQ_USERNAME]) . '</div>
			<div id="password-label">' . $l[self::REQ_PASSWORD] . '</div>
			<div id="password-field">' . $e[self::addNamespace(self::REQ_PASSWORD)] . Util::validationError($r[self::REQ_PASSWORD]) . '</div>
			' . $remember . '
			<div id="login-button">' . $e[self::addNamespace(self::REQ_LOGIN)] . '</div>
			' . $this->form->getEnd() . '</div>';
	}

	protected function generateForm() {
		$this->form = new Form(array(
			Form::NAME => self::NAMESP
		));

		$e = new TextLine(array(
			FormElement::NAME => self::addNamespace(self::REQ_USERNAME),
			FormElement::VALUE => $this->username,
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_USERNAME] = new Label(array(
			Label::TEXT => Lang::get('Username'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new Password(array(
			FormElement::NAME => self::addNamespace(self::REQ_PASSWORD),
			FormElement::VALUE => '',
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_PASSWORD] = new Label(array(
			Label::TEXT => Lang::get('Password'),
			Label::FOR_ELEMENT => $e	
		)); 

		if ($this->hasRemember) {
			$e = new Checkbox(array(
				FormElement::NAME => self::addNamespace(self::REQ_REMEMBER),
				FormElement::VALUE => $this->remember,
				FormElement::REQUIRED => false
			));
			$this->form->addElement($e);

			$this->labels[self::REQ_REMEMBER] = new Label(array(
				Label::TEXT => Lang::get('Remember me'),
				Label::FOR_ELEMENT => $e	
			)); 
		}

		$e = new Submit(array(
			FormElement::NAME => self::addNamespace(self::REQ_LOGIN), 
			FormElement::VALUE => Lang::get('Login')
		));
		$this->form->addElement($e);
	}

}
