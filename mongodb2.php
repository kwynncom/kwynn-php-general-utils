<?php

require_once('/opt/kwynn/kwutils.php');
require_once('/opt/kwynn/lock.php');

class kwcoll2 extends kwcoll {
    public function __construct($mgr, $db, $coll, $tma, $tpid, $path) {
	parent::__construct($mgr, $db, $coll, $tma);
	$this->db   = $db;
	$this->tpid = $tpid;
	$this->cname = $coll;
	$this->callingPath = $path;
    }
    
    public function getSeq2($retid = false) {
	if (!isset($this->seqo)) $this->seqo = new dao_seq_kw2();
	$seq = $this->seqo->get($this->db, $this->cname, $this->callingPath, $this->tpid, $retid);
	return $seq;
    }
    
    public function find($q = [], $o = []) { return parent::find($q, $o)->toArray();  }
}

class kwmoncli2 extends kwmoncli {
    
    public function __construct($path) {
	parent::__construct();
	$this->callingPath = $path;
    }
    
    public function selectCollection2($db, $coll, $tableid) {
	return new kwcoll2($this->getManager(), $db, $coll, ['typeMap' => ['array' => 'array','document' => 'array', 'root' => 'array']], $tableid, $this->callingPath); 
    }    
}

class dao_generic_2 extends dao_generic {

    public function __construct($dbname, $path) {
	$this->dbname = $dbname;
	$this->client = new kwmoncli2($path);
    }
    
    protected function creTabs($dbin, $ts) {
	foreach($ts as $k => $t) {
	    $k = $k[0];
	    $v = $k . 'coll';
	    $this->$v = $this->client->selectCollection2($dbin, $t, $k);
	}	
    }
}

class dao_seq_kw2 extends dao_generic_2 {
    
    const dbName = 'seqs';
    const iddel  = '-';
    
    public function __construct() {
	parent::__construct(self::dbName, __FILE__);
	
	$this->creTabs(self::dbName, ['s' => 'seqs2']);
	
	$this->scoll->createIndex(['db'	     => -1, 'name' => -1], ['unique' => true ]);
	$this->scoll->createIndex(['sem_key' => -1		], ['unique' => true ]);
    }
    
    public function get($db, $coll, $path, $prid, $retid = false) {
	
	static $locko = false;
	
	if (!$locko) $locko = new sem_lock($path, $prid);
	$q = ['db' => $db, 'name' => $coll];
	$pr = ['projection' => ['seq' => 1, '_id' => 0]];
	$skey = $locko->getKey();

	$locko->lock();
	$now = time();
	$dat['ts' ]  = $now;
	$dat['r'  ]  = date('r', $now);
	$r = $this->scoll->findOne($q, $pr);
	if (!$r) $r = $this->create($db, $coll, $skey);
	$newseq = $r['seq'] + 1;
	$dat['seq'] = $newseq;
	$r2 = $this->scoll->upsert($q, $dat);
	$locko->unlock();
	
	if (!$retid) return $newseq;

	unset($dat['r']); 	
	return self::getID($dat, $now, $newseq);

    }
    
    private static function getID($din, $now, $seq) {

	$id  = '';
	$id .= (intval(date('Y', $now)) % 10);
	$id .= self::iddel;
	$id .= date('m-d-H:i:s');
	$id .= self::iddel;
	$id .=  $seq;
	$id .= self::iddel;
	$id .= date('Y');

	$dat['seqmeta'] = $din;
	$dat['_id'] = $id;	
	return $dat;
    }
    
    private function create($db, $coll, $skey) {
	$now = time();
	$r   = date('r', $now);
	$dat['created' ] = $now;
	$dat['createdR'] = $r;
	$dat['sem_key' ] = $skey;
	$dat['db'      ] = $db;
	$dat['name'    ] = $coll;
	$dat['seq'     ] = 0;
	$dat['_id'     ] = $db . '-' . $coll;
	
	$this->scoll->insertOne($dat);
	return $dat;
    }
    
}
