#!/bin/sh

cd /
rm -f node.zip
wget -O node.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;node;smb'
unzip -o node.zip

cp /conf/.npmrc /root/
chmod 777 /root/.npmrc
chmod 777 *.sh
chmod -R 777 /svcs/*.sh
chmod -R 777 /runonce/*.sh