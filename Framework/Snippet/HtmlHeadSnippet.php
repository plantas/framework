<?php

class HtmlHeadSnippet extends Snippet {

	protected static $headString = '';

	public function run() {
		ob_start();
?>

		<title><?=Util::escape($this->getSection()->getTitle())?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=$this->buildCssIncludes()?>
<?=$this->buildJsIncludes()?>
<?=self::$headString?>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	protected function buildJsIncludes() {
		$files = File::getJsFiles();
		$ret = '';
		if (is_array($files)) {
			foreach ($files as $f) {
				$ret .= "\t\t" . '<script type="text/javascript" src="' . (substr($f, 0, 4) == 'http' ? $f : $this->getUrl()->getBase() . '/' . $f) . '"></script>'."\n";
			}
		}
		return $ret;
	}

	protected function buildCssIncludes() {
		$files = File::getCssFiles();
		$ret = '';
		if (is_array($files)) {
			foreach ($files as $f) {
				$ret .= "\t\t" . '<link rel="stylesheet" href="' . (substr($f, 0, 4) == 'http' ? $f : $this->getUrl()->getBase() . '/' . $f) . '" type="text/css" />'."\n";
			}
		}
		return $ret;
	}

	public static function addHeadString($string) {
		self::$headString .= $string . "\n";
	}

	public static function getHeadString() {
		return self::$headString;
	}
}
