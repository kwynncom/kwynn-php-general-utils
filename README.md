# kwynn-php-general-utils
This is PHP code I wrote and use repeatedly in different types of projects, including a growing number here on GitHub.

isAWS() running at http://kwynn.com/t/20/06/machineID.php?testisAWS

*****
2020/11/28 10:42pm

I was playing with Linux containers and noticed that kwutils.php assumes that a Composer vendor/autoload.php exists.  It also assumes that the 
MongoDB composer library exists.  So I hopefully fixed this so someone can use other features without composer and composer MongoDB.

So, several changes:

I moved the MongoDB code to a separate file.
I created a function "include_exists" to determine whether requiring autoload is safe, and conditionally include it, and conditionally create my 
database functions based on inclusion and the existence of a needed MongoDB class.
I tried to account for those with earlier versions of my utilities, who don't download the new mongodb.php file.


****
2020/06/24 7:45pm 

I thumped on isAWS() some more.  I read further in the below-linked Amazon doc page, and I found some answers that still work.

I've also decided that, for now, isKwDev() is identical to !isAWS()

The purposes of these is to identify whether I'm live or not.  I suppose I should create isLive() and deprecate isAWS(), but anyhow...

Below I mention the definitive, crypto-verified solution, but my immediate purpose is much simpler than that.

*****
2020/06/22 - regarding isAWS()

I just upgraded to an AWS EC2 t3a.nano type (from t2.nano) and Ubuntu 20.04 (from 18.04) at the same time.  isAWS() in kwutils.php broke.  

Assuming you are I are not running certain types of virtualization (presumably Xen and probably others), it used to be this simple:

function isAWS() { return file_exists('/sys/hypervisor/uuid'); }

Per this comment: https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/identify_ec2_instances.html

Now it is not that simple.  Both my local, non-virtualized machine and my new AWS instance have /sys/hypervisor, but NEITHER has uuid or anything else under 
the hypervisor directory.  

A solution that works well enough for now is to fall back on what I used to do to identify machines--use both Apache and cli / shell environment variables.  
I added some comments in the isAWS() demonstrating this.

I believe it's my clock project that has a stupdendously complex (relatively speaking) but definitive solution using crypto validation that you're in AWS EC2.  I 
suppose I will elaborate on that one day.

Yeah, below is the in-progress version.  So far this is just a toy, but I'll have to work on it:

https://github.com/kwynncom/javascript-synchronized-clock/blob/master/services/isAWS.php

***
2020/06/22 (also)

Per my earlier comment, on June 4, the code is aleady much better tested.

*******
Very First Commit Comment (ca. 2020/06/04)
For the most part, I'm going to commit now and comment later.  I will say that several of these files are brand new.  Out of the brand new ones, some are already 
fairly well tested and some not so much.
