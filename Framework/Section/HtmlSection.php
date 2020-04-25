<?php

abstract class HtmlSection extends Section {

	protected $title;
	protected $bodyOnLoad = '';

	abstract protected function getBody();

	public function __construct(Application $app) {
		parent::__construct($app);
	}

	protected function getHead() {
		$s = new HtmlHeadSnippet($this);
		return $s->run();
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

	public function addBodyOnLoad($js) {
		$this->bodyOnLoad .= $js;
	}

	public function run() {
		$body = $this->getBody();
		$head = $this->getHead(); //it is important to execute head snippet last

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?=$head?>
	</head>
	<body<?=($this->bodyOnLoad ? ' onload="'.$this->bodyOnLoad.'"' : '')?>>
		<?=$body?>	
	</body>
</html>
<?php 
	}

}
