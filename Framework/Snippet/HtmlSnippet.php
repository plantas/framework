<?php

class HtmlSnippet extends Snippet {

	const TEMPLATE = 'template';
	const HTML = 'html';

	protected $template;
	protected $html;

	public function __construct(Section $section, $params = array()) {
		parent::__construct($section, $params);

		if (isset($params[self::HTML])) {
			$this->setHtml($params[self::HTML]);
		} else if (isset($params[self::TEMPLATE])) {
			$this->setTemplate($params[self::TEMPLATE]);
		}
	}

	public function run() {
		if ($this->html) {
			return $this->html;
		}
		if ($this->template) {
			$tpl = Config::get('TEMPLATE_DIR') . $this->template;
			if (is_file($tpl)) {
				ob_start();
				include($tpl);
				$ret = ob_get_contents();
				ob_end_clean();
				return $ret;
			} else {
				throw new Exception('Template ' . $tpl . ' does not exist');
			}
		}
	}

	public function setHtml($html) {
		$this->html = $html;
	}

	public function getHtml() {
		return $this->html;
	}

	public function setTemplate($tpl) {
		$this->template = $tpl;
	}

	public function getTemplate() {
		return $this->template;
	}
}
