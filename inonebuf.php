<?php

class inonebuf extends dao_generic_3 {

	const bufc = 1000;
	
	public function __construct($db, $conm) {
		parent::__construct($db);
		$this->coll = $this->client->selectCollection($db, $conm);
		$this->init();
	}

private function init() {
	$this->b = [];
	$this->i = 0;
	$this->t = 0;
}
public function ino($d) {
	
	static $bc = self::bufc;

	$isd = is_array($d) || is_object($d);

	if ($isd) { $this->b[] = $d; $this->i++; }

	if (($this->i >= $bc) || (!$isd && $this->i > 0))
	{ 
		$r = $this->coll->insertMany($this->b); 
		kwas($r->getInsertedCount() === $this->i, 'bad bulk insert count kwutils 0240');
		$this->t += $this->i; $this->b = []; $this->i = 0;
	}	

	
	return $this->t;
}
}
