<?php 

require_once(__DIR__ . '/lock.php');

class testKw_sem_lock extends dao_generic_3 {
	
	const dbname = 'locktest2023';
	
	private sem_lock $olock;
	
	public function __construct() {
		$this->do10();
		$this->dbinit();
		$this->do20();
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
		$pn = 12;
		$jmax = 30;
		$ps = [];
		for($i=0; $i < $pn; $i++) {
			$myps = $ps[] = pcntl_fork(); // ASKING FOR DISASTER!!!!!!!!!!!!!!!!!
			$this->olock = new sem_lock(__FILE__, 'f');
			for ($j = 0; $j < $jmax; $j++)
				$this->insertNext();
		}
		
		if ($myps === 0) return;
		foreach($ps as $p) {
			pcntl_waitpid($p, $status);
		}
		
	}
	
	private function insertNext() {
		$this->olock->lock();
		$a = $this->dcoll->findOne([], ['sort' => ['_id' => -1]]);
		if (!$a) $i = 1;
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