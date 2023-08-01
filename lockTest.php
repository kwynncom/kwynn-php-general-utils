<?php 

require_once(__DIR__ . '/lock.php');

class testKw_sem_lock extends dao_generic_3 {
	
	const dbname = 'locktest2023';
	
	private sem_lock | null $olock;
	
	public function __construct() {
		$this->do10();
		$this->dbinit();
		// $this->do20();
		$this->do30();
	}
	
	private function do10() {
		$o = new sem_lock(__FILE__, 'a');
		$o->lock();
		$o->unlock();
	}
	
	private function do20() {
		$this->olock = new sem_lock(__FILE__, '');
		$this->insertNext();
	}
	
	private function do30() {
		$pn = 10;
		$jmax = 1000;
		$ps = [];
		$pid = false;
		$this->olock = null;
		for($i=0; $i < $pn; $i++) {
			
			if ($pid !== 0) {
				$pid = $ps[] = pcntl_fork();
				if ($pid !== 0) continue;
			}
			
			$this->olock = new sem_lock(__FILE__, 'f');
			for ($j = 0; $j < $jmax; $j++) $this->insertNext();
			if ($pid === 0) return;
		}
		
		foreach($ps as $p) { pcntl_waitpid($p, $status); }
		
	}
	
	private function insertNext() {
		$this->olock->lock();
		$a = $this->dcoll->findOne([], ['sort' => ['_id' => -1]]);
		if (!$a) $i = 0;
		else $i = $a['_id'];
		$this->dcoll->insertOne(['_id' => $i + 1, 'pid' => getmypid()], ['kwnoup' => true]);
		$this->olock->unlock();		
	}
	
	private function dbinit() {
		parent::__construct(self::dbname);
		$this->creTabs('dat');
	}
	
}

new testKw_sem_lock();