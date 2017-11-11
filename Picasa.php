<?php

class Picasa {

	protected $httpClient;

	public function __construct($httpClient) {
		$this->httpClient = $httpClient;
	}

	private function debugResponse($response){
		print_r($response);
		echo "\ngetStatusCode: " . $response->getStatusCode();      // >>> 200
		echo "\ngetReasonPhrase: " . $response->getReasonPhrase();    // >>> OK
		echo "\nbody: " . $response->getBody();
		echo "\n";
	}

	public function getAlbumById($userId, $albumId) {
		if (!$this->httpClient) return array();
		$response = $this->httpClient->get('https://picasaweb.google.com/data/feed/api/user/'.$userId.'/albumid/'.$albumId);
		$xml = simplexml_load_string($response->getBody());

		$album = new PicasaAlbum();
		$album->setId((string) $xml->id);
		$album->setTitle((string) $xml->title);

		$images = array();
		for($i = 0; $i < count($xml->entry); $i++) {
			$image = new PicasaImage();

			$image->setId((string) $xml->entry[$i]->id);
			$image->setTitle((string) $xml->entry[$i]->title);
			$image->setSummary((string) $xml->entry[$i]->summary);

			$src = (string) $xml->entry[$i]->content['src'];

			// hack for speedup
			//feed returns this: https://lh3.googleusercontent.com/-Prsyyp4W3B8/Wc9DFvuy6KI/AAAAAAAAUt0/1n6Tp0oxoNYGAjxBHofV9dVVLvzqsnUogCHMYBhgL/P1060236.jpg
			//and we need this: https://lh3.googleusercontent.com/-Prsyyp4W3B8/Wc9DFvuy6KI/AAAAAAAAUt0/1n6Tp0oxoNYGAjxBHofV9dVVLvzqsnUogCHMYBhgL/s144/P1060236.jpg
			if (($lastSlash = strrpos($src, '/')) !== false) {
				$src = substr_replace($src, '/s144', $lastSlash, 0);
			}
			$image->setThumb($src);

			$images[] = $image;
		}
		$album->setImages($images);

		return $album;
	}

	public function getAlbums($userId){
		if (!$this->httpClient) return array();
		$response = $this->httpClient->get('https://picasaweb.google.com/data/feed/api/user/'.$userId);
		$xml = simplexml_load_string($response->getBody());

		$albums = array();
		for ($i = 0; $i < count($xml->entry); $i++) {
			//echo '<pre>';var_dump($xml->entry);exit;
			$album = new PicasaAlbum();
			
			$album->setId((string) $xml->entry[$i]->id);
			$album->setTitle((string) $xml->entry[$i]->title);
			$album->setPublished((string) $xml->entry[$i]->published);
			$album->setRights((string) $xml->entry[$i]->rights);

			$albums[] = $album;
		}
		return $albums;
	}
}

class PicasaAlbum {

	protected $id;
	protected $title;
	protected $published;
	protected $rights;
	protected $images = array();
	
	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setPublished($date) {
		$this->published = $date;
	}

	public function getPublished() {
		return $this->published;
	}

	public function setRights($rights) {
		$this->rights = $rights;
	}

	public function getRights() {
		return $this->rights;
	}

	public function setImages(Array $images) {
		$this->images = $images;
	}

	public function getImages() {
		return $this->images;
	}
}

class PicasaImage {

	protected $id;
	protected $title;
	protected $published;
	protected $summary;
	protected $thumb;
	
	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setPublished($date) {
		$this->published = $date;
	}

	public function getPublished() {
		return $this->published;
	}

	public function setSummary($summary) {
		$this->summary = $summary;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function setThumb($thumb) {
		return $this->thumb = $thumb;
	}

	public function getThumb() {
		return $this->thumb;
	}
}
