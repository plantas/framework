<?php

// wrapper za swift
class MailMessage {

	const ENC_GMAIL_USERNAME = 'bWVAaWdvcnBsYW50YXMuY29t';
	const ENC_GMAIL_PASSWD = 'bzUzamV6YW4hIQ==';

	public static function getInstance() {
		require_once(dirname(__FILE__) . '/external/Swift/5.4.5/lib/swift_required.php');
		return Swift_Message::newInstance();
	}

	public static function send(Swift_Message $m) {
		$transporter = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
			->setUsername(base64_decode(self::ENC_GMAIL_USERNAME))
			->setPassword(base64_decode(self::ENC_GMAIL_PASSWD));

		$mailer = Swift_Mailer::newInstance($transporter);
		return $mailer->send($m);

		//$transport = Swift_SendmailTransport::newInstance();
		//$transport = Swift_SmtpTransport::newInstance('localhost');
		//$transport = Swift_MailTransport::newInstance();
		//$mailer = Swift_Mailer::newInstance($transport);
		//return $mailer->send($m);
	}
}
