<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/opt/composer');

$va = 'vendor/autoload.php';
$autolr = true;
$ci = 'include_exists';
if (function_exists($ci)) $autolr = $ci($va); unset($ci);

if ($autolr) {
require_once($va); unset($va, $__composer_autoload_files, $autolr);

if (class_exists('MongoDB\Client')) {

class kw3moncli extends MongoDB\Client {

    
    public function __construct() {
		$cs  = '';
		$cs .= 'mongodb://127.0.0.1';
		$cs .= '/';
		parent::__construct($cs, [], ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]);
    }

    public function selectCollection     ($db, $coll, array $optionsINGORED_see_below = []) {
		return new kw3mdbcoll($this->getManager(), $db, $coll, ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]); 
    }
}

class kw3mdbcoll extends MongoDB\Collection {
	
	public function upsertMany($q, $set) {
		return $this->upsert($q, $set, 2);
	}
	
	
    public function upsert($q, $set, $oom = 1, $upc = true) {
		if ($upc) {
			$now = time();
			$set['up_r' ] = date('r', $now);
			$set['up_ts'] = $now;
		}
		
		if ($oom === 1) $r = $this->updateOne ($q, ['$set' => $set], ['upsert' => true]);
		else			$r = $this->updateMany($q, ['$set' => $set]);
		
		$sum  = 0;
		$mc   = $r->getMatchedCount();
		$sum += $r->getUpsertedCount();
		$sum += $r->getModifiedCount();
		$sum += $mc;
		kwas($sum >= 1, "kw3mdbcoll upsert sum = $sum when should be >= 1");
		if ($mc === 0 && $upc) {
			$ca['cre_r' ] = $set['up_r'];
			$ca['cre_ts'] = $set['up_ts'];
			if ($oom === 1) $this->updateOne ($q, ['$set' => $ca], ['upsert' => true]);
		}
		return $r;
    }
	
	public function find($q = [], $o = []) { 
		return parent::find($q, $o)->toArray();	
	}
	
	public function insertOne($dat, $o = []) {
		if (!isset($o['kwnos'])) { if (!isset($dat['_id'])) $dat['_id'] = dao_generic_3::get_oids(); }
		else unset($o['kwnos']);
		
		parent::insertOne($dat, $o);
	}

}

class dao_generic_3  {
    
    private $dbname;
    protected $client;
    
    protected function __construct($dbname) {
		$this->dbname = $dbname;
		$this->client = new kw3moncli();
    }
	
	protected function creTabs($ts) {
		foreach($ts as $k => $t) {
			$v = $k . 'coll';
			$this->$v = $this->client->selectCollection($this->dbname, $t);
		}	
    }
	
	public static function get_oids($rand = false) {
		$o   = new MongoDB\BSON\ObjectId();
		$s   = $o->__toString();
		$ts  = $o->getTimestamp(); unset($o);
		$tss = date('md-Hi-Y-s', $ts); unset($ts);
		$fs  = $tss . 's-' .  substr($s  ,  8);
		if ($rand) 
		$fs .= '-' . base62(15); unset($tss, $s, $rand);
		return $fs;
	}

	public static function oidsvd($sin, $ckrand = false) {
		kwas(is_string($sin), 'bad id - 1 - 234');
		
		$res = ['/^[\w-]{35}$/',
				'/^[\w-]{51}$/'	];
		$rr = preg_match($res[1], $sin, $ms);
		if ($rr) return $ms[0];
		if ($ckrand) kwas(0, 'not oids with rand');
		kwas(preg_match($res[0], $sin, $ms), 'not valid oids - either type');
		return $ms[0];
	}
	
} // class
} // if MongoDB stuff exists
} // if autoload exists

unset($autolr, $va);
