<?php

abstract class TemplateSection extends Section {

	protected const HTML_HEAD_PLACEHOLDER = '##html-head-placeholder-meta-css-js##';

	protected $title;

	public function __construct(Application $app) {
		parent::__construct($app);
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function addTitle($title, $separator = ' - ') {
		if (empty($this->title)) {
			$this->title .= $title;
		} else {
			$this->title .= $separator . $title;
		}
	}

	public function run() {
		ob_start();
		include($this->getTemplate());
		$html = ob_get_contents();
		ob_end_clean();

		echo str_replace(self::HTML_HEAD_PLACEHOLDER, $this->getIncludes() . HtmlHeadSnippet::getHeadString(), $html);
	}

	protected function getIncludes() {
		$files = File::getJsFiles();
		$ret = '';
		if (is_array($files)) {
			foreach ($files as $f) {
				// jquery and jquery-ui needs to be included by app, skip legacy includes
				if (in_array($f, array('lib/jquery.js', 'lib/jquery-ui.js'))) continue;

				if (substr($f, 0, 4) == 'http' || substr($f, 0, 2) == '//') {
					$url = $f;
				} else {
					$url = $this->getUrl()->getBase() . '/' . $f;
				}

				$ret .= '
	<script type="text/javascript" src="' . $url . '"></script>';
			}
		}
		$files = File::getCssFiles();
		if (is_array($files)) {
			foreach ($files as $f) {
				if (substr($f, 0, 4) == 'http' || substr($f, 0, 2) == '//') {
					$url = $f;
				} else {
					$url = $this->getUrl()->getBase() . '/' . $f;
				}
				$ret .= '
	<link rel="stylesheet" href="' . $url . '" type="text/css" />';
			}
		}
		return $ret;
	}

	abstract protected function getTemplate();
}
