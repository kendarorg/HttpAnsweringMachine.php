#!/bin/sh

ip tuntap add name tun0 mode tun

iptables -t nat -A POSTROUTING -o tun0 -j MASQUERADE
iptables -A FORWARD -i eth0 -o tun0 -j ACCEPT
iptables -A FORWARD -o tun0 -j ACCEPT
iptables -A FORWARD -i tun0 -m conntrack --ctstate ESTABLISHED,RELATED   -j ACCEPT
iptables -A INPUT -i tun0 -j ACCEPT

iptables -L -v -n