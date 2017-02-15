<?php

class Captcha extends FormElement {

	const SESS_NAME = 'captchaCode';

	const IMAGE_URL = 'imageUrl';

	private $imageUrl;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::IMAGE_URL])) {
			$this->imageUrl = $params[self::IMAGE_URL];
		}
	}

	public static function isValid($req) {
		return strtolower($req) == Session::get(self::SESS_NAME);
	}

	public function getImage(){
		header('Content-type: image/gif');
		$img = imagecreate(192, 50);
		$white = imagecolorallocate($img, 255, 255, 255);
		$black = imagecolorallocate($img, 0, 0, 0);
		imageline($img, 1, 1, 1, 49, $black);
		imageline($img, 1, 1, 190, 1, $black);
		imageline($img, 1, 49, 190, 49, $black);
		imageline($img, 190, 1, 190, 49, $black);
		$letterOptions = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','3','4','5','6','7','8','9');
		$font = dirname(__FILE__) . '/ACME.ttf';
		$tehThing = 5;
		for ($t=0; $t<5; $t++) {
			$randColor1 = rand(20, 200);
			$randColor2 = rand(20, 200);
			$randColor3 = rand(20, 200);
			$letterColor = imagecolorallocate($img, $randColor1, $randColor2, $randColor3); 
			$height = rand(40, 45);
			$letter = rand(0, count($letterOptions)-1);
			$string .= $letterOptions[$letter];
			$size = rand(20, 30);
			$angle = rand(-20, 20);
			imagettftext($img, $size, $angle, $tehThing, $height, $letterColor, $font, $letterOptions[$letter]);
			$tehThing = $tehThing+40;
		}
		// lines
		for ($i=0; $i<20; $i++) {
			$y1 = rand(1, 49);
			$y2 = rand(1, 49);
			$x1 = rand(1, 190);
			$x2 = rand(1, 190);
			$randColor1 = rand(20, 200);
			$randColor2 = rand(20, 200);
			$randColor3 = rand(20, 200);
			$lineColor = imagecolorallocate($img, $randColor1, $randColor2, $randColor3); 
			imageline($img, $x1, $y1, $x2, $y2, $lineColor);
		}
		imagegif($img);	
		imagedestroy($img);

		Session::set(self::SESS_NAME, strtolower($string));
	}

	public function getHtml() {
		if (empty($this->imageUrl)) throw new Exception('Captcha IMAGE_URL must be set');
		$ret = '<img src="' . $this->imageUrl . '" id="captchaimage" />
			<a tabindex="-1" style="border-style: none;" href="#" title="' . Lang::get('Refresh Image') . '" onclick="document.getElementById(\'captchaimage\').src = \'' . $this->imageUrl . '?rand=\' + Math.random(); return false"><img src="http://lh5.ggpht.com/_y_Mp1p_xDUM/TBDqsych8WI/AAAAAAAAA98/ElkSHbgzisw/refresh.jpg" alt="" border="0" width="25" height="25" /></a>
			<br />';
		$e = new TextLine(array(
			FormElement::NAME => 'captcha',
			TextLine::SIZE => 10
		));
		$ret .= $e->getHtml();
		return $ret;
	}
}
