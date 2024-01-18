<?php

require_once(__DIR__ . '/base62/base62.php'); // I put didCLICallMe() in there
require_once(__DIR__ . '/js/kwjsrecv.php');

function base62($len) { return base62::get($len); }

function cliOrDie() {
    if (PHP_SAPI !== 'cli') die('cli only - kwutils edition');
}

/* Kwynn's assert.  It's similar to the PHP assert() except it throws an exception rather than dying.  I use this ALL THE TIME.  
  I'm sure there are 100s if not 1,000s of references to this in my code. */
function kwas($data = false, $msg = 'no message sent to kwas()', $code = 12345) {
    if (!isset($data) || !$data) throw new Exception($msg, $code); 
/* The isset may not be necessary, but I'm not touching anything I've used this much and for this long. */
	return $data;
}


function kw_cond_set_error_handler() {
	if (!defined('KWYNN_DO_NOT_INSTALL_MY_ERROR_HANDLER_V2022') || !KWYNN_DO_NOT_INSTALL_MY_ERROR_HANDLER_V2022) {
		set_error_handler('kw_error_handler');	
	}
}

kw_cond_set_error_handler();

/* The major purpose of this (below) is to make warnings and notices an error.  I have found it's best to "die" on warnings and uncaught exceptions. */
function kw_error_handler($errno, $errstr, $errfile, $errline) {
    echo "ERROR: ";
    echo pathinfo($errfile, PATHINFO_FILENAME) . '.';
    echo pathinfo($errfile, PATHINFO_EXTENSION);
    echo ' LINE: ' . $errline . ' - ' . $errstr . ' ' . $errfile;
    exit(37); // an arbitrary number, other than it should be non-zero to indicate failure
}

function kwifs($a, ...$ks) { // if set return, else FALSE
	
	static $defk = 'kwiff'; // if not set / if false; if not exists return the value assoc with this key ; usually falsey
	static $fdefr = FALSE;
	
	$i = 0;
	if (is_object($a)) $b = (array)$a;
	else $b = $a;
	
	$defOnly = false;
	
	while (isset      ($ks[$i])) {
		
		if (is_array($ks[$i]) && 
				( isset($ks[$i][$defk]) || key($ks[$i]) === $defk )
			) {	$defr = $ks[$i][$defk];
				if ($defOnly) return $defr;
				 $i++;
				continue;
		}
		
		if ($defOnly || !isset( $b[$ks[$i]])) {
			$defOnly = true;
		} else {
			if (is_object($b)) $b = (array)$b;
			if (is_object($b[$ks[$i]])) $b[$ks[$i]] = (array)$b[$ks[$i]];
			$b =	$b[$ks[$i]];
		}
		
		$i++;
	}
	
	if ($defOnly) {
		if (isset($defr)) return $defr;
		return $fdefr;
	}
	
	return $b;
}

function didAnyCallMe($fin) {
	if ( didCLICallMe($fin)) return TRUE;
	if ( iscli()) return FALSE;
	if (basename($fin) === basename($_SERVER['PHP_SELF'])) return TRUE;
	return FALSE;
}

// function isrv($k) { } // moving to js/kwjsrecv.php


function didCLICallMe($callingFile) { // $call with __FILE__
	return base62::didCLICallMe($callingFile);
}

function iscli() { return PHP_SAPI === 'cli'; } 
