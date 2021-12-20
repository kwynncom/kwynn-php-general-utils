<?php

require_once('/opt/kwynn/kwutils.php');
require_once(__DIR__ . '/utils/date.php');
require_once(__DIR__ . '/config.php');

class machine_id {
    
    const minStrlenW = 7;
    
    const idBase = '/sys/class/dmi/id/';
    const idFileInit  = 'chassis_vendor';
    const idFilesU    = ['product_name', 'product_serial'];
    const idFilesReal = ['product_uuid'];
    const idFilesAWS  = ['board_asset_tag'];
    const idPublic    = ['chassis_vendor', 'product_name', 'board_asset_tag', 'isAWS', 'public_name', 'private_field_count'];
    
    const ofBase    = '/tmp/';
    const ofPrivate = 'midpr';
    const ofPublic  = 'midpu';
    const ofsfx     = '_namespace_kwynn_com_2020_12_mid_1';
    
    const midv = 'v0.0.13 - 2020/12/31 5:23pm+';
    
    const testUntil = '2015-12-13 19:10';
    
    private static function isTest() { return time() < strtotime(self::testUntil);  }
    
    private static function doArgs() {
	global $argc;
	global $argv;
	static $key = '-clean';
	
	$exit = false;
	
	for ($i=1; $i < $argc; $i++) {
	    if (substr($argv[$i], 0, strlen($key)) === '-clean') self::rm();
	    if ($argv[$i] === '-cleanonly') $exit = true;
	}
	
	if ($exit) exit(0);
    }

    public static function rm() {
	
	$ps = [self::getPublicPath(),  self::getPrivatePath ()];
	foreach($ps as $p) {
	    if (!file_exists($p)) continue;
	    $r = trim(shell_exec('rm -f ' . $p . ' 2>&1 ')); 
	    if ($r) echo("$r\n");
	    
	}
    }
    
    
    private static function setBootInfo(&$aref) {
	$uo = uptime();
	$ts = $uo['Ubest'];
	$aref['Uboot'] = $ts;
	$aref['rboot'] = date('r', $ts);
    }
    
    public static function get($showstdout = false) {
	
	self::doargs();
	
	$ret = self::getExisting();
	if (!$ret) {
	    $a = self::get20();
	    $a30 = self::get30($a); unset($a);
	    $ret = $a30; unset($a30);
	}
	if ($showstdout) var_dump($ret);
	return $ret;
    }
    
    public static function getExisting() {
	$p = self::getPublicPath();
	if (!file_exists($p)) return false;
	$j = file_get_contents($p);
	$a = json_decode($j, 1);
	if ($a['midv'] === self::midv) return $a;
	return false;
    }
    
    private static function isPublic($fin) { return in_array($fin, self::idPublic);   }
    
    private static function getPublicPath() { return self::ofBase . self::ofPublic . self::ofsfx;  }
    
    private static function getPrivatePath() { return self::ofBase . self::ofPrivate . self::ofsfx; }
    
    private static function get30($ain) {
    	self::outPrivate($ain);
	$r = [];
	foreach($ain as $k => $v) {
	    $fn = pathinfo($k, PATHINFO_FILENAME);
	    if (self::isPublic($fn)) $r[$k] = $v;
	}
	
	machine_id_specific::set($ain, $r);
	$r['midv'] = self::midv;
	$now = time();
	$r['at'] = $now;
	$r['atr'] = date('r', $now);
	$created = mid_creation_date::get($r['isAWS']);
	$r['increated'] = $created;
	$r['increatedR'] = date('r', $created);

	self::setBootInfo($r);
	
	$p = self::getPublicPath();
	
	$json = json_encode($r);
	kwas(file_put_contents($p, $json) === strlen($json), 'public file_put failed machine_id');
	kwas(chmod($p, 0444), "chmod public failed on $p - machine_id out()");
	
	return $r;
    }
    
    private static function outPrivate($ain) {

	$prf = self::getPrivatePath();
	if (file_exists($prf)) kwas(unlink($prf), "cannot delete existing $prf - machine_id outPrivate()");
	touch($prf);
	kwas(chmod($prf, 0600), "machine_id chmod failed out()");
	$out = $ain['private_string'];
	kwas(file_put_contents($prf, $out) === strlen($out), 'file_put private failed - machine_id');
	chmod($prf, 0400);
    }
    
    private static function get20() {
	$a[0] = self::idFileInit;
	$a    = array_merge($a, self::idFilesU);
	$minit = self::getMin(self::idFileInit);
	$r = [];
	if ($minit['s'] === 'Amazon EC2') { $a2 = self::idFilesAWS ; $r['isAWS'] = true; }
	else				  { $a2 = self::idFilesReal; $r['isAWS'] = false; }
	

	$a = array_merge($a, $a2);

	$ss = ['public_name', 'private_string'];
	
	$r['public_name'] = $r['private_string'] = '';
	
	$prcnt = 0;
	foreach($a as $f) {
	    $ma = self::getMin($f);
	    $s  = $ma['s'];
	    $ts = $ma['s'] . ' ';
	    $r['private_string']   .= $ts;
	    if (self::isPublic($ma['p']))
	    $r['public_name']    .= $ts;
	    else if ($s) ++$prcnt;
	    $r[$ma['p']] = $s;
	}
	
	$r['private_field_count'] = $prcnt;
	
	foreach($ss as $ts) $r[$ts] = trim($r[$ts]);
		
	return $r;
    }
    
    private static function getMin($fin) {
	
	if (self::isTest() && !self::isPublic($fin)) return ['s' => 'test only', 'p' => $fin];
	
	$p = self::idBase . $fin;
	$s = trim(shell_exec('cat ' . $p . ' 2> /dev/null '));
	$st = preg_replace('/\W/', '', $s);
	$re = '/^[\w]{' . self::minStrlenW . '}/';
	if (!preg_match($re, $st, $m)) $s = '';
	return ['s' => $s, 'p' => $fin];
    }


}

if (didCLICallMe(__FILE__)) machine_id::get(true);
