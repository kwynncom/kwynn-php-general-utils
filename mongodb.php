<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/opt/composer');

$va = 'vendor/autoload.php';
$autolr = true;
$ci = 'include_exists';
if (function_exists($ci)) $autolr = $ci($va); unset($ci);

if ($autolr) {
require_once($va); unset($va, $__composer_autoload_files, $autolr); // I unset $__composer... because (often) I am trying to keep a very clean set of active variables. 

if (class_exists('MongoDB\Client')) {

class kwmoncli extends MongoDB\Client {
    
    const altportf = '/var/kwynn_www/altmdbport.txt';
    
    public function __construct($altport = false) {
	$cs  = '';
	$cs .= 'mongodb://127.0.0.1';
	if ($altport) $cs .= ':' . self::getAltPort();
	$cs .= '/';
	parent::__construct($cs, [], ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]);
    }

    public function selectCollection     ($db, $coll, array $optionsINGORED_see_below = []) {
	return new kwcoll($this->getManager(), $db, $coll, ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]); 
    }
    
    private static function getAltPort() {
	static $f = self::altportf;
	
	kwas(file_exists($f), 'MongoDB Kwynn alt port file does not exist');
	$port = intval(trim(file_get_contents($f))); 
	kwas(is_integer($port) && $port >= 1, 'bad port - MongoDB Kwynn alt port');
	return $port;
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
} // if MongoDB stuff exists
} // if autoload exists

unset($autolr, $va);
