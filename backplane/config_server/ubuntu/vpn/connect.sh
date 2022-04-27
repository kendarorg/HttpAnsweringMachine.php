#!/bin/sh
echo "password"'!'"$1"|/usr/sbin/openconnect -i tun0 -u "username" --mtu=1422 \
--passwd-on-stdin --protocol=gp --csd-wrapper=/usr/libexec/openconnect/hipreport.sh\
 --usergroup=gateway https://gp.company.it