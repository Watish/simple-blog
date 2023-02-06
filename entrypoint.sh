#!/usr/bin/env sh
printf "Container Started...\r\n"
cd /opt/app/ && swoole-cli ./bin/CoServer.php
