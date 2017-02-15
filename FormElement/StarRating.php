<?php

class StarRating extends Radio {

	const MIN_RATING = 'minRating';
	const MAX_RATING = 'maxRating';

	protected $minRating = 1;
	protected $maxRating = 5;

	public function __construct($params = array()) {
		parent::__construct($params);

		if (isset($params[self::MIN_RATING]) && is_numeric($params[self::MIN_RATING])) {
			$this->minRating = $params[self::MIN_RATING];
		}
		if (isset($params[self::MAX_RATING]) && is_numeric($params[self::MAX_RATING])) {
			$this->maxRating = $params[self::MAX_RATING];
		}

		for ($i = $this->minRating; $i <= $this->maxRating; $i++) {
			$this->options[$i] = null;
		}
		$this->cssClass = 'star'; // for jQuery selector
	}

	public function getHtml() {
		File::includeJs('jquery.js', File::LIB_DIR);
		File::includeJs('jquery-plugins/starrating/jquery.rating.js', File::LIB_DIR);
		File::includeCss('jquery-plugins/starrating/jquery.rating.css', File::LIB_DIR);
			
		return parent::getHtml();
	}
}
