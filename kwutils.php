<?php

/* This is a collection of code that is general enough that I use it in a number of projects. */

define('KW_DEFAULT_COOKIE_LIFETIME_S', 345600);

require_once('kwshortu.php');
require_once('mongodb3.php');
require_once('mongodb4.php');
require_once(__DIR__ . '/lock.php');
require_once('machineID.php');
require_once(__DIR__ . '/base62/base62.php'); // both base62() and didCLICallMe()
require_once(__DIR__ . '/mongoDBcli.php');
require_once(__DIR__ . '/' . 'inonebuf.php');
require_once(__DIR__ . '/fork.php');
require_once(__DIR__ . '/jscss.php');
require_once(__DIR__ . '/sntp.php');
require_once(__DIR__ . '/filePtrTracker.php');
require_once(__DIR__ . '/ips.php');
   		         //  123456789 digits
define('M_BILLION', 1000000000);
				 //  123456
define('M_MILLION', 1000000);
define('DAY_S', 86400);

function kwSetTimezone() {
	static $iamset = false;
	if ($iamset) return;
	$iamset = true;
	
	$f = '/etc/timezone';
	
	if (!is_readable($f)) return;
	$t = file_get_contents('/etc/timezone');
	if (!$t || !is_string($t)) return;
	$t = trim($t);
	if (!$t) return;
	date_default_timezone_set($t);
	
}

kwSetTimezone();

function dr() : string {
	static $default = '/opt/www/git20/';
	$p = kwifs($_SERVER, 'DOCUMENT_ROOT');
	if ($p) return $p . '/';
	if (is_readable($default)) return $default;
	return '';

}

function amDebugging() { static $f = 'xdebug_is_debugger_active'; return function_exists($f) && $f(); }

function roint($x) { return intval(round($x)); }

function setVTZ($sin) {
	$sin = trim($sin);
	try {
		kwas(preg_match('/^[A-Za-z\/_]{1,75}$/', $sin), 'bad tz form');
		$o = new DateTimeZone($sin);
		kwas($o->getName() === $sin, 'in out mismatch tz');
		date_default_timezone_set($sin);
		return $o;
	} catch (Exception $ex) { }
	
	return new DateTimeZone(date_default_timezone_get());
}

// used just below
function tuf_path($prefix, $suffix = '') {
	$p  = '';
	$p .= '/tmp/';
	$p .= $prefix . '_';
	$p .= get_current_user();
	if ($suffix) $p .= '.' . $suffix;
	return $p;
}

// write to temporary user file once - do nothing if it exists; returns the file path
function tuf_once($contents, $prefix, $suffix = '') {

	$p = tuf_path($prefix, $suffix);

	if (file_exists($p)) return $p;

	unset($prefix, $suffix);

	kwas($r = fopen($p, 'c'), "fopen fail $p");
	kwas(flock($r, LOCK_EX), "lock on $p failed");
	kwas(chmod($p, 0600), "cannot chmod $p");
	kwas(fwrite($r, $contents) === strlen($contents), "write to $p failed");
	kwas(flock($r, LOCK_UN), "unlock failed for $p");
	fclose($r);
	chmod($p, 0400);
	
	return $p;
}

function tuf_get ($prefix, $suffix = '') {
	$p = tuf_path($prefix, $suffix);
	if (file_exists($p)) return file_get_contents($p);
	return false;
}

// only used in main project and new msg / msgs web form - 2022/01
function kwifse($a, $k, $ifnot = false) { // if set return, else return ifnot
	if (isset(   $a[$k])) 
		return   $a[$k];
	
	return $ifnot;
}

function kwam(...$aa) { 
	$ra = [];
	foreach($aa as $i => $v) {
		if (!is_array($v) && $v) $v = [$v];
		if ($v) $ra = array_merge($ra, $v);
	}
	return $ra;
}

function kwua() { return 'Mozilla/5.0 (X11; Linux x86_64; rv:101.0) Gecko/20100101 Firefox/101.0'; }

function kw_null_error_handler($errno, $errstr, $errfile, $errline) { 
    return;
}

// make sure mongodb.php file exists and include if so
$minc = __DIR__ . '/mongodb.php';
if (file_exists($minc)) require_once($minc); unset($minc); // unset so as to not clutter global variable space

