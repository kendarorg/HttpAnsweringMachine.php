#!/bin/sh

cd /
rm -f java.zip
wget -O java.zip 'http://172.25.0.2/index.php?os=alpine&config=basic;java;smb'
unzip -o java.zip

mkdir -p /root/.m2
cp /conf/settings.xml /root/.m2/
chmod 777 *.sh
chmod -R 777 /svcs/*.sh
chmod -R 777 /runonce/*.sh

cp /conf/HttpsClientUtil.java /
rm -f /HttpsClientUtil.class
javac /HttpsClientUtil.java