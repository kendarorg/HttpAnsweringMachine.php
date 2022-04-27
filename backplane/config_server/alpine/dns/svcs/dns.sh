#!/bin/sh
#route add -net 10.0.0.0 netmask 255.0.0.0 gw 192.168.1.4

webproc --config /etc/dnsmasq.conf -- dnsmasq --no-daemon
