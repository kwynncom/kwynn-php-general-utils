<?php

require_once(__DIR__ . '/composer.php');

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
		if ($upc) self::addCreUp($set, false);
		
		if ($oom === 1) $r = $this->updateOne ($q, ['$set' => $set], ['upsert' => true]);
		else			$r = $this->updateMany($q, ['$set' => $set]);
		
		$sum  = 0;
		$mc   = $r->getMatchedCount();
		$sum += $r->getUpsertedCount();
		$sum += $r->getModifiedCount();
		$sum += $mc;
		kwas($sum >= 1, "kw3mdbcoll upsert sum = $sum when should be >= 1");
		if ($mc === 0 && $upc) {
			self::addCreUp($set, true);
			if ($oom === 1) $this->updateOne ($q, ['$set' => $set], ['upsert' => true]);
		}
		return $r;
    }
	
	public static function addCreUp(&$set, $crec = true) {
		
		if (!isset($set['up_ts' ]))			 $set['up_ts' ] = time();
		if (!isset($set['up_r'  ]))			 $set['up_r'  ] = date('r', $set['up_ts']);
		if (!isset($set['cre_ts']) && $crec) $set['cre_ts'] = $set['up_ts'];
		if (!isset($set['cre_r' ]) && $crec) $set['cre_r' ] = $set['up_r'];
	}
	
	public function findc($q = [], $o = []) { return parent::find($q, $o);			  }
	public function find ($q = [], $o = []) { return parent::find($q, $o)->toArray(); }
	
	public function insertOne($dat, $o = []) {
		if (!isset($o['kwnos'])) { if (!isset($dat['_id'])) $dat['_id'] = dao_generic_3::get_oids(); }
		else unset($o['kwnos']);
		
		if (!isset($o['kwnoup']))
			self::addCreUp($dat);
		else unset($o['kwnoup']);
				
		return parent::insertOne($dat, $o);
	}

}

#[\AllowDynamicProperties] // PHP 8.2
class dao_generic_3  {
	
	const defOidsFmt = 'md-Hi-Y-s'; // usage below assumes seconds at the end *if* using this default
    
    private $dbname;
    protected $client;
    
    public function __construct($dbname) {
		$this->dbname = $dbname;
		$this->client = new kw3moncli();
    }
	
	public function creTabs($tsin) {
		
		if (is_string($tsin)) $ts = [$tsin[0] => $tsin];
		else $ts = $tsin; unset($tsin);
		
		foreach($ts as $k => $t) {
			if (is_integer($k)) $kl = substr($t, 0, 1);
			else				$kl = $k;
			$v = $kl . 'coll';
			$this->$v = $this->client->selectCollection($this->dbname, $t);
		}	
    }
	
	public static function get_oids(bool $rand = false, int $tsin = null, string $fmtin = null, $ntonly = false) {
		$o   = new MongoDB\BSON\ObjectId();
		$s   = $o->__toString();
		
		if ($ntonly) return substr($s, 8);
		
		if ($tsin) $ts = $tsin;  
		else       $ts  = $o->getTimestamp(); unset($tsin, $o);
		
		if ($fmtin) $fmt = $fmtin;
		else        $fmt = self::defOidsFmt;
		
		$tss = date($fmt, $ts); unset($ts);
		$fs  = $tss . ($fmtin ? '-' : 's-') .  substr($s  ,  8);
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
