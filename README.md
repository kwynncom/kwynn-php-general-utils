# kwynn-php-general-utils
PHP (and client-side JavaScript) code I wrote and use repeatedly in different projects

I include this library in almost everything I write.  I have it cloned to /opt/kwynn.  Almost everything I write begins with:

require_once('/opt/kwynn/kwutils.php');

Most popular with my own usage are kwas() and my MongoDB class dao_generic_3.  kwas($condition, $errorMessage); is "Kwynn's assert()": 
either the condition or true or an exception is thrown with the error message.

isAWS() is running at https://kwynn.com/t/20/06/machineID.php?testisAWS

I'm surprised it's still running.  I've tried to stop using that, but it's one item that is running live that I can easily show, so I mention it.  
As of late 2022, it may not last long.

*******
function isKwGoo()

This interacts with my email checker at https://github.com/kwynncom/positive-gmail-check
The email checker uses Google OAUTH / OAUTH2 to correlate a session to an email address.  So isKwGoo() is "Does this session belong to Kwynn's 
email (GMail) address as confirmed by Google OAUTH?" or "is Kwynn? (as confirmed by Google)" 

WARNING: you have to use this before there is a chance of output, otherwise you may get the dreaded "cannot be changed after headers have already 
been sent"

I haven't seen that one in many months, although that's because I'm so careful of it.

This is NOT included by default with the rest of the library.

*************
THIS FILE HISTORY

2022/07/29: I will erase a lot of this because it's in the git history.  The truly geeky might find the previous versions interesting.  I 
should probably review them myself one day.
