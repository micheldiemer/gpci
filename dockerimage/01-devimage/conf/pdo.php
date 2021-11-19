<?php

//$host = '127.0.0.1';
$db   = 'gpci';
$user = 'gpciweb';
$pass = 'VGD1SGX3KR0G7PRJ';
$charset = 'utf8mb4';

$dsn = "mysql:dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $rs = $pdo->query("select * from roles");
    $roles = $rs->fetchAll();
    echo "<pre>\n";
    var_dump($roles);
    echo "</pre>\n";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
