<?php

require_once(__DIR__ . '/crackObject.php');

class dao_email_out_audit extends dao_generic {
    const db = 'emails_auto_out';

    private readonly object $ecoll;
    private string $exuqid;

    public function setExtraId(string $id) {
	$this->exuqid = trim($id);
    }

    
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
	
	private function getExId() : string {
	    if (!isset($this->exuqid) || !$this->exuqid) return '';
	    return $this->exuqid;
	    
	}

	private function setExId(array &$a) {
	    $id = $this->getExId();
	    if (!$id) return;
	    $a['idexuq'] = $id;
	}

	private function popDat($ain) {
		$od = [];
		$this->setExId($od);
		$od['sendResult'] = $ain['sendResult'];
		$od['Ubefore'] = $ain['Ubsend'];
		$od['sendTime'] = $ain['Uasend'] - $ain['Ubsend'];
		$ist = $od['isTest'] = $ain['isTest'];
		$mo = crackObject::crack($ain['mail']);
		$od['to'] = implode(', ', array_keys($mo['all_recipients']));
		if (!$ist) {
			$sm = $mo['smtp'];
			$od['smtpErr'] = trim(implode(' ', $sm['error']));
			$od['signoff'] = $sm['last_reply'];
			
		}
		$od['status'] = 'post';
		$od['subject'] = substr($mo['Subject'], 0, 50);
		$od['body'] = substr($mo['Body'], 0, 200);
		$od['mid'] = $mo['lastMessageID'];
		$od['uname'] = $mo['Username'];
		return $od;
	}
	
	private function pd20($ain) {
		
		
	}
}
