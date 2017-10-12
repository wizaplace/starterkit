#!/bin/bash

FILEBEAT_BIN=/app/bin/filebeat
FILEBEAT_CONF=/app/app/config/filebeat.yml
FILEBEAT_PID=/app/var/filebeat/filebeat.pid

function start_filebeat {
    echo "Starting filebeat..."
    nohup $FILEBEAT_BIN -e -c $FILEBEAT_CONF > /dev/null &
    echo $! > $FILEBEAT_PID
}

function filebeat_is_running {
    PID=$(<$FILEBEAT_PID)
    kill -0 $PID 2> /dev/null
    echo $?
}

# ensure filebeat data dir exists
mkdir -p /app/var/filebeat/logs

# start filebeat if PID file does not exists
if [ ! -f $FILEBEAT_PID ]; then
    start_filebeat
fi

if [ $(filebeat_is_running) -ne 0 ]; then
    start_filebeat
fi
