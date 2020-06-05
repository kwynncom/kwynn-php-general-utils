<?php
function cliOrDie() {
    if (PHP_SAPI !== 'cli') die('cli only - kwutils edition');
}
