<?php 

require_once(__DIR__ . '/lock.php');

class testKw_sem_lock extends dao_generic_3 {
	
	const dbname = 'locktest2023';
	
	private readonly sem_lock $olock;
	
	public function __construct() {
		$this->do10();
		$this->dbinit();
		$this->do20();
	}
	
	private function do10() {
		$o = new sem_lock(__FILE__, 'a');
		$o->lock();
		$o->unlock();
	}
	
	private function do20() {
		$this->olock = sem_lock();
		$this->olock->lock();
		
	}
	
	private function dbinit() {
		parent::__contruct(self::dbname);
		$this->creTabs('dat');
	}
	
}

new testKw_sem_lock();