#!/bin/bash

echo "capperi!$1" |/opt/openconnect/sbin/openconnect -i tun0 -u "username"\
	 --mtu=1422 --passwd-on-stdin --protocol=gp --csd-wrapper=/opt/openconnect/libexec/openconnect/hipreport.sh\
	  --usergroup=gateway https://gp.fuffica.it
