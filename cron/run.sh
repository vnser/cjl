#!/bin/bash
killall monitoring.sh >& /dev/null
killall down_video.sh >& /dev/null
killall rep_avtao.sh >& /dev/null
killall rep_xvideo.sh >& /dev/null
killall rep_qyule.sh >& /dev/null

declare -x ROOT=`dirname $0`;
source ${ROOT}/../config/config.sh;
${ROOT}/monitoring.sh >& /dev/null &
${ROOT}/down_video.sh >& /dev/null &
${ROOT}/rep_qyule.sh >& /dev/null &
${ROOT}/rep_avtao.sh >& /dev/null &
${ROOT}/rep_xvideo.sh >& /dev/null &
echo "Running success~";