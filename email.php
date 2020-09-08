<?php

require_once('kwutils.php');
require_once('creds.php');
require_once('emailDao.php');

class kwynn_email {
    
    const devActiveTS = '2020-09-07 23:10';
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

private function audit($when, $dat = false) {

   
    if ($when === 'pre') {
	$dat = [];
        $this->seq = $this->auditDao->getSeq('email_auto');
	$dat['seq'] = $this->seq;
	$dat['state'] = $when;
	$this->auditDao->put($dat);
	return;
    }

    $dat['seq'] = $this->seq;
    $dat['state'] = $when;
    
    $this->auditDao->put($dat);
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
    
    $this->audit('pre');
    
    $sendRet = 'test only - no send attempt';
    
    $bsend   = microtime();
    if ($this->shouldSend()) $sendRet = $mail->Send();
    $asend   = microtime();

    $this->audit('post', get_defined_vars());
    
    return $sendRet;
} // func

public static function test() {
    cliOrDie();
    $o = new self(self::tov);
    return $o->smail('test', 'test', 0);
}
} // class

if (didCliCallMe(__FILE__)) kwynn_email::test();
