<?php

require_once('/opt/kwynn/kwutils.php');
require_once('config.php');

class AWSCryptoV { 
    
     
    const upfx    = 'http://169.254.169.254/latest/dynamic/instance-identity/';
    const pubf    = 'AWSPubKey_2020_01_1.txt';
    const pubsha256 = 'c02cf542248f66abbea9df49591344d161510d63337b0fd782c4ecd5e959f07a';
    const publen    = 1074;
    const pubp	    = __DIR__ . '/';
    const tmp     = '/tmp/iid_kwns202/';
    const iiddocs    = ['document', 'pkcs7', 'rsa2048', 'signature'];
    
    public function getPut($inp, $inf, $outp) {
	$p = $inp . $inf;
	
	if ($inf !== self::pubf && file_exists($outp)) kwas(unlink($outp), "failed to delete $outp - AWS ID crypto");
	$c = file_get_contents($p); 
	$b = file_put_contents($outp, $c);
	kwas(strlen($c) === $b, 'file_put byte fail AWS crypto');
	$this->$inf = $c;
	return $c;
    }

    private function setValidPub() {
	$res = $this->getPut(self::pubp, self::pubf, self::tmp . self::pubf, self::pubf);	
	$hash = hash('sha256', $res);
	kwas($hash === self::pubsha256, 'pub AWS key hash fail'); unset($hash);
	$l = strlen($res);
	kwas($l === self::publen, 'pub AWS key size fail'); unset($l);
    }
    
    private function crypto() {
    
	if (!file_exists(self::tmp)) mkdir(self::tmp);
	chmod(self::tmp, 0700);

	$this->setValidPub();

	foreach(self::iiddocs as $f) $this->getPut(self::upfx, $f, self::tmp . $f, $f);
	$this->allSigs = $this->pkcs7 . $this->rsa2048 . $this->signature;
	$vsr = machine_id_specific::verifyWithSigsOrDie($this->allSigs);
	$docFromV = self::getVerifiedDoc();
	kwas($this->document === $docFromV, 'first and 2nd document read do not match');
	
	echo('v 953' . "\n");

	exit(0);
}

private static function getVerifiedDoc() {
    $c = self::getVCmd();
    $pd = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    $inpr = proc_open($c, $pd, $pipes); unset($c, $pd);
    $docr = fread($pipes[1], 10000);    fclose($pipes[1]);
    $vrr  = fread($pipes[2], 10000);    fclose($pipes[2]); unset($pipes); proc_close($inpr); unset($inpr);
    $vr   = trim($vrr); unset($vrr);
    kwas($vr === 'Verification successful', 'AWS EC2 ID doc verification failed - crypto'); unset($vr);
    return $docr;
}

private static function getVCmd() {
    
    $pkmp = self::getModdedPKFPath();
    
    $c  = 'openssl smime -verify -in ';
    $c .= $pkmp . ' ';
    $c .= '-inform PEM -content ';
    $c .= self::tmp . 'document ';
    $c .= '-certfile ';
    $c .= self::tmp . self::pubf;
    $c .= ' -noverify ';
    return $c;
}

private static function getModdedPKFPath() {
    $pkfn =  'pkcs7';
    $pks  = "-----BEGIN PKCS7-----\n";
    $pkp = self::tmp . $pkfn;
    $pks .= file_get_contents($pkp);
    $pks .=  "\n-----END PKCS7-----\n";
    $pkmp = $pkp . '_mod';
    file_put_contents($pkmp, $pks); unset($pks, $pkfn);    
    return $pkmp;
}

public function __construct() {
    $this->crypto();
}

}

if (didCLICallMe(__FILE__)) new AWSCryptoV();

// https://medium.com/@bugzeeeeee/cryptographic-adventures-ec2-instance-identity-verification-in-javascript-b0edfad09de9
    