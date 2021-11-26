<?php

/* This is a collection of code that is general enough that I use it in a number of projects. */

require_once('kwshortu.php');
require_once(__DIR__ . '/lock.php');
require_once('machineID.php');
require_once(__DIR__ . '/base62/base62.php'); // both base62() and didCLICallMe()

/* user agent, for when a server will ignore a request without a UA.  I am changing this 2020/01/16.  I'm moving towards releasing this file
 * to GitHub, so I should show myself to be properly open source fanatical. */
function kwua() { return 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36'; }

/* The major purpose of this (below) is to make warnings and notices an error.  I have found it's best to "die" on warnings and uncaught exceptions. */
function kw_error_handler($errno, $errstr, $errfile, $errline) {
    echo "ERROR: ";
    echo pathinfo($errfile, PATHINFO_FILENAME) . '.';
    echo pathinfo($errfile, PATHINFO_EXTENSION);
    echo ' LINE: ' . $errline . ' - ' . $errstr . ' ' . $errfile;
    exit(37); // an arbitrary number, other than it should be non-zero to indicate failure
}

set_error_handler('kw_error_handler');

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

/* Kwynn's assert.  It's similar to the PHP assert() except it throws an exception rather than dying.  I use this ALL THE TIME.  
  I'm sure there are 100s if not 1,000s of references to this in my code. */
function kwas($data = false, $msg = 'no message sent to kwas()', $code = 12345) {
    if (!isset($data) || !$data) throw new Exception($msg, $code); 
/* The isset may not be necessary, but I'm not touching anything I've used this much and for this long. */
}

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

function iscli() { return PHP_SAPI === 'cli'; } 

function sslOnly($force = 0) { // make sure the page is SSL
    
    if (iscli()) return;
    
    if (isKwDev() && !$force) return; // but don't force it if it's my machine and I don't have SSL set up.

    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
	header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	exit(0);
    }
}

function startSSLSession($force = 0) { // session as in a type of cookie
    if (session_id()) return session_id();
    sslOnly($force);
    session_set_cookie_params(163456789); // over 5 years expiration
    session_start();
    $sid = vsidod();
    vsidod($sid);
    return $sid;
}

function vsidod() { 
    $sid = session_id();
    kwas($sid && is_string($sid), 'startSSLSessionFail 1');
    $prr = preg_match('/^[A-Za-z0-9]{20}/', $sid);
    kwas($prr, 'startSSLSessionFail 2'); unset($prr);
    return $sid;
}

function kwjae($o) { // JSON encode, echo, and exit
    header('application/json');
    echo json_encode($o);
    exit(0);
}

function didCLICallMe($callingFile) { // $call with __FILE__
	return base62::didCLICallMe($callingFile);
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
					    //  123456789 digits
if (!defined('M_BILLION')) define('M_BILLION', 1000000000);
else kwas(false, 'billion constant already defined');
		 //  123456
define('M_MILLION', 1000000);