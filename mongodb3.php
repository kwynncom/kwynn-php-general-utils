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
    public function upsert($q, $set) {
		$now = time();
		$set['up_r' ] = date('r', $now);
		$set['up_ts'] = $now;
		$r = $this->updateOne($q, ['$set' => $set], ['upsert' => true]);
		$sum  = 0;
		$mc   = $r->getMatchedCount();
		$sum += $r->getUpsertedCount();
		$sum += $r->getModifiedCount();
		$sum += $mc;
		kwas($sum >= 1, "kw3mdbcoll upsert sum = $sum when should be >= 1");
		if ($mc === 0) {
			$ca['cre_r' ] = $set['up_r'];
			$ca['cre_ts'] = $set['up_ts'];
			$this->updateOne($q, ['$set' => $ca], ['upsert' => true]);
		}
		return $r;
    }
	
	public function find($q = [], $o = []) { return parent::find($q, $o)->toArray();	}

}

class dao_generic_3  {
    
    private $dbname;
    private $client;
    
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
	
} // class
} // if MongoDB stuff exists
} // if autoload exists

unset($autolr, $va);
