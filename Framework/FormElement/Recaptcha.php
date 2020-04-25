<?php

class Recaptcha extends FormElement {

	private $secretKey;
	private $siteKey;

	const RESPONSE = 'g-recaptcha-response';

	public function __construct($params = array()) {
		parent::__construct($params);

		$this->secretKey = Config::get('GOOGLE_SECRET_KEY');
		if (!$this->secretKey) throw new Exception('GOOGLE_SECRET_KEY must be set in order to make ' . __CLASS__ . ' work');

		$this->siteKey = Config::get('GOOGLE_SITE_KEY');
		if (!$this->siteKey) throw new Exception('GOOGLE_SITE_KEY must be set in order to make ' . __CLASS__ . ' work');
	}

	public function getHtml() {
		if ($this->readOnly) return ''; // disable if readonly

		HtmlHeadSnippet::addHeadString('
		<script src="https://www.google.com/recaptcha/api.js"></script>');

		$ret = '<div class="g-recaptcha" data-sitekey="' . $this->siteKey . '"></div>';
		return $ret;
	}

	/*
        validates captcha user input
        configuration here: https://www.google.com/recaptcha/admin#site/337349106
         */
	public static function isValid($req) {
		$response = isset($req[self::RESPONSE]) ? $req[self::RESPONSE] : null;
                if (empty($response)) return false;

                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $data = array(
                        'secret' => Config::get('GOOGLE_SECRET_KEY'),
                        'response' => $response
                );
                $verify = curl_init();
                curl_setopt($verify, CURLOPT_URL, $url);
                curl_setopt($verify, CURLOPT_POST, true);
                curl_setopt($verify, CURLOPT_POSTFIELDS, $data);
                curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
                $o = curl_exec($verify);
                curl_close($verify);

                if (!$o) return true;
                $val = json_decode($o);
		//var_dump($val);exit;
                return $val->success ? true : false;
        }

}