/* I am finally defining myself as a null function.  I am sick and tired of creating fake variables for something for NetBeans' debugger to set a 
 * breakpoint to.  A breakpoint has to have something there.  So "Kwynn's null" is recursively kwynn() */
function kwynn() {}

function kwnull() {}

/* make sure any timestamps you're using make sense: make sure you haven't done something weird and such: make sure you don't have zero 
values or haven't rolled over bits; make sure your time isn't way in the future or past. Obviously both min and max are somewhat arbitrary, but 
this has served it's purpose since roughly (northern hemisphere) summer 2019. */
function strtotimeRecent($strorts) {
    static $min = 1561500882; // June 25, 2019, depending on your tz
    static $max = false;
    
    $strorts = trim($strorts);

    $alreadyTS = false;
    if (is_numeric($strorts)) $alreadyTS = true;
    
    if (!$alreadyTS) $ts = strtotime($strorts); 
    else	     $ts = intval($strorts);
    
    kwas($ts && is_integer($ts) && $ts >= $min, 'bad string to timestamp pass 1 = ' . $strorts);
    
    if (!$max) $max = time() + 87000; kwas($ts < $max, 'bad string to timestamp pass 2 = ' . $strorts);

    return $ts;
}

// I've found changing timezones to be oddly difficult, so this works:
function dateTZ($format, $ts, $tz) {
    
    $dateO = new DateTime();
    $dateO->setTimestamp($ts);
    $dateO->setTimezone(new DateTimeZone($tz));
    return $dateO->format($format);
}

// Get XML / HTML DOM object from the HTML.
function getDOMO($html) {
	$ddO = new DOMDocument;
	libxml_use_internal_errors(true); // Otherwise non-valid HTML will throw warnings and such.  
	$ddO->loadHTML($html);
	libxml_clear_errors();	
	return $ddO;
}

function kwTSHeaders(int $tsin = 0, string $etag = '') { // timestamp in; etag is an HTTP specified concept
    
	if (!$tsin) $tsin = time();
	
    if (!$etag) $etag = '' . $tsin;
    
    $gmt = new DateTimeZone('Etc/GMT+0');
    $serverDTimeO = new DateTime("now", $gmt);
    $serverDTimeO->setTimestamp($tsin);
    $times = $serverDTimeO->format("D, d M Y H:i:s") . " GMT";
    
    header('Last-Modified: ' . $times);
    header('ETag: ' . '"' . $etag . '"');

    if (	kwifs($_SERVER, 'HTTP_IF_NONE_MATCH'	) === $etag
		 ||	kwifs($_SERVER, 'HTTP_IF_MODIFIED_SINCE') === $times
		) { 
				http_response_code(304); // 304 equals "not modified since last you checked" as I remember.
				exit(0);
		}  
}

function sslOnly() { // make sure the page is SSL
	
	// if (file_exists('/var/kwynn/i_am_Kwynn_local_dev_2021_11')) return;
    if (iscli()) return;
    kwas(kwifs($_SERVER, 'HTTPS') === 'on', 'SSL only invoked 0136');
}

/* WARNING: you have to use this before there is a chance of output, otherwise you may get the dreaded 
 * "cannot be changed after headers have already been sent" */
function startSSLSession(int $life = KW_DEFAULT_COOKIE_LIFETIME_S) {
	
	if (PHP_SAPI === 'cli') {
		return;
	} else {
		kwnull();
	}
	
	try {
		$vsc10 = vsidod();
		return $vsc10;
	} catch(Exception $ex) {}
    sslOnly();
	kwscookie(null, null, $life);
	session_start(['cookie_lifetime' => $life]);
    return vsidod();
}

function contSSLSession() : string {
	if (isset($_COOKIE['PHPSESSID'])) return startSSLSession();
	return '';
}

function vsidod() { 
    $sid = session_id();
	$isv = isvsid($sid);
    kwas($isv, 'start SSL Session Fail 2'); unset($prr);
    return $sid;
}

function isvsid($sid) : bool {
    kwas($sid && is_string($sid), 'start SSL Session Fail 1');
    $prr = preg_match('/^[A-Za-z0-9\-_]{20}/', $sid); // Drupal 7 uses _ and -, too
	return $prr >= 1;
}

