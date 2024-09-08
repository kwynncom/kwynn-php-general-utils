<?php // 2024/02/15 - Kwynn - v4 is identical to and uses 3 except no dynamic properites allowed

require_once(__DIR__ . '/composer.php');

if (class_exists('MongoDB\Client')) {

class dao_generic_4  {
	
	const defOidsFmt = 'md-Hi-Y-s'; // usage below assumes seconds at the end *if* using this default

	const defaultHost = '127.0.0.1';
    
    private $dbname;
    protected $client;
    


    public static function    get_oids(bool $rand = false, int $tsin = null, string $fmtin = null, $ntonly = false) {
	return dao_generic_3::get_oids(     $rand,	       $tsin	   ,        $fmtin,	   $ntonly);    }
    
    public function __construct(string $dbname, string $host = self::defaultHost) {
	if (!$host) $host = self::defaultHost; // still needs accounting for
	$this->dbname = $dbname;
	$this->client = new kw3moncli($host);
    }
	
	public function kwsel(string $cname) {
		return $this->client->selectCollection($this->dbname, $cname);
	
    }
	
	public static function    oidsvd($sin, $ckrand = false) {
	    return dao_generic_3::oidsvd($sin, $ckrand	      );
	}
	
} // class
} // if MongoDB stuff exists
