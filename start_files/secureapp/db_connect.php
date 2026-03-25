<?php
$dsn = 'mysql:host=localhost;dbname=webseclab;charset=utf8mb4';
$dbusername = 'root';
$dbpassword = '';

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // prave prepared statements
    ]);
} catch (PDOException $e) {
    error_log('DB konekcija neuspješna: ' . $e->getMessage()); // piše u PHP error log
    http_response_code(500);
    die('Greška pri spajanju na bazu. Pokušajte kasnije.');
}