<?php

class Sms {
	public function sendSms($message, $to)
	{
		if (substr($to,0,1) == '+')
			$to = substr($to, 1);
		if (substr($to,0,2) != "33")
			$to = "33" . substr($to, 1);
		require_once APPLICATION_PATH . '/../library/Webshell.php';
		$webshell = Webshell::getInstance();
		$res = Zend_Json::decode($webshell->exec('@orange -sms send "' . str_replace('"', '\\"', $message) . '" -to "' . $to . '"'));
	}
}