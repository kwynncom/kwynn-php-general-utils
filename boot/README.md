Mid as in machine ID.

Again this goes to primary keys or a form of UUID in that I want to identify CPU tick, core / thread number, which boot session (or timestamp), 
and which machine.  

I may also add which filesystem, filesystem creation date, which OS version, etc.

I should save AWS EC2 image ID and filesystem time because when you build one instance from an older image, the "old" filesystem time is still recorded.

COMMANDS:

 sudo dmidecode --type processor | grep -i current
	Current Speed: 1333 MHz [not my real number]
        Depending on type, closest to correct

sudo lshw -C CPU
    size: 1333MHz

The above is barely accurate to 2 - 3 digits of precision when inverted and compared against tick.php

sudo tune2fs -l /dev/sda1

Shows filesystem creation time, which may be instances and years ago.

mount | grep 'on / '

Shows where root is mounted

/sys/class/dmi/id$ cat chassis_vendor

Make, either HP or "Amazon EC2"

/sys/class/dmi/id$ cat product_name

Model: Fabulous X22 or t3a.nano [EC2 type]

/sys/class/dmi/id$ sudo cat product_serial

Actual computer box serial number.  With AWS it's a UUID that doesn't correspond to anything else. 

/sys/class/dmi/id$ cat board_asset_tag

AWS instance ID.

curl http://169.254.169.254/latest/dynamic/instance-identity/document

Lots of AWS info that I'd want, in JSON format.

lsb_release -a

Distributor ID:	Ubuntu
Description:	Ubuntu 20.04.1 LTS
Release:	20.04
Codename:	focal

sudo dmidecode | grep 'Release Date: '

BIOS release date
*******
// Kwynn 2020/01/25 9:10pm - goes to "is kwynn.com?" or at least a start
// echo Q | openssl s_client -showcerts -connect 127.0.0.1:443 2>/dev/null | grep 'CN = kwynn\.com'
// if use whole output, then "Verify return code: 0 (ok)" MIGHT be definitive


********
*************
systemd reference:

https://transang.me/create-startup-scripts-in-ubuntu/
https://wiki.archlinux.org/index.php/systemd#Get_current_targets
