#!/bin/sh

cd /
rm -f ssh.zip
wget -O ssh.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;ssh'
unzip -o ssh.zip

cp /conf/sshd_config /etc/ssh/


chmod 777 *.sh
chmod -R 777 /svcs/*.sh
chmod -R 777 /runonce/*.sh