<?php // 2024/02/15 - Kwynn - v4 is identical to and uses 3 except no dynamic properites allowed

require_once(__DIR__ . '/composer.php');

if (class_exists('MongoDB\Client')) {

class dao_generic_4  {
	
	const defOidsFmt = 'md-Hi-Y-s'; // usage below assumes seconds at the end *if* using this default
    
    private $dbname;
    protected $client;
    
    public function __construct(string $dbname) {
		$this->dbname = $dbname;
		$this->client = new kw3moncli();
    }
	
	public function kwsel(string $cname) {
		return $this->client->selectCollection($this->dbname, $cname);
	
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
