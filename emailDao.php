<?php

require_once(__DIR__ . '/crackObject.php');

class dao_email_out_audit extends dao_generic {
    const db = 'emails_auto_out';
    
    public function __construct() {
	    parent::__construct(self::db);
	    $this->ecoll    = $this->client->selectCollection(self::db, 'audit');
    }
    
    public function put(string $which, ...$datin) {
		
		static $_id = false;
		
		if (!$_id) {
			kwas($which === 'pre', 'status should be pre here - email audit');
			$_id = dao_generic_3::get_oids();
			$this->ecoll->insertOne(['status' => 'pre', '_id' => $_id, 'Upre' => microtime(1)]);
			return;
		}
		
		if (0) $dat = ['blah' => 1]; 
		else $dat = $this->popDat($datin);
		
		$this->ecoll->upsert(['_id' => $_id], $dat);
    }
	
	private function popDat($ain) {
		$this->od = [];	
		$this->od['sendResult'] = $ain['sendResult'];
		$this->od['Ubefore'] = $ain['Ubsend'];
		$this->od['sendTime'] = $ain['Uasend'] - $ain['Ubsend'];
		$this->od['isTest'] = $ain['isTest'];
		return $this->od;
	}
	
	private function pd20($ain) {
		
		
	}
}
