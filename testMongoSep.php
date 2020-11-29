<?php // 2020/11/28 10:42pm - This file is not needed.  I'm keeping it for now in case of problems.  See README for details.
// testMongoSep as in test the separation of that file.

require_once('kwutils.php');

$ci = 'include_exists';
if (function_exists($ci)) $ci10 = $ci('blah');

if (class_exists('MongoDB\Client')) {
    kwynn();
}

include_exists('blah');
include('testingThatMyOwnErrorHandlerIsWorking');