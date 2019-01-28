<?php

class MessageSnippet extends Snippet {

	const SESS_MSG_VAR = __CLASS__;

	const MSG_TXT = 'message';
	const MSG_TYPE = 'type';
	// 0 - fadeout on click
	// > 1 - fadeout after n seconds
	const MSG_TIMEOUT = 'timeout';


	const TYPE_NOTICE = 'notice';
	const TYPE_ERROR = 'error';

	public function run() {
		$msg = $this->getMessage();
		if (!$msg) return '';

		File::includeCss('css/message.css?v=1', File::LIB_DIR);
		File::includeJs('jquery.js', File::LIB_DIR);
?>
<div class="message-box">
	<div class="message-icon-<?=$msg[self::MSG_TYPE]?>">&nbsp;</div>
	<div class="message-text"><?=$msg[self::MSG_TXT]?></div>
</div>
<script>
<?php if ($msg[self::MSG_TIMEOUT] == 0) : ?>
$(".message-box").click(function () {
	$(".message-box").fadeOut("slow");
});
<?php else : ?>
$(".message-box").delay(<?=$msg[self::MSG_TIMEOUT] * 1000?>).fadeOut("slow");
<?php endif; ?>
</script>
<?php
	}

	public static function setMessage($txt, $type = self::TYPE_NOTICE, $timeout = 0) {
		$msg = array(
			self::MSG_TXT => $txt,
			self::MSG_TYPE => $type ? $type : self::TYPE_NOTICE,
			self::MSG_TIMEOUT => intval($timeout)
		);
		Session::set(self::SESS_MSG_VAR, $msg);
	}

	protected function getMessage() {
		$msg = Session::get(self::SESS_MSG_VAR);
		Session::set(self::SESS_MSG_VAR, null);
		return $msg;
	}

}
