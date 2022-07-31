<?php

/* This is a collection of code that is general enough that I use it in a number of projects. */

define('KW_DEFAULT_COOKIE_LIFETIME_S', 400000);

require_once('kwshortu.php');
require_once('mongodb3.php');
require_once(__DIR__ . '/lock.php');
require_once('machineID.php');
require_once(__DIR__ . '/base62/base62.php'); // both base62() and didCLICallMe()
require_once(__DIR__ . '/mongoDBcli.php');
require_once(__DIR__ . '/' . 'inonebuf.php');
require_once(__DIR__ . '/fork.php');

   		         //  123456789 digits
define('M_BILLION', 1000000000);
				 //  123456
define('M_MILLION', 1000000);
define('DAY_S', 86400);

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


/* Tests whether it's safe to include a file--file_exists() does not account for the include path.  My function does.  
 * Returns true for safe / exists and false for unsafe / does not exist.
 * It is ironic to create a handler and then reset it, but that's the way this file worked out. */
function include_exists($f) {
    set_error_handler('kw_null_error_handler'); // because fopen failure emits a warning
    $r = fopen($f, 'r', true);
    set_error_handler('kw_error_handler');
    if ($r) {
	fclose($r);
	require_once($f); // Kwynn 2020/12/29 12:28am
	return true;
    }
    return false;
}

function require_once_ifex($f) { include_exists($f); } // Kwynn 2020/12/29 12:31am

function kw_null_error_handler($errno, $errstr, $errfile, $errline) { 
    return;
}

// make sure mongodb.php file exists and include if so
$minc = __DIR__ . '/mongodb.php';
if (file_exists($minc)) require_once($minc); unset($minc); // unset so as to not clutter global variable space

/* I am finally defining myself as a null function.  I am sick and tired of creating fake variables for something for NetBeans' debugger to set a 
 * breakpoint to.  A breakpoint has to have something there.  So "Kwynn's null" is recursively kwynn() */
function kwynn() {}


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

/* in case you are trying to indicate whether an HTML page has changed.  This is useful if you're doing a single page application.  You want to 
 * communicate from your server to a client whether a database entry or underlying file has changed.  As of January, 2020, this is not well 
 * tested, but I leave it. Also, I don't remember why I gave a default ts or return if it's my machine.  */
function kwTSHeaders($tsin = 1568685376, $etag = false) { // timestamp in; etag is an HTTP specified concept
    
    if (isKwDev()) return; // defined below
    
    if (!$etag) $etag = $tsin;
    
    $gmt = new DateTimeZone('Etc/GMT+0');
    $serverDTimeO = new DateTime("now", $gmt);
    $serverDTimeO->setTimestamp($tsin);
    $times = $serverDTimeO->format("D, d M Y H:i:s") . " GMT";
    
    header('Last-Modified: ' . $times);
    header('ETag: ' . '"' . $etag . '"');

    if ( 1 &&
    (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
	&& $_SERVER['HTTP_IF_MODIFIED_SINCE'] === $times)
	||
	(isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
	
	    trim($_SERVER['HTTP_IF_NONE_MATCH']) === $md5
		)
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
function startSSLSession() {
	try {
		$vsc10 = vsidod();
		return $vsc10;
	} catch(Exception $ex) {}
    sslOnly();
	kwscookie();
	session_start();
    return vsidod();
}

function contSSLSession() {
	if (isset($_COOKIE['PHPSESSID'])) return startSSLSession();
	return false;
}

function vsidod() { 
    $sid = session_id();
    kwas($sid && is_string($sid), 'start SSL Session Fail 1');
    $prr = preg_match('/^[A-Za-z0-9]{20}/', $sid);
    kwas($prr, 'start SSL Session Fail 2'); unset($prr);
    return $sid;
}

function kwscookie(string $kin = null, $v = null, $copt = null) {
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
	
	if ($iss) session_set_cookie_params($o);
	else			   setcookie($kin, $v, $o);


}	

function kwjae($din, $isj = false) { // JSON encode, echo, and exit
    header('Content-Type: application/json');
	if (!$isj) $j = json_encode($din, JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES);
	else       $j = $din;
    echo($j);
    exit(0);
}

function base62($len) { return base62::get($len); }

/* base62() as derived from https://kwynn.com/t/7/11/blog.html
 * Entry dated: Feb 2, 2018 - base62
 * random base62 - Kwynn.com, 2018/02/02 3:11AM EST, UQID: VMbAlZQ13ojI
 * What I published on my web site is the CLI standalone command version */

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
