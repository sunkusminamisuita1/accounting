<?php 
//$dsn = 'mysql:host=localhost;dbname=accounting;charset=utf8mb4';
//$user = 'test'; 
//$pass = '@C6jwqknm'; 
//$options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
//			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ];
//$pdo = new PDO($dsn, $user, $pass, $options);
//echo "db.php";
function getPDO(): PDO
{

    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=accounting;charset=utf8mb4';
        $user = 'test';
        $password = '@C6jwqknm';

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }


    return $pdo;
}
?>
