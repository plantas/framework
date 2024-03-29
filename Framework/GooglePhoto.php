<?php

class GooglePhoto {

	private $url;

	public function __construct($url) {
		if (!$url || filter_var($url, FILTER_VALIDATE_URL) === false) {
			throw new Exception('Invalid URL');
		} else {
			$this->url = $url;
		}
	}

	public function getImg($size, $crop = false, $alt = '') {
		return '<img src="' . $this->getResizedUrl($size, $crop) . '" alt="' . Util::escape($alt) . '" />';
	}

	public function getSrc($size, $crop = false) {
		return $this->getResizedUrl($size, $crop);
	}

	private function getResizedUrl($size, $crop) {
		// google plus photo
		// https://lh3.googleusercontent.com/-ppmMIKADnH3ejlumN3tF9wbfcRvcehkPR3-CKVWTnJTYpR4fXsAOHvKaqbl4jaSDiJZSdSHWQCytRlRWCu3HAe0ekZw
		// ...vOeDYKGeNIeuu_-BXabpqq4RReno8_qKr_4mcHUxGMaFY7oBiZSuRT6XdTydDHn2g=w1200-h900

		if (preg_match('!=w(\d+)\-h(\d+)!', $this->url, $m)) {
			return preg_replace('!=w(\d+)\-h(\d+)!', '=s' . $size . ($crop ? '-c' : ''), $this->url);

		// picasa
		// https://lh6.googleusercontent.com/-6EHicLePl5M/U0RZkLhROYI/AAAAAAAALQk/ouEQARB2sP4/s198/SAM_3217.JPG
		// https://lh3.googleusercontent.com/-Ew6RUi4r4pw/Vb0zF3-GA1I/AAAAAAAAPis/cGTgCgNiF2w/s1152-Ic42/majica-prijedlog.jpg

		} else if (preg_match('!\/s(\d+).*?\/!', $this->url, $m)) {
			return preg_replace('!\/s(\d{2,})(.*?)\/!', '/s' . $size . ($crop ? '-c' : '') . '$2/', $this->url);

		// https://lh3.googleusercontent.com/fVzg62SbhLN4-JPbw9_nhTpykm-q0dKiJ8MAH_5t_naVWctAh5599eXZl5Eem8koX95E3yr0mqt2Vbc=s640-rw
		} else if (preg_match('!\=s(\d+)-(\w{2}+)$!', $this->url, $m)) {
			return preg_replace('!\=s(\d{2,})(.*)$!', '=s' . $size . ($crop ? '-c' : '') . '$2', $this->url);

		} else if (preg_match('!\=s(\d+)$!', $this->url, $m)) {
			return preg_replace('!\=s(\d+)$!', '=s' . $size . ($crop ? '-c' : ''), $this->url);
		} else {
			return $this->url . '=s' . $size . ($crop ? '-c' : '');
		}
	}
}
