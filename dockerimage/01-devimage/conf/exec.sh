#!/bin/bash

# mysql
if  ! pgrep -x "mysqld" > /dev/null
then
    echo 'mysqld start root no password'
    mysqld --user mysql </dev/null &>/dev/null &
    sleep 2s
fi

if pgrep -x "mysqld" > /dev/null
then
    if `echo "select ''" | mysql -u root -p8LH45Ey7F6a6Y4hS 2>/dev/null`
    then
        echo 'mysqld started with password'
    else
        echo 'mysql setting password'
        export PID=`cat /var/run/mysqld/mysqld.pid`
        mysql_tzinfo_to_sql /usr/share/zoneinfo 2>/dev/null | mysql -u root mysql
        mysql -u root < mysql_secure_installation.sql
        echo 'mysqld restart'
        kill $PID ; while ps -p $PID >/dev/null; do sleep 1; done   
        mysqld --user mysql </dev/null &>/dev/null &
        sleep 2s
        if `echo "select ''" | mysql -u root -p8LH45Ey7F6a6Y4hS 2>/dev/null`
        then
            echo 'mysqld secured started with password'
            echo 'mysql creating database'
            mysql -u root -p8LH45Ey7F6a6Y4hS < ./create_database.sql
        else
            echo 'mysql password error'
        fi
    fi
else
    echo 'mysqld error starting'
fi

# apache2
if ! pgrep -x "httpd" > /dev/null
then
    rm -f /var/log/apache2/httpd.pid
    httpd
    sleep 2s
fi

if ! pgrep -x "httpd" > /dev/null
then
    echo "httpd not started"
else
    echo "httpd started"
fi
