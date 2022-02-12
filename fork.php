<?php

require_once('/opt/kwynn/kwutils.php');

interface fork_worker {
	public static function shouldSplit (int $low, int $high, int $cpuCount) : bool;
	public static function workit	  (int $low, int $high, int $workerN);		  
}

class fork {
    
    const reallyFork = true;
    
public static function dofork($reallyForkIn, $startat, $endat, $childCl, ...$fargs) {
	
	$should = call_user_func([$childCl, 'shouldSplit'], $startat, $endat, multi_core_ranges::CPUCount());
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
			call_user_func([$childCl, 'workit' ], $mcr[$i]['l'], $mcr[$i]['h'], $i, $fargs);
			if ($reallyFork) exit(0);
	    }  
	    $cpids[] = $pid;
	}
	
	if ($reallyFork) for($i=0; $i < $cpun; $i++) pcntl_waitpid($cpids[$i], $status);
} // func
} // class

class multi_core_ranges {
    
    const maxcpus = 600; // AWS has a 192 core processor as of early 2022
    
    public static function CPUCount() { return self::getValidCPUCount(shell_exec('grep -c processor /proc/cpuinfo'));   }
    
    public static function getValidCPUCount($nin) {
	$nin = trim($nin);
	kwas(is_numeric($nin), 'cpu count not a number');
	$nin = intval($nin);
	kwas($nin >= 1 && $nin <= self::maxcpus, 'invalid number of (hyper)threads / cores / cpus');
	return $nin;
    }
    
    public static function get($stat, $endat, $cpuin = false) { 

	kwas(is_numeric($endat) && is_numeric($stat), 'bad numbers 1 getRanges()');
	$endat = intval($endat); $stat = intval($stat); kwas($endat >= 0 && $stat >=0, 'bad numbers 2 getRanges()');
	
	if ($cpuin) $cpun = self::validCPUCount($cpuin);
	else	    $cpun = self::CPUCount();
	
	$rs = [];
	
	if ($endat === 0) $itd = 0;
	else		 $itd = $endat - $stat + 1;
	
	$h = true; // just because the logic works
	$l = true;

	for ($i=0; $i < $cpun; $i++) {
	    
	    if ($l === false || $h === false) { $rs[$i]['l'] = $rs[$i]['h'] = false; continue; }
	    
	    if ($i === 0) self::set($l, $rs, 'l', $i, $i + $stat, $stat, $endat);
	    else          self::set($l, $rs, 'l', $i, $h + 1, $stat, $endat);
	    if ($i < $cpun - 1) {
		$h = intval(round(($itd / $cpun) * ($i + 1))) + $stat;   
	    } else $h = $itd + $stat - 1;

	    self::set($h, $rs, 'h', $i    , $h , $stat, $endat, $l, $h);

   
	}

	return $rs;
    }
    
    private static function set(&$lhr, &$a, $lhk, $i, $to, $stat, $endat, $l = false, $h = false) {
	if ($endat === 0) return self::set20($lhr, $a, $lhk, false, $i);
        if ($to > $endat) $to = false;
        else $to = $to;
	
	if ($lhk === 'h' && $l === false) return self::set20($lhr, $a, $lhk, false, $i);
	
	if ($h < $l && $lhk === 'h') $to = $l; 
	
	$lhr = $to;
	$a[$i][$lhk] = $to;
	return $to;
    }
    
    private static function set20(&$lhr, &$a, $lhk, $to, $i) {
	$lhr = $a[$i][$lhk] = $to;
	
    }
    
    public static function tests() {
	$ts = [
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
	

	$max = count($ts) - 1;
	
	for ($i=3; $i <= 3; $i++) {
	$t = $ts[$i];
	if (!isset($t[2])) $t[2] = 12;
	try {
	    $res = self::get($t[0], $t[1], $t[2]);
	    $out = [];
	    $out['in'] = $t;
	    $out['out'] = $res;
	    print_r($out);
	} catch (Exception $ex) {
	    throw $ex;
	}
	}
    } // func

}

if (didCLICallMe(__FILE__)) multi_core_ranges::tests();
