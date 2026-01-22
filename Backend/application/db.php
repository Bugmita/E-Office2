<?php


function getPDO(): PDO{
    static $pdo = null;
    if($pdo) return $pdo;

    
    $user = "root";
    $pass ="Alokesh@1";

    $sourceName = "mysql:host=localhost; port =3306; db =college_auth; charset =utf8mb4";

    // establish db connection
    $pdo = new PDO($sourceName, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    return $pdo;

}

?>