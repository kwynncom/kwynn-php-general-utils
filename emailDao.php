<?php

class dao_email_out_audit extends dao_generic {
    const db = 'emails_auto_out';
    
    public function __construct() {
	    parent::__construct(self::db);
	    $this->ecoll    = $this->client->selectCollection(self::db, 'audit');
    }
    
    public function put($vin) {

	$dat = self::parseVars($vin);
	$this->ecoll->upsert(['seq' => $vin['seq']], $dat);
    }
    
    private static function parseElapsedI(&$dat, $vin) {
	
	if	    (isset($vin['bsend']) && isset($vin['asend'])) 
	self::parseElapsed($vin['bsend'],          $vin['asend'], $dat);
    }
    
    public static function parseElapsed($b, $a, &$dat) {
	
	$mb = 1000000;
	$p1 = 'emore';
	$p2 = 'datemore';
	
	try {
	    $ba = explode(' ', $b);
	    $aa = explode(' ', $a);
	    $ts = intval($ba[1]);
	    $dat[$p1][$p2]['ts']   = $ts;
	    $dat[$p1][$p2]['tsus'] = $ts * $mb + intval(round($ba[0] * $mb));
	    $dat[$p1][$p2]['tss'] = $b;
	    $dat['emore']['datemore']['elapsed'] = round(($aa[1] - $ba[1]) + ($aa[0] - $ba[0]), 6);
	    $dat['r'] = date('r', $ts);
	    $x = 2;
	} catch (Exception $ex) { return false; }
    }
    
    private static function parseVars($vin) {
	$vin['iaminput'] = true;
	$dat = [];
	self::setVar($dat, 'servi', 'serv', $vin, 'mail', 'Host');
	self::setVar($dat, 'servi', 'uname', $vin, 'mail', 'Username');
	self::setVar($dat, 'emore', 'err', $vin, 'mail', 'ErrorInfo');
	self::setVar($dat, 'msgID', $vin, 'mail', 'smtp', 'last_smtp_transaction_id');
	self::setIMsgID($dat, $vin);
	self::setVar($dat, 'emore', 'to', $vin, 'mail', 'to');

	if ($vin['state'] === 'post') {
	    $dat['sendRet'] = isset($vin['sendRet']) ? 
				    $vin['sendRet'] : null;
	    self::parseElapsedI($dat, $vin);
	    $dat['subject'] = substr($vin['subject'], 0, 500);
	    $dat['body']    = substr($vin['body'], 0, 100);
	}
	
	$dat['state'] = $vin['state'];
	
	return $dat;
    }
    
    private static function setIMsgID(&$dat, $vin) {

	$nobyr = true;
	$long  = self::setVar($dat, 'emore', 'imsgID', $vin, 'mail', 'lastMessageID');
	$short = self::setVar($dat, 'emore', 'iuID'  , $vin, 'mail', 'uniqueid');

// $mail->lastMessageID -- internal between me and Amazon <...@myMachineName>
// $mail->uniqueid -- without <> and before @
	
    }
    
    private static function setVar(&$ret) {
	
	$tko = $ret;
	$tk = &$tko;
	$vstate = 'valid';
	
	try {
	    $argstate = 'pre';
	    $n  = func_num_args();
	    
	    for ($i = 1; $i < $n; $i++) {
		$arg = func_get_arg($i);
		
		if ($argstate === 'pre' && is_array($arg) && isset($arg['iaminput'])) {
		    $argstate = 'post';
		    $tv = $arg;
		    continue;
		}
		
		// if ($argstate === 'pre') if (is_array($ret)) $ret[$arg] = null;
		
		if ($argstate === 'pre') {
		    if (!isset($tk[$arg])) $tk[$arg] = [];
		    $tk = &$tk[$arg];
		    continue;
		}
		
					
		if (is_object($tv)) {
		    $tv = self::accessProtected($tv, $arg);
		    if (!$tv) break;
		} else { 
		    if (!isset($tv[$arg])) { $tv = null; break; }
		    else		   $tv = $tv[func_get_arg($i)];
		}
	    }
	    
	    $tk = $tv;
	    
	    if (is_array($ret)) $ret = array_merge($ret, $tko);
	    return $tv;
	} catch (Exception $ex) {}
    }
    
    public static function accessProtected($obj, $prop) { // see credits at bottom
	$reflection = new ReflectionClass($obj);
	$property = $reflection->getProperty($prop);
	$property->setAccessible(true);
	return $property->getValue($obj);
    }
}

/* accessProtected see https://stackoverflow.com/questions/20334355/how-to-get-protected-property-of-object-in-php
 answered Feb 5 '15 at 19:38 ; edited Nov 10 '15 at 14:01  [ EST / GMT -5 if my time]
user: drewish, https://stackoverflow.com/users/203673/drewish  */


