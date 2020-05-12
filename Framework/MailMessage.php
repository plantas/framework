<?php

// wrapper za swift
class MailMessage {

	const ENC_GMAIL_USERNAME = 'bWVAaWdvcnBsYW50YXMuY29t';
	const ENC_GMAIL_PASSWD = 'bzUzamV6YW4hIQ==';

	public static function getInstance() {
		return new Swift_Message();
	}

	public static function send(Swift_Message $m) {
		$transporter = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
			->setUsername(base64_decode(self::ENC_GMAIL_USERNAME))
			->setPassword(base64_decode(self::ENC_GMAIL_PASSWD));

		$mailer = new Swift_Mailer($transporter);
		return $mailer->send($m);

		//$transport = Swift_SendmailTransport::newInstance();
		//$transport = Swift_SmtpTransport::newInstance('localhost');
		//$transport = Swift_MailTransport::newInstance();
		//$mailer = Swift_Mailer::newInstance($transport);
		//return $mailer->send($m);
	}
}
