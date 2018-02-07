#!/bin/bash
while true;do
    ${phpBin}  -c ${phpIniPath} ${ROOT}/reptile/down_video.php &> /dev/null
    sleep 20;
done;
