#!/bin/bash

cd /var/www/html/mxray/util

mkdir ../log 2> /dev/null

nohup php msg-wsserver.php 2>&1 > ../log/ws.out &
nohup php msg-udpserver.php 2>&1 > ../log/udp.out &
nohup ./msg-tcpserver /opt/genapp/mxray/appconfig.json 2>&1 > ../log/tcp.out &
nohup php msg-keepalive.php 2>&1 > ../log/keepalive.out &

