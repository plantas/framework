<?php

/*
 * https://developers.facebook.com/docs/opengraphprotocol/
 * */
class OpenGraph {

	private $title;
	private $type;
	private $images = array();
	private $url;

	private $siteName;
	private $description;

	public function getHeaders() {
		$headers = array();
		if ($this->title) $headers['title'] = Util::escape($this->title);	
		if ($this->type) $headers['type'] = $this->type;	
		if ($this->url) $headers['url'] = $this->url;	
		if ($this->siteName) $headers['site_name'] = Util::escape($this->siteName);	
		if ($this->description) $headers['description'] = Util::escape($this->description);	

		$html = '';
		foreach ($headers as $k => $v) {
			$html .= '
		<meta property="og:'.$k.'" content="'.$v.'" />';
		}
		if (is_array($this->images) && !empty($this->images)) {
			foreach ($this->images as $img) {
				$html .= '
		<meta property="og:image" content="'.$img.'" />';
			}
		}
		return $html;
	}

	/* setters */

	public function setTitle($title) {
		$this->title = $this->normalizeString($title, 128);
	}

	public function setImage($image) {
		$this->images[] = $image;
	}	

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setSiteName($name) {
		$this->siteName = $name;
	}

	public function setDescription($desc) {
		$this->description = $this->normalizeString($desc, 255);
	}

	/* Valid types
Activities
    activity
    sport

Businesses
    bar
    company
    cafe
    hotel
    restaurant

Groups
    cause
    sports_league
    sports_team

Organizations
    band
    government
    non_profit
    school
    university

People
    actor
    athlete
    author
    director
    musician
    politician
    public_figure

Places
    city
    country
    landmark
    state_province

Products and Entertainment
    album
    book
    drink
    food
    game
    product
    song
    movie
    tv_show
	
	 * */
	public function setType($type) {
		$this->type = $type;
	}

	private function normalizeString($string, $length = null) {
		if (is_string($string)) {
			$string = trim($string);
			mb_internal_encoding("UTF-8");
			if ($length != null && mb_strlen($string) > $length) {
				$string = mb_substr($string, 0, $length - 3) . '...';
			}
		}
		return $string;
	}
}
