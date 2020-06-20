<?php  // see notes at bottom

require_once('/opt/kwynn/kwutils.php');

function isGoo2($rin) {
    if (!$rin) return false;
    $fs = [  'type' => 'Kwynn_own_addr_hash',
	     'rand' => 'KxkhCOkkq0RaE5BNBZvjyvSPI',
	     'myr'  => '2019-09-07 21:17'    ];

    foreach ($fs as $f => $v) {
	if (!isset($rin[$f]))       return false;
	if (       $rin[$f] !== $v) return false;
    }
    
    return TRUE;
}

function isKwGoo() {
try {
    $sid = startSSLSession();
    $hsid = hash('sha256', $sid);
    $dao = new kwmoncli();
    $scoll = $dao->selectCollection('qemail', 'sessions');
    $res = $scoll->findOne(['sid' => $hsid]);
    kwas(isset($res['addr']) && $res['addr'], 'no valid addr hash');
    $hash = hash('sha256', $res['addr']);
    $ccoll = $dao->selectCollection('creds', 'creds');
    $res = $ccoll->findOne(['addr_hash' => $hash]);
    $bigres = isGoo2($res);
    if ($bigres) {
	file_put_contents('/tmp/iskgoo.txt', date('r') . "\n", FILE_APPEND);
	return TRUE;
    }
} catch(Exception $ex) { }

return false;

}
/* This interacts with my email checker at https://github.com/kwynncom/positive-gmail-check
 * The email checker uses Google OAUTH / OAUTH2 to correlate a session to an email address.  So isKwGoo() is "Does this session belong to Kwynn's 
 * email (GMail) address as confirmed by Google OAUTH?" or "is Kwynn? (as confirmed by Google)" 
 * 
 * The "rand" is not for security.  It was just a unique ID for searching and otherwise keeping track.  Upon further thought, I'm not sure there is a 
 * point to it.  "myr" meaning the PHP 'r' date readout, or something close.  It just means a human-readable, complete date of when I created that 
 * hash entry.
 * 
 * HISTORY
 * 
 * History up through 2020/06/06 is now in GitHub, so I will erase most and then all of this.
 */