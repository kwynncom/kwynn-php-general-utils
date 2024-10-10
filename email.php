<?php

require_once('kwutils.php');
require_once('creds.php');
require_once('emailDao.php');
require_once('emailDefaults.php');

class kwynn_email {
    
    const devActiveTS = '2024-09-20 23:20';

    protected object $omo;
    private $auditDao;

    const defaultPort = 587;
   
    public static function send($subject, $body, $isHTML = false) {
		$o = new self();
		return $o->smail($body, $subject, $isHTML);
    }
    
    
    function __construct() {
		$this->omo = new PHPMailer\PHPMailer\PHPMailer();
		$this->auditDao = new dao_email_out_audit();
    }
    
    public function setExtraId(string $id) {
	$this->auditDao->setExtraId($id);
    }

    private function shouldSend() {
		if (!ispkwd()) return true;
		if (time() < strtotime(self::devActiveTS)) return true;
		return false;
    }
 
	private function getMO() {
		if (($a = kwifs($this, 'omo'))) {
			if (is_array($a)) $a = (object)$a;
			if ($a->Password) return $a;
		}
		return kwynn_email_default::get();
	}
	
	public function smail($body, $subject, $isHTML = true) {
		$mail = $this->getMO();
		$mail->Subject = $subject;
		if ($isHTML) {
			$mail->IsHTML(true);
			$mail->MsgHTML($body);
		}
		else {
			$mail->IsHTML(false);
			$mail->Body =  $body;
		}

		$this->auditDao->put('pre');

		$sendResult = false;

		$isTest = !$this->shouldSend();

		$bsend   = microtime(1);
		if (!$isTest) $sendResult = $mail->Send();
		$asend   = microtime(1);

		$this->auditDao->put('post', sendResult: $sendResult, mail: $mail, Ubsend: $bsend, Uasend: $asend, isTest: $isTest);

		return $sendResult;
	} // func


public static function test() {
    cliOrDie();
    if (0) $o = new kwynn_email();
	else   $o = new kwynn_email_default();
    $ret = $o->smail('test', 'test  2022/10/16 01:41', false);
	return $ret;
}
} // class

if (didCliCallMe(__FILE__)) kwynn_email::test();
