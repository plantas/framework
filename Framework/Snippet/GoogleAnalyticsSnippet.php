<?php

class GoogleAnalyticsSnippet extends Snippet {
	
	public function run() {
			
		if (!Config::get('GOOGLE_ANALYTICS_ENABLED', false)) return '';

		$gaId = Config::get('GOOGLE_ANALYTICS_ID');
		if (empty($gaId)) throw new Exception('Google analytics site ID missing in a config file');

		return <<<EOF
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$gaId', 'auto');
  ga('send', 'pageview');
</script>
EOF;
/*
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("'.$gaId.'");
pageTracker._trackPageview();
} catch(err) {}</script>
		';
*/
	}
}
