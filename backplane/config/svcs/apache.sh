#!/bin/sh
#route add -net 10.0.0.0 netmask 255.0.0.0 gw 192.168.1.4

/usr/sbin/httpd -D FOREGROUND

while sleep 60; do
	sleep 1
done
