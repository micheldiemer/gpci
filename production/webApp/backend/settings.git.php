<?php

$settings = array(
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'gpci',
    'username' => 'wwwgpci',
    'password' => 'wwwgpci',
    'collation' => 'utf8_general_ci',
    'charset' => 'utf8',
    'prefix' => ''
);

$smtpSettings = array(
    'MAIL_HOST' => 'localhost',
    'MAIL_FROM' => ['ifide@ifide.net', 'IFIDE SupFormation'],
    'MAIL_BCC' => ['supformation@ifide.net', 'IFIDE SupFormation'],
    'MAIL_PORT' => 1025,
    'MAIL_PROTOCOL' => null, # ssl/tls
    'MAIL_USERNAME' => 'root@localhost',
    'MAIL_PASSWORD' => ''
);
