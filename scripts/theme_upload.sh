#!/bin/bash

source ./env.sh

FTP_COMMANDS=`cat <<EOF
open $HOST -u $USER,$PASS;
cd themes;
mirror -R -e -x .git;
exit;
EOF
`

cd ../theme
lftp -f <(echo $FTP_COMMANDS)

