#!/bin/sh
mkdir /c
mount -o user=main,password=foo -t cifs //192.168.4.2/c /c || true