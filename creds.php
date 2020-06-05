<?php

require_once('kwutils.php');

class kwynn_creds extends dao_generic {
    const db = 'creds';
    function __construct() {
	parent::__construct(self::db);
	$this->ccoll    = $this->client->selectCollection(self::db, self::db);
    }
    
    public function getType($tin, $f = false) { 
	$res = $this->ccoll->findOne(['type' => $tin]); 
	kwas($res && is_array($res) && count($res) > 0, 'no cred type of ' . $tin);
	if ($f) {
	    kwas(isset($res[$f]), 'cred field not set');
	    return     $res[$f];
	}
	return $res;
    }
}


