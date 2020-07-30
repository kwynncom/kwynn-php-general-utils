<?php // 2020/07/06 7:51pm

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
require_once('machineID.php');

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
    global $argv;
    if (!isset($argv[0]) || !iscli()) return false;
    
    $cf = basename($callingFile);
    $af = basename($argv[0]);
    
    return $cf === $af;
}

function base62($len = 20) { // see sourcing below the func

    $basea = [ord('A'), ord('a'), ord('0')]; // preg [A-Za-z0-9]

    for ($i=0, $rs = ''; $i < $len; $i++)
       for ($j=0, $ri = random_int(0, 61); $j < count($basea); $j++, $ri -= 26)
	    if ($ri < 26) { $rs .= chr($basea[$j] + $ri); break; }

    return $rs;
}
/* base62() as derived from https://kwynn.com/t/7/11/blog.html
 * Entry dated: Feb 2, 2018 - base62
 * random base62 - Kwynn.com, 2018/02/02 3:11AM EST, UQID: VMbAlZQ13ojI
 * What I published on my web site is the CLI standalone command version */