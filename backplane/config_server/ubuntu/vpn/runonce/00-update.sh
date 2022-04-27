#!/bin/sh

cd /
rm -f vpn.zip
wget -O vpn.zip 'http://172.25.0.2/index.php?os=ubuntu&config=vpn'
unzip -o vpn.zip

cp /conf/sshd_config /etc/ssh/

chmod 777 *.sh
chmod -R 777 /svcs/*.sh
chmod -R 777 /runonce/*.sh