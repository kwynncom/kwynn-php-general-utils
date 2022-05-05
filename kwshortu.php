<?php

require_once(__DIR__ . '/base62/base62.php'); // I put didCLICallMe() in there
require_once(__DIR__ . '/js/kwjsrecv.php');

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

/* The major purpose of this (below) is to make warnings and notices an error.  I have found it's best to "die" on warnings and uncaught exceptions. */
function kw_error_handler($errno, $errstr, $errfile, $errline) {
    echo "ERROR: ";
    echo pathinfo($errfile, PATHINFO_FILENAME) . '.';
    echo pathinfo($errfile, PATHINFO_EXTENSION);
    echo ' LINE: ' . $errline . ' - ' . $errstr . ' ' . $errfile;
    exit(37); // an arbitrary number, other than it should be non-zero to indicate failure
}

set_error_handler('kw_error_handler');

function kwifs($a, ...$ks) { // if set return, else FALSE
	
	$i = 0;
	if (is_object($a)) $b = (array)$a;
	else $b = $a;
	while (isset      ($ks[$i])) {
		if (!isset( $b[$ks[$i]])) return FALSE;
		$b	=		$b[$ks[$i]];
		
		$i++;
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
