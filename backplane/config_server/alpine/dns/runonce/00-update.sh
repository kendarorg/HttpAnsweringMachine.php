#!/bin/sh

cd /
rm -f apache.zip
wget -O dns.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;dns'
unzip -o dns.zip
cp /conf/dnsmasq.conf /etc/dnsmasq.conf


chmod 777 *.sh
chmod -R 777 /svcs/*.sh
chmod -R 777 /runonce/*.sh