<?php

abstract class UserForgotPasswordSnippet extends Snippet {
	
	const REQ_USERNAME = 'username';
	const REQ_EMAIL = 'email';
	const REQ_CAPTCHA = 'captcha';
	const REQ_SEND = 'send';

	const NAMESP = 'forgot';

	protected $req;

	protected $form;
	protected $labels = array();
	protected $errors = array();

	abstract protected function getResetPasswordBaseUrl(); //$this->getUrl()->getBase(true) . UrlMap::RESET_PASSWORD_URL
	abstract protected function getUserByUsername($username);
	abstract protected function sendEmail();

	/*
	protected function sendEmail() {
		$msg = MailMessage::getInstance()
			->setSubject('HPD Jastrebarsko - zaboravljena lozinka')
			->setFrom(MailSection::MAIL_ADDRESS)
			->setTo($this->user->getEmail())
			->setBody($this->getEmailTemplate(), 'text/html');

		return MailMessage::send($msg);

	}*/

	public function run() {
		Lang::addDictionary('login.php');
		$this->req = $this->getRequest();

		$ret = $this->form();

		return $ret;
	}

	public static function getHash(IUserManagable $user) {
		return md5($user->getUsername() . $user->getEmail() . $user->getPasswordHash()); // after passwd change this hash won't be valid
	}

	protected function validate() {
		$ret = array();
		$username = $this->req[self::REQ_USERNAME];
		if (empty($username)) {
			$ret[self::REQ_USERNAME][] = Lang::get('Enter your username');
		}
		if (empty($this->req[self::REQ_EMAIL])) {
			$ret[self::REQ_EMAIL][] = Lang::get('Enter your email');
		} else if (!filter_var($this->req[self::REQ_EMAIL], FILTER_VALIDATE_EMAIL)) {
			$ret[self::REQ_EMAIL][] = Lang::get('Invalid email');
		} else if (!empty($username)) {
			// valid email non empty username
			//$s = new UserStorage();
			$this->user = $this->getUserByUsername($username);
			if (!$this->user instanceof IUserManagable) {
				$ret[self::REQ_EMAIL][] = Lang::get('Username/email missmatch');
			} else if (strcasecmp($this->user->getEmail(), $this->req[self::REQ_EMAIL]) != 0) {
				$ret[self::REQ_EMAIL][] = Lang::get('Username/email missmatch');
			}
		}
		if (!Recaptcha::isValid($this->req)) {
			$ret[self::REQ_CAPTCHA][] = Lang::get('Verify you are a human');
		}
		return $ret;
	}

	protected function getResetLink() {
		return $this->getResetPasswordBaseUrl() . '?' . UserChangePasswordSnippet::REQ_USER_ID . '=' . $this->user->getId() . '&' . 
			UserChangePasswordSnippet::REQ_HASH . '=' . self::getHash($this->user);
	}

	protected function getEmailTemplate() {
		return '
<h3>Promjena lozinke</h3>
Ovaj mail poslan Vam je jer ste zatražili promjenu lozinke na web stranici HPD Jastrebarsko za korisnika <b>' . Util::escape($this->user[UserModel::MEMBER_NAME]) . '</b>.
<br />Ukoliko to niste bili Vi molimo ignorirajte ovaj mail.
<br /><h4>Za promjenu lozinke kliknite <a href="' . $this->getResetLink() . '">ovdje</a>.</h4>
<br /><a href="' . $this->getUrl()->getBase(true) . '">HPD Jastrebarsko</a>
';
	}

	protected function form() {
		if (isset($this->req[self::REQ_SEND]) && isset($this->req[Form::REQ_FORM_NAME]) && $this->req[Form::REQ_FORM_NAME] == self::NAMESP) {
			$this->errors = $this->validate();
			if (empty($this->errors)) {

				if ($this->sendEmail()) {
					MessageSnippet::setMessage(Lang::get('Successfully sent'));
				} else {
					MessageSnippet::setMessage(Lang::get('Failed to send'), MessageSnippet::TYPE_ERROR);
				}

				Http::redirect($this->getUrl()->getBase());
			}
		}
		return $this->renderForm();
	}

	protected function renderForm() {
		$this->generateForm();
		$e = $this->form->getElements();
		$l = $this->labels;
		$r = $this->errors;

		return '<div class="forgot-form">' . $this->form->getBegin() . '
			<div class="username-label">' . $l[self::REQ_USERNAME] . '</div>
			<div class="username-field">' . $e[self::REQ_USERNAME] . Util::validationError($r[self::REQ_USERNAME]) . '</div>
			<div class="email-label">' . $l[self::REQ_EMAIL] . '</div>
			<div class="email-field">' . $e[self::REQ_EMAIL] . Util::validationError($r[self::REQ_EMAIL]) . '</div>
			<div class="captcha" style="margin-top:10px">' . $e[self::REQ_CAPTCHA] . Util::validationError($r[self::REQ_CAPTCHA]) . '</div>
			<div class="send-button">' . $e[self::REQ_SEND] . '</div>
			' . $this->form->getEnd() . '</div>';
	}

	protected function generateForm() {
		$this->form = new Form(array(
			Form::NAME => self::NAMESP
		));

		$e = new TextLine(array(
			FormElement::NAME => self::REQ_USERNAME,
			FormElement::VALUE => $this->req[self::REQ_USERNAME],
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_USERNAME] = new Label(array(
			Label::TEXT => Lang::get('Username'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new TextLine(array(
			FormElement::NAME => self::REQ_EMAIL,
			FormElement::VALUE => $this->req[self::REQ_EMAIL],
			FormElement::REQUIRED => true
		));
		$this->form->addElement($e);

		$this->labels[self::REQ_EMAIL] = new Label(array(
			Label::TEXT => Lang::get('Email'),
			Label::FOR_ELEMENT => $e	
		)); 

		$e = new Recaptcha(array(
			FormElement::NAME => self::REQ_CAPTCHA,
		));
		$this->form->addElement($e);


		$e = new Submit(array(
			FormElement::NAME => self::REQ_SEND, 
			FormElement::VALUE => Lang::get('Send')
		));
		$this->form->addElement($e);
	}
}
