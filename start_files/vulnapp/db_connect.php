<?php
// ============================================================
//  VulnApp — db_connect.php
//  NAMJERNO RANJIVA konekcija — za edukativne svrhe
//  Problem: greška otkriva detalje baze korisniku
// ============================================================

$dsn        = 'mysql:host=localhost;dbname=webseclab;charset=utf8mb4';
$dbusername = 'root';
$dbpassword = '';

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // RANJIVOST: prikazujemo punu grešku korisniku
    // Otkriva: ime baze, korisnika, host, strukturu servera
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
