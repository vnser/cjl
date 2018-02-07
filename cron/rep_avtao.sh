#!/bin/bash
while true;do
    curl -s ${rootPath}/cron/reptile/avtaobao.php &
    sleep 2;
    curl -s ${rootPath}/cron/reptile/avtaobao.php
    sleep 2;
done;
