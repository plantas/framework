<?php

abstract class Html5Section extends HtmlSection {

	public function run() {
		$body = $this->getBody();
		$head = $this->getHead(); //it is important to execute head snippet last

?>
<!DOCTYPE html>
<html>
	<head>
		<?=$head?>
	</head>
	<body>
		<?=$body?>	
	</body>
</html>
<?php 
	}

}
