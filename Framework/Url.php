<?php

/**
 *	Example:
 *	app dir = appdir
 *	section url = /foo/bar
 *
 *	http://www.test.com/appdir/foo/bar/asd
 *
 * 	if (current script is at /foo/bar)
 *	self:	/appdir/foo/bar
 *	self (full):  http://www.test.com/appdir/foo/bar
 *
 * 	rest: /asd
 *
 * 	base: /appdir
 * 	base (full): http://www.test.com/appdir
 *
 * 	app: /foo/bar
 *
 */

class Url {

	protected $baseUrl = '';
	protected $selfUrl = '';
	protected $restUrl = '';
	protected $appUrl = '';

	public function __construct(Request $req) {
		$this->initUrls($req);
	}

	public function getSelf($hostname = true) {
		return ($hostname ? Config::get('HOSTNAME') : '') . $this->selfUrl;
	}

	public function getBase($hostname = true) {
		return ($hostname ? Config::get('HOSTNAME') : '') . $this->baseUrl;
	}

	public function getRest() {
		return $this->restUrl;
	}

	public function getApp() {
		return $this->appUrl;
	}

	protected function initUrls($req) {
		$rewriteUrl = $this->fixUrl($req->get('application_url'));
		$currentSectionUrl = $this->matchUrl($rewriteUrl);

		$this->baseUrl = Config::get('APPLICATION_URL');
		$this->selfUrl = $this->baseUrl . $currentSectionUrl;
		$this->restUrl = substr($rewriteUrl, strlen($this->selfUrl) - strlen($this->baseUrl));
		$this->appUrl = $currentSectionUrl;
	}

	protected function matchUrl($url) {
//		var_dump($url);
		
		if (empty($url)) return ''; // default url

		$map = Config::get('URL_MAP');
		$origUrl = $url;

		while (true) {
			if (array_key_exists($url, $map)) {
				return $url;
			} else {
				$lastSlashPos = strrpos($url, '/');
				$url = substr($url, 0, $lastSlashPos);
				if (empty($url)) {
					Http::response(404);
				}
			}
		}
	}

	private function fixUrl($url) {
		if (empty($url)) return '';

		$url = strtolower($url);

		// strip ending slashes
		$url = preg_replace('!/*$!', '', $url);

		if (substr($url, 0, 1) != '/') {
			$url = '/' . $url;
		}

		return $url;
	}
}
