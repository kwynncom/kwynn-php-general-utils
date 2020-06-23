<?php 

/* This is a collection of code that is general enough that I use it in a number of projects. */

/* DATABASE USAGE EXAMPLE - the database stuff currently begins on line 36

  class radar_dao extends dao_generic {
    const db = 'radar';
	function __construct() {
	    parent::__construct(self::db);
	    $this->icoll    = $this->client->selectCollection(self::db, 'img');
      }
  } 

 */

require_once('kwshortu.php');

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


set_include_path(get_include_path() . PATH_SEPARATOR . '/opt/composer');
require_once('vendor/autoload.php');  
unset($__composer_autoload_files);
/* I do this unset, above, because often I am trying to keep a very clean set of active variables.  I have used this without harm since very 
roughly the (northern hemisphere) summer of 2019.  */

class kwmoncli extends MongoDB\Client {
    public function __construct() {
	parent::__construct('mongodb://127.0.0.1/', [], ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]);
    }

    public function selectCollection     ($db, $coll, array $optionsINGORED_see_below = []) {
	return new kwcoll($this->getManager(), $db, $coll, ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]); 
    }
}

class kwcoll extends MongoDB\Collection {
    public function upsert($q, $set) {
	return $this->updateOne($q, ['$set' => $set], ['upsert' => true]);
    }
}

class dao_generic  {
    
    protected $dbname;
    protected $client;
    
    const seqDB = 'seqs';
    
    public function __construct($dbname) {
	$this->dbname = $dbname;
	$this->client = new kwmoncli();
    }

    public function getSeq($name) { 

	$osr = self::getOldSeqInfo($name);
		
	$c = $this->client->selectCollection(self::seqDB, 'seqs');
	$q  = ['db' => self::seqDB, 'name' => $name];
	
	$this->setSeq($c, $q, $name, $osr);
	$ret = $c->findOneAndUpdate($q, [ '$inc' => [ 'seq' => 1 ]]);
	
	if ($osr) return $osr['seq'];
        return $ret['seq'];
    }
    
    private function setSeq($c, $q, $name, $oldr = false) {

	$res = $c->findOne($q);
	if ($res) return;
	
	$now = time();
	$c->createIndex(['db' => -1, 'name' => -1], ['unique' => true ]);
	
	$dat['db']   = self::seqDB;
	$dat['name'] = $name;

	if (!$oldr) {
	    $dat['seq'] = 1;
	    $now = time();
	    $dat['initts'] = $now;
	    $dat['initR' ] = date('r', $now);
	} else $this->popOldSeqDat($dat, $oldr);

	$c->insertOne($dat);
    }
    
    private function popOldSeqDat(&$dat, $oldr) {
	if (!$oldr) return;
	$dat['seq'] = $oldr['seq'];
	$dat['initR'] = $oldr['initR'];
	$dat['initts'] = strtotime($oldr['initR']);
    }
    
    private function getOldSeqInfo($name) {
	$oldsc = $this->client->selectCollection($this->dbname, 'seqs');
	$oldq  = ['_id' => $name];
	$osr = $oldsc->findOne($oldsc);
	if ($osr) {
	    $oldsc->drop();
	    return $osr;
	}
	
	return false;
    }
}


/* Kwynn's assert.  It's similar to the PHP assert() except it throws an exception rather than dying.  I use this ALL THE TIME.  
  I'm sure there are 100s if not 1,000s of references to this in my code. */
function kwas($data = false, $msg = 'no message sent to kwas()', $var = null) {
    if (!isset($data) || !$data) throw new Exception($msg); 
/* The isset may not be necessary, but I'm not touching anything I've used this much and for this long. */
}

/* make sure any timestamps you're using make sense: make sure you haven't done something weird and such: make sure you don't have zero 
values or haven't rolled over bits; make sure your time isn't way in the future or past. Obviously both min and max are somewhat arbitrary, but 
this has served it's purpose since roughly (northern hemisphere) summer 2019. */
function strtotimeRecent($strorts, $alreadyTS = false) {
    static $min = 1561500882; // June 25, 2019, depending on your tz
    static $max = false;
    
    if (!$alreadyTS) $ts = strtotime($strorts); 
    else	     $ts = $strorts;
    
    kwas($ts && $ts >= $min, 'bad string to timestamp pass 1 = ' . $strorts);
    
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

/* Often it's very useful to know if this is my local / own / test computer.  I want to know without revealing my machine name, which 
 * might be security sensitive.  2020/01/16 9:38pm - a brand new version.  Let's hope it works. */
function isKwDev() {
   $path = '/opt/kwynn/';
   $name = 'i_am_kwynn_local_dev_201704_to_2020_01.txt';
   return file_exists($path . $name);
}

function sslOnly($force = 0) { // make sure the page is SSL
    
    if (isKwDev() && !$force) return; // but don't force it if it's my machine and I don't have SSL set up.

    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
	header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	exit(0);
    }
}

function startSSLSession($force = 0) { // session as in a type of cookie
    if (session_id()) return;
    sslOnly($force);
    session_set_cookie_params(163456789); // over 5 years expiration
    session_start();
    $sid = session_id();
    kwas($sid && is_string($sid) && strlen($sid) > 5, 'startSSLSessionFail');
    return $sid;
}

function kwjae($o) { // JSON encode, echo, and exit
    header('application/json');
    echo json_encode($o);
    exit(0);
}

// ID whether you are in the Amazon Web Services cloud - See README.md 2020/06/22
// As of 2020/06/22, I consider this temporary, but it works well enough.
// For Apache, do this:
// /etc/apache2/sites-enabled$ head -n 3 000-default.conf
// <VirtualHost *:80>
//    SetEnv KWYNN_ID_201701 aws-nano-1
// 
// For cli/bash/shell, do this:
// $ tail -n 2 /etc/environment
// KWYNN_ID_201701=aws-nano-1

function isAWS() { 
    if (function_exists('apache_getenv') &&  apache_getenv('KWYNN_ID_201701') === 'aws-nano-1') return true;
    return getenv('KWYNN_ID_201701') === 'aws-nano-1'  ;
}

function iscli() { return PHP_SAPI === 'cli'; } 

// testing isAWS()
if (PHP_SAPI === 'cli' && $argc >= 2 && $argv[0] === __FILE__ && $argv[1] == 'isaws') echo (isAWS() ? 'Y' : 'N') . "\n";
