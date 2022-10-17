<?php

require_once('/opt/kwynn/kwutils.php');

class filePtrTracker extends dao_generic_3 {
	
	const dbname	= 'files';
	const initChars =    8192;
	
	public  readonly string $name;
	private readonly mixed	$ohan;
	private readonly bool	$collExists;
	private readonly array	$oq;

	public function __construct(string $name, bool $delete = false) {
		$this->name = $name;
		$this->dbmg($delete);
		$this->ohan = fopen($this->name, 'r');
		$this->getEndInit();
	}

	public function __destruct() { fclose($this->ohan);	}
	
	private function dbmg($delete) {
		parent::__construct(self::dbname);
		$this->creTabs('files');
		$this->oq = ['_id' => $this->name];
		if ($delete) $this->fcoll->deleteOne($this->oq);
	}
	
	private function getEndInit() {
		$res = $this->fcoll->findOne($this->oq);
		if (!$res) $this->setInitViaTail();
		else fseek($this->ohan, $res['end']);
	}
	
	private function setInitViaTail() {
		$st = fstat($this->ohan);
		if ($st['size']  <= self::initChars) return;
		fseek($this->ohan, -self::initChars, SEEK_END);
		kwas(fgets($this->ohan), 'throwaway line nonexistent'); // throw away because we may be in middle
	}
	
	private function setEndF() {
		if (!isset($this->collExists) && $this->fcoll->count($this->oq) === 0) {
			$dat['_id'] = $this->name;
			$this->fcoll->insertOne($dat);
		}  
		if (!isset($this->collExists)) $this->collExists = true;
		$this->fcoll->upsert($this->oq, ['end' => ftell($this->ohan)]);
	}
	
	public function fgets() {
		$l = fgets($this->ohan);
		$this->setEndF();
		return $l;
	}
}

function testFPT() {
	$o = new filePtrTracker('/var/log/chrony/measurements.log');
	while ($l = $o->fgets()) echo($l);
}

if (didCLICallMe(__FILE__)) testFPT();

