#!/bin/bash
while true;do
    curl -s ${rootPath}/cron/reptile/qyule.php &
    sleep 2;
    curl -s ${rootPath}/cron/reptile/qyule.php
    sleep 2;
done;
