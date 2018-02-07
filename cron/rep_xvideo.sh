#!/bin/bash
while true;do
    curl -s ${rootPath}/cron/reptile/xvideo.php &
    sleep 2;
    curl -s ${rootPath}/cron/reptile/xvideo.php
    sleep 5;
#    sleep 5;
done;
