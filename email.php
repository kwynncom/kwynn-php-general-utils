<?php

require_once('kwutils.php');
require_once('creds.php');
require_once('emailDao.php');

class kwynn_email {
    
    const devActiveTS = '2022-10-15 23:31';
    const devActive = 0;
    
    const smtp_server = 'email-smtp.us-east-1.amazonaws.com';
    
    const tov = 'cli_self_test_override';
    
    public static function send($subject, $body, $isHTML = false) {
	$o = new self();
	return $o->smail($body, $subject, $isHTML);
    }
    
    
    function __construct($isTest = false) {
	$this->mailo = $this->getMailO();
	$this->setDefaultTo();
	$this->auditDao = new dao_email_out_audit();
	$this->setTestV($isTest);
    }
    
    private function setTestV($p) {
	if (!iscli()) return;
	if ($p === self::tov) 
	     $this->cli_self_test_override = true;
    }
    
    private function isTestOverride() {
	return      isset($this->cli_self_test_override)
		 &&       $this->cli_self_test_override
	    ;
    }
    
    private function shouldSend() {
	if (isAWS()) return true;
	if (self::devActive) return true;
	if (time() < strtotime(self::devActiveTS)) return true;
	if ($this->isTestOverride()) return true;
	return false;
    }
    
    private function getMailO() {
	$mailo = self::getCommonO();
	$this->popCreds($mailo);
	return $mailo;
    }
    
    private function getCreds() {
	$dao = new kwynn_creds('creds');
	$c = $dao->getType('email'); kwas($c && is_array($c) && count($c) >0, 'bad creds array');
	$t = ['uname', 'pwd', 'from', 'from_name'];
	foreach($t as $i) {
	    kwas(isset($c[$i]),"bad $i in kwynn email");
	    $s = $c[$i];
	    kwas(	 isset    ($s)  
		    && 	 is_string($s)
		    &&   strlen(   $s) > 10, "bad $i in kwynn email");
	}
	
	$this->creds = $c;
	return $c;
    }
    
    private static function getCommonO() {
    $mail             = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->SMTPDebug  = 0;  // 1 = errors and messages; 2 = messages only
    $mail->SMTPAuth   = true;                  
    $mail->SMTPSecure = 'tls';                 
    $mail->Port       = 587;   
    
    return $mail;
}

private function popCreds(&$mail) {
    $creds = $this->getCreds();
    $mail->Host       = self::smtp_server;  
    $mail->Username   = $creds['uname'];
    $mail->Password   = $creds['pwd'];
    $mail->SetFrom(     $creds['from'], 
			$creds['from_name']);
}

private function setDefaultTo() {
    $this->mailo->AddAddress($this->creds['default_to'], $this->creds['default_to_name']);
}

public function smail($body, $subject, $isHTML = true) {
    $mail = $this->mailo;
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
    $o = new self(self::tov);
    $ret = $o->smail('test', 'test', false);
	return $ret;
}
} // class

if (didCliCallMe(__FILE__)) kwynn_email::test();
