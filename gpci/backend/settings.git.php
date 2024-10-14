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

const BASE_URL = 'https://dev.mshome.net/gpci/backend';
const GPCI_URL = 'https://dev.mshome.net/gpci';
const IFIDE_LOGO_URL = 'https://supformation.fr/wp-content/uploads/2023/03/LOGO-SUP-FORMATION-AVANCER-SE-FORMER-1024x529.png';

$smtpSettings = array(
    'MAIL_HOST' => 'localhost',
    'MAIL_FROM' => ['ifide@ifide.net', 'IFIDE SupFormation'],
    'MAIL_BCC' => ['supformation@ifide.net', 'IFIDE SupFormation'],
    'MAIL_PORT' => 1025,
    'MAIL_PROTOCOL' => null, # ssl/tls
    'MAIL_USERNAME' => 'root@localhost',
    'MAIL_PASSWORD' => ''
);

$profiler = false;
