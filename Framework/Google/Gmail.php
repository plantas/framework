<?php

use Symfony\Component\Mime\Email;

class Gmail {

	public function send(Email $message) {

		$service = new Google_Service_Gmail($this->getClient());

		$msg = new Google_Service_Gmail_Message();
		$msg->setRaw(base64_encode($message->toString()));

		try {
			//The special value **me** can be used to indicate the authenticated user.
			$message = $service->users_messages->send('me', $msg);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	private function getClient() {
		$tokenPath = Config::get('GOOGLE_API_TOKEN_PATH');
		$credentialsPath = Config::get('GOOGLE_API_CREDENTIALS_PATH');

		if (!isset($tokenPath, $credentialsPath)) {
			throw new Exception('Configure GOOGLE_API_TOKEN_PATH and GOOGLE_API_CREDENTIALS_PATH to use Gmail API');
		}

		$client = new Google_Client();
		$client->setApplicationName('Gmail API');
		$client->setScopes(Google_Service_Gmail::GMAIL_SEND);
		$client->setAuthConfig($credentialsPath);
		$client->setAccessType('offline');
		$client->setPrompt('select_account consent');

		if (file_exists($tokenPath)) {
			$client->setAccessToken(json_decode(file_get_contents($tokenPath), true));
		} else {
			die('Google API token path is not set');
		}

		if ($client->isAccessTokenExpired()) {
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			}
		}

		return $client;
	}

}
