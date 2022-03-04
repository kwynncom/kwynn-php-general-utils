<?php

require_once('/opt/kwynn/kwutils.php');

interface forker {
	public function __construct(bool $worker = false, int $low = -1, int $high = -1, int $workerN = -1);
	public static function shouldSplit (int $low, int $high, int $cpuCount) : bool;
}

interface forkerrr {
	public function __construct(bool $worker = false, int $workerN = -1);
}

class forkrr {
	public static function dofork($reallyFork, $startat, $endat, $thecl, ...$fargs) {

		$reallyFork = $reallyFork && !amDebugging();
		
		$cpids = [];
		for ($i=$startat; $i <= $endat; $i++) {
		    $pid = -1;	
			if ($reallyFork) $pid = pcntl_fork();
			if ($pid === 0 || !$reallyFork) {
				new $thecl(true, $i, $fargs);
				if ($reallyFork) exit(0);
			}  
			$cpids[$i] = $pid;	
		}

		if ($reallyFork) for($i=$startat; $i <= $endat; $i++) pcntl_waitpid($cpids[$i], $status);
	}
}

class fork {
    
const reallyFork = true;
    

public static function dofork($reallyForkIn, $startat, $endat, $thecl, ...$fargs) {
	
	$should = call_user_func([$thecl, 'shouldSplit'], $startat, $endat, multi_core_ranges::CPUCount());
	$reallyFork = $reallyForkIn && self::reallyFork && $should; unset($reallyForkIn);
	
	if ($reallyFork && amDebugging()) {
		$reallyFork = false;
		echo('This code is being debugged, so it will not fork().' . "\n");
	}
	
	$mcr = [];
	if ($should) $mcr    = multi_core_ranges::get($startat, $endat, false); 
	else		 $mcr[0] = ['l' => $startat, 'h' => $endat]; unset($should);
		
	$cpun = count($mcr);
	$cpids = [];
	for($i=0; $i < $cpun; $i++) {
	    $pid = -1;
	    if ($reallyFork) $pid = pcntl_fork();
	    if ($pid === 0 || !$reallyFork) {
			new $thecl(true, $mcr[$i]['l'], $mcr[$i]['h'], $i, $fargs);
			if ($reallyFork) exit(0);
	    }  
	    $cpids[] = $pid;
	}
	
	if ($reallyFork) for($i=0; $i < $cpun; $i++) pcntl_waitpid($cpids[$i], $status);
} // func
} // class

class multi_core_ranges {
	const maxcpus = 600; // AWS has a 192 core processor as of early 2022
	const testcpus = 12;

	public static function CPUCount() { return self::getValidCPUCount(shell_exec('grep -c processor /proc/cpuinfo'));   }
    
    public static function getValidCPUCount($nin) {
		$nin = trim($nin);
		kwas(is_numeric($nin), 'cpu count not a number');
		$nin = intval($nin);
		kwas($nin >= 1 && $nin <= self::maxcpus, 'invalid number of (hyper)threads / cores / cpus');
		return $nin;
	}

public static function vse($v) {
	kwas(is_numeric($v), 'not a number vse kw');
	$v = intval($v);
	kwas($v >= 0, 'not 0 or postive int vse kw');
	return $v;
	
}

private function getInc(int $s, int $e) {
	$n = $this->theon;
	$d = $e - $s;
	$i = roint($d / $n);
	if ($i < 1) return 1;
	return $i;
}

private function __construct(int $s, int $e, $n) {
	$this->setN($n);
	$tres = $this->do10($s, $e);
	$this->setVOR($tres, $s, $e);
}

private function setVOR(array $a, int $s, int $e) {
	kwas($a && is_array($a), 'bad array ranges kw');
	$an = count($a); kwas($an >= 1 && $an <= $this->theon, 'bad array count kw ranges');
	kwas($a[0]['l'] === $s, 'bad start ranges kw');
	kwas($a[$an - 1]['h'] === $e, 'bad end ranges kw');
	kwas($a[$an - 1]['l'] <= 
		 $a[$an - 1]['h'], 'bad ranges 2254 kw');
	
	$l = $s; 
	$tot = 0;
	for($i=0; $i < $an; $i++) {
		kwas($a[$i]['l'] <= $a[$i]['h'], 'bad iter ranges kw 2254');
		$tot += $a[$i]['h'] - $a[$i]['l'] + 1;
		if ($i === $an - 1) break;
		kwas($a[$i + 1]['l'] === $a[$i]['h'] + 1);
	}
	
	kwas($tot === ($e - $s + 1), 'bad sum ranges kw');
	$this->oares = $a;
	
}

public static function get(int $s, int $e, $n = false) {
	$o = new self($s, $e, $n);
	return $o->getR();
}

private function setN($n) {
	if ($n === false) $n = self::CPUCount(); kwas(self::getValidCPUCount($n), 'bad cpu / divide by count');
	$this->theon = $n;
	
}

public function getR() {  return $this->oares;  }

public function do10(int $s, int $e) {

	self::vse($s); self::vse($e);
	kwas($e >= $s, 'start end reversed');
	$inc = $this->getInc($s, $e);

	$ite = 0;
	$res = [];
	for ($ite = 0, $l=$s; $ite < $this->theon; $ite++) {
		unset($s);

		if ($l > $e) return $res;
		
		$res[$ite]['l'] = $l;
		$th = $l + $inc;
		if ($th >= $e) {
			$res[$ite]['h'] = $e;
			return $res;
		}
		$res[$ite]['h'] = $th;
		$l = $th + 1;
		
	}
	

	
	
	
	
}

    public static function tests() {
	$ts = [
		[-1,0],
		[0,0, 0],
		[0,0],
		[1,2],
		[1592696603, 1603313775],
		[1, 284717],
		[0, 0],
		[1, 1],
		[1, 2, 4],
	    	[0, 2, 1],
	    	[1, 2, 1],
		[1, 0],
		[1, 4, 6],
		[12,1],
		[1, 6],
	        [0, 1],
		[0, 200],
		
	    ];
	
	for ($i=0; $i < count($ts); $i++) {
		$t = $ts[$i];
		if (!isset($t[2])) $t[2] = self::testcpus;
		try {
			$out = [];
			$out['in'] = $t;
			$res = self::get($t[0], $t[1], $t[2]);
			$out['out'] = $res;
			print_r($out);
		} catch (Exception $ex) {
			print_r($out);
			echo($ex->getMessage() . "\n");
		}
		
		print("*************\n");
	}
} // func
	

}

if (didCLICallMe(__FILE__)) multi_core_ranges::tests();
