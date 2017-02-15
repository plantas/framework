<?php

class CaptchaSection extends Section {

	public function run() {
		$c = new Captcha();
		return $c->getImage();
	}
}
