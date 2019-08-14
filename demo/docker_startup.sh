#!/bin/bash
FOLDER=/data/asyncCallerHub/demo
CMD=${FOLDER}/server_start.php
LOG_FILE=${FOLDER}/async.log
php -f ${CMD}
tail -f ${LOG_FILE}