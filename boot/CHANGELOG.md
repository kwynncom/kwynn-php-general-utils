2020/12/26 11:15pm - possibly getting rid of C stuff.  I think it's time to combine mid and bootd and run it at boot.

************
bootd standalone

HISTORY

2020/12/30 11:01pm

Starting aws stuff from:
https://github.com/kwynncom/javascript-synchronized-clock/blob/891e8bc82c13fdfdf343ae1b39a1ea0c8b66dd1c/services/isAWS.php

2020/12/26 11:00pm
The boot20.php FIFO attempt is a disaster.  The damnest things happen with FIFOs.  Perhaps one day I'll sort it all out.

Until around 7:30pm EST (GMT -5) 2020/12/26, I used shared memory.  Then I noticed that a simple temp file was about 
100 times faster.  Playing with shared memory was fun, though.  I don't consider it a waste.

TECH NOTES

Regarding the shm_ / shared memory functions, every way I try to get it to work, I have to use 0666 permission.  
There may be a bug in the shm_ library itself.  

One alternative is to create a group for www-data and any other users who will either create or use the segment.

This problem is one of several reasons I tried the file method.