function kwscookie(string $kin = null, $v = null, $copt = null) {
	
	if (PHP_SAPI === 'cli') {
		return;
	} else {
		kwnull();
	}
	
	$iss = !$kin;
	if (!$iss) $now = time();
	$o = [];
	
	if (isset( $copt['kwcex'])) {
		$cxo = $copt['kwcex'];
		unset ($copt['kwcex']);
	} else $cxo = $copt;

	if (!$iss && $cxo === 0) $o['expires'] = 0; 
	else {
		if ((is_string($cxo) || $cxo === false) &&  $iss) $o['lifetime'] = -100000; // will this work?
		if ((is_string($cxo) || $cxo === false) && !$iss) $o['expires' ] = $now - 100000;
		if (is_null	  ($cxo)					  && !$iss) $o['expires' ] = $now + KW_DEFAULT_COOKIE_LIFETIME_S;
		if (is_null	  ($cxo)					  &&  $iss) $o['lifetime'] = KW_DEFAULT_COOKIE_LIFETIME_S;
		if (is_numeric($cxo)					  &&  $iss) $o['lifetime'] = $cxo;
		if (is_numeric($cxo)					  && !$iss && $cxo <= M_BILLION) 
														$o['expires' ] = $cxo + $now;
		if (is_numeric($cxo)					  && !$iss && $cxo >  M_BILLION) 
														$o['expires' ] = $cxo;
		if (is_numeric($cxo)					  &&  $iss) $o['lifetime'] = $cxo;
	}
		
	$ds = ['secure' => true, 'httponly' => true, 'samesite' => 'Strict', 'path' => '/'];
	if (!$copt || !is_array($copt)) $o = kwam($o, $ds);
	else {
		$o = kwam($copt, $o);
		foreach($ds as $kt => $vt) if (!isset($copt[$kt]))  $o[$kt] = $ds[$kt];
	}
	
	if ($iss) {
		session_set_cookie_params($o);
	}
	else			   setcookie($kin, $v, $o);


}	

function kwjae($din, $isj = false) { // JSON encode, echo, and exit
    if (!iscli()) header('Content-Type: application/json');
	if (!$isj) $j = json_encode($din, JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES);
	else       $j = $din;
    echo($j);
    exit(0);
}

class stddev { // 2021/01/12 11:01pm - into kwutils
    public function __construct() {
	$this->sum = 0;
	$this->dat = [];
    }
    
    public function put($din) {
	if (!is_numeric($din)) return;
	$this->sum  += $din;
	$this->dat[] = $din;
    }
    
    public function get() {
	$n = count($this->dat);
	if ($n === 0) return null;
	$avg = $this->sum / $n;
	
	$min = PHP_INT_MAX;
	$max = PHP_INT_MIN;
	
	$acc = 0;
	foreach($this->dat as $v) { 
	    $acc += pow($v - $avg, 2);
	    if ($v < $min) $min = $v;
	    if ($v > $max) $max = $v;
	}
	$stdd = sqrt($acc / $n);
	return ['a' => $avg, 's' => $stdd, 'n' => $n , 'min' => $min, 'max' => $max];
    }
}

function kwnohup($cmdin) { // This does NOT seem to work if run within NetBeans.  

	$cmdf = 'nohup ' . $cmdin . ' < /dev/null > /dev/null 2>&1 & echo $! ';

	$pids = trim(shell_exec($cmdf));
	if ($pids && is_numeric($pids)) {
		$pid = intval($pids);
		if ($pid > 0) return $pid;
	}
	
	return 0;
}

function kwtouch(string $f,  string $t = '', int $perm = 0600, int $flags = 0) : bool {
	kwas(file_put_contents($f, '', FILE_APPEND) === 0, 'kwtouch fail 1-2325');
	kwas(chmod($f, $perm), 'kwtouch cannot change perm - kwutils');
	if ($t) {
		kwas(file_put_contents($f, $t, $flags) === strlen($t), 'kwtouch fail 2-2325');
	}
	return true;
}

function kwtrufl(array $ain, string $fin = '') : array {
	$ret = [];
	foreach($ain as $k => $v) {
		if ($fin) $ret[$v[$fin]] = true;
		else $ret[$v] = true;
		
	}
	return $ret;
}
