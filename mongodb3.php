<?php

require_once(__DIR__ . '/composer.php');

if (class_exists('MongoDB\Client')) {

class kw3moncli extends MongoDB\Client {

    const mondburi = 'mongodb://';
    
    private function cleanMoURI(string $s) : string {
	$l = strlen($s);
	$ss = substr($s, $l - 2);
	if ($ss === '//') $s = substr($s, 0, $l - 1);
	return $s;
    }

    public function __construct(string $host = '127.0.0.1') {
		$cs  = '';
		if (strpos($host, self::mondburi) === false)  {
		    $cs .= self::mondburi;
		}
		$cs .= $host;
		$cs .= '/';
		$fin = $this->cleanMoURI($cs);
		parent::__construct($fin, [], ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']]);
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
	
	
	const defOidsFmt = 'md-Hi-Y-s'; // the default format for my customized human-readable date IDs get_oids()  
			// usage below assumes seconds at the end *if* using this default
			// May 7, 2024 16:34 would be "0507-1634-2024-52"
    
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
	
    /* Kwynn 2024/05/07 16:37 EDT - writing a bunch of comments per a tech blog entry.  This code will almost certainly be posted before the entry.
     * The following is my customized human-readable data _id generator get_oids() as in object IDs.  
     * I wrote this 2 - 3 (or more?) years ago.  I'm not sure it all works anymore, but some of it gets used many times a day.  The lawyer project uses this function.
     * 
     * Whether I remember it all perfectly or not, I'll try my best to explain the intent.  
     * 
     * I'm going to add a return value of "string" right now.  I can't imagine that biting me, but we'll see.
     * 
     * None of the paramters are mandatory (as you can see because they all have defaults).
     * 
     * Given no external input arguments, an example of a return value is "0330-0418-2024-16s-22094de9fb019722"
     * It starts with a date formatted by default per defOidsFmt above (March 30, 2024, 4:18am, 16 seconds)
     * 
     * I'm using the default BSON OID as you can see from the first line.  The default has a the date as the hex value of the UNIX Epoch integer.
     * 
     * Then I use the default sequence number and random number just like BSON does.
     * 
     * The rand argument, if true, will add more random numbers, in case the _id should be particularly hard to guess.
     * 
     * $tsin covers the case where the user needs to enter a UNIX timestamp rather than use "now"
     * 
     * $fmtin is the PHP date() format that defaults to self::defOidsFmt
     * 
     * $ntonly looks like it returns the hex value UNIX time, but I don't remember when I use that.
     */

	public static function get_oids(bool $rand = false, int $tsin = null, string $fmtin = null, $ntonly = false) : string {
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

	// The following confirms that the input is in my default _id format.  It either returns a valid ID or throws and exception.
	public static function oidsvd($sin, $ckrand = false) : string { 
		kwas(is_string($sin), 'bad id - 1 - 234'); // Kwynn's assert - either the first param is true or throw exception.  
		
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
