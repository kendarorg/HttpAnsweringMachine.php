#!/bin/sh

cd /
rm -f apache.zip
wget -O apache.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;apache'
unzip -o apache.zip

# Setup * certificates
mkdir -p /certs
chmod 555 /certs
cp /conf/company.it.key /certs/company.it.key
cp /conf/company.it.crt /certs/company.it.crt
cp /conf/basic.apache.conf /etc/apache2/conf.d/basic.apache.conf


chmod 777 *.sh
chmod -R 777 /svcs/*.sh
chmod -R 777 /runonce/*.sh